<?php

namespace App\Policies;

use App\Models\LearningStyleSurvey;
use App\Models\User;

class LearningStyleSurveyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'teacher';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LearningStyleSurvey $survey): bool
    {
        // Students can view active, published surveys
        if ($user->role === 'student') {
            return $survey->is_active && 
                   $survey->published_at && 
                   $survey->published_at <= now();
        }

        // Admins and teachers can view all surveys
        return $user->role === 'admin' || $user->role === 'teacher';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LearningStyleSurvey $survey): bool
    {
        // Only admins or the creator can update
        return $user->role === 'admin' || $survey->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LearningStyleSurvey $survey): bool
    {
        // Only admins can delete surveys
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can take the survey.
     */
    public function take(User $user, LearningStyleSurvey $survey): bool
    {
        // Only students can take surveys
        if ($user->role !== 'student' || !$user->student) {
            return false;
        }

        // Survey must be active and published
        if (!$survey->is_active || !$survey->published_at || $survey->published_at > now()) {
            return false;
        }

        // Check if student has already completed this survey
        $existingResponse = $user->student->surveyResponses()
            ->where('survey_id', $survey->id)
            ->completed()
            ->exists();

        return !$existingResponse;
    }

    /**
     * Determine whether the user can view survey results.
     */
    public function viewResults(User $user, LearningStyleSurvey $survey): bool
    {
        // Students can view results if they've completed the survey
        if ($user->role === 'student' && $user->student) {
            return $user->student->surveyResponses()
                ->where('survey_id', $survey->id)
                ->completed()
                ->exists();
        }

        // Admins and teachers can view all results
        return $user->role === 'admin' || $user->role === 'teacher';
    }

    /**
     * Determine whether the user can manage survey status.
     */
    public function toggleStatus(User $user, LearningStyleSurvey $survey): bool
    {
        return $user->role === 'admin';
    }
}