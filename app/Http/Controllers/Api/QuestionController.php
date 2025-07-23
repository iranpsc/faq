<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Question::class, 'question');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return QuestionResource::collection(Question::with('user', 'category', 'tags')->latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create([
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'content' => $request->content,
            'published' => true,
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        $tagIds = collect($request->tags)->pluck('id')->toArray();

        $question->tags()->sync($tagIds);

        return new QuestionResource($question->load('user', 'category', 'tags'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return new QuestionResource($question->load('user', 'category', 'tags', 'answers.user', 'comments.user'));
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

        $tagIds = collect($request->tags)->pluck('id')->toArray();
        $question->tags()->sync($tagIds);

        return new QuestionResource($question->load('user', 'category', 'tags'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->noContent();
    }
}
