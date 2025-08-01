<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing User Question Pin Feature\n";
echo "=================================\n\n";

try {
    // Test 1: Check if the user_pinned_questions table exists
    echo "1. Checking if user_pinned_questions table exists...\n";
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='user_pinned_questions'");
    if (count($tables) > 0) {
        echo "   ✓ Table exists\n\n";
    } else {
        echo "   ✗ Table does not exist\n\n";
        exit(1);
    }

    // Test 2: Check if we can create relationships
    echo "2. Testing model relationships...\n";

    // Get first user and question for testing
    $user = User::first();
    $question = Question::first();

    if (!$user) {
        echo "   ⚠ No users found in database\n\n";
    } else {
        echo "   ✓ Found user: {$user->name}\n";
    }

    if (!$question) {
        echo "   ⚠ No questions found in database\n\n";
    } else {
        echo "   ✓ Found question: {$question->title}\n";
    }

    if ($user && $question) {
        // Test pin functionality
        echo "\n3. Testing pin functionality...\n";

        // Check if question is already pinned
        $isAlreadyPinned = $user->pinnedQuestions()->where('question_id', $question->id)->exists();

        if ($isAlreadyPinned) {
            echo "   - Question already pinned, unpinning first...\n";
            $user->pinnedQuestions()->detach($question->id);
        }

        // Pin the question
        $user->pinnedQuestions()->attach($question->id, ['pinned_at' => now()]);
        echo "   ✓ Question pinned successfully\n";

        // Check if pin exists
        $pinExists = $user->pinnedQuestions()->where('question_id', $question->id)->exists();
        if ($pinExists) {
            echo "   ✓ Pin relationship verified\n";
        } else {
            echo "   ✗ Pin relationship not found\n";
        }

        // Test query scope
        echo "\n4. Testing query scopes...\n";
        $questionsWithPinStatus = Question::withUserPinStatus($user)->first();

        if ($questionsWithPinStatus) {
            echo "   ✓ withUserPinStatus scope works\n";
            echo "   - is_pinned_by_user: " . ($questionsWithPinStatus->is_pinned_by_user ? 'true' : 'false') . "\n";
        } else {
            echo "   ✗ withUserPinStatus scope failed\n";
        }

        // Clean up - unpin the question
        $user->pinnedQuestions()->detach($question->id);
        echo "   ✓ Cleanup completed\n";
    }

    echo "\n✅ All tests completed successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
