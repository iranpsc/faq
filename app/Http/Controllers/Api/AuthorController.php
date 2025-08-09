<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth.optional');
    }

    /**
     * Get paginated list of authors/users with their activity statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 20);
            $sortBy = $request->get('sort_by', 'score'); // score, questions_count, answers_count, name, created_at
            $sortOrder = $request->get('sort_order', 'desc');
            $search = $request->get('search');

            $query = User::withCount(['questions', 'answers', 'comments'])
                ->with(['questions' => function ($query)use($request) {
                    $query->where('published', true)
                    ->visible($request->user())
                    ->latest()->limit(3);
                }]);

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            switch ($sortBy) {
                case 'questions_count':
                    $query->orderBy('questions_count', $sortOrder);
                    break;
                case 'answers_count':
                    $query->orderBy('answers_count', $sortOrder);
                    break;
                case 'name':
                    $query->orderBy('name', $sortOrder);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $sortOrder);
                    break;
                case 'score':
                default:
                    $query->orderBy('score', $sortOrder);
                    break;
            }

            // Add secondary sorting by created_at for consistency
            if ($sortBy !== 'created_at') {
                $query->orderBy('created_at', 'desc');
            }

            $users = $query->paginate($perPage);

            $formattedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'score' => $user->score ?? 0,
                    'level' => $user->level ?? 1,
                    'role' => $user->role,
                    'questions_count' => $user->questions_count,
                    'answers_count' => $user->answers_count,
                    'comments_count' => $user->comments_count,
                    'total_activity' => $user->questions_count + $user->answers_count + $user->comments_count,
                    'created_at' => $user->created_at,
                    'recent_questions' => $user->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'title' => $question->title,
                            'slug' => $question->slug,
                            'created_at' => $question->created_at,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'message' => 'لیست نویسندگان با موفقیت دریافت شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست نویسندگان',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single author details with their activity
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::with(['questions' => function ($query) {
                $query->where('published', true)->with('user', 'category')->latest();
            }])
                ->withCount(['questions', 'answers', 'comments'])
                ->findOrFail($id);

            $formattedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image,
                'score' => $user->score ?? 0,
                'level' => $user->level ?? 1,
                'role' => $user->role,
                'questions_count' => $user->questions_count,
                'answers_count' => $user->answers_count,
                'comments_count' => $user->comments_count,
                'created_at' => $user->created_at,
                'questions' => $user->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'title' => $question->title,
                        'slug' => $question->slug,
                        'content' => $question->content,
                        'created_at' => $question->created_at,
                        'updated_at' => $question->updated_at,
                        'views' => $question->views,
                        'votes_count' => $question->votes_count,
                        'answers_count' => $question->answers_count,
                        'user' => $question->user ? [
                            'id' => $question->user->id,
                            'name' => $question->user->name,
                            'image' => $question->user->image,
                        ] : null,
                        'category' => $question->category ? [
                            'id' => $question->category->id,
                            'name' => $question->category->name,
                        ] : null,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedUser,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'نویسنده یافت نشد.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
