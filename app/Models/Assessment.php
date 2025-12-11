<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject',
        'topic',
        'assessment_type',
        'score',
        'max_score',
        'percentage',
        'difficulty_level',
        'time_taken_seconds',
        'submission_data',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'time_taken_seconds' => 'integer',
            'submission_data' => 'array',
            'graded_at' => 'datetime',
        ];
    }

    /**
     * Get the student that owns this assessment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who graded this assessment.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if assessment is passed.
     */
    public function isPassed(float $passingGrade = 60): bool
    {
        return $this->percentage >= $passingGrade;
    }

    /**
     * Get formatted time taken.
     */
    public function getFormattedTimeAttribute(): string
    {
        if (!$this->time_taken_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->time_taken_seconds / 60);
        $seconds = $this->time_taken_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get grade letter based on percentage.
     */
    public function getGradeLetterAttribute(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'E';
    }

    /**
     * Scope for specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for specific subject.
     */
    public function scopeSubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope for recent assessments.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for passed assessments.
     */
    public function scopePassed($query, $passingGrade = 60)
    {
        return $query->where('percentage', '>=', $passingGrade);
    }

    /**
     * Scope for failed assessments.
     */
    public function scopeFailed($query, $passingGrade = 60)
    {
        return $query->where('percentage', '<', $passingGrade);
    }
}
