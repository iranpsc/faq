<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnswerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_answers_for_a_question()
    {
        $question = Question::factory()->create();
        Answer::factory()->count(5)->create(['question_id' => $question->id, 'published' => true]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson("/api/questions/{$question->id}/answers");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'content', 'user', 'votes']
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_guest_can_only_see_published_answers()
    {
        $question = Question::factory()->create();
        Answer::factory()->create(['question_id' => $question->id, 'published' => true]);
        Answer::factory()->create(['question_id' => $question->id, 'published' => false]);

        $response = $this->getJson("/api/questions/{$question->id}/answers");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_see_own_unpublished_answers()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();
        Answer::factory()->create(['question_id' => $question->id, 'user_id' => $user->id, 'published' => false]);
        Answer::factory()->create(['question_id' => $question->id, 'published' => true]);

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson("/api/questions/{$question->id}/answers");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_create_answer()
    {
        $user = User::factory()->create(['score' => 0]);
        $question = Question::factory()->create();

        Sanctum::actingAs($user);

        $answerData = [
            'content' => $this->faker->paragraph,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/questions/{$question->id}/answers", $answerData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'content' => $answerData['content'],
                    'published' => false
                ]
            ]);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'user_id' => $user->id,
            'content' => $answerData['content'],
        ]);

        $user->refresh();
        $this->assertEquals(5, $user->score);
    }

    public function test_guest_cannot_create_answer()
    {
        $question = Question::factory()->create();
        $answerData = ['content' => 'Some content'];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/questions/{$question->id}/answers", $answerData);

        $response->assertStatus(401);
    }

    public function test_create_answer_validation_fails_without_content()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/questions/{$question->id}/answers", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_answer_owner_can_update_answer()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $updateData = ['content' => 'Updated content'];
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/answers/{$answer->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['data' => ['content' => 'Updated content']]);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'content' => 'Updated content'
        ]);
    }

    public function test_non_owner_cannot_update_answer()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $updateData = ['content' => 'Updated content'];
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/answers/{$answer->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_answer_owner_can_delete_answer()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_admin_can_delete_any_answer()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_non_owner_cannot_delete_answer()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(403);
    }

    public function test_higher_level_user_can_publish_answer()
    {
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $highLevelUser = User::factory()->create(['level' => 3, 'score' => 0]);
        $answer = Answer::factory()->create(['user_id' => $lowLevelUser->id, 'published' => false]);

        Sanctum::actingAs($highLevelUser);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/publish");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'published' => true,
            'published_by' => $highLevelUser->id,
        ]);

        $highLevelUser->refresh();
        $this->assertEquals(3, $highLevelUser->score);
    }

    public function test_user_cannot_publish_own_answer()
    {
        $user = User::factory()->create(['level' => 2]);
        $answer = Answer::factory()->create(['user_id' => $user->id, 'published' => false]);

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/publish");

        $response->assertStatus(403);
    }

    public function test_cannot_publish_already_published_answer()
    {
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $highLevelUser = User::factory()->create(['level' => 3]);
        $answer = Answer::factory()->create(['user_id' => $lowLevelUser->id, 'published' => true]);

        Sanctum::actingAs($highLevelUser);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/publish");

        $response->assertStatus(403);
    }

    public function test_user_can_upvote_answer()
    {
        $answerOwner = User::factory()->create(['score' => 0]);
        $voter = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id]);

        Sanctum::actingAs($voter);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/vote", ['type' => 'up']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('votes', [
            'votable_id' => $answer->id,
            'votable_type' => Answer::class,
            'user_id' => $voter->id,
            'type' => 'up'
        ]);

        $answerOwner->refresh();
        $this->assertEquals(10, $answerOwner->score);
    }

    public function test_user_can_downvote_answer()
    {
        $answerOwner = User::factory()->create(['score' => 10]);
        $voter = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $answerOwner->id]);

        Sanctum::actingAs($voter);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/vote", ['type' => 'down']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('votes', [
            'votable_id' => $answer->id,
            'votable_type' => Answer::class,
            'user_id' => $voter->id,
            'type' => 'down'
        ]);

        $answerOwner->refresh();
        $this->assertEquals(8, $answerOwner->score);
    }

    public function test_user_cannot_vote_more_than_once_on_answer()
    {
        $voter = User::factory()->create();
        $answer = Answer::factory()->create();
        $answer->votes()->create(['user_id' => $voter->id, 'type' => 'up']);

        Sanctum::actingAs($voter);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/vote", ['type' => 'up']);

        $response->assertStatus(409);
        $this->assertDatabaseHas('votes', [
            'votable_id' => $answer->id,
            'user_id' => $voter->id,
            'type' => 'up'
        ]);
    }

    public function test_user_cannot_change_vote_on_answer()
    {
        $voter = User::factory()->create();
        $answer = Answer::factory()->create();
        $answer->votes()->create(['user_id' => $voter->id, 'type' => 'up']);

        Sanctum::actingAs($voter);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/vote", ['type' => 'down']);

        $response->assertStatus(409);
        $this->assertDatabaseHas('votes', [
            'votable_id' => $answer->id,
            'user_id' => $voter->id,
            'type' => 'up'
        ]);
    }

    public function test_vote_validation_fails_with_invalid_type()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson("/api/answers/{$answer->id}/vote", ['type' => 'invalid']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }
}
