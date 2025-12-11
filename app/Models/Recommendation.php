<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'content_id',
        'recommendation_type',
        'relevance_score',
        'reason',
        'algorithm_version',
        'is_viewed',
        'is_completed',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'relevance_score' => 'decimal:2',
            'is_viewed' => 'boolean',
            'is_completed' => 'boolean',
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * Get the student that owns this recommendation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the content that is recommended.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Scope for specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for viewed recommendations.
     */
    public function scopeViewed($query)
    {
        return $query->where('is_viewed', true);
    }

    /**
     * Scope for completed recommendations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for recent recommendations.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as viewed.
     */
    public function markAsViewed(): void
    {
        $this->update([
            'is_viewed' => true,
            'viewed_at' => now(),
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['is_completed' => true]);
    }
}
