<?php

namespace App\Repositories;

use App\Models\Content;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentRepository
{
    /**
     * Get filtered content with pagination.
     */
    public function getFilteredContent(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Content::query()->active()->with(['creator']);

        // Apply filters
        if (!empty($filters['subject'])) {
            $query->subject($filters['subject']);
        }

        if (!empty($filters['grade_level'])) {
            $query->gradeLevel($filters['grade_level']);
        }

        if (!empty($filters['content_type'])) {
            $query->contentType($filters['content_type']);
        }

        if (!empty($filters['target_learning_style'])) {
            $query->learningStyle($filters['target_learning_style']);
        }

        if (!empty($filters['difficulty_level'])) {
            $query->difficultyLevel($filters['difficulty_level']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['topic'])) {
            $query->where('topic', 'LIKE', "%{$filters['topic']}%");
        }

        // Default ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Get content recommendations for a student.
     */
    public function getRecommendationsForStudent(Student $student, int $limit = 10): Collection
    {
        $query = Content::query()->active();

        // Filter by student's grade level
        $query->gradeLevel($student->grade_level);

        // Filter by learning style if available
        $learningProfile = $student->learningStyleProfile;
        if ($learningProfile) {
            $query->learningStyle($learningProfile->dominant_style);
        }

        // Exclude completed content
        $completedContentIds = $student->learningActivities()
            ->where('activity_type', 'complete')
            ->pluck('content_id')
            ->toArray();

        if (!empty($completedContentIds)) {
            $query->whereNotIn('id', $completedContentIds);
        }

        // Prioritize content based on performance
        $avgScore = $student->assessments()
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('percentage');

        if ($avgScore !== null) {
            if ($avgScore >= 80) {
                $query->difficultyLevel('advanced');
            } elseif ($avgScore >= 60) {
                $query->difficultyLevel('intermediate');
            } else {
                $query->difficultyLevel('beginner');
            }
        }

        return $query
            ->orderBy('rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit * 2) // Get more for AI filtering
            ->get();
    }

    /**
     * Get popular content by grade level.
     */
    public function getPopularContent(string $gradeLevel, int $limit = 10): Collection
    {
        return Content::query()
            ->active()
            ->gradeLevel($gradeLevel)
            ->orderBy('views_count', 'desc')
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently added content.
     */
    public function getRecentContent(string $gradeLevel = null, int $limit = 10): Collection
    {
        $query = Content::query()->active();

        if ($gradeLevel) {
            $query->gradeLevel($gradeLevel);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get content by topic.
     */
    public function getByTopic(string $topic, string $gradeLevel = null): Collection
    {
        $query = Content::query()
            ->active()
            ->where('topic', 'LIKE', "%{$topic}%");

        if ($gradeLevel) {
            $query->gradeLevel($gradeLevel);
        }

        return $query
            ->orderBy('rating', 'desc')
            ->get();
    }

    /**
     * Get content for teacher by subject.
     */
    public function getTeacherContent(string $subject, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Content::query()
            ->with(['creator'])
            ->subject($subject);

        // Apply additional filters
        if (!empty($filters['grade_level'])) {
            $query->gradeLevel($filters['grade_level']);
        }

        if (!empty($filters['content_type'])) {
            $query->contentType($filters['content_type']);
        }

        if (!empty($filters['is_active'])) {
            if ($filters['is_active'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['is_active'] === 'inactive') {
                $query->where('is_active', false);
            }
        } else {
            // Default to all content for teachers
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get content statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_content' => Content::count(),
            'active_content' => Content::where('is_active', true)->count(),
            'by_type' => Content::active()
                ->selectRaw('content_type, COUNT(*) as count')
                ->groupBy('content_type')
                ->pluck('count', 'content_type')
                ->toArray(),
            'by_grade' => Content::active()
                ->selectRaw('grade_level, COUNT(*) as count')
                ->groupBy('grade_level')
                ->pluck('count', 'grade_level')
                ->toArray(),
            'by_subject' => Content::active()
                ->selectRaw('subject, COUNT(*) as count')
                ->groupBy('subject')
                ->pluck('count', 'subject')
                ->toArray(),
            'total_views' => Content::active()->sum('views_count'),
            'avg_rating' => Content::active()->avg('rating'),
        ];
    }

    /**
     * Create new content.
     */
    public function create(array $data): Content
    {
        return Content::create($data);
    }

    /**
     * Update content.
     */
    public function update(Content $content, array $data): bool
    {
        return $content->update($data);
    }

    /**
     * Delete content.
     */
    public function delete(Content $content): bool
    {
        return $content->delete();
    }

    /**
     * Find content by ID with relationships.
     */
    public function findWithRelations(int $id): ?Content
    {
        return Content::with(['creator', 'learningActivities'])->find($id);
    }

    /**
     * Get content for specific learning style.
     */
    public function getByLearningStyle(string $style, string $gradeLevel, int $limit = 10): Collection
    {
        return Content::query()
            ->active()
            ->learningStyle($style)
            ->gradeLevel($gradeLevel)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search content with advanced filters.
     */
    public function advancedSearch(array $criteria): Collection
    {
        $query = Content::query()->active();

        foreach ($criteria as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'title':
                    case 'topic':
                    case 'description':
                        $query->where($field, 'LIKE', "%{$value}%");
                        break;
                    case 'tags':
                        $query->where('metadata->tags', 'LIKE', "%{$value}%");
                        break;
                    default:
                        $query->where($field, $value);
                        break;
                }
            }
        }

        return $query->get();
    }
}