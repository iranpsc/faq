<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
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
        Question::factory(34)
            ->published()
            ->hasComments(3)
            ->hasVotes(2)
            ->hasVerifications(1)
            ->hasAttached(
                Tag::factory(3),
                ['created_at' => now(), 'updated_at' => now()]
            )
            ->create();
    }
}
