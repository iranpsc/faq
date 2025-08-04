<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
    private function canMarkCorrectness(User $user, Answer $answer): bool
    {
        // Rule 1: Must be level 4 or above
        if ($user->level < 4) {
            return false;
        }

        // Rule 2: Cannot mark own answers
        if ($user->id === $answer->user_id) {
            return false;
        }

        // Rule 3: Check if user has remaining quota
        $usedMarks = $user->correctnessMarks()->count();
        $existingMark = $answer->correctnessMarks()
            ->where('marker_user_id', $user->id)
            ->first();

        // If changing existing mark, don't count against quota
        if (!$existingMark && $usedMarks >= $user->level) {
            return false;
        }

        // Rule 4: Check if user can override existing marks
        $highestLevelMark = $answer->correctnessMarks()
            ->join('users', 'users.id', '=', 'answer_correctness_marks.marker_user_id')
            ->where('marker_user_id', '!=', $user->id)
            ->orderBy('users.level', 'desc')
            ->first();

        // If there's a higher or equal level mark and user doesn't have own mark, deny
        if ($highestLevelMark && !$existingMark && $user->level <= $highestLevelMark->level) {
            return false;
        }

        return true;
    }
}
