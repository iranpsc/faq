<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Question;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(MigrateOldDatabaseSeeder::class);

        // Populate question slugs after migration
        // $this->command->info('Populating question slugs...');
        // Artisan::call('questions:populate-slugs');
        // $this->command->info('Question slugs populated successfully.');
    }
}
