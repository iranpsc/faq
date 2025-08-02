<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\AnswerCorrectnessMark;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Policies\AnswerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AnswerPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected AnswerPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AnswerPolicy();
    }

    #[Test]
    public function user_with_level_4_or_above_can_mark_correctness()
    {
        $marker = User::factory()->create(['level' => 4]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        $this->assertTrue($this->policy->markAsCorrect($marker, $answer));
        $this->assertTrue($this->policy->markAsIncorrect($marker, $answer));
    }

    #[Test]
    public function user_with_level_below_4_cannot_mark_correctness()
    {
        $marker = User::factory()->create(['level' => 3]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        $this->assertFalse($this->policy->markAsCorrect($marker, $answer));
        $this->assertFalse($this->policy->markAsIncorrect($marker, $answer));
    }

    #[Test]
    public function user_cannot_mark_their_own_answer()
    {
        $user = User::factory()->create(['level' => 5]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $user->id, 'question_id' => $question->id]);

        $this->assertFalse($this->policy->markAsCorrect($user, $answer));
        $this->assertFalse($this->policy->markAsIncorrect($user, $answer));
    }

    #[Test]
    public function user_can_change_their_existing_mark()
    {
        $marker = User::factory()->create(['level' => 4]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        // Create existing mark
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id,
            'is_correct' => true
        ]);

        $this->assertTrue($this->policy->markAsCorrect($marker, $answer));
        $this->assertTrue($this->policy->markAsIncorrect($marker, $answer));
    }

    #[Test]
    public function user_quota_is_respected_when_no_existing_mark()
    {
        $marker = User::factory()->create(['level' => 4]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);

        // Use up the quota (4 marks for level 4 user)
        for ($i = 0; $i < 4; $i++) {
            $answerOwner = User::factory()->create(['level' => 2]);
            $otherAnswer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);
            AnswerCorrectnessMark::factory()->create([
                'answer_id' => $otherAnswer->id,
                'marker_user_id' => $marker->id,
                'is_correct' => true
            ]);
        }

        // Try to mark a new answer (should fail)
        $answerOwner = User::factory()->create(['level' => 2]);
        $newAnswer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        $this->assertFalse($this->policy->markAsCorrect($marker, $newAnswer));
        $this->assertFalse($this->policy->markAsIncorrect($marker, $newAnswer));
    }

    #[Test]
    public function higher_level_user_can_mark_when_lower_level_mark_exists()
    {
        $lowerLevelMarker = User::factory()->create(['level' => 4]);
        $higherLevelMarker = User::factory()->create(['level' => 6]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        // Lower level user marks first
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $lowerLevelMarker->id,
            'is_correct' => true
        ]);

        $this->assertTrue($this->policy->markAsCorrect($higherLevelMarker, $answer));
        $this->assertTrue($this->policy->markAsIncorrect($higherLevelMarker, $answer));
    }

    #[Test]
    public function lower_level_user_cannot_mark_when_higher_level_mark_exists()
    {
        $higherLevelMarker = User::factory()->create(['level' => 6]);
        $lowerLevelMarker = User::factory()->create(['level' => 4]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        // Higher level user marks first
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $higherLevelMarker->id,
            'is_correct' => true
        ]);

        $this->assertFalse($this->policy->markAsCorrect($lowerLevelMarker, $answer));
        $this->assertFalse($this->policy->markAsIncorrect($lowerLevelMarker, $answer));
    }

    #[Test]
    public function equal_level_user_cannot_mark_when_mark_exists()
    {
        $firstMarker = User::factory()->create(['level' => 4]);
        $secondMarker = User::factory()->create(['level' => 4]);
        $answerOwner = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id, 'question_id' => $question->id]);

        // First user marks
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $firstMarker->id,
            'is_correct' => true
        ]);

        $this->assertFalse($this->policy->markAsCorrect($secondMarker, $answer));
        $this->assertFalse($this->policy->markAsIncorrect($secondMarker, $answer));
    }
}
