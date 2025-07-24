<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'votable_type', 'votable_id', 'type'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    /**
     * Get the user that owns the vote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent votable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function votable()
    {
        return $this->morphTo();
    }
}
