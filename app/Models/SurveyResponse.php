<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'student_id',
        'responses',
        'calculated_scores',
        'status',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'session_id',
        'metadata',
    ];

    protected $casts = [
        'responses' => 'array',
        'calculated_scores' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(LearningStyleSurvey::class, 'survey_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['started', 'in_progress']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getCompletionPercentage(): float
    {
        if (!$this->responses || !$this->survey) {
            return 0;
        }

        $totalQuestions = $this->survey->getTotalQuestions();
        $answeredQuestions = count($this->responses);

        return $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;
    }

    public function calculateScores(): array
    {
        $scores = [
            'visual' => 0,
            'auditory' => 0,
            'kinesthetic' => 0
        ];

        if (!$this->responses || !$this->survey) {
            return $scores;
        }

        $questions = $this->survey->questions;
        
        foreach ($this->responses as $questionId => $answer) {
            $question = collect($questions)->firstWhere('id', $questionId);
            
            if ($question && isset($question['category']) && isset($scores[$question['category']])) {
                $scores[$question['category']] += (int) $answer;
            }
        }

        // Normalize scores to percentage
        $maxScore = 5; // Assuming 5-point Likert scale
        $questionsPerCategory = count($questions) / 3; // Assuming equal distribution
        
        foreach ($scores as $category => $score) {
            $scores[$category] = ($score / ($maxScore * $questionsPerCategory)) * 100;
        }

        return $scores;
    }

    public function getDominantLearningStyle(): string
    {
        $scores = $this->calculated_scores ?: $this->calculateScores();
        
        if (empty($scores)) {
            return 'unknown';
        }

        $maxScore = max($scores);
        $dominantStyles = array_keys($scores, $maxScore);

        if (count($dominantStyles) > 1) {
            return 'mixed';
        }

        return $dominantStyles[0];
    }
}
