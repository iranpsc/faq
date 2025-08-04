<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use App\Models\Category;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_questions_with_sort_and_order_parameters()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Create test questions with different attributes
        $oldQuestion = Question::factory()->create([
            'title' => 'Old Question',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
            'created_at' => now()->subDays(5),
            'views' => 10,
        ]);

        $newQuestion = Question::factory()->create([
            'title' => 'New Question',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
            'created_at' => now()->subDays(1),
            'views' => 20,
        ]);

        // Create votes for questions
        $oldQuestion->votes()->create(['user_id' => $user->id, 'type' => 'up']);
        $oldQuestion->votes()->create(['user_id' => User::factory()->create()->id, 'type' => 'up']);
        $newQuestion->votes()->create(['user_id' => User::factory()->create()->id, 'type' => 'up']);

        // Create answers
        Answer::factory()->create(['question_id' => $oldQuestion->id, 'user_id' => $user->id, 'published' => true]);
        Answer::factory()->create(['question_id' => $oldQuestion->id, 'user_id' => User::factory()->create()->id, 'published' => true]);

        // Test sort by created_at desc (newest first)
        $response = $this->getJson('/api/questions?sort=created_at&order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($newQuestion->id, $data[0]['id']);
        $this->assertEquals($oldQuestion->id, $data[1]['id']);

        // Test sort by created_at asc (oldest first)
        $response = $this->getJson('/api/questions?sort=created_at&order=asc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($oldQuestion->id, $data[0]['id']);
        $this->assertEquals($newQuestion->id, $data[1]['id']);

        // Test sort by votes desc (most votes first)
        $response = $this->getJson('/api/questions?sort=votes&order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($oldQuestion->id, $data[0]['id']); // Should have 2 votes
        $this->assertEquals($newQuestion->id, $data[1]['id']); // Should have 1 vote

        // Test sort by answers_count desc
        $response = $this->getJson('/api/questions?sort=answers_count&order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($oldQuestion->id, $data[0]['id']); // Should have 2 answers

        // Test sort by views_count desc
        $response = $this->getJson('/api/questions?sort=views_count&order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($newQuestion->id, $data[0]['id']); // Should have 20 views
        $this->assertEquals($oldQuestion->id, $data[1]['id']); // Should have 10 views
    }

    public function test_can_filter_questions_with_filter_parameter()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Create question with answers
        $questionWithAnswers = Question::factory()->create([
            'title' => 'Question with Answers',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
        ]);

        // Create question without answers
        $questionWithoutAnswers = Question::factory()->create([
            'title' => 'Question without Answers',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
        ]);

        // Create question with solved answer
        $questionWithSolvedAnswer = Question::factory()->create([
            'title' => 'Question with Solved Answer',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
        ]);

        // Add answers
        Answer::factory()->create([
            'question_id' => $questionWithAnswers->id,
            'user_id' => $user->id,
            'published' => true,
            'is_correct' => false,
        ]);

        Answer::factory()->create([
            'question_id' => $questionWithSolvedAnswer->id,
            'user_id' => $user->id,
            'published' => true,
            'is_correct' => true,
        ]);

        // Test filter=unanswered
        $response = $this->getJson('/api/questions?filter=unanswered');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($questionWithoutAnswers->id, $data[0]['id']);

        // Test filter=unsolved
        $response = $this->getJson('/api/questions?filter=unsolved');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Should include both the unanswered question and the one with incorrect answer
        $questionIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($questionWithoutAnswers->id, $questionIds);
        $this->assertContains($questionWithAnswers->id, $questionIds);
    }

    public function test_legacy_parameters_still_work()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $oldQuestion = Question::factory()->create([
            'title' => 'Old Question',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
            'created_at' => now()->subDays(5),
        ]);

        $newQuestion = Question::factory()->create([
            'title' => 'New Question',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'published' => true,
            'created_at' => now()->subDays(1),
        ]);

        // Test legacy newest parameter
        $response = $this->getJson('/api/questions?newest=1');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($newQuestion->id, $data[0]['id']);

        // Test legacy oldest parameter
        $response = $this->getJson('/api/questions?oldest=1');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($oldQuestion->id, $data[0]['id']);
    }
}
