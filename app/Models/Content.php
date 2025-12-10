<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject',
        'topic',
        'grade_level',
        'content_type',
        'target_learning_style',
        'difficulty_level',
        'file_url',
        'external_url',
        'thumbnail_url',
        'duration_minutes',
        'metadata',
        'views_count',
        'rating',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'views_count' => 'integer',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * Get the user who created this content.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get learning activities for this content.
     */
    public function learningActivities(): HasMany
    {
        return $this->hasMany(LearningActivity::class);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return sprintf('%d jam %d menit', $hours, $minutes);
        }

        return sprintf('%d menit', $minutes);
    }

    /**
     * Get content URL (prioritize file_url over external_url).
     */
    public function getContentUrlAttribute(): ?string
    {
        return $this->file_url ?: $this->external_url;
    }

    /**
     * Check if content has media file.
     */
    public function hasMediaFile(): bool
    {
        return !empty($this->file_url) || !empty($this->external_url);
    }

    /**
     * Check if content is suitable for learning style.
     */
    public function isSuitableForStyle(string $learningStyle): bool
    {
        return $this->target_learning_style === 'all' || $this->target_learning_style === $learningStyle;
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Scope for active content.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific subject.
     */
    public function scopeSubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope for specific grade level.
     */
    public function scopeGradeLevel($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope for specific content type.
     */
    public function scopeContentType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope for specific learning style.
     */
    public function scopeLearningStyle($query, $style)
    {
        return $query->where(function ($q) use ($style) {
            $q->where('target_learning_style', $style)
              ->orWhere('target_learning_style', 'all');
        });
    }

    /**
     * Scope for specific difficulty level.
     */
    public function scopeDifficultyLevel($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope for search by title or topic.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('topic', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }
}