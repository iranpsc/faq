<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AnswerCorrectnessMark;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AnswerCorrectnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed some basic data
        $this->artisan('migrate:fresh');
    }

    #[Test]
    public function user_with_level_4_or_above_can_mark_answer_correctness()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'is_correct' => true
                ]);

        // Check database
        $this->assertDatabaseHas('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id,
            'is_correct' => true
        ]);

        // Check answer is marked as correct
        $this->assertTrue($answer->fresh()->is_correct);

        // Check points awarded
        $this->assertEquals(10, $answerOwner->fresh()->score); // Answer owner gets 10 points
        $this->assertEquals(2, $marker->fresh()->score); // Marker gets 2 points
    }

    #[Test]
    public function user_with_level_below_4_cannot_mark_answer_correctness()
    {
        $marker = User::factory()->create(['level' => 3, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'شما مجوز علامت‌گذاری این پاسخ را ندارید'
                ]);

        // Check database - no marks should be created
        $this->assertDatabaseMissing('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id
        ]);
    }

    #[Test]
    public function user_cannot_mark_their_own_answer()
    {
        $user = User::factory()->create(['level' => 5, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'is_correct' => false
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'شما مجوز علامت‌گذاری این پاسخ را ندارید'
                ]);
    }

    #[Test]
    public function user_can_toggle_their_existing_mark()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        // Create initial mark
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id,
            'is_correct' => true
        ]);
        $answer->update(['is_correct' => true]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'is_correct' => false
                ]);

        // Check the mark was toggled
        $this->assertDatabaseHas('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id,
            'is_correct' => false
        ]);

        $this->assertFalse($answer->fresh()->is_correct);
    }

    #[Test]
    public function user_quota_is_respected()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);

        // Create 4 answers and mark them (using up the quota)
        for ($i = 0; $i < 4; $i++) {
            $answerOwner = User::factory()->create(['level' => 2]);
            $answer = Answer::factory()->create([
                'question_id' => $question->id,
                'user_id' => $answerOwner->id,
                'is_correct' => false
            ]);

            AnswerCorrectnessMark::factory()->create([
                'answer_id' => $answer->id,
                'marker_user_id' => $marker->id,
                'is_correct' => true
            ]);
        }

        // Try to mark a 5th answer (should fail)
        $answerOwner = User::factory()->create(['level' => 2]);
        $fifthAnswer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$fifthAnswer->id}/toggle-correctness");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'شما مجوز علامت‌گذاری این پاسخ را ندارید'
                ]);
    }

    #[Test]
    public function higher_level_user_can_override_lower_level_mark()
    {
        $lowerLevelMarker = User::factory()->create(['level' => 4, 'score' => 0]);
        $higherLevelMarker = User::factory()->create(['level' => 6, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        // Lower level user marks as correct
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $lowerLevelMarker->id,
            'is_correct' => true
        ]);
        $answer->update(['is_correct' => true]);

        Sanctum::actingAs($higherLevelMarker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'is_correct' => true // Higher level user's mark (new mark defaults to true)
                ]);

        // Check both marks exist
        $this->assertDatabaseHas('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $lowerLevelMarker->id,
            'is_correct' => true
        ]);

        $this->assertDatabaseHas('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $higherLevelMarker->id,
            'is_correct' => true
        ]);
    }

    #[Test]
    public function lower_level_user_cannot_override_higher_level_mark()
    {
        $higherLevelMarker = User::factory()->create(['level' => 6, 'score' => 0]);
        $lowerLevelMarker = User::factory()->create(['level' => 4, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        // Higher level user marks first
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $higherLevelMarker->id,
            'is_correct' => true
        ]);
        $answer->update(['is_correct' => true]);

        Sanctum::actingAs($lowerLevelMarker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'شما مجوز علامت‌گذاری این پاسخ را ندارید'
                ]);

        // Check only higher level mark exists
        $this->assertDatabaseHas('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $higherLevelMarker->id,
            'is_correct' => true
        ]);

        $this->assertDatabaseMissing('answer_correctness_marks', [
            'answer_id' => $answer->id,
            'marker_user_id' => $lowerLevelMarker->id
        ]);
    }

    #[Test]
    public function marking_answer_as_correct_awards_points_correctly()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 10]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 20]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(200);

        // Check points awarded
        $this->assertEquals(30, $answerOwner->fresh()->score); // 20 + 10
        $this->assertEquals(12, $marker->fresh()->score); // 10 + 2
    }

    #[Test]
    public function unmarking_answer_as_correct_deducts_points_correctly()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 10]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 30]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => true
        ]);

        // Create existing correct mark
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $marker->id,
            'is_correct' => true
        ]);

        Sanctum::actingAs($marker);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(200);

        // Check points deducted
        $this->assertEquals(20, $answerOwner->fresh()->score); // 30 - 10
        $this->assertEquals(12, $marker->fresh()->score); // 10 + 2 (marker still gets points for the action)
    }

    #[Test]
    public function question_is_marked_as_solved_when_answer_is_correct()
    {
        $marker = User::factory()->create(['level' => 4, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        $this->assertFalse($question->isSolved());

        Sanctum::actingAs($marker);

        $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $this->assertTrue($question->fresh()->isSolved());
    }

    #[Test]
    public function unauthenticated_user_cannot_mark_answer_correctness()
    {
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        $response = $this->postJson("/api/answers/{$answer->id}/toggle-correctness");

        $response->assertStatus(401);
    }

    #[Test]
    public function highest_level_mark_takes_precedence()
    {
        $level4User = User::factory()->create(['level' => 4, 'score' => 0]);
        $level5User = User::factory()->create(['level' => 5, 'score' => 0]);
        $level6User = User::factory()->create(['level' => 6, 'score' => 0]);
        $answerOwner = User::factory()->create(['level' => 2, 'score' => 0]);
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $answerOwner->id,
            'is_correct' => false
        ]);

        // Level 4 user marks as correct
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $level4User->id,
            'is_correct' => true
        ]);

        // Level 5 user marks as incorrect
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $level5User->id,
            'is_correct' => false
        ]);

        // Level 6 user marks as correct (should take precedence)
        AnswerCorrectnessMark::factory()->create([
            'answer_id' => $answer->id,
            'marker_user_id' => $level6User->id,
            'is_correct' => true
        ]);

        // Simulate the correctness status update
        $this->artisan('tinker', ['--execute' => "
            use App\Models\Answer;
            \$answer = Answer::find({$answer->id});
            \$marks = \$answer->correctnessMarks()
                ->join('users', 'users.id', '=', 'answer_correctness_marks.marker_user_id')
                ->orderBy('users.level', 'desc')
                ->select('answer_correctness_marks.*')
                ->get();
            \$highestLevelMark = \$marks->first();
            \$answer->update(['is_correct' => \$highestLevelMark->is_correct]);
        "]);

        $this->assertTrue($answer->fresh()->is_correct); // Level 6 user's mark (correct) should win
    }
}
