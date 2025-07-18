<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'pinned' => fake()->boolean(),
            'featured' => fake()->boolean(),
            'last_activity' => fake()->dateTime(),
            'views' => fake()->numberBetween(0, 1000),
            'published' => fake()->boolean(),
            'published_at' => fake()->dateTime(),
            'published_by' => User::factory(),
        ];
    }
}
