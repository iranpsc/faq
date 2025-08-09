<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class QuestionWithCommentsAndAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create some users if they don't exist
            $users = User::factory(10)->create([
                'level' => rand(1, 5),
                'score' => rand(100, 5000),
            ]);

            // Get or create categories
            $categories = Category::factory(5)->create();

            // Create some tags
            $tags = Tag::factory(15)->create();

            // Create questions with answers and comments
            for ($i = 1; $i <= 5; $i++) {
                $title = "سوال تست شماره $i - آیا امکان پیاده‌سازی سیستم صفحه‌بندی در کامنت‌ها وجود دارد؟";
                $slug = str_replace(' ', '-', $title);

                $question = Question::factory()->create([
                    'user_id' => $users->random()->id,
                    'category_id' => $categories->random()->id,
                    'title' => $title,
                    'slug' => $slug,
                    'content' => "این یک سوال تست برای بررسی قابلیت صفحه‌بندی در سیستم کامنت‌ها و پاسخ‌ها است. لطفا پاسخ‌های متنوع و کامنت‌های مختلف ارائه دهید تا بتوانیم عملکرد سیستم را بررسی کنیم.\n\nدر این سوال قصد داریم موارد زیر را بررسی کنیم:\n- صفحه‌بندی کامنت‌ها\n- صفحه‌بندی پاسخ‌ها\n- عملکرد دکمه نمایش بیشتر\n- سرعت بارگذاری\n\nآیا کسی تجربه‌ای در این زمینه دارد؟",
                    'published' => true,
                    'published_at' => now(),
                ]);

                // Attach random tags to the question
                $question->tags()->attach($tags->random(rand(2, 5))->pluck('id'));

                // Create 15-25 comments for each question
                $commentCount = rand(15, 25);
                for ($j = 1; $j <= $commentCount; $j++) {
                    Comment::factory()->create([
                        'user_id' => $users->random()->id,
                        'commentable_type' => Question::class,
                        'commentable_id' => $question->id,
                        'content' => "این کامنت شماره $j برای سوال $i است. " . fake()->paragraph(rand(1, 3)),
                        'published' => true,
                        'published_at' => now()->subDays(rand(0, 30)),
                    ]);
                }

                // Create 12-20 answers for each question
                $answerCount = rand(12, 20);
                for ($k = 1; $k <= $answerCount; $k++) {
                    $answer = Answer::factory()->create([
                        'user_id' => $users->random()->id,
                        'question_id' => $question->id,
                        'content' => "<h3>پاسخ شماره $k</h3><p>این پاسخ شماره $k برای سوال $i است.</p><p>" . fake()->paragraph(rand(2, 4)) . "</p><p>برای بررسی صفحه‌بندی، محتوای بیشتری اضافه می‌کنیم:</p><ul><li>نکته اول: " . fake()->sentence() . "</li><li>نکته دوم: " . fake()->sentence() . "</li><li>نکته سوم: " . fake()->sentence() . "</li></ul>",
                        'published' => true,
                        'published_at' => now()->subDays(rand(0, 30)),
                        'is_correct' => $k <= 2 ? rand(0, 1) == 1 : false, // First 2 answers might be correct
                    ]);

                    // Create 8-15 comments for each answer
                    $answerCommentCount = rand(8, 15);
                    for ($l = 1; $l <= $answerCommentCount; $l++) {
                        Comment::factory()->create([
                            'user_id' => $users->random()->id,
                            'commentable_type' => Answer::class,
                            'commentable_id' => $answer->id,
                            'content' => "کامنت شماره $l برای پاسخ $k از سوال $i. " . fake()->paragraph(rand(1, 2)),
                            'published' => true,
                            'published_at' => now()->subDays(rand(0, 30)),
                        ]);
                    }
                }

                $this->command->info("Created question $i with $commentCount comments and $answerCount answers");
            }

            $this->command->info('Successfully created 5 questions with comments and answers for testing pagination');
        });
    }
}
