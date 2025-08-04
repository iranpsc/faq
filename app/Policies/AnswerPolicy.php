<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class AnswerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Answer $answer)
    {
        return $answer->user->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Answer $answer)
    {
        return $answer->user->is($user) || $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Answer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function publish(User $user, Answer $answer)
    {
        // Cannot publish if already published
        if ($answer->published) {
            return false;
        }

        // Higher level users can publish answers from lower level users
        return $user->level > $answer->user->level;
    }

    /**
     * Check if user can mark answer correctness.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Answer  $answer
     * @return bool
     */
    public function canMarkCorrectness(User $user, Answer $answer): bool
    {
        // Rule 1: Must be level 4 or above
        if ($user->level < 4) {
            return false;
        }

        // Rule 2: Cannot mark own answers
        if ($answer->user->is($user)) {
            return false;
        }

        // Check if the user has already marked this answer
        $existingMark = $answer->correctnessMarks()
            ->where('marker_user_id', $user->id)
            ->exists();

        // If user has already marked this answer, they can't mark it again
        if ($existingMark) {
            return false;
        }

        // Rule 3: Check if user has remaining quota
        $usedMarks = $user->correctnessMarks()->count();

        // If user is running out of remaining quota, they can't mark correctness
        if ($usedMarks >= $user->level) {
            return false;
        }

        return true;
    }
}
