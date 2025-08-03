<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\QuestionFilterService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreQuestionRequest;

class QuestionController extends Controller
{
    public function __construct(
        private QuestionFilterService $questionFilterService
    ) {
        $this->middleware('auth.optional')->only(['search', 'index', 'show']);
        $this->middleware('auth:sanctum')->except(['search', 'index', 'show']);

        $this->authorizeResource(Question::class, 'question', [
            'except' => ['search', 'index', 'show']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $questions = $this->questionFilterService->getPaginatedQuestions($request, 10);
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
        $user = $request->user();

        $question = Question::create([
            'category_id' => $request->category_id,
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $tagIds = $this->processTags($request->tags);
        $question->tags()->sync($tagIds);

        // Auto-publish for users with level >= 2
        if ($user->level >= 2) {
            $question->update([
                'published' => true,
                'published_at' => now(),
                'published_by' => $user->id,
            ]);

            // Award 2 points for publishing a question
            $user->increment('score', 2);
        }

        $question->load('user', 'category', 'tags', 'upVotes', 'downVotes');

        return new QuestionResource($question);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $this->incrementViews($question);
        $user = request()->user();

        $this->checkQuestionVisibility($question, $user);
        $this->loadPinStatus($question, $user);
        $this->loadFeatureStatus($question, $user);
        $this->loadQuestionRelations($question, $user);

        return new QuestionResource($question);
    }

    private function incrementViews(Question $question): void
    {
        $question->increment('views');
    }

    private function checkQuestionVisibility(Question $question, $user): void
    {
        if (!$question->published && (!$user || !$user->can('view', $question))) {
            abort(403, 'You do not have permission to view this question.');
        }
    }

    private function loadPinStatus(Question $question, $user): void
    {
        if (!$user) {
            return;
        }

        $questionWithPinStatus = Question::where('questions.id', $question->id)
            ->withUserPinStatus($user)
            ->first();

        if ($questionWithPinStatus) {
            $question->is_pinned_by_user = $questionWithPinStatus->is_pinned_by_user;
            $question->pinned_at = $questionWithPinStatus->pinned_at;
        }
    }

    private function loadFeatureStatus(Question $question, $user): void
    {
        if (!$user) {
            return;
        }

        $questionWithFeatureStatus = Question::where('questions.id', $question->id)
            ->withUserFeatureStatus($user)
            ->first();

        if ($questionWithFeatureStatus) {
            $question->is_featured_by_user = $questionWithFeatureStatus->is_featured_by_user;
            $question->featured_at = $questionWithFeatureStatus->featured_at;
        }
    }

    private function loadQuestionRelations(Question $question, $user): void
    {
        $question->load([
            'user',
            'category',
            'tags',
            'upVotes',
            'downVotes',
            'comments',
            'answers' => function ($query) use ($user) {
                $query->visible($user)->with(['user', 'votes']);
            }
        ])->loadCount('votes', 'upVotes', 'downVotes', 'answers');
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

        $tagIds = $this->processTags($request->tags);
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
     * Publish a question.
     */
    public function publish(Request $request, Question $question)
    {
        $this->authorize('publish', $question);

        $user = $request->user();

        $question->update([
            'published' => true,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        // Award 2 points for publishing a question
        $user->increment('score', 2);

        $question->load('user', 'category', 'tags', 'upVotes', 'downVotes');

        return response()->json([
            'success' => true,
            'data' => new QuestionResource($question),
            'message' => 'سوال با موفقیت منتشر شد'
        ]);
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

    /**
     * Pin a question for the authenticated user.
     */
    public function pin(Request $request, Question $question)
    {
        $user = $request->user();

        // Check if user already pinned this question
        if ($user->pinnedQuestions()->where('question_id', $question->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'شما قبلاً این سوال را پین کرده‌اید'
            ], 409);
        }

        // Check pin limit (optional: limit to 20 pinned questions per user)
        if ($user->pinnedQuestions()->count() >= 20) {
            return response()->json([
                'success' => false,
                'message' => 'حداکثر تعداد سوالات پین شده (20 سوال) به حد نصاب رسیده است'
            ], 422);
        }

        $user->pinnedQuestions()->attach($question->id, [
            'pinned_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'سوال با موفقیت پین شد',
            'is_pinned_by_user' => true,
            'pinned_at' => now()->toISOString()
        ]);
    }

    /**
     * Unpin a question for the authenticated user.
     */
    public function unpin(Request $request, Question $question)
    {
        $user = $request->user();

        if (!$user->pinnedQuestions()->where('question_id', $question->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این سوال پین نشده است'
            ], 404);
        }

        $user->pinnedQuestions()->detach($question->id);

        return response()->json([
            'success' => true,
            'message' => 'پین سوال برداشته شد',
            'is_pinned_by_user' => false,
            'pinned_at' => null
        ]);
    }

    /**
     * Feature a question for the authenticated user.
     */
    public function feature(Request $request, Question $question)
    {
        $user = $request->user();

        // Check authorization
        if (!$user->can('feature', $question)) {
            return response()->json([
                'success' => false,
                'message' => 'شما مجاز به ویژه کردن این سوال نیستید'
            ], 403);
        }

        // Check if user already featured this question
        if ($user->featuredQuestions()->where('question_id', $question->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'شما قبلاً این سوال را ویژه کرده‌اید'
            ], 409);
        }

        // Check feature limit (2 questions per user)
        if ($user->featuredQuestions()->count() >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'حداکثر تعداد سوالات ویژه (2 سوال) به حد نصاب رسیده است'
            ], 422);
        }

        $user->featuredQuestions()->attach($question->id, [
            'featured_at' => now()
        ]);

        // Update question's featured status
        $question->update(['featured' => true]);

        return response()->json([
            'success' => true,
            'message' => 'سوال با موفقیت ویژه شد',
            'is_featured_by_user' => true,
            'featured_at' => now()->toISOString()
        ]);
    }

    /**
     * Unfeature a question for the authenticated user.
     */
    public function unfeature(Request $request, Question $question)
    {
        $user = $request->user();

        // Check authorization
        if (!$user->can('unfeature', $question)) {
            return response()->json([
                'success' => false,
                'message' => 'شما مجاز به برداشتن ویژگی این سوال نیستید'
            ], 403);
        }

        // Check if user has reached the 2-action limit
        if ($user->featuredQuestions()->count() >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'شما به حد نصاب عملیات (2 عملیات) رسیده‌اید'
            ], 422);
        }

        $removedFeature = false;

        // Check if this user has featured the question
        if ($user->featuredQuestions()->where('question_id', $question->id)->exists()) {
            $user->featuredQuestions()->detach($question->id);
            $removedFeature = true;
        } else {
            // Higher level users can unfeature questions featured by lower level users
            $lowerLevelFeaturedUsers = $question->featuredByUsers()
                ->where('level', '<', $user->level)
                ->get();

            if ($lowerLevelFeaturedUsers->isNotEmpty()) {
                // Remove the first lower level user's feature
                $lowerLevelUser = $lowerLevelFeaturedUsers->first();
                $question->featuredByUsers()->detach($lowerLevelUser->id);
                $removedFeature = true;
            }
        }

        if (!$removedFeature) {
            return response()->json([
                'success' => false,
                'message' => 'این سوال ویژه نشده است یا شما مجاز به برداشتن ویژگی آن نیستید'
            ], 404);
        }

        // Update question's featured status if no more users have featured it
        if ($question->featuredByUsers()->count() === 0) {
            $question->update(['featured' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'ویژگی سوال برداشته شد',
            'is_featured_by_user' => $user->featuredQuestions()->where('question_id', $question->id)->exists(),
            'featured_at' => null
        ]);
    }

    /**
     * Process tags array to handle both existing and new tags
     *
     * @param array $tags
     * @return array Array of tag IDs
     */
    private function processTags(array $tags): array
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (isset($tag['id']) && is_numeric($tag['id'])) {
                // Existing tag
                $tagIds[] = $tag['id'];
            } elseif (isset($tag['name']) && !empty($tag['name'])) {
                // New tag - create it if it doesn't exist
                $tagName = trim($tag['name']);
                $existingTag = \App\Models\Tag::where('name', $tagName)->first();

                if ($existingTag) {
                    $tagIds[] = $existingTag->id;
                } else {
                    $newTag = \App\Models\Tag::create(['name' => $tagName]);
                    $tagIds[] = $newTag->id;
                }
            }
        }

        return array_unique($tagIds);
    }
}
