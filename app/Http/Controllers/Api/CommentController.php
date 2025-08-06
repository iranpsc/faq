<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use App\Notifications\QuestionInteractionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('auth.optional')->only(['index', 'show']);

        $this->authorizeResource(Comment::class, 'comment', [
            'except' => ['index']
        ]);
    }

    public function index(Request $request, $parent, $parentId = null)
    {
        $user = $request->user();

        // Handle both question comments and answer comments
        if ($parent instanceof Question) {
            $comments = $parent->comments()->visible($user)->with('user', 'votes')->latest()->paginate(10);
        } elseif ($parent instanceof Answer) {
            $comments = $parent->comments()->visible($user)->with('user', 'votes')->latest()->paginate(10);
        } else {
            // Legacy route binding - determine parent type
            if (request()->route()->getName() === 'questions.comments.index') {
                $question = Question::findOrFail($parent);
                $comments = $question->comments()->visible($user)->with('user', 'votes')->latest()->paginate(10);
            } elseif (request()->route()->getName() === 'answers.comments.index') {
                $answer = Answer::findOrFail($parent);
                $comments = $answer->comments()->visible($user)->with('user', 'votes')->latest()->paginate(10);
            }
        }

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, $parent)
    {
        $user = $request->user();

        $commentData = [
            'user_id' => $user->id,
            'content' => $request->content,
        ];

        $question = null;

        // Handle both question comments and answer comments
        if ($parent instanceof Question) {
            $comment = $parent->comments()->create($commentData);
            $question = $parent;
        } elseif ($parent instanceof Answer) {
            $comment = $parent->comments()->create($commentData);
            $question = $parent->question;
        } else {
            // Legacy route binding - determine parent type
            if (request()->route()->getName() === 'questions.comments.store') {
                $question = Question::findOrFail($parent);
                $comment = $question->comments()->create($commentData);
            } elseif (request()->route()->getName() === 'answers.comments.store') {
                $answer = Answer::findOrFail($parent);
                $comment = $answer->comments()->create($commentData);
                $question = $answer->question;
            }
        }

        // Add 2 score scores for commenting
        $user->increment('score', 2);

        if ($user->can('publish', $comment)) {
            $comment->update([
                'published' => true,
                'published_at' => now(),
                'published_by' => $user->id,
            ]);
        }

        $question->user->notify(new QuestionInteractionNotification($user, $question, 'comment'));

        return new CommentResource($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }

    /**
     * Publish a comment.
     */
    public function publish(Request $request, Comment $comment)
    {
        $this->authorize('publish', $comment);

        $user = $request->user();

        $comment->update([
            'published' => true,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        // Award 2 points for publishing a comment
        $user->increment('score', 2);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($comment),
            'message' => 'نظر با موفقیت منتشر شد'
        ]);
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
