<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringRule extends Model
{
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['name', 'points'];

    /**
     * Get the attributes that should be cast to native types.
     * @return array
     */
    protected function casts()
    {
        return [
            'points' => 'integer',
        ];
    }

    /**
     * Get the Persian name of the scoring rule.
     * @return string
     */
    public function getPersianNameAttribute()
    {
        return __($this->attributes['name']);
    }
}
