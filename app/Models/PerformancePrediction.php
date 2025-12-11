<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformancePrediction extends Model
{
    protected $fillable = [
        'student_id',
        'prediction_type',
        'predicted_value',
        'confidence_score',
        'contributing_factors',
        'prediction_date',
        'target_date',
        'actual_outcome',
        'accuracy_score',
    ];

    protected $casts = [
        'predicted_value' => 'decimal:4',
        'confidence_score' => 'decimal:4',
        'contributing_factors' => 'array',
        'prediction_date' => 'datetime',
        'target_date' => 'date',
        'actual_outcome' => 'decimal:4',
        'accuracy_score' => 'decimal:4',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('prediction_type', $type);
    }

    public function scopePending($query)
    {
        return $query->whereNull('actual_outcome');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('actual_outcome');
    }

    public function scopeHighConfidence($query, $threshold = 0.8)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function calculateAccuracy(): ?float
    {
        if ($this->actual_outcome === null || $this->predicted_value === null) {
            return null;
        }

        $difference = abs($this->actual_outcome - $this->predicted_value);
        $range = max($this->actual_outcome, $this->predicted_value);
        
        if ($range == 0) {
            return 1.0;
        }
        
        return max(0, 1 - ($difference / $range));
    }

    public function updateActualOutcome(float $actualValue): void
    {
        $this->actual_outcome = $actualValue;
        $this->accuracy_score = $this->calculateAccuracy();
        $this->save();
    }
}
