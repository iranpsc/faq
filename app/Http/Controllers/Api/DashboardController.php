<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'totalQuestions' => Question::published()->count(),
                'totalAnswers' => Answer::published()->count(),
                'totalUsers' => User::count(),
                'solvedQuestions' => Question::whereHas('answers', function ($query) {
                    $query->where('is_correct', true);
                })->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'آمار با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت آمار',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended questions (random selection)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recommendedQuestions(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 15);

            $questions = Question::with(['user', 'tags', 'category'])
                ->withCount(['answers', 'votes', 'comments'])
                ->where('published', true)
                ->inRandomOrder()
                ->limit($limit)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'title' => $question->title,
                        'slug' => $question->slug,
                        'created_at' => $question->created_at,
                        'answers_count' => $question->answers_count,
                        'votes_count' => $question->votes_count,
                        'views_count' => $question->views ?? 0,
                        'user' => [
                            'id' => $question->user?->id,
                            'name' => $question->user?->name,
                        ],
                        'category' => $question->category ? [
                            'id' => $question->category->id,
                            'name' => $question->category->name,
                        ] : null,
                        'tags' => $question->tags->map(function ($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name,
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $questions,
                'message' => 'سوالات پیشنهادی با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت سوالات پیشنهادی',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular questions based on views and period
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function popularQuestions(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 15);
            $period = $request->get('period', 'all'); // week, month, year, all

            $query = Question::with(['user', 'tags', 'category'])
                ->withCount(['answers', 'votes', 'comments'])
                ->where('published', true);

            // Apply date filter based on period
            switch ($period) {
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
                case 'all':
                default:
                    // No date filter for 'all'
                    break;
            }

            $questions = $query
                ->orderByDesc(DB::raw('COALESCE(views, 0)')) // Order by views (handle null values)
                ->orderByDesc('votes_count') // Secondary sort by votes
                ->orderByDesc('answers_count') // Tertiary sort by answers
                ->limit($limit)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'title' => $question->title,
                        'slug' => $question->slug,
                        'created_at' => $question->created_at,
                        'answers_count' => $question->answers_count,
                        'votes_count' => $question->votes_count,
                        'views_count' => $question->views ?? 0,
                        'user' => [
                            'id' => $question->user?->id,
                            'name' => $question->user?->name,
                        ],
                        'category' => $question->category ? [
                            'id' => $question->category->id,
                            'name' => $question->category->name,
                        ] : null,
                        'tags' => $question->tags->map(function ($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name,
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $questions,
                'message' => 'سوالات محبوب با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت سوالات محبوب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get most active users based on score and recent activity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activeUsers(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);

            $users = User::withCount(['questions', 'answers', 'comments'])
                ->orderByDesc('score')
                ->orderByDesc('questions_count')
                ->orderByDesc('answers_count')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'image' => $user->image,
                        'score' => $user->score ?? 0,
                        'questions_count' => $user->questions_count,
                        'answers_count' => $user->answers_count,
                        'comments_count' => $user->comments_count,
                        'total_activity' => $user->questions_count + $user->answers_count + $user->comments_count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'کاربران فعال با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت کاربران فعال',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity feed showing activities for a specific period
     * Loads 3 months by default, with load more functionality
     *
     * Query Parameters:
     * - load_more (boolean): Set to true to load 3 more months from current offset
     * - offset (int): Number of months to offset from current date (auto-calculated for load_more)
     * - months (int): Number of months to load (default: 3, ignored when load_more=true)
     * - questions_limit (int): Max questions per month (default: 10)
     * - answers_limit (int): Max answers per month (default: 8)
     * - comments_limit (int): Max comments per month (default: 5)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activity(Request $request): JsonResponse
    {
        try {
            // Default to 3 months, but allow custom months for load more
            $months = (int) $request->get('months', 3);
            $offset = (int) $request->get('offset', 0);
            $loadMore = filter_var($request->get('load_more', false), FILTER_VALIDATE_BOOLEAN);

            if ($loadMore) {
                $months = 3;
                $offset = max(0, $offset);
            } else {
                $offset = 0;
                $months = max(1, $months);
            }

            // Custom limits for activity types per month
            $limits = [
                'questions' => $request->get('questions_limit', 10),
                'answers' => $request->get('answers_limit', 8),
                'comments' => $request->get('comments_limit', 5)
            ];

            $activityService = new ActivityService();
            $report = $activityService->generateActivityReport($months, $offset, $limits);

            $activities = $report['activities'];
            $groupedActivities = $report['grouped_activities'];

            // Calculate next offset for load more functionality
            $nextOffset = $offset + $months;
            $hasMore = $activityService->hasMoreActivities($nextOffset);

            return response()->json([
                'success' => true,
                'data' => $activities,
                'grouped_data' => $groupedActivities,
                'period' => $report['period'],
                'limits' => $report['limits'],
                'pagination' => [
                    'current_offset' => $offset,
                    'next_offset' => $nextOffset,
                    'has_more' => $hasMore,
                    'months_loaded' => $months
                ],
                'message' => 'فعالیت‌ها با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت فعالیت‌ها',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
