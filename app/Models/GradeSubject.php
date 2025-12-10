<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_level',
        'subject_code',
        'subject_name_id',
        'subject_name_en',
        'category',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    /**
     * Get localized subject name.
     */
    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'id' ? $this->subject_name_id : $this->subject_name_en;
    }

    /**
     * Get formatted grade level display.
     */
    public function getFormattedGradeAttribute(): string
    {
        return "Kelas {$this->grade_level}";
    }

    /**
     * Get formatted category display.
     */
    public function getFormattedCategoryAttribute(): string
    {
        return match($this->category) {
            'wajib' => 'Mata Pelajaran Wajib',
            'peminatan' => 'Mata Pelajaran Peminatan',
            'lintas_minat' => 'Mata Pelajaran Lintas Minat',
            default => $this->category
        };
    }

    /**
     * Scope for active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific grade level.
     */
    public function scopeGradeLevel($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope for specific category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope ordered by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
