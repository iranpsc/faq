<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
        $categories = Category::when($query, function ($q) use ($query) {
            return $q->where('name', 'like', "%{$query}%");
        });

        $categories = $query ? $categories->get() : $categories->simplePaginate();

        return CategoryResource::collection($categories);
    }

    /**
     * Get popular categories based on questions, answers, and comments count.
     */
    public function popular(Request $request)
    {
        $limit = $request->input('limit', 15);

        $categories = Category::withCount([
            'questions',
            'questions as answers_count' => function ($query) {
                $query->join('answers', 'questions.id', '=', 'answers.question_id');
            },
            'questions as comments_count' => function ($query) {
                $query->join('comments', 'questions.id', '=', 'comments.commentable_id')
                      ->where('comments.commentable_type', 'App\\Models\\Question');
            }
        ])
        ->get()
        ->map(function ($category) {
            $category->activity_score = $category->questions_count +
                                      ($category->answers_count ?? 0) +
                                      ($category->comments_count ?? 0);
            return $category;
        })
        ->sortByDesc('activity_score')
        ->take($limit)
        ->values();

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
        ]);

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
