<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ybazli\Faker\Facades\Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'title' => Faker::sentence(),
            'content' => Faker::paragraph(3, true),
            'pinned' => fake()->boolean(10), // 10% chance of being pinned
            'featured' => fake()->boolean(5), // 5% chance of being featured
            'last_activity' => fake()->dateTime(),
            'views' => fake()->numberBetween(0, 1000),
            'published' => true, // Default to published for tests
            'published_at' => now(), // Set to current time for tests
            'published_by' => User::factory(),
        ];
    }
}
