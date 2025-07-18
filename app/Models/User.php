<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'phone',
        'code',
        'score',
        'image',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'expires_in' => 'datetime',
            'score' => 'integer',
        ];
    }

    /**
     * Get all questions created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get all questions published by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publishedQuestions()
    {
        return $this->hasMany(Question::class, 'published_by');
    }

    /**
     * Get all answers created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get all answers published by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publishedAnswers()
    {
        return $this->hasMany(Answer::class, 'published_by');
    }

    /**
     * Get all comments created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all comments published by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publishedComments()
    {
        return $this->hasMany(Comment::class, 'published_by');
    }

    /**
     * Get all votes by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get all verifications by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    /**
     * The levels that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }
}
