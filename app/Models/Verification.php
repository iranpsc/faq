<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'verifiable_type', 'verifiable_id'];

    /**
     * Get the user that owns the verification.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent verifiable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function verifiable()
    {
        return $this->morphTo();
    }
}
