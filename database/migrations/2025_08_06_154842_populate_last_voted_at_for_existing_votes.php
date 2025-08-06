<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Vote;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing votes to set last_voted_at to their created_at timestamp
        // This ensures backward compatibility with the new hour limit feature
        Vote::whereNull('last_voted_at')->update([
            'last_voted_at' => DB::raw('created_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all last_voted_at to null (optional, since the column will be dropped anyway)
        Vote::update(['last_voted_at' => null]);
    }
};
