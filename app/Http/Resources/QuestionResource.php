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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'votes_count' => $this->whenCounted('votes'),
            'views' => $this->views,
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
