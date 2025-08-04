<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Question;
use App\Notifications\QuestionInteractionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index']);
        $this->middleware('auth.optional')->only(['index']);

        $this->authorizeResource(Answer::class, 'answer', [
            'except' => ['index', 'store']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Question $question)
    {
        $user = $request->user();

        $answers = $question->answers()
            ->with('user', 'votes')
            ->visible($user)
            ->latest()
            ->paginate(10);

        return AnswerResource::collection($answers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnswerRequest $request, Question $question)
    {
        $user = $request->user();

        $answer = $question->answers()->create([
            'user_id' => $user->id,
            'content' => $request->content,
            'published' => false, // All answers are unpublished by default
        ]);

        $user->increment('score', 5); // Increment score for answering

        $question->user->notify(new QuestionInteractionNotification($user, $question, 'answer'));

        return new AnswerResource($answer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnswerRequest $request, Answer $answer)
    {
        $answer->update($request->validated());

        return new AnswerResource($answer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $answer)
    {
        $answer->delete();

        return response()->noContent();
    }

    /**
     * Publish an answer.
     */
    public function publish(Request $request, Answer $answer)
    {
        $this->authorize('publish', $answer);

        $user = $request->user();

        $answer->update([
            'published' => true,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        // Award 3 points for publishing an answer
        $user->increment('score', 3);

        return response()->json([
            'success' => true,
            'data' => new AnswerResource($answer),
            'message' => 'پاسخ با موفقیت منتشر شد'
        ]);
    }

    /**
     * Vote on the specified answer.
     */
    public function vote(Request $request, Answer $answer)
    {
        $request->validate([
            'vote' => 'required|in:up,down',
        ]);

        $user = $request->user();
        $voteType = $request->input('vote');

        if ($voteType === 'up') {
            $answer->user->increment('score', 10); // Increment score for upvote
        } elseif ($voteType === 'down') {
            $answer->user->decrement('score', 2); // Decrement score for downvote
        }

        $existingVote = $answer->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->type === $voteType) {
                // User is casting the same vote again, so remove it (toggle off)
                $existingVote->delete();
            } else {
                // User is changing their vote
                $existingVote->update(['type' => $voteType]);
            }
        } else {
            // New vote
            $answer->votes()->create([
                'user_id' => $user->id,
                'type' => $voteType,
            ]);
        }

        return new AnswerResource($answer->load('votes'));
    }

    /**
     * Toggle answer correctness marking.
     */
    public function toggleCorrectness(Request $request, Answer $answer)
    {
        $this->authorize('canMarkCorrectness', $answer);

        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $answer) {
                // Get current mark by this user
                $currentMark = $answer->correctnessMarks()
                    ->where('marker_user_id', $user->id)
                    ->first();

                // Store the original state before any changes
                $originalIsCorrect = $currentMark ? $currentMark->is_correct : null;

                // Determine new state
                $newIsCorrect = $currentMark ? !$currentMark->is_correct : true;

                if ($currentMark) {
                    // Update existing mark
                    $currentMark->update(['is_correct' => $newIsCorrect]);
                } else {
                    // Create new mark
                    $answer->correctnessMarks()->create([
                        'marker_user_id' => $user->id,
                        'is_correct' => $newIsCorrect
                    ]);
                }

                // Update answer's final correctness status
                $this->updateAnswerCorrectnessStatus($answer);

                // Process points - pass the original state
                $this->processCorrectnessPoints($originalIsCorrect, $newIsCorrect, $answer->user, $user);
            });

            // Reload answer with fresh data
            $answer->refresh();
            $answer->load(['correctnessMarks.marker', 'user']);

            return response()->json([
                'success' => true,
                'message' => $answer->is_correct ? 'پاسخ به عنوان صحیح علامت‌گذاری شد' : 'علامت صحیح از پاسخ حذف شد',
                'is_correct' => $answer->is_correct,
                'data' => new AnswerResource($answer)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در علامت‌گذاری پاسخ'
            ], 500);
        }
    }

    /**
     * Update answer's correctness status based on all marks.
     */
    private function updateAnswerCorrectnessStatus(Answer $answer): void
    {
        // Get all marks for this answer ordered by user level (highest first)
        $marks = $answer->correctnessMarks()
            ->join('users', 'users.id', '=', 'answer_correctness_marks.marker_user_id')
            ->orderBy('users.level', 'desc')
            ->select('answer_correctness_marks.*')
            ->get();

        if ($marks->isEmpty()) {
            $answer->update(['is_correct' => false]);
            return;
        }

        // The highest level user's mark takes precedence
        $highestLevelMark = $marks->first();
        $answer->update(['is_correct' => $highestLevelMark->is_correct]);
    }

    /**
     * Process points for correctness marking.
     */
    private function processCorrectnessPoints($originalIsCorrect, $newIsCorrect, $answerOwner, $marker)
    {
        if ($originalIsCorrect === $newIsCorrect) {
            // No change in correctness, but marker still gets points for action
            $marker->increment('score', 2);
            return;
        }

        if ($newIsCorrect) {
            // Answer was marked as correct
            $answerOwner->increment('score', 10);
            $marker->increment('score', 2);
        } else {
            // Answer was unmarked as correct
            $answerOwner->decrement('score', 10);
            $marker->increment('score', 2);
        }
    }
}
