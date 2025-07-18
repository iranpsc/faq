<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Verification>
 */
class VerificationFactory extends Factory
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
            'verifiable_type' => fake()->randomElement(['App\Models\Question', 'App\Models\Answer']),
            'verifiable_id' => fn (array $attributes) => $attributes['verifiable_type']::factory(),
        ];
    }
}
