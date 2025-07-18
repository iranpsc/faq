<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraphs(2, true),
            'published' => fake()->boolean(),
            'published_at' => fake()->dateTime(),
            'published_by' => User::factory(),
            'is_correct' => fake()->boolean(),
            'is_best' => fake()->boolean(),
        ];
    }
}
