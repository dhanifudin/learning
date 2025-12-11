<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAnalytic extends Model
{
    protected $fillable = [
        'student_id',
        'metric_type',
        'metric_value',
        'calculation_date',
        'context_data',
        'aggregation_period',
    ];

    protected $casts = [
        'metric_value' => 'decimal:4',
        'calculation_date' => 'date',
        'context_data' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByMetricType($query, $metricType)
    {
        return $query->where('metric_type', $metricType);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('aggregation_period', $period);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('calculation_date', [$startDate, $endDate]);
    }
}
