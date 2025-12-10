<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_number',
        'subject',
        'department',
    ];

    /**
     * Get the user that owns the teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get contents created by this teacher.
     */
    public function createdContents(): HasMany
    {
        return $this->hasMany(Content::class, 'created_by', 'user_id');
    }

    /**
     * Get teacher's full name from user relationship.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /**
     * Get teacher's email from user relationship.
     */
    public function getEmailAttribute(): string
    {
        return $this->user->email ?? '';
    }

    /**
     * Get formatted subject display.
     */
    public function getFormattedSubjectAttribute(): string
    {
        return ucfirst($this->subject);
    }

    /**
     * Scope for specific subject.
     */
    public function scopeSubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope for specific department.
     */
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }
}