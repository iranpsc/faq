<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_comments_for_a_question()
    {
        $question = Question::factory()->create();
        Comment::factory()->count(3)->for($question, 'commentable')->create(['published' => true]);

        $response = $this->getJson("/api/questions/{$question->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_list_comments_for_an_answer()
    {
        $answer = Answer::factory()->create();
        Comment::factory()->count(3)->for($answer, 'commentable')->create(['published' => true]);

        $response = $this->getJson("/api/answers/{$answer->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_comment_on_a_question()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();
        Sanctum::actingAs($user);

        $commentData = ['content' => 'This is a comment on a question.'];
        $response = $this->postJson("/api/questions/{$question->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJson(['data' => ['content' => $commentData['content']]]);

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $question->id,
            'commentable_type' => Question::class,
            'user_id' => $user->id,
            'content' => $commentData['content'],
        ]);
    }

    public function test_authenticated_user_can_comment_on_an_answer()
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();
        Sanctum::actingAs($user);

        $commentData = ['content' => 'This is a comment on an answer.'];
        $response = $this->postJson("/api/answers/{$answer->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJson(['data' => ['content' => $commentData['content']]]);

        $this->assertDatabaseHas('comments', [
            'commentable_id' => $answer->id,
            'commentable_type' => Answer::class,
            'user_id' => $user->id,
            'content' => $commentData['content'],
        ]);
    }

    public function test_guest_cannot_create_comment()
    {
        $question = Question::factory()->create();
        $commentData = ['content' => 'A guest comment.'];

        $response = $this->postJson("/api/questions/{$question->id}/comments", $commentData);

        $response->assertStatus(401);
    }

    public function test_user_can_update_own_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $updateData = ['content' => 'Updated comment content.'];
        $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['data' => ['content' => $updateData['content']]]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $updateData['content'],
        ]);
    }

    public function test_user_cannot_update_others_comment()
    {
        $commentOwner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $commentOwner->id]);
        Sanctum::actingAs($anotherUser);

        $updateData = ['content' => 'Trying to update others comment.'];
        $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_any_comment()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_others_comment()
    {
        $commentOwner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $commentOwner->id]);
        Sanctum::actingAs($anotherUser);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_create_comment_validation_fails_without_content()
    {
        $user = User::factory()->create();
        $question = Question::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/questions/{$question->id}/comments", []);

        $response->assertStatus(422)->assertJsonValidationErrors(['content']);
    }
}
