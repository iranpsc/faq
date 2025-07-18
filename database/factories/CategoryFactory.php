<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null,
            'last_activity' => fake()->dateTime(),
        ];
    }

    /**
     * Indicate that the category is a child category.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function child()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => CategoryFactory::new(),
            ];
        });
    }
}
