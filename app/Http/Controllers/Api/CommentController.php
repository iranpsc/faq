<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);

        $this->authorizeResource(Comment::class, 'comment', [
            'except' => ['index', 'show']
        ]);
    }

    public function index($parent, $parentId = null)
    {
        // Handle both question comments and answer comments
        if ($parent instanceof Question) {
            $comments = $parent->comments()->with('user', 'votes')->latest()->paginate(10);
        } elseif ($parent instanceof Answer) {
            $comments = $parent->comments()->with('user', 'votes')->latest()->paginate(10);
        } else {
            // Legacy route binding - determine parent type
            if (request()->route()->getName() === 'questions.comments.index') {
                $question = Question::findOrFail($parent);
                $comments = $question->comments()->with('user', 'votes')->latest()->paginate(10);
            } elseif (request()->route()->getName() === 'answers.comments.index') {
                $answer = Answer::findOrFail($parent);
                $comments = $answer->comments()->with('user', 'votes')->latest()->paginate(10);
            }
        }

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, $parent)
    {
        // Handle both question comments and answer comments
        if ($parent instanceof Question) {
            $comment = $parent->comments()->create([
                'user_id' => $request->user()->id,
                'content' => $request->content,
            ]);
        } elseif ($parent instanceof Answer) {
            $comment = $parent->comments()->create([
                'user_id' => $request->user()->id,
                'content' => $request->content,
            ]);
        } else {
            // Legacy route binding - determine parent type
            if (request()->route()->getName() === 'questions.comments.store') {
                $question = Question::findOrFail($parent);
                $comment = $question->comments()->create([
                    'user_id' => $request->user()->id,
                    'content' => $request->content,
                ]);
            } elseif (request()->route()->getName() === 'answers.comments.store') {
                $answer = Answer::findOrFail($parent);
                $comment = $answer->comments()->create([
                    'user_id' => $request->user()->id,
                    'content' => $request->content,
                ]);
            }
        }

        // Increment user's score for commenting
        $request->user()->increment('score', 2);

        return new CommentResource($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $comment->update($request->validated());

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }

    public function vote(Request $request, Comment $comment)
    {
        $request->validate([
            'vote' => 'required|in:up,down',
        ]);

        $user = $request->user();
        $voteType = $request->input('vote');

        $existingVote = $comment->votes()->where('user_id', $user->id)->first();

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
            $comment->votes()->create([
                'user_id' => $user->id,
                'type' => $voteType,
            ]);
        }

        return new CommentResource($comment->load('votes'));
    }
}
