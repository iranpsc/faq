<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'content',
        'pinned',
        'featured',
        'last_activity',
        'views',
        'published',
        'published_at',
        'published_by'
    ];

    /**
     * The attributes with default values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'pinned' => false,
        'featured' => false,
        'published' => true,
        'last_activity' => null,
        'published_at' => null,
        'published_by' => null
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pinned' => 'boolean',
            'featured' => 'boolean',
            'published' => 'boolean',
            'last_activity' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the category that owns the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user that owns the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that published the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Get the tags for the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the answers for the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get all of the question's comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all of the question's votes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    /**
     * Get the upvotes for the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function upVotes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'up');
    }

    /**
     * Get the downvotes for the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function downVotes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'down');
    }

    /**
     * Get all of the question's verifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function verifications()
    {
        return $this->morphMany(Verification::class, 'verifiable');
    }

    /**
     * Check if the question is solved (has a correct or best answer).
     *
     * @return bool
     */
    public function isSolved()
    {
        return $this->answers()->where(function ($query) {
            $query->where('is_correct', true)->orWhere('is_best', true);
        })->exists();
    }

    /**
     * Scope a query to only include published questions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
