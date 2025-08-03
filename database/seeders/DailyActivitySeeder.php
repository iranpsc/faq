<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some users if they don't exist
        $users = User::factory(5)->create();

        // Create some questions for today
        $questions = Question::factory(3)->create([
            'user_id' => $users->random()->id,
            'created_at' => now(),
            'updated_at' => now(),
            'published_at' => now(),
            'published' => true
        ]);

        // Create some answers for today
        foreach ($questions as $question) {
            Answer::factory(2)->create([
                'user_id' => $users->random()->id,
                'question_id' => $question->id,
                'created_at' => now()->subMinutes(rand(10, 120)),
                'updated_at' => now()->subMinutes(rand(10, 120)),
                'published_at' => now()->subMinutes(rand(10, 120)),
                'published' => true
            ]);
        }

        // Create some comments for today
        $answers = Answer::where('created_at', '>=', today())->get();

        foreach ($questions as $question) {
            Comment::factory(2)->create([
                'user_id' => $users->random()->id,
                'commentable_type' => Question::class,
                'commentable_id' => $question->id,
                'created_at' => now()->subMinutes(rand(5, 90)),
                'updated_at' => now()->subMinutes(rand(5, 90)),
                'published_at' => now()->subMinutes(rand(5, 90)),
                'published' => true
            ]);
        }

        foreach ($answers as $answer) {
            Comment::factory(1)->create([
                'user_id' => $users->random()->id,
                'commentable_type' => Answer::class,
                'commentable_id' => $answer->id,
                'created_at' => now()->subMinutes(rand(5, 60)),
                'updated_at' => now()->subMinutes(rand(5, 60)),
                'published_at' => now()->subMinutes(rand(5, 60)),
                'published' => true
            ]);
        }
    }
}
