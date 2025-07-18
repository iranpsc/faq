<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commentable_type' => fake()->randomElement(['App\Models\Question', 'App\Models\Answer']),
            'commentable_id' => fn (array $attributes) => $attributes['commentable_type']::factory(),
            'content' => fake()->paragraph(),
            'published' => fake()->boolean(),
            'published_at' => fake()->dateTime(),
            'published_by' => User::factory(),
        ];
    }
}
