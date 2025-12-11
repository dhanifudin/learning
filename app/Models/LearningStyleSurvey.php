<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningStyleSurvey extends Model
{
    protected $fillable = [
        'title',
        'description',
        'version',
        'language',
        'questions',
        'scoring_rules',
        'time_limit_minutes',
        'is_active',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'scoring_rules' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'survey_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeForLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    public function getQuestionsByCategory(): array
    {
        $categorized = [
            'visual' => [],
            'auditory' => [],
            'kinesthetic' => []
        ];

        foreach ($this->questions as $question) {
            if (isset($question['category'])) {
                $categorized[$question['category']][] = $question;
            }
        }

        return $categorized;
    }

    public function getTotalQuestions(): int
    {
        return count($this->questions);
    }
}
