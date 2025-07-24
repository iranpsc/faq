<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Question;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Category::factory(140)->create();
        Tag::factory(50)->create();
        Question::factory(100)
            ->hasComments(3)
            ->hasVotes(2)
            ->hasVerifications(1)
            ->create()
            ->each(function ($question) {
                $question->tags()->attach(Tag::inRandomOrder()->take(rand(1, 5))->pluck('id'));
            });
    }
}
