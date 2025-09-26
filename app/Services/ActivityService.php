<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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

        // Create cache key for this specific request
        $cacheKey = "activity_report_{$months}_{$offset}_" . md5(serialize($limits));

        // Cache for 5 minutes for recent data, 30 minutes for older data
        $cacheTime = $offset === 0 ? 5 : 30;

        return Cache::remember($cacheKey, $cacheTime, function () use ($startDate, $endDate, $months, $offset, $limits) {
            $allActivities = collect();
            $groupedActivities = [];

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
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'months' => $months,
                    'offset' => $offset
                ],
                'limits' => $limits
            ];
        });
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
            $questions = Question::select(['id', 'title', 'slug', 'user_id', 'category_id', 'created_at'])
                ->with(['user:id,name,image', 'category:id,name'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limits['questions'])
                ->get()
                ->map(function ($question) {
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
                        'created_at' => $question->created_at,
                        'url' => "/questions/{$question->slug}",
                        'month' => $this->getPersianMonth($question->created_at)
                    ];
                });

            $activities = $activities->merge($questions);
        }

        // Get top answers for the period (optimized query)
        if ($limits['answers'] > 0) {
            $answers = Answer::select(['id', 'question_id', 'user_id', 'is_correct', 'created_at'])
                ->with(['user:id,name,image', 'question:id,title,slug'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limits['answers'])
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
                        'url' => "/questions/{$answer->question->slug}",
                        'is_correct' => $answer->is_correct,
                        'month' => $this->getPersianMonth($answer->created_at)
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
                    'users.name',
                    'users.id as user_id',
                    'users.image'
                ])
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->whereBetween('comments.created_at', [$startDate, $endDate])
                ->where('comments.published', true)
                ->orderBy('comments.created_at', 'desc')
                ->limit($limits['comments'])
                ->get()
                ->map(function ($comment) {
                    $title = '';
                    $questionSlug = null;

                    if ($comment->commentable_type === 'App\Models\Question') {
                        $question = Question::select(['id', 'title', 'slug'])->find($comment->commentable_id);
                        $title = $question ? $question->title : 'سوال حذف شده';
                        $questionSlug = $question ? $question->slug : null;
                    } elseif ($comment->commentable_type === 'App\Models\Answer') {
                        $answer = Answer::select(['id'])->with('question:id,title,slug')->find($comment->commentable_id);
                        $title = $answer && $answer->question ? $answer->question->title : 'پاسخ حذف شده';
                        $questionSlug = $answer && $answer->question ? $answer->question->slug : null;
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
                        'created_at' => $comment->created_at,
                        'url' => $questionSlug ? "/questions/{$questionSlug}" : null,
                        'month' => $this->getPersianMonth($comment->created_at)
                    ];
                });

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
        $persianMonths = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند'
        ];

        // Get Gregorian month and year
        $gregorianMonth = (int) $carbon->format('n');
        $gregorianYear = (int) $carbon->format('Y');

        // Map Gregorian months to Persian months (approximate mapping)
        $monthMapping = [
            1 => 10,  // January -> Dey
            2 => 11,  // February -> Bahman
            3 => 12,  // March -> Esfand
            4 => 1,   // April -> Farvardin
            5 => 2,   // May -> Ordibehesht
            6 => 3,   // June -> Khordad
            7 => 4,   // July -> Tir
            8 => 5,   // August -> Mordad
            9 => 6,   // September -> Shahrivar
            10 => 7,  // October -> Mehr
            11 => 8,  // November -> Aban
            12 => 9   // December -> Azar
        ];

        $persianMonthNum = $monthMapping[$gregorianMonth];

        // Convert Gregorian year to Persian year
        // Persian year is approximately 621 years behind Gregorian year
        // But we need to account for the fact that Persian new year starts in March
        $persianYear = $gregorianYear - 621;

        // Adjust for Persian new year (starts around March 20-21)
        if ($gregorianMonth < 3 || ($gregorianMonth == 3 && $carbon->format('j') < 21)) {
            $persianYear = $persianYear - 1;
        }

        return $persianMonths[$persianMonthNum] . ' ' . $persianYear;
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
