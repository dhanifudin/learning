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
     * Get all learning activities for this student.
     */
    public function learningActivities(): HasMany
    {
        return $this->hasMany(LearningActivity::class);
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