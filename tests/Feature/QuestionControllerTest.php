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

        // Run migrations
        $this->artisan('migrate');
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
            'title' => 'Test Question'
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
}
