<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningActivity extends Model
{
    use HasFactory;

    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'student_id',
        'content_id',
        'activity_type',
        'duration_seconds',
        'session_id',
        'device_type',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the student that performed this activity.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the content related to this activity.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '0 detik';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours} jam";
        if ($minutes > 0) $parts[] = "{$minutes} menit";
        if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds} detik";

        return implode(' ', $parts);
    }

    /**
     * Check if activity is a completion.
     */
    public function isComplete(): bool
    {
        return $this->activity_type === 'complete';
    }

    /**
     * Scope for specific activity type.
     */
    public function scopeActivityType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for specific session.
     */
    public function scopeSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope for activities within date range.
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
