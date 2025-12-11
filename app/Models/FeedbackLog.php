<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackLog extends Model
{
    protected $fillable = [
        'student_id',
        'assessment_id',
        'feedback_type',
        'feedback_text',
        'action_items',
        'sentiment',
        'is_read',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'action_items' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('feedback_type', $type);
    }

    public function scopeBySentiment($query, $sentiment)
    {
        return $query->where('sentiment', $sentiment);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getActionItemsByPriority(): array
    {
        if (!$this->action_items) {
            return [];
        }

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        
        return collect($this->action_items)
            ->sortBy(fn($item) => $priorityOrder[$item['priority'] ?? 'medium'])
            ->values()
            ->toArray();
    }
}
