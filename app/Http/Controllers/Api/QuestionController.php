<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['search', 'index', 'show']);

        $this->middleware('auth.optional')->only(['search', 'index', 'show']);

        $this->authorizeResource(Question::class, 'question', [
            'except' => ['search']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::with('user', 'category')
            ->withCount('votes', 'answers')
            ->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $questions = $query->paginate(10);

        return QuestionResource::collection($questions);
    }

    /**
     * Search questions by title
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        $questions = Question::with('user', 'category')
            ->withCount('votes', 'upVotes', 'downVotes', 'answers')
            ->published()
            ->where('title', 'like', '%' . $query . '%')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => QuestionResource::collection($questions),
            'message' => 'جستجو با موفقیت انجام شد'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {

        $question = Question::create([
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $tagIds = collect($request->tags)->pluck('id')->toArray();

        $question->tags()->sync($tagIds);

        if ($request->user()->can('publish', $question)) {
            $question->update([
                'published' => true,
                'published_at' => now(),
                'published_by' => $request->user()->id,
            ]);
        }

        $question->load('user', 'category', 'upVotes', 'downVotes');

        return new QuestionResource($question);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $question->increment('views');

        $question->load([
            'user',
            'category',
            'tags',
            'upVotes',
            'downVotes',
            'answers.user',
            'answers.votes'
        ])->loadCount('votes', 'upVotes', 'downVotes', 'answers');

        return new QuestionResource($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $question->update([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $tagIds = collect($request->tags)->pluck('id')->toArray();
        $question->tags()->sync($tagIds);

        $question->load('user', 'category', 'tags', 'upVotes', 'downVotes');

        return new QuestionResource($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->noContent();
    }

    /**
     * Vote on a question (upvote or downvote).
     */
    public function vote(Request $request, Question $question)
    {
        $request->validate([
            'type' => 'required|in:up,down'
        ]);

        $userId = $request->user()->id;
        $voteType = $request->type;

        if ($voteType == 'up') {
            $question->user->increment('score', 10);
        } elseif ($voteType == 'down') {
            $question->user->decrement('score', 2);
        }

        // Check if user has already voted on this question
        $existingVote = $question->votes()->where('user_id', $userId)->first();

        if ($existingVote) {
            // If same vote type, remove the vote (toggle off)
            if ($existingVote->type === $voteType) {
                $existingVote->delete();
            } else {
                // If different vote type, update the vote
                $existingVote->update(['type' => $voteType]);
            }
        } else {
            // Create new vote
            $question->votes()->create([
                'user_id' => $userId,
                'type' => $voteType
            ]);
        }

        // Return updated vote counts and user vote status
        $question->load('upVotes', 'downVotes');

        // Get user's current vote
        $userVoteRecord = $question->votes()->where('user_id', $userId)->first();
        $userVote = $userVoteRecord ? $userVoteRecord->type : null;

        return response()->json([
            'upvotes' => $question->upVotes->count(),
            'downvotes' => $question->downVotes->count(),
            'user_vote' => $userVote
        ]);
    }
}
