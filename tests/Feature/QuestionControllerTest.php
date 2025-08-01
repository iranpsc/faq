<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuestionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_questions()
    {
        // Create test data
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
        $question->tags()->attach($tags->pluck('id'));

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'created_at',
                            'user',
                            'category',
                            'tags',
                            'can' => [
                                'view',
                                'update',
                                'delete'
                            ]
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    public function test_can_show_single_question()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => true,
        ]);
        $question->tags()->attach($tags->pluck('id'));

        // Create answers and comments
        $answer = Answer::factory()->create(['question_id' => $question->id]);
        $comment = Comment::factory()->create([
            'commentable_type' => Question::class,
            'commentable_id' => $question->id
        ]);

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'created_at',
                        'user',
                        'category',
                        'tags',
                        'answers',
                        'comments',
                        'can'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $question->id,
                        'title' => $question->title,
                        'content' => $question->content,
                    ]
                ]);
    }

    public function test_authenticated_user_can_create_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'created_at',
                        'user',
                        'category',
                        'tags'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'title' => $questionData['title'],
                        'content' => $questionData['content'],
                    ]
                ]);

        $this->assertDatabaseHas('questions', [
            'title' => $questionData['title'],
            'content' => $questionData['content'],
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $question = Question::where('title', $questionData['title'])->first();
        $this->assertEquals(2, $question->tags()->count());
    }

    public function test_guest_cannot_create_question()
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(401);
    }

    public function test_create_question_validation_fails_without_required_fields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/questions', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id', 'title', 'content', 'tags']);
    }

    public function test_create_question_validation_fails_with_invalid_category()
    {
        $user = User::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => 999999, // Non-existent category
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id']);
    }

    public function test_create_question_validation_fails_with_invalid_tags()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => [['id' => 999999]] // Non-existent tag
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['tags.0.id']);
    }

    public function test_question_owner_can_update_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(3)->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
        $question->tags()->attach($tags->pluck('id'));

        Sanctum::actingAs($user);

        $updateData = [
            'category_id' => $newCategory->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'tags' => $newTags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $question->id,
                        'title' => 'Updated Title',
                        'content' => 'Updated content',
                    ]
                ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'category_id' => $newCategory->id,
        ]);

        $question->refresh();
        $this->assertEquals(3, $question->tags()->count());
    }

    public function test_update_question_validation_fails_without_all_required_fields()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Original Title',
            'content' => 'Original content',
        ]);
        $question->tags()->attach($tags->pluck('id'));

        Sanctum::actingAs($user);

        // Test with only title (missing other required fields)
        $updateData = [
            'title' => 'Updated Title Only',
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id', 'content', 'tags']);

        // Test with empty data
        $response = $this->putJson("/api/questions/{$question->id}", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id', 'title', 'content', 'tags']);
    }

    public function test_non_owner_cannot_update_question()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $question = Question::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($otherUser);

        $updateData = [
            'category_id' => $category->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_update_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $updateData = [
            'category_id' => $category->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(401);
    }

    public function test_question_owner_can_delete_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_non_owner_cannot_delete_question()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_guest_cannot_delete_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(401);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_questions_are_returned_with_proper_relationships()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $user = User::factory()->create(['name' => 'Test User']);
        $tags = Tag::factory()->count(2)->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Test Question',
            'published' => true,
        ]);
        $question->tags()->attach($tags->pluck('id'));

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $question->id,
                        'title' => 'Test Question',
                        'user' => [
                            'name' => 'Test User'
                        ],
                        'category' => [
                            'name' => 'Test Category'
                        ]
                    ]
                ]);

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData['tags']);
    }

    public function test_questions_list_is_paginated()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        // Create more questions than the default pagination limit
        Question::factory()->count(20)->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'links' => [
                        'first',
                        'last',
                        'prev',
                        'next'
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);

        $this->assertEquals(20, $response->json('meta.total'));
    }

    public function test_update_question_with_empty_tags_array_removes_all_tags()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $question = Question::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Test Title',
            'content' => 'Test Content',
        ]);
        $question->tags()->attach($tags->pluck('id'));

        $this->assertEquals(3, $question->tags()->count());

        Sanctum::actingAs($user);

        $updateData = [
            'category_id' => $category->id,
            'title' => 'Test Title',
            'content' => 'Test Content',
            'tags' => []
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(200);

        $question->refresh();
        $this->assertEquals(0, $question->tags()->count());
    }

    public function test_404_error_for_nonexistent_question()
    {
        $response = $this->getJson('/api/questions/999999');
        $response->assertStatus(404);

        $user = User::factory()->create();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $updateData = [
            'category_id' => $category->id,
            'title' => 'Test Title',
            'content' => 'Test Content',
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->putJson('/api/questions/999999', $updateData);
        $response->assertStatus(404);

        $response = $this->deleteJson('/api/questions/999999');
        $response->assertStatus(404);
    }

    public function test_can_search_questions_by_title()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $question1 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Laravel PHP Framework',
            'published' => true,
        ]);

        $question2 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Vue.js JavaScript Framework',
            'published' => true,
        ]);

        $question3 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'React Framework',
            'published' => true,
        ]);

        // Search for "Laravel"
        $response = $this->getJson('/api/questions/search?q=Laravel');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'content',
                            'user',
                            'category'
                        ]
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'جستجو با موفقیت انجام شد'
                ]);

        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals($question1->id, $responseData[0]['id']);
    }

    public function test_search_returns_empty_when_no_matches()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Laravel PHP Framework',
            'published' => true,
        ]);

        $response = $this->getJson('/api/questions/search?q=nonexistent');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [],
                    'message' => 'جستجو با موفقیت انجام شد'
                ]);
    }

    public function test_search_respects_limit_parameter()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        // Create 5 questions with "Framework" in title
        for ($i = 1; $i <= 5; $i++) {
            Question::factory()->create([
                'category_id' => $category->id,
                'user_id' => $user->id,
                'title' => "Framework Example {$i}",
                'published' => true,
            ]);
        }

        $response = $this->getJson('/api/questions/search?q=Framework&limit=3');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(3, $responseData);
    }

    public function test_search_only_returns_published_questions()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $publishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Published Laravel Question',
            'published' => true,
        ]);

        $unpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Unpublished Laravel Question',
            'published' => false,
        ]);

        $response = $this->getJson('/api/questions/search?q=Laravel');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals($publishedQuestion->id, $responseData[0]['id']);
    }

    public function test_can_filter_questions_by_category()
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);
        $user = User::factory()->create();

        $question1 = Question::factory()->create([
            'category_id' => $category1->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        $question2 = Question::factory()->create([
            'category_id' => $category2->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        $response = $this->getJson("/api/questions?category_id={$category1->id}");

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals($question1->id, $responseData[0]['id']);
    }

    public function test_authorized_user_can_publish_question()
    {
        $lowLevelUser = User::factory()->create(['level' => 1, 'score' => 0]); // Lower level user
        $highLevelUser = User::factory()->create(['level' => 3, 'score' => 0]); // Higher level user
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $lowLevelUser->id,
            'published' => false,
        ]);

        // Higher level user can publish lower level user's question
        Sanctum::actingAs($highLevelUser);

        $response = $this->postJson("/api/questions/{$question->id}/publish");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'content',
                        'published',
                        'published_at',
                        'published_by'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'سوال با موفقیت منتشر شد'
                ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'published' => true,
            'published_by' => $highLevelUser->id,
        ]);

        // Check that the publishing user's score was incremented
        $highLevelUser->refresh();
        $this->assertEquals(2, $highLevelUser->score);
    }

    public function test_guest_cannot_publish_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => false,
        ]);

        $response = $this->postJson("/api/questions/{$question->id}/publish");

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_publish_question()
    {
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $sameLevelUser = User::factory()->create(['level' => 1]);
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $lowLevelUser->id,
            'published' => false,
        ]);

        // Same level user cannot publish
        Sanctum::actingAs($sameLevelUser);

        $response = $this->postJson("/api/questions/{$question->id}/publish");

        $response->assertStatus(403);
    }

    public function test_user_can_upvote_question()
    {
        $questionOwner = User::factory()->create(['score' => 0]);
        $voter = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $questionOwner->id,
        ]);

        Sanctum::actingAs($voter);

        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'up'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'upvotes',
                    'downvotes',
                    'user_vote'
                ])
                ->assertJson([
                    'upvotes' => 1,
                    'downvotes' => 0,
                    'user_vote' => 'up'
                ]);

        // Check that vote was created
        $this->assertDatabaseHas('votes', [
            'votable_type' => Question::class,
            'votable_id' => $question->id,
            'user_id' => $voter->id,
            'type' => 'up'
        ]);

        // Check that question owner's score was incremented
        $questionOwner->refresh();
        $this->assertEquals(10, $questionOwner->score);
    }

    public function test_user_can_downvote_question()
    {
        $questionOwner = User::factory()->create(['score' => 10]);
        $voter = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $questionOwner->id,
        ]);

        Sanctum::actingAs($voter);

        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'down'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'upvotes' => 0,
                    'downvotes' => 1,
                    'user_vote' => 'down'
                ]);

        // Check that vote was created
        $this->assertDatabaseHas('votes', [
            'votable_type' => Question::class,
            'votable_id' => $question->id,
            'user_id' => $voter->id,
            'type' => 'down'
        ]);

        // Check that question owner's score was decremented
        $questionOwner->refresh();
        $this->assertEquals(8, $questionOwner->score);
    }

    public function test_user_can_toggle_vote_off()
    {
        $questionOwner = User::factory()->create(['score' => 0]);
        $voter = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $questionOwner->id,
        ]);

        // First vote
        $question->votes()->create([
            'user_id' => $voter->id,
            'type' => 'up'
        ]);

        Sanctum::actingAs($voter);

        // Vote again with same type to toggle off
        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'up'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'upvotes' => 0,
                    'downvotes' => 0,
                    'user_vote' => null
                ]);

        // Check that vote was removed
        $this->assertDatabaseMissing('votes', [
            'votable_type' => Question::class,
            'votable_id' => $question->id,
            'user_id' => $voter->id,
        ]);
    }

    public function test_user_can_change_vote_type()
    {
        $questionOwner = User::factory()->create(['score' => 0]);
        $voter = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $questionOwner->id,
        ]);

        // First vote (upvote)
        $question->votes()->create([
            'user_id' => $voter->id,
            'type' => 'up'
        ]);

        Sanctum::actingAs($voter);

        // Change to downvote
        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'down'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'upvotes' => 0,
                    'downvotes' => 1,
                    'user_vote' => 'down'
                ]);

        // Check that vote was updated
        $this->assertDatabaseHas('votes', [
            'votable_type' => Question::class,
            'votable_id' => $question->id,
            'user_id' => $voter->id,
            'type' => 'down'
        ]);
    }

    public function test_guest_cannot_vote_on_question()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'up'
        ]);

        $response->assertStatus(401);
    }

    public function test_vote_validation_fails_with_invalid_type()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/questions/{$question->id}/vote", [
            'type' => 'invalid'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    public function test_question_increments_views_on_show()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'views' => 0,
            'published' => true,
        ]);

        $initialViews = $question->views;

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200);

        $question->refresh();
        $this->assertEquals($initialViews + 1, $question->views);
    }

    public function test_auto_publish_for_high_level_users()
    {
        $user = User::factory()->create(['level' => 3, 'score' => 0]); // User with level >= 2
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201);

        // Check that question was auto-published
        $question = Question::where('title', $questionData['title'])->first();
        $this->assertTrue($question->published);
        $this->assertNotNull($question->published_at);
        $this->assertEquals($user->id, $question->published_by);

        // Check that user score was incremented
        $user->refresh();
        $this->assertEquals(2, $user->score);
    }

    public function test_no_auto_publish_for_low_level_users()
    {
        $user = User::factory()->create(['level' => 1, 'score' => 0]); // User with level < 2
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray()
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201);

        // Check that question was NOT auto-published
        $question = Question::where('title', $questionData['title'])->first();
        $this->assertFalse($question->published);
        $this->assertNull($question->published_at);
        $this->assertNull($question->published_by);

        // Check that user score was NOT incremented
        $user->refresh();
        $this->assertEquals(0, $user->score);
    }

    public function test_question_with_answers_loads_properly()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        // Create answers
        $answers = Answer::factory()->count(3)->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'answers' => [
                            '*' => [
                                'id',
                                'content',
                                'user',
                                'votes'
                            ]
                        ]
                    ]
                ]);

        $responseData = $response->json('data');
        $this->assertCount(3, $responseData['answers']);
    }

    public function test_visible_scope_shows_published_questions_to_guests()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $publishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        $unpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => false,
        ]);

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200);
        $responseData = $response->json('data');

        $this->assertCount(1, $responseData);
        $this->assertEquals($publishedQuestion->id, $responseData[0]['id']);
    }

    public function test_visible_scope_shows_own_unpublished_questions_to_authenticated_users()
    {
        $user = User::factory()->create(['level' => 2]);
        $otherUser = User::factory()->create(['level' => 2]);
        $category = Category::factory()->create();

        $ownUnpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => false,
        ]);

        $otherUnpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $otherUser->id,
            'published' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200);
        $responseData = $response->json('data');

        // Should only see own unpublished question
        $this->assertCount(1, $responseData);
        $this->assertEquals($ownUnpublishedQuestion->id, $responseData[0]['id']);
    }

    public function test_higher_level_user_can_see_lower_level_unpublished_questions()
    {
        $highLevelUser = User::factory()->create(['level' => 3]);
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $category = Category::factory()->create();

        $lowLevelUnpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $lowLevelUser->id,
            'published' => false,
        ]);

        Sanctum::actingAs($highLevelUser);

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200);
        $responseData = $response->json('data');

        $this->assertCount(1, $responseData);
        $this->assertEquals($lowLevelUnpublishedQuestion->id, $responseData[0]['id']);
    }

    public function test_question_view_policy_allows_owner_to_view_unpublished()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $unpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/questions/{$unpublishedQuestion->id}");

        $response->assertStatus(200);
    }

    public function test_question_view_policy_blocks_non_owner_from_unpublished()
    {
        $owner = User::factory()->create(['level' => 1]);
        $otherUser = User::factory()->create(['level' => 1]);
        $category = Category::factory()->create();

        $unpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $owner->id,
            'published' => false,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/questions/{$unpublishedQuestion->id}");

        $response->assertStatus(403);
    }

    public function test_higher_level_user_can_view_lower_level_unpublished_question()
    {
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $highLevelUser = User::factory()->create(['level' => 3]);
        $category = Category::factory()->create();

        $unpublishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $lowLevelUser->id,
            'published' => false,
        ]);

        Sanctum::actingAs($highLevelUser);

        $response = $this->getJson("/api/questions/{$unpublishedQuestion->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_publish_already_published_question()
    {
        $user = User::factory()->create(['level' => 3]);
        $lowLevelUser = User::factory()->create(['level' => 1]);
        $category = Category::factory()->create();

        $publishedQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $lowLevelUser->id,
            'published' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/questions/{$publishedQuestion->id}/publish");

        $response->assertStatus(403);
    }

    public function test_cannot_publish_question_from_same_or_higher_level_user()
    {
        $user = User::factory()->create(['level' => 2]);
        $sameLevelUser = User::factory()->create(['level' => 2]);
        $higherLevelUser = User::factory()->create(['level' => 3]);
        $category = Category::factory()->create();

        $sameLevelQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $sameLevelUser->id,
            'published' => false,
        ]);

        $higherLevelQuestion = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $higherLevelUser->id,
            'published' => false,
        ]);

        Sanctum::actingAs($user);

        // Cannot publish same level user's question
        $response = $this->postJson("/api/questions/{$sameLevelQuestion->id}/publish");
        $response->assertStatus(403);

        // Cannot publish higher level user's question
        $response = $this->postJson("/api/questions/{$higherLevelQuestion->id}/publish");
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_question()
    {
        $admin = User::factory()->create(['level' => 5, 'role' => 'admin']);
        $user = User::factory()->create(['level' => 1]);
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_question_is_solved_method()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // Question is not solved initially
        $this->assertFalse($question->isSolved());

        // Add a correct answer
        Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'is_correct' => true,
        ]);

        // Question should now be solved
        $this->assertTrue($question->fresh()->isSolved());
    }

    public function test_question_is_solved_with_best_answer()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        // Add a best answer
        Answer::factory()->create([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'is_best' => true,
        ]);

        // Question should be solved
        $this->assertTrue($question->fresh()->isSolved());
    }

    public function test_question_loads_votes_count_properly()
    {
        $user = User::factory()->create();
        $voter1 = User::factory()->create();
        $voter2 = User::factory()->create();
        $voter3 = User::factory()->create();
        $category = Category::factory()->create();

        $question = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'published' => true,
        ]);

        // Add votes
        $question->votes()->create(['user_id' => $voter1->id, 'type' => 'up']);
        $question->votes()->create(['user_id' => $voter2->id, 'type' => 'up']);
        $question->votes()->create(['user_id' => $voter3->id, 'type' => 'down']);

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200);
        $responseData = $response->json('data');

        $this->assertEquals(2, count($responseData['votes']['upvotes']));
        $this->assertEquals(1, count($responseData['votes']['downvotes']));
    }

    public function test_create_question_with_pinned_and_featured_fields()
    {
        $user = User::factory()->create(['level' => 3]);
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $questionData = [
            'category_id' => $category->id,
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'tags' => $tags->map(fn($tag) => ['id' => $tag->id])->toArray(),
            'pinned' => true,
            'featured' => true,
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201);

        // Check defaults are applied correctly (these fields shouldn't be mass assignable from user input)
        $question = Question::where('title', $questionData['title'])->first();
        $this->assertFalse($question->pinned); // Should be false due to default
        $this->assertFalse($question->featured); // Should be false due to default
    }

    public function test_search_orders_by_views_and_created_at()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        // Create questions with different views and dates
        $question1 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Framework Question 1',
            'published' => true,
            'views' => 10,
            'created_at' => now()->subDays(2),
        ]);

        $question2 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Framework Question 2',
            'published' => true,
            'views' => 20,
            'created_at' => now()->subDays(1),
        ]);

        $question3 = Question::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Framework Question 3',
            'published' => true,
            'views' => 15,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/questions/search?q=Framework');

        $response->assertStatus(200);
        $responseData = $response->json('data');

        // Should be ordered by views DESC, then created_at DESC
        $this->assertEquals($question2->id, $responseData[0]['id']); // 20 views
        $this->assertEquals($question3->id, $responseData[1]['id']); // 15 views, newest
        $this->assertEquals($question1->id, $responseData[2]['id']); // 10 views
    }
}
