<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class QuestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Question $question): bool
    {
        if ($user) {
            if ($question->user->is($user) || ($user->level > $question->user->level)) {
                return true;
            }
        }

        return $question->published;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question): bool
    {
        return $question->user->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): bool
    {
        return $question->user->is($user) || $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Question $question): bool
    {
        // Cannot publish if already published
        if ($question->published) {
            return false;
        }

        // Higher level users can publish questions from lower level users
        return $user->level > $question->user->level;
    }

    /**
     * Determine whether the user can feature the model.
     */
    public function feature(User $user, Question $question): bool
    {
        if ($question->featured) {
            return false;
        }

        if ($question->user->is($user)) {
            return false;
        } elseif (($user->level > $question->user->level) && $question->user->isNot($user)) {
            return true;
        }

        return false;
    }
}
