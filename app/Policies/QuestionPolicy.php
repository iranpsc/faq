<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return true;
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
        return $question->user->is($user);
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }

    /**
     * Determine whether the user can pin the model.
     */
    public function pin(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }
    /**
     * Determine whether the user can feature the model.
     */
    public function feature(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }
}
