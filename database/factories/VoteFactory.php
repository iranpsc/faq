<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
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
            'votable_type' => fake()->randomElement(['App\Models\Question', 'App\Models\Answer']),
            'votable_id' => fn (array $attributes) => $attributes['votable_type']::factory(),
            'type' => fake()->randomElement(['upvote', 'downvote']),
        ];
    }
}
