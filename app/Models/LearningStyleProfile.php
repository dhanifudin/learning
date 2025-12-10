<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningStyleProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'visual_score',
        'auditory_score',
        'kinesthetic_score',
        'dominant_style',
        'survey_data',
        'analysis_date',
        'ai_confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'visual_score' => 'decimal:2',
            'auditory_score' => 'decimal:2',
            'kinesthetic_score' => 'decimal:2',
            'ai_confidence_score' => 'decimal:2',
            'survey_data' => 'array',
            'analysis_date' => 'datetime',
        ];
    }

    /**
     * Get the student that owns this learning style profile.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the highest scoring learning style.
     */
    public function getDominantStyleAttribute(): string
    {
        $scores = [
            'visual' => $this->visual_score,
            'auditory' => $this->auditory_score,
            'kinesthetic' => $this->kinesthetic_score,
        ];

        $maxScore = max($scores);
        $dominantStyles = array_keys($scores, $maxScore);

        // If multiple styles have the same max score, it's mixed
        return count($dominantStyles) > 1 ? 'mixed' : $dominantStyles[0];
    }

    /**
     * Get learning style scores as array.
     */
    public function getStyleScoresAttribute(): array
    {
        return [
            'visual' => $this->visual_score,
            'auditory' => $this->auditory_score,
            'kinesthetic' => $this->kinesthetic_score,
        ];
    }

    /**
     * Check if the profile analysis is highly confident.
     */
    public function isHighConfidence(): bool
    {
        return $this->ai_confidence_score >= 80;
    }

    /**
     * Get learning style preferences as percentages.
     */
    public function getStylePercentagesAttribute(): array
    {
        $total = $this->visual_score + $this->auditory_score + $this->kinesthetic_score;
        
        if ($total == 0) {
            return ['visual' => 0, 'auditory' => 0, 'kinesthetic' => 0];
        }

        return [
            'visual' => round(($this->visual_score / $total) * 100, 1),
            'auditory' => round(($this->auditory_score / $total) * 100, 1),
            'kinesthetic' => round(($this->kinesthetic_score / $total) * 100, 1),
        ];
    }

    /**
     * Scope for specific dominant style.
     */
    public function scopeDominantStyle($query, $style)
    {
        return $query->where('dominant_style', $style);
    }

    /**
     * Scope for high confidence profiles.
     */
    public function scopeHighConfidence($query)
    {
        return $query->where('ai_confidence_score', '>=', 80);
    }
}