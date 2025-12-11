<?php

namespace App\Services;

use App\Models\Content;
use App\Models\LearningActivity;
use App\Models\Student;
use App\Repositories\ContentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentService
{
    public function __construct(
        private ContentRepository $contentRepository
    ) {}

    /**
     * Get filtered content for display.
     */
    public function getFilteredContent(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->contentRepository->getFilteredContent($filters, $perPage);
    }

    /**
     * Get content recommendations for student.
     */
    public function getRecommendationsForStudent(Student $student, int $limit = 10): Collection
    {
        $candidateContent = $this->contentRepository->getRecommendationsForStudent($student, $limit);
        
        // If we have few candidates, supplement with popular content
        if ($candidateContent->count() < $limit) {
            $popularContent = $this->contentRepository->getPopularContent($student->grade_level, $limit);
            $candidateContent = $candidateContent->merge($popularContent)->unique('id');
        }

        return $candidateContent->take($limit);
    }

    /**
     * Create new content.
     */
    public function createContent(array $data, ?UploadedFile $file = null, ?UploadedFile $thumbnail = null): Content
    {
        // Handle file upload
        if ($file) {
            $data['file_url'] = $this->uploadContentFile($file);
        }

        // Handle thumbnail upload
        if ($thumbnail) {
            $data['thumbnail_url'] = $this->uploadThumbnail($thumbnail);
        }

        // Set creator
        $data['created_by'] = Auth::id();

        // Set defaults
        $data['views_count'] = 0;
        $data['rating'] = 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->contentRepository->create($data);
    }

    /**
     * Update existing content.
     */
    public function updateContent(Content $content, array $data, ?UploadedFile $file = null, ?UploadedFile $thumbnail = null): bool
    {
        // Handle file replacement
        if ($file) {
            // Delete old file if exists
            if ($content->file_url) {
                $this->deleteContentFile($content->file_url);
            }
            $data['file_url'] = $this->uploadContentFile($file);
        }

        // Handle thumbnail replacement
        if ($thumbnail) {
            // Delete old thumbnail if exists
            if ($content->thumbnail_url) {
                $this->deleteThumbnail($content->thumbnail_url);
            }
            $data['thumbnail_url'] = $this->uploadThumbnail($thumbnail);
        }

        return $this->contentRepository->update($content, $data);
    }

    /**
     * Delete content and associated files.
     */
    public function deleteContent(Content $content): bool
    {
        // Delete associated files
        if ($content->file_url) {
            $this->deleteContentFile($content->file_url);
        }

        if ($content->thumbnail_url) {
            $this->deleteThumbnail($content->thumbnail_url);
        }

        return $this->contentRepository->delete($content);
    }

    /**
     * Record learning activity.
     */
    public function recordActivity(Student $student, Content $content, string $activityType, array $metadata = []): LearningActivity
    {
        $sessionId = session()->getId();
        
        // Calculate duration for 'complete' activities
        $duration = 0;
        if ($activityType === 'complete' && $content->duration_minutes) {
            $duration = $content->duration_minutes * 60; // Convert to seconds
        }

        $activity = LearningActivity::create([
            'student_id' => $student->id,
            'content_id' => $content->id,
            'activity_type' => $activityType,
            'duration_seconds' => $duration,
            'session_id' => $sessionId,
            'device_type' => $this->detectDeviceType(),
            'ip_address' => request()->ip(),
            'metadata' => $metadata,
        ]);

        // Update content views for 'view' activities
        if ($activityType === 'view') {
            $content->incrementViews();
        }

        return $activity;
    }

    /**
     * Get content with view tracking.
     */
    public function getContentForViewing(int $contentId, Student $student): ?Content
    {
        $content = $this->contentRepository->findWithRelations($contentId);
        
        if ($content) {
            // Record view activity
            $this->recordActivity($student, $content, 'view');
        }

        return $content;
    }

    /**
     * Rate content.
     */
    public function rateContent(Content $content, float $rating): void
    {
        // For simplicity, using a basic average rating system
        // In production, you might want a separate ratings table
        $currentRating = $content->rating ?? 0;
        $currentViews = $content->views_count;
        
        if ($currentViews > 0) {
            $newRating = (($currentRating * $currentViews) + $rating) / ($currentViews + 1);
        } else {
            $newRating = $rating;
        }

        $content->update(['rating' => round($newRating, 2)]);
    }

    /**
     * Get content statistics.
     */
    public function getStatistics(): array
    {
        return $this->contentRepository->getStatistics();
    }

    /**
     * Search content.
     */
    public function searchContent(string $query, array $filters = []): Collection
    {
        $filters['search'] = $query;
        return $this->contentRepository->getFilteredContent($filters, 100)->getCollection();
    }

    /**
     * Get content by subject and grade.
     */
    public function getContentBySubjectAndGrade(string $subject, string $grade, array $filters = []): LengthAwarePaginator
    {
        $filters['subject'] = $subject;
        $filters['grade_level'] = $grade;
        
        return $this->contentRepository->getFilteredContent($filters);
    }

    /**
     * Upload content file.
     */
    private function uploadContentFile(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('content/files', $filename, 'public');
        
        return Storage::url($path);
    }

    /**
     * Upload thumbnail.
     */
    private function uploadThumbnail(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('content/thumbnails', $filename, 'public');
        
        return Storage::url($path);
    }

    /**
     * Delete content file.
     */
    private function deleteContentFile(string $url): void
    {
        $path = str_replace('/storage/', '', $url);
        Storage::disk('public')->delete($path);
    }

    /**
     * Delete thumbnail.
     */
    private function deleteThumbnail(string $url): void
    {
        $path = str_replace('/storage/', '', $url);
        Storage::disk('public')->delete($path);
    }

    /**
     * Detect device type from user agent.
     */
    private function detectDeviceType(): string
    {
        $userAgent = request()->userAgent();
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get content suitable for learning style.
     */
    public function getContentForLearningStyle(string $style, string $gradeLevel, int $limit = 10): Collection
    {
        return $this->contentRepository->getByLearningStyle($style, $gradeLevel, $limit);
    }

    /**
     * Get recent content.
     */
    public function getRecentContent(string $gradeLevel = null, int $limit = 10): Collection
    {
        return $this->contentRepository->getRecentContent($gradeLevel, $limit);
    }

    /**
     * Get content by topic.
     */
    public function getContentByTopic(string $topic, string $gradeLevel = null): Collection
    {
        return $this->contentRepository->getByTopic($topic, $gradeLevel);
    }

    /**
     * Get teacher's content.
     */
    public function getTeacherContent(string $subject, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->contentRepository->getTeacherContent($subject, $filters, $perPage);
    }

    /**
     * Toggle content active status.
     */
    public function toggleContentStatus(Content $content): bool
    {
        return $content->update(['is_active' => !$content->is_active]);
    }

    /**
     * Duplicate content.
     */
    public function duplicateContent(Content $originalContent, array $updates = []): Content
    {
        $data = $originalContent->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        
        // Update title to indicate it's a copy
        $data['title'] = $updates['title'] ?? ($data['title'] . ' (Copy)');
        $data['is_active'] = false; // New copies start as inactive
        $data['views_count'] = 0;
        $data['rating'] = 0;
        $data['created_by'] = Auth::id();

        // Merge any additional updates
        $data = array_merge($data, $updates);

        return $this->contentRepository->create($data);
    }
}