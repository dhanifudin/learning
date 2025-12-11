<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Recommendation;
use App\Models\Student;
use App\Repositories\ContentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecommendationEngine
{
    public function __construct(
        private ContentRepository $contentRepository,
        private GeminiAIService $geminiService
    ) {}

    /**
     * Generate personalized recommendations for a student.
     */
    public function generateRecommendations(Student $student, int $limit = 10): Collection
    {
        $cacheKey = "recommendations_student_{$student->id}_{$limit}";
        
        return Cache::remember($cacheKey, 3600, function () use ($student, $limit) {
            try {
                // Get candidate content
                $candidateContents = $this->getCandidateContent($student, $limit * 3);
                
                if ($candidateContents->isEmpty()) {
                    return $this->getFallbackRecommendations($student, $limit);
                }

                // Use AI to score and rank content
                $scoredContents = $this->scoreContentWithAI($student, $candidateContents);
                
                // Save recommendations to database
                $this->saveRecommendations($student, $scoredContents);
                
                return $scoredContents->take($limit);
            } catch (\Exception $e) {
                Log::error('Recommendation generation failed', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getFallbackRecommendations($student, $limit);
            }
        });
    }

    /**
     * Get candidate content for recommendations.
     */
    private function getCandidateContent(Student $student, int $limit): Collection
    {
        $query = Content::query()->active();

        // Filter by grade level
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

        // Consider student's performance for difficulty level
        $avgScore = $this->getStudentAverageScore($student);
        if ($avgScore !== null) {
            $difficulty = $this->mapScoreToDifficulty($avgScore);
            $query->difficultyLevel($difficulty);
        }

        // Include subjects from student interests
        if (!empty($student->learning_interests)) {
            $query->whereIn('subject', $student->learning_interests);
        }

        return $query
            ->orderBy('rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Score content using AI service.
     */
    private function scoreContentWithAI(Student $student, Collection $contents): Collection
    {
        $studentProfile = $this->buildStudentProfile($student);
        $contentData = $contents->map(function ($content) {
            return [
                'id' => $content->id,
                'title' => $content->title,
                'description' => $content->description,
                'subject' => $content->subject,
                'topic' => $content->topic,
                'content_type' => $content->content_type,
                'target_learning_style' => $content->target_learning_style,
                'difficulty_level' => $content->difficulty_level,
                'rating' => $content->rating,
                'views_count' => $content->views_count,
            ];
        })->toArray();

        try {
            $recommendations = $this->geminiService->generateRecommendations(
                $studentProfile,
                $contentData,
                $student->preferred_language ?? 'id'
            );

            return $this->mapAIRecommendations($contents, $recommendations);
        } catch (\Exception $e) {
            Log::warning('AI recommendation scoring failed, using fallback', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->scoreContentWithRules($student, $contents);
        }
    }

    /**
     * Map AI recommendations to content collection.
     */
    private function mapAIRecommendations(Collection $contents, array $recommendations): Collection
    {
        $contentMap = $contents->keyBy('id');
        $scoredContents = collect();

        foreach ($recommendations as $rec) {
            $content = $contentMap->get($rec['content_id']);
            if ($content) {
                $content->relevance_score = $rec['relevance_score'];
                $content->recommendation_reason = $rec['reason'];
                $scoredContents->push($content);
            }
        }

        return $scoredContents->sortByDesc('relevance_score')->values();
    }

    /**
     * Score content using rule-based system (fallback).
     */
    private function scoreContentWithRules(Student $student, Collection $contents): Collection
    {
        $learningProfile = $student->learningStyleProfile;
        $avgScore = $this->getStudentAverageScore($student);

        return $contents->map(function ($content) use ($learningProfile, $avgScore) {
            $score = 0.5; // Base score

            // Learning style match
            if ($learningProfile && $content->isSuitableForStyle($learningProfile->dominant_style)) {
                $score += 0.3;
            }

            // Difficulty match
            if ($avgScore !== null) {
                $preferredDifficulty = $this->mapScoreToDifficulty($avgScore);
                if ($content->difficulty_level === $preferredDifficulty) {
                    $score += 0.2;
                }
            }

            // Rating boost
            if ($content->rating > 3) {
                $score += 0.1;
            }

            // Popularity boost
            if ($content->views_count > 100) {
                $score += 0.05;
            }

            $content->relevance_score = min($score, 1.0);
            $content->recommendation_reason = __('recommendations.rule_based_match');

            return $content;
        })->sortByDesc('relevance_score')->values();
    }

    /**
     * Build student profile for AI.
     */
    private function buildStudentProfile(Student $student): array
    {
        $profile = [
            'grade_level' => $student->grade_level,
            'major' => $student->major,
            'learning_interests' => $student->learning_interests ?? [],
        ];

        // Add learning style information
        $learningProfile = $student->learningStyleProfile;
        if ($learningProfile) {
            $profile['learning_style'] = [
                'dominant_style' => $learningProfile->dominant_style,
                'visual_score' => $learningProfile->visual_score,
                'auditory_score' => $learningProfile->auditory_score,
                'kinesthetic_score' => $learningProfile->kinesthetic_score,
            ];
        }

        // Add performance data
        $profile['avg_score'] = $this->getStudentAverageScore($student);
        $profile['recent_topics'] = $this->getRecentTopics($student);
        $profile['weak_areas'] = $this->getWeakAreas($student);
        $profile['strong_areas'] = $this->getStrongAreas($student);

        return $profile;
    }

    /**
     * Get student's average score.
     */
    private function getStudentAverageScore(Student $student): ?float
    {
        return $student->assessments()
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('percentage');
    }

    /**
     * Get recent topics studied.
     */
    private function getRecentTopics(Student $student): array
    {
        return $student->learningActivities()
            ->with('content')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->pluck('content.topic')
            ->filter()
            ->unique()
            ->values()
            ->take(5)
            ->toArray();
    }

    /**
     * Get weak areas based on assessment performance.
     */
    private function getWeakAreas(Student $student): array
    {
        return $student->assessments()
            ->where('created_at', '>=', now()->subDays(30))
            ->where('percentage', '<', 60)
            ->pluck('topic')
            ->unique()
            ->values()
            ->take(3)
            ->toArray();
    }

    /**
     * Get strong areas based on assessment performance.
     */
    private function getStrongAreas(Student $student): array
    {
        return $student->assessments()
            ->where('created_at', '>=', now()->subDays(30))
            ->where('percentage', '>=', 80)
            ->pluck('topic')
            ->unique()
            ->values()
            ->take(3)
            ->toArray();
    }

    /**
     * Map score to difficulty level.
     */
    private function mapScoreToDifficulty(float $score): string
    {
        if ($score >= 80) return 'advanced';
        if ($score >= 60) return 'intermediate';
        return 'beginner';
    }

    /**
     * Save recommendations to database.
     */
    private function saveRecommendations(Student $student, Collection $scoredContents): void
    {
        // Clear old recommendations
        Recommendation::where('student_id', $student->id)
            ->where('created_at', '<', now()->subHours(24))
            ->delete();

        foreach ($scoredContents as $content) {
            Recommendation::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'content_id' => $content->id,
                ],
                [
                    'recommendation_type' => 'hybrid',
                    'relevance_score' => $content->relevance_score,
                    'reason' => $content->recommendation_reason,
                    'algorithm_version' => '1.0',
                    'is_viewed' => false,
                    'is_completed' => false,
                ]
            );
        }
    }

    /**
     * Get fallback recommendations when AI fails.
     */
    private function getFallbackRecommendations(Student $student, int $limit): Collection
    {
        // Return popular content for the student's grade level
        return $this->contentRepository->getPopularContent($student->grade_level, $limit);
    }

    /**
     * Get existing recommendations for student.
     */
    public function getStoredRecommendations(Student $student, int $limit = 10): Collection
    {
        $recommendations = Recommendation::where('student_id', $student->id)
            ->with(['content'])
            ->orderBy('relevance_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $recommendations->pluck('content')->filter();
    }

    /**
     * Mark recommendation as viewed.
     */
    public function markAsViewed(Student $student, Content $content): void
    {
        Recommendation::where('student_id', $student->id)
            ->where('content_id', $content->id)
            ->update([
                'is_viewed' => true,
                'viewed_at' => now(),
            ]);
    }

    /**
     * Mark recommendation as completed.
     */
    public function markAsCompleted(Student $student, Content $content): void
    {
        Recommendation::where('student_id', $student->id)
            ->where('content_id', $content->id)
            ->update(['is_completed' => true]);
    }

    /**
     * Get recommendation effectiveness metrics.
     */
    public function getEffectivenessMetrics(Student $student): array
    {
        $totalRecommendations = Recommendation::where('student_id', $student->id)->count();
        $viewedRecommendations = Recommendation::where('student_id', $student->id)
            ->where('is_viewed', true)->count();
        $completedRecommendations = Recommendation::where('student_id', $student->id)
            ->where('is_completed', true)->count();

        return [
            'total_recommendations' => $totalRecommendations,
            'view_rate' => $totalRecommendations > 0 ? ($viewedRecommendations / $totalRecommendations) * 100 : 0,
            'completion_rate' => $totalRecommendations > 0 ? ($completedRecommendations / $totalRecommendations) * 100 : 0,
            'engagement_score' => $this->calculateEngagementScore($student),
        ];
    }

    /**
     * Calculate engagement score.
     */
    private function calculateEngagementScore(Student $student): float
    {
        $recentActivities = $student->learningActivities()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentRecommendations = Recommendation::where('student_id', $student->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentRecommendations === 0) return 0;

        return min(($recentActivities / $recentRecommendations) * 100, 100);
    }
}