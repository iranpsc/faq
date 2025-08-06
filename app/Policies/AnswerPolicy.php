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

        // User can not publish their own answers
        if ($answer->user->is($user)) {
            return false;
        }

        // Higher level users can publish answers from lower level users
        return ($user->level > 2) && ($user->level > $answer->user->level);
    }

    /**
     * Check if user can toggle correctness of answer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Answer  $answer
     * @param  string  $action
     * @return bool
     */
    public function toggleCorrectness(User $user, Answer $answer, string $action): bool
    {
        // Rule 1: User cannot toggle their own answer's correctness
        if ($answer->user->is($user)) {
            return false;
        }

        // Rule 2: If action is 'markAsCorrect', user level must be higher than 3
        if ($action === 'markAsCorrect' && $user->level <= 3) {
            return false;
        }

        // Rule 3: If action is 'markAsIncorrect', user level must be higher than 4
        if ($action === 'markAsIncorrect' && $user->level <= 4) {
            return false;
        }

        // Rule 4: If user already marked this answer as correct or incorrect,
        // They cannot mark it again
        $questionMarkCorrectnessExists = $user->markedAnswers()->where('answer_id', $answer->id)->exists();

        if($questionMarkCorrectnessExists) {
            return false;
        }

        return true;
    }
}
