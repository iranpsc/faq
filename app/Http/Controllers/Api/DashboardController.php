<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use App\Models\User;
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
                        'created_at' => $question->created_at,
                        'answers_count' => $question->answers_count,
                        'votes_count' => $question->votes_count,
                        'views_count' => $question->views ?? 0,
                        'user' => [
                            'id' => $question->user->id,
                            'name' => $question->user->name,
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
            $period = $request->get('period', 'week'); // week, month, year, all

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
                        'created_at' => $question->created_at,
                        'answers_count' => $question->answers_count,
                        'votes_count' => $question->votes_count,
                        'views_count' => $question->views ?? 0,
                        'user' => [
                            'id' => $question->user->id,
                            'name' => $question->user->name,
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
     * Get daily activity feed showing recent activities
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dailyActivity(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $date = $request->get('date', now()->format('Y-m-d'));

            $activities = collect();

            // Get today's questions
            $questions = Question::with('user', 'category')
                ->whereDate('created_at', $date)
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => 'question_' . $question->id,
                        'type' => 'question',
                        'user_name' => $question->user->name,
                        'user_id' => $question->user->id,
                        'user_image' => $question->user->image_url,
                        'title' => $question->title,
                        'question_id' => $question->id,
                        'category_name' => $question->category->name ?? null,
                        'description' => "کاربر '{$question->user->name}' سوال جدیدی با عنوان '{$question->title}' پرسید",
                        'created_at' => $question->created_at,
                        'url' => "/questions/{$question->id}"
                    ];
                });

            // Get today's answers
            $answers = Answer::with('user', 'question')
                ->whereDate('created_at', $date)
                ->latest()
                ->take($limit)
                ->get()
                ->map(function ($answer) {
                    return [
                        'id' => 'answer_' . $answer->id,
                        'type' => 'answer',
                        'user_name' => $answer->user->name,
                        'user_id' => $answer->user->id,
                        'user_image' => $answer->user->image_url,
                        'title' => $answer->question->title,
                        'question_id' => $answer->question->id,
                        'description' => "کاربر '{$answer->user->name}' به سوال '{$answer->question->title}' پاسخ داد",
                        'created_at' => $answer->created_at,
                        'url' => "/questions/{$answer->question->id}",
                        'is_correct' => $answer->is_correct
                    ];
                });

            // Get today's comments
            $comments = DB::table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->whereDate('comments.created_at', $date)
                ->orderBy('comments.created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($comment) {
                    $title = '';
                    $questionId = null;

                    if ($comment->commentable_type === 'App\Models\Question') {
                        $question = Question::find($comment->commentable_id);
                        $title = $question ? $question->title : 'سوال حذف شده';
                        $questionId = $comment->commentable_id;
                    } elseif ($comment->commentable_type === 'App\Models\Answer') {
                        $answer = Answer::with('question')->find($comment->commentable_id);
                        $title = $answer && $answer->question ? $answer->question->title : 'پاسخ حذف شده';
                        $questionId = $answer ? $answer->question_id : null;
                    }

                    return [
                        'id' => 'comment_' . $comment->id,
                        'type' => 'comment',
                        'user_name' => $comment->name,
                        'user_id' => $comment->user_id,
                        'user_image' => $comment->image ? asset('storage/' . $comment->image) : null,
                        'title' => $title,
                        'question_id' => $questionId,
                        'description' => "کاربر '{$comment->name}' نظری در '{$title}' ثبت کرد",
                        'created_at' => $comment->created_at,
                        'url' => $questionId ? "/questions/{$questionId}" : null
                    ];
                });

            // Merge and sort all activities
            $allActivities = $activities
                ->merge($questions)
                ->merge($answers)
                ->merge($comments)
                ->sortByDesc('created_at')
                ->take($limit)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $allActivities,
                'message' => 'فعالیت روزانه با موفقیت دریافت شد',
                'date' => $date
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت فعالیت روزانه',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
