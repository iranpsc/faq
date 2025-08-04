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
        if ($request->user() && $this->relationLoaded('votes')) {
            $userVoteRecord = $this->votes()->where('user_id', $request->user()->id)->first();
            $userVote = $userVoteRecord ? $userVoteRecord->type : null;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published' => $this->published,
            'published_at' => $this->published_at,
            'published_by' => $this->published_by,
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
            'is_pinned_by_user' => (bool) ($this->is_pinned_by_user ?? false),
            'pinned_at' => $this->pinned_at ? $this->pinned_at : null,
            'is_featured_by_user' => (bool) ($this->is_featured_by_user ?? false),
            'featured_at' => $this->featured_at ? $this->featured_at : null,
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'comments' => \App\Http\Resources\CommentResource::collection($this->whenLoaded('comments')),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'publish' => $request->user()?->can('publish', $this->resource) ?? false,
                'feature' => $request->user()?->can('feature', $this->resource) ?? false,
                'unfeature' => $request->user()?->can('unfeature', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ]
        ];
    }
}
