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
        // Check if user is actively filtering (has any filter parameters)
        $hasActiveFilters = $this->hasActiveFilters($request);

        // Handle filter-based parameters (unanswered, unsolved)
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'unanswered':
                    $query->groupBy('questions.id')
                        ->having('answers_count', '=', 0)
                        ->orderBy('created_at', 'desc');
                    return;
                case 'unsolved':
                    // Assuming unsolved means no accepted answer
                    $query->whereDoesntHave('answers', function ($q) {
                        $q->where('is_correct', true);
                    })->orderBy('created_at', 'desc');
                    return;
            }
        }

        // Handle sort and order parameters
        if ($request->filled('sort') && $request->filled('order')) {
            $sortField = $request->sort;
            $sortOrder = $request->order;

            switch ($sortField) {
                case 'created_at':
                    $query->orderBy('created_at', $sortOrder);
                    break;
                case 'votes':
                    $query->orderBy('votes_count', $sortOrder);
                    break;
                case 'answers_count':
                    $query->orderBy('answers_count', $sortOrder);
                    break;
                case 'views_count':
                    $query->orderBy('views', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
            return;
        }

        // Legacy parameter support (backwards compatibility)
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
            $query->groupBy('questions.id')
                ->having('answers_count', '=', 0)
                ->orderBy('created_at', 'desc');
        } elseif ($request->has('unsolved')) {
            // Assuming unsolved means no accepted answer
            $query->whereDoesntHave('answers', function ($q) {
                $q->where('is_correct', true);
            })->orderBy('created_at', 'desc');
        } else {
            // Only apply pin status ordering when no active filters are applied
            if (!$hasActiveFilters) {
                $query->orderByPinStatus($user);
            }
            // Default ordering when no specific sort is requested
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Check if the request has any active filtering parameters
     *
     * @param Request $request
     * @return bool
     */
    private function hasActiveFilters(Request $request): bool
    {
        return $request->filled('category_id') ||
               $request->filled('tags') ||
               $request->filled('filter') ||
               $request->filled('sort') ||
               $request->has('newest') ||
               $request->has('oldest') ||
               $request->has('most_votes') ||
               $request->has('most_answers') ||
               $request->has('most_views') ||
               $request->has('unanswered') ||
               $request->has('unsolved');
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
