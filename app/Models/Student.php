<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'grade_level',
        'class',
        'major',
        'learning_interests',
        'enrollment_year',
        'status',
        'profile_completed',
        'preferred_language',
    ];

    protected function casts(): array
    {
        return [
            'learning_interests' => 'array',
            'profile_completed' => 'boolean',
            'enrollment_year' => 'integer',
        ];
    }

    /**
     * Get the user that owns the student profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student's learning style profile.
     */
    public function learningStyleProfile(): HasOne
    {
        return $this->hasOne(LearningStyleProfile::class);
    }

    /**
     * Get all learning style profiles for this student (historical).
     */
    public function learningStyleProfiles(): HasMany
    {
        return $this->hasMany(LearningStyleProfile::class);
    }

    /**
     * Get all survey responses for this student.
     */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get all learning activities for this student.
     */
    public function learningActivities(): HasMany
    {
        return $this->hasMany(LearningActivity::class);
    }

    /**
     * Get all recommendations for this student.
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Get all assessments for this student.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Get all feedback logs for this student.
     */
    public function feedbackLogs(): HasMany
    {
        return $this->hasMany(FeedbackLog::class);
    }

    /**
     * Get all performance predictions for this student.
     */
    public function performancePredictions(): HasMany
    {
        return $this->hasMany(PerformancePrediction::class);
    }

    /**
     * Get all competency maps for this student.
     */
    public function competencyMaps(): HasMany
    {
        return $this->hasMany(CompetencyMap::class);
    }

    /**
     * Get all learning analytics for this student.
     */
    public function learningAnalytics(): HasMany
    {
        return $this->hasMany(LearningAnalytic::class);
    }

    /**
     * Check if student profile is completed.
     */
    public function isProfileCompleted(): bool
    {
        return $this->profile_completed;
    }

    /**
     * Check if student is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get student's full name from user relationship.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /**
     * Get student's email from user relationship.
     */
    public function getEmailAttribute(): string
    {
        return $this->user->email ?? '';
    }

    /**
     * Get formatted class display.
     */
    public function getFormattedClassAttribute(): string
    {
        return "Kelas {$this->class}";
    }

    /**
     * Scope for active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific grade level.
     */
    public function scopeGradeLevel($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope for completed profiles.
     */
    public function scopeCompletedProfile($query)
    {
        return $query->where('profile_completed', true);
    }
}