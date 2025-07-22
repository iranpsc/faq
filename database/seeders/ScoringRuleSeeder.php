<?php

namespace Database\Seeders;

use App\Models\ScoringRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScoringRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = json_decode(file_get_contents(storage_path('app/private/scoring-rules.json')), true);
        ScoringRule::insert($rules['rules']);
    }
}
