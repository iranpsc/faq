<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnswerRequest $request, Question $question)
    {
        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return new AnswerResource($answer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnswerRequest $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        $answer->update($request->validated());

        return new AnswerResource($answer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);

        $answer->delete();

        return response()->noContent();
    }

    /**
     * Vote on the specified answer.
     */
    public function vote(Request $request, Answer $answer)
    {
        $request->validate([
            'vote' => 'required|in:up,down',
        ]);

        $user = $request->user();
        $voteType = $request->input('vote');

        $existingVote = $answer->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->type === $voteType) {
                // User is casting the same vote again, so remove it (toggle off)
                $existingVote->delete();
            } else {
                // User is changing their vote
                $existingVote->update(['type' => $voteType]);
            }
        } else {
            // New vote
            $answer->votes()->create([
                'user_id' => $user->id,
                'type' => $voteType,
            ]);
        }

        return new AnswerResource($answer->load('votes'));
    }
}
