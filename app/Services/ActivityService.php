<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityService
{
    /**
     * Generate activity report for a specific period with selective loading
     *
     * @param int $months Number of months to generate report for
     * @param int $offset Number of months to offset from current date
     * @param array $limits Activity limits per type per month
     * @return array
     */
    public function generateActivityReport(int $months = 3, int $offset = 0, array $limits = []): array
    {
        $defaultLimits = [
            'questions' => 10,
            'answers' => 8,
            'comments' => 5
        ];

        $limits = array_merge($defaultLimits, $limits);
        $endDate = Carbon::now()->subMonths($offset);
        $startDate = $endDate->copy()->subMonths($months);

        $allActivities = collect();
        $groupedActivities = [];

        Log::info('Date Range: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));

        // Generate activities for each month in the period
        $currentDate = $startDate->copy();
        while ($currentDate->lt($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();

            // Don't go beyond the end date
            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate->copy();
            }

            $monthActivities = $this->getSelectiveActivitiesForPeriod(
                $monthStart,
                $monthEnd,
                $limits
            );

            if ($monthActivities->isNotEmpty()) {
                $monthName = $this->getPersianMonth($monthStart);
                $allActivities = $allActivities->merge($monthActivities);
                $groupedActivities[$monthName] = $monthActivities->toArray();
            }

            $currentDate->addMonth();
        }

        // Sort all activities by creation date
        $allActivities = $allActivities->sortByDesc('created_at')->values();

        return [
            'activities' => $allActivities,
            'grouped_activities' => $groupedActivities,
            'period' => [
                'start_date' => jdate($startDate)->format('Y/m/d'),
                'end_date' => jdate($endDate)->format('Y/m/d'),
                'months' => $months,
                'offset' => $offset
            ],
            'limits' => $limits
        ];
    }

    /**
     * Get selective activities for a specific period with performance optimization
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $limits
     * @return \Illuminate\Support\Collection
     */
    private function getSelectiveActivitiesForPeriod($startDate, $endDate, array $limits): \Illuminate\Support\Collection
    {
        $activities = collect();

        // Get top questions for the period (optimized query)
        if ($limits['questions'] > 0) {
            $questions = Question::select(['id', 'title', 'slug', 'user_id', 'category_id', 'created_at', 'published_at'])
                ->with(['user:id,name,image', 'category:id,name'])
                ->where('published', true)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('published_at', [$startDate, $endDate])
                          ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                              // Fallback to created_at if published_at is null
                              $subQuery->whereNull('published_at')
                                       ->whereBetween('created_at', [$startDate, $endDate]);
                          });
                })
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit($limits['questions'])
                ->get()
                ->map(function ($question) {
                    $activityDate = $question->published_at ?? $question->created_at;
                    return [
                        'id' => 'question_' . $question->id,
                        'type' => 'question',
                        'user_name' => $question->user->name,
                        'user_id' => $question->user->id,
                        'user_image' => $question->user->image_url,
                        'title' => $question->title,
                        'slug' => $question->slug,
                        'question_id' => $question->id,
                        'category_name' => $question->category->name ?? null,
                        'description' => "کاربر '{$question->user->name}' سوال جدیدی با عنوان '{$question->title}' پرسید",
                        'created_at' => $activityDate,
                        'url' => "/questions/{$question->slug}",
                        'month' => $this->getPersianMonth($activityDate)
                    ];
                });

            $activities = $activities->merge($questions);
        }

        // Get top answers for the period (optimized query)
        if ($limits['answers'] > 0) {
            $answers = Answer::select(['id', 'question_id', 'user_id', 'is_correct', 'created_at', 'published_at'])
                ->with(['user:id,name,image', 'question:id,title,slug'])
                ->where('published', true)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('published_at', [$startDate, $endDate])
                          ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                              // Fallback to created_at if published_at is null
                              $subQuery->whereNull('published_at')
                                       ->whereBetween('created_at', [$startDate, $endDate]);
                          });
                })
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit($limits['answers'])
                ->get()
                ->map(function ($answer) {
                    $activityDate = $answer->published_at ?? $answer->created_at;
                    return [
                        'id' => 'answer_' . $answer->id,
                        'type' => 'answer',
                        'user_name' => $answer->user->name,
                        'user_id' => $answer->user->id,
                        'user_image' => $answer->user->image_url,
                        'title' => $answer->question->title,
                        'question_id' => $answer->question->id,
                        'description' => "کاربر '{$answer->user->name}' به سوال '{$answer->question->title}' پاسخ داد",
                        'created_at' => $activityDate,
                        'url' => "/questions/{$answer->question->slug}",
                        'is_correct' => $answer->is_correct,
                        'month' => $this->getPersianMonth($activityDate)
                    ];
                });

            $activities = $activities->merge($answers);
        }

        // Get top comments for the period (optimized query)
        if ($limits['comments'] > 0) {
            $comments = DB::table('comments')
                ->select([
                    'comments.id',
                    'comments.commentable_type',
                    'comments.commentable_id',
                    'comments.created_at',
                    'comments.published_at',
                    'users.name',
                    'users.id as user_id',
                    'users.image'
                ])
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->where('comments.published', true)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('comments.published_at', [$startDate, $endDate])
                          ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                              // Fallback to created_at if published_at is null
                              $subQuery->whereNull('comments.published_at')
                                       ->whereBetween('comments.created_at', [$startDate, $endDate]);
                          });
                })
                ->orderByRaw('COALESCE(comments.published_at, comments.created_at) DESC')
                ->limit($limits['comments'] * 2) // Get more to account for filtering
                ->get()
                ->map(function ($comment) {
                    $title = '';
                    $questionSlug = null;
                    $activityDate = $comment->published_at ?? $comment->created_at;

                    if ($comment->commentable_type === 'App\Models\Question') {
                        $question = Question::select(['id', 'title', 'slug'])->find($comment->commentable_id);
                        $title = $question ? $question->title : 'سوال حذف شده';
                        $questionSlug = $question ? $question->slug : null;
                    } elseif ($comment->commentable_type === 'App\Models\Answer') {
                        $answer = Answer::select(['id'])->with('question:id,title,slug')->find($comment->commentable_id);
                        if ($answer && $answer->question) {
                            $title = $answer->question->title;
                            $questionSlug = $answer->question->slug;
                        } else {
                            $title = 'پاسخ حذف شده';
                            $questionSlug = null;
                        }
                    }

                    return [
                        'id' => 'comment_' . $comment->id,
                        'type' => 'comment',
                        'user_name' => $comment->name,
                        'user_id' => $comment->user_id,
                        'user_image' => $comment->image ? asset('storage/' . $comment->image) : null,
                        'title' => $title,
                        'question_slug' => $questionSlug,
                        'description' => "کاربر '{$comment->name}' نظری در '{$title}' ثبت کرد",
                        'created_at' => $activityDate,
                        'url' => $questionSlug ? "/questions/{$questionSlug}" : null,
                        'month' => $this->getPersianMonth($activityDate)
                    ];
                })
                ->filter(function ($comment) {
                    // Only include comments that have valid URLs (not deleted content)
                    return $comment['url'] !== null;
                })
                ->take($limits['comments']); // Limit to the desired number after filtering

            $activities = $activities->merge($comments);
        }

        return $activities->sortByDesc('created_at');
    }

    /**
     * Get Persian month name with year from date
     *
     * @param string $date
     * @return string
     */
    private function getPersianMonth($date): string
    {
        $carbon = Carbon::parse($date);

        return jdate($carbon)->format('F Y');
    }

    /**
     * Check if there are more activities available beyond the given offset
     *
     * @param int $offset
     * @return bool
     */
    public function hasMoreActivities(int $offset): bool
    {
        // Check if there are any activities older than the current offset
        $checkDate = Carbon::now()->subMonths($offset);

        // Check for any activities (questions, answers, or comments) before this date
        // Use published_at if available, fallback to created_at
        $hasQuestions = Question::where('published', true)
            ->where(function ($query) use ($checkDate) {
                $query->where('published_at', '<', $checkDate)
                      ->orWhere(function ($subQuery) use ($checkDate) {
                          $subQuery->whereNull('published_at')
                                   ->where('created_at', '<', $checkDate);
                      });
            })
            ->exists();

        $hasAnswers = Answer::where('published', true)
            ->where(function ($query) use ($checkDate) {
                $query->where('published_at', '<', $checkDate)
                      ->orWhere(function ($subQuery) use ($checkDate) {
                          $subQuery->whereNull('published_at')
                                   ->where('created_at', '<', $checkDate);
                      });
            })
            ->exists();

        $hasComments = Comment::where('published', true)
            ->where(function ($query) use ($checkDate) {
                $query->where('published_at', '<', $checkDate)
                      ->orWhere(function ($subQuery) use ($checkDate) {
                          $subQuery->whereNull('published_at')
                                   ->where('created_at', '<', $checkDate);
                      });
            })
            ->exists();

        return $hasQuestions || $hasAnswers || $hasComments;
    }

    /**
     * Get activity statistics for a period
     *
     * @param int $months
     * @param int $offset
     * @return array
     */
    public function getActivityStats(int $months = 3, int $offset = 0): array
    {
        $endDate = Carbon::now()->subMonths($offset);
        $startDate = $endDate->copy()->subMonths($months);

        $stats = [
            'total_questions' => Question::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_answers' => Answer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_comments' => Comment::whereBetween('created_at', [$startDate, $endDate])->count(),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'months' => $months,
                'offset' => $offset
            ]
        ];

        return $stats;
    }
}
