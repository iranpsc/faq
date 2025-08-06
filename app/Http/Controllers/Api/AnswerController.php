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
            'type' => 'required|in:up,down'
        ]);

        $userId = $request->user()->id;
        $voteType = $request->type;

        $existingVote = $answer->votes()->where('user_id', $userId)->first();

        if ($existingVote) {
            $lastVotedAt = $existingVote->last_voted_at->diffInMinutes(now());
            // abort_if($lastVotedAt < 60, 429, 'شما هر ساعت یک بار مجاز به تغییر رای خود هستید');
        }

        $answer->votes()->updateOrCreate([
            'user_id' => $userId
        ], [
            'type' => $voteType,
            'last_voted_at' => now()
        ]);

        if ($voteType == 'up') {
            $answer->user->increment('score', 10);
        } elseif ($voteType == 'down') {
            $answer->user->decrement('score', 2);
        }

        // Return updated vote counts and user vote status
        $answer->load('upVotes', 'downVotes');

        // Get user's current vote
        $userVoteRecord = $answer->votes()->where('user_id', $userId)->first();
        $userVote = $userVoteRecord ? $userVoteRecord->type : null;

        return response()->json([
            'upvotes' => $answer->upVotes->count(),
            'downvotes' => $answer->downVotes->count(),
            'user_vote' => $userVote
        ]);
    }

    /**
     * Toggle answer correctness marking.
     */
    public function toggleCorrectness(Request $request, Answer $answer)
    {
        // Get current correctness state
        $currentCorrectness = $answer->is_correct;

        $action = $currentCorrectness ? 'markAsIncorrect' : 'markAsCorrect';

        $this->authorize('toggleCorrectness', [$answer, $action]);

        $user = $request->user();

        // Create new mark
        $user->correctnessMarks()->create([
            'answer_id' => $answer->id,
            'is_correct' => !$currentCorrectness,
        ]);

        // Update the correctness
        $answer->update(['is_correct' => !$currentCorrectness]);

        // Process points
        if (!$currentCorrectness) {
            // Answer was marked as correct (previously false, now true)
            $answer->user->increment('score', 10);
            $user->increment('score', 2);
        } else {
            // Answer was unmarked as correct (previously true, now false)
            $answer->user->decrement('score', 10);
            $user->increment('score', 2);
        }

        return response()->json([
            'success' => true,
            'message' => $answer->is_correct ? 'پاسخ به صحیح تغییر داده شد' : 'پاسخ به عادی تغییر داده شد.',
            'is_correct' => $answer->is_correct,
            'data' => new AnswerResource($answer)
        ]);
    }
}
