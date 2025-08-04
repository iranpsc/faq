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
        // Check if user has reached the 2-feature limit
        if ($user->featuredQuestions()->count() >= 2) {
            return false;
        }

        // Check if question is already featured by this user
        if ($user->featuredQuestions()->where('question_id', $question->id)->exists()) {
            return false;
        }

        // User can not feature their own questions
        if ($question->user->is($user)) {
            return false;
        }

        // User can feature questions from users with lower levels
        if (($user->level > 4) && ($user->level > $question->user->level)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can unfeature the model.
     */
    public function unfeature(User $user, Question $question): bool
    {
        if($user->lelvel < 4 && $question->user->isNot($user)) {
            // Only higher level users can unfeature questions
            return false;
        }

        // Check if user has reached the 2-action limit
        if ($user->featuredQuestions()->count() >= 2) {
            return false;
        }

        // Check if this user has featured the question
        $userFeaturedQuestion = $user->featuredQuestions()->where('question_id', $question->id)->exists();

        if ($userFeaturedQuestion) {
            return true;
        }

        // Higher level users can unfeature questions featured by lower level users
        $lowerLevelFeaturedUsers = $question->featuredByUsers()
            ->where('level', '<', $user->level)
            ->exists();

        return $lowerLevelFeaturedUsers;
    }
}
