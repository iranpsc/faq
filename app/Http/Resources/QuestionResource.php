<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if current user has voted
        $userVote = null;
        if ($request->user()) {
            $userVoteRecord = $this->votes()->where('user_id', $request->user()->id)->first();
            $userVote = $userVoteRecord ? $userVoteRecord->type : null;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'answers_count' => $this->whenCounted('answers'),
            'votes_count' => $this->whenCounted('votes'),
            'votes' => [
                'upvotes' => $this->whenLoaded('upVotes'),
                'downvotes' => $this->whenLoaded('downVotes'),
                'user_vote' => $userVote,
            ],
            'views' => $this->views,
            'is_solved' => $this->isSolved(),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'publish' => $request->user()?->can('publish', $this->resource) ?? false,
                'pin' => $request->user()?->can('pin', $this->resource) ?? false,
                'feature' => $request->user()?->can('feature', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ]
        ];
    }
}
