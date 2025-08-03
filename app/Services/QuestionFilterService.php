<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QuestionFilterService
{
    /**
     * Filter questions based on request parameters
     *
     * @param Request $request
     * @return Builder
     */
    public function filter(Request $request): Builder
    {
        $query = Question::query();

        $user = $request->user();

        // Apply base query with relations and counts
        $query = $query->with('user', 'category', 'tags')
            ->withCount('votes', 'answers')
            ->visible($user)
            ->withUserPinStatus($user)
            ->withUserFeatureStatus($user);

        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply tags filter
        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', $tags);
            });
        }

        // Apply sorting filters
        $this->applySortingFilters($request, $query, $user);

        return $query;
    }

    /**
     * Apply sorting filters based on request parameters
     *
     * @param Request $request
     * @param Builder $query
     * @param mixed $user
     * @return void
     */
    private function applySortingFilters(Request $request, Builder $query, $user): void
    {
        // Default ordering by pin status
        $query->orderByPinStatus($user);

        // Apply additional sorting based on parameters
        if ($request->has('newest')) {
            $query->orderBy('created_at', 'desc');
        } elseif ($request->has('oldest')) {
            $query->orderBy('created_at', 'asc');
        } elseif ($request->has('most_votes')) {
            $query->orderBy('votes_count', 'desc');
        } elseif ($request->has('most_answers')) {
            $query->orderBy('answers_count', 'desc');
        } elseif ($request->has('most_views')) {
            $query->orderBy('views', 'desc');
        } elseif ($request->has('unanswered')) {
            $query->having('answers_count', '=', 0)
                ->orderBy('created_at', 'desc');
        } elseif ($request->has('unsolved')) {
            // Assuming unsolved means no accepted answer
            $query->whereDoesntHave('answers', function ($q) {
                $q->where('is_accepted', true);
            })->orderBy('created_at', 'desc');
        } else {
            // Default ordering when no specific sort is requested
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get paginated questions with filters applied
     *
     * @param Request $request
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedQuestions(Request $request, int $perPage = 10)
    {
        $query = $this->filter($request);
        return $query->paginate($perPage);
    }
}
