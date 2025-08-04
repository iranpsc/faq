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

        // Get current correctness state
        $currentCorrectness = $answer->is_correct;

        // Toggle the correctness
        $answer->update(['is_correct' => !$currentCorrectness]);

        // Process points
        if (!$currentCorrectness) {
            // Answer was marked as correct
            $answer->user->increment('score', 10);
            $user->increment('score', 2);
        } else {
            // Answer was unmarked as correct
            $answer->user->decrement('score', 10);
            $user->increment('score', 2);
        }

        $user->correctnessMarks()->create([
            'answer_id' => $answer->id,
            'is_correct' => !$currentCorrectness,
        ]);

        return response()->json([
            'success' => true,
            'message' => $answer->is_correct ? 'پاسخ به عنوان صحیح علامت‌گذاری شد' : 'علامت صحیح از پاسخ حذف شد',
            'is_correct' => $answer->is_correct,
            'data' => new AnswerResource($answer)
        ]);
    }
}
