<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SurveyResponse;
use App\Models\LearningStyleProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LearningStyleClassifier
{
    protected GeminiAIService $aiService;

    public function __construct(GeminiAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Analyze survey response and create/update learning style profile
     */
    public function analyzeSurveyResponse(SurveyResponse $surveyResponse): LearningStyleProfile
    {
        // Build student context for AI analysis
        $studentContext = $this->buildStudentContext($surveyResponse->student);

        // Get AI analysis with caching for performance
        $cacheKey = "learning_analysis_{$surveyResponse->id}";
        $analysis = Cache::remember($cacheKey, 3600, function () use ($surveyResponse, $studentContext) {
            return $this->aiService->analyzeLearningStyle(
                $surveyResponse->responses,
                $studentContext
            );
        });

        // Calculate base scores from survey responses
        $calculatedScores = $surveyResponse->calculateScores();

        // Combine AI analysis with calculated scores (weighted approach)
        $finalScores = $this->combineScores($calculatedScores, $analysis);

        // Create or update learning style profile
        return $this->createOrUpdateProfile($surveyResponse, $finalScores, $analysis);
    }

    /**
     * Generate learning recommendations based on profile
     */
    public function generateRecommendations(LearningStyleProfile $profile, array $availableContent = []): array
    {
        $cacheKey = "recommendations_{$profile->id}_" . md5(json_encode($availableContent));
        
        return Cache::remember($cacheKey, 1800, function () use ($profile, $availableContent) {
            $profileData = [
                'visual_score' => $profile->visual_score,
                'auditory_score' => $profile->auditory_score,
                'kinesthetic_score' => $profile->kinesthetic_score,
                'dominant_style' => $profile->dominant_style,
                'student_grade' => $profile->student->grade_level,
                'student_interests' => $profile->student->learning_interests ?? [],
            ];

            return $this->aiService->generateRecommendations($profileData, $availableContent);
        });
    }

    /**
     * Assess confidence in learning style classification
     */
    public function assessClassificationConfidence(SurveyResponse $surveyResponse): float
    {
        $scores = $surveyResponse->calculateScores();
        
        if (empty($scores)) {
            return 0;
        }

        // Calculate spread of scores (higher spread = more confident)
        $maxScore = max($scores);
        $minScore = min($scores);
        $spread = $maxScore - $minScore;
        
        // Factor in completion rate
        $completionRate = $surveyResponse->getCompletionPercentage() / 100;
        
        // Factor in response consistency (how consistent are responses within categories)
        $consistencyScore = $this->calculateResponseConsistency($surveyResponse);
        
        // Combine factors for final confidence score
        $confidence = (($spread / 100) * 40) + ($completionRate * 30) + ($consistencyScore * 30);
        
        return min(100, max(0, $confidence));
    }

    /**
     * Get learning style trends for a student over time
     */
    public function getStyleEvolution(Student $student): array
    {
        $profiles = $student->learningStyleProfiles()
            ->orderBy('created_at')
            ->get(['visual_score', 'auditory_score', 'kinesthetic_score', 'created_at']);

        if ($profiles->isEmpty()) {
            return [];
        }

        $evolution = [];
        foreach ($profiles as $profile) {
            $evolution[] = [
                'date' => $profile->created_at->format('Y-m-d'),
                'visual' => $profile->visual_score,
                'auditory' => $profile->auditory_score,
                'kinesthetic' => $profile->kinesthetic_score,
            ];
        }

        return $evolution;
    }

    /**
     * Compare student's learning style with class averages
     */
    public function compareWithPeers(Student $student): array
    {
        $studentProfile = $student->learningStyleProfiles()->latest()->first();
        
        if (!$studentProfile) {
            return [];
        }

        // Get class averages (anonymized)
        $classAverages = LearningStyleProfile::whereHas('student', function ($query) use ($student) {
            $query->where('grade_level', $student->grade_level)
                  ->where('class', $student->class);
        })
        ->selectRaw('AVG(visual_score) as avg_visual, AVG(auditory_score) as avg_auditory, AVG(kinesthetic_score) as avg_kinesthetic')
        ->first();

        return [
            'student' => [
                'visual' => $studentProfile->visual_score,
                'auditory' => $studentProfile->auditory_score,
                'kinesthetic' => $studentProfile->kinesthetic_score,
            ],
            'class_average' => [
                'visual' => round($classAverages->avg_visual ?? 0, 2),
                'auditory' => round($classAverages->avg_auditory ?? 0, 2),
                'kinesthetic' => round($classAverages->avg_kinesthetic ?? 0, 2),
            ],
            'percentile_ranking' => $this->calculatePercentileRanking($studentProfile),
        ];
    }

    /**
     * Build student context for AI analysis
     */
    private function buildStudentContext(Student $student): array
    {
        return [
            'grade' => $student->grade_level,
            'class' => $student->class,
            'major' => $student->major,
            'interests' => $student->learning_interests ?? [],
            'language_preference' => $student->preferred_language ?? 'id',
        ];
    }

    /**
     * Combine AI analysis with calculated scores
     */
    private function combineScores(array $calculatedScores, array $aiAnalysis): array
    {
        // Weight: 60% calculated scores, 40% AI analysis
        $weights = ['calculated' => 0.6, 'ai' => 0.4];

        $finalScores = [];
        foreach (['visual', 'auditory', 'kinesthetic'] as $style) {
            $calculated = $calculatedScores[$style] ?? 0;
            $ai = $aiAnalysis[$style . '_score'] ?? $calculated;
            
            $finalScores[$style] = ($calculated * $weights['calculated']) + ($ai * $weights['ai']);
        }

        return $finalScores;
    }

    /**
     * Create or update learning style profile
     */
    private function createOrUpdateProfile(SurveyResponse $surveyResponse, array $scores, array $analysis): LearningStyleProfile
    {
        $dominantStyle = $this->determineDominantStyle($scores);
        $confidenceScore = $this->assessClassificationConfidence($surveyResponse);

        $profileData = [
            'student_id' => $surveyResponse->student_id,
            'survey_response_id' => $surveyResponse->id,
            'visual_score' => round($scores['visual'], 2),
            'auditory_score' => round($scores['auditory'], 2),
            'kinesthetic_score' => round($scores['kinesthetic'], 2),
            'dominant_style' => $dominantStyle,
            'survey_data' => [
                'survey_id' => $surveyResponse->survey_id,
                'responses' => $surveyResponse->responses,
                'ai_analysis' => $analysis,
                'completion_time' => $surveyResponse->time_spent_seconds,
            ],
            'analysis_date' => now(),
            'ai_confidence_score' => $confidenceScore,
        ];

        // Update calculated scores in survey response
        $surveyResponse->update(['calculated_scores' => $scores]);

        return LearningStyleProfile::updateOrCreate(
            ['student_id' => $surveyResponse->student_id],
            $profileData
        );
    }

    /**
     * Determine dominant learning style from scores
     */
    private function determineDominantStyle(array $scores): string
    {
        $maxScore = max($scores);
        $dominantStyles = array_keys($scores, $maxScore);

        // If multiple styles have similar scores (within 10 points), consider it mixed
        $threshold = 10;
        $similarScores = array_filter($scores, function ($score) use ($maxScore, $threshold) {
            return abs($maxScore - $score) <= $threshold;
        });

        if (count($similarScores) > 1) {
            return 'mixed';
        }

        return $dominantStyles[0];
    }

    /**
     * Calculate response consistency within learning style categories
     */
    private function calculateResponseConsistency(SurveyResponse $surveyResponse): float
    {
        if (!$surveyResponse->survey || !$surveyResponse->responses) {
            return 0;
        }

        $questions = $surveyResponse->survey->questions;
        $responses = $surveyResponse->responses;
        
        $categoryResponses = [
            'visual' => [],
            'auditory' => [],
            'kinesthetic' => []
        ];

        // Group responses by category
        foreach ($responses as $questionId => $response) {
            $question = collect($questions)->firstWhere('id', $questionId);
            if ($question && isset($question['category'])) {
                $categoryResponses[$question['category']][] = (int) $response;
            }
        }

        $consistencyScores = [];
        foreach ($categoryResponses as $category => $categoryResponseList) {
            if (count($categoryResponseList) > 1) {
                $variance = $this->calculateVariance($categoryResponseList);
                $maxVariance = 4; // Maximum possible variance for 5-point scale
                $consistencyScores[] = max(0, 100 - ($variance / $maxVariance * 100));
            }
        }

        return empty($consistencyScores) ? 50 : array_sum($consistencyScores) / count($consistencyScores);
    }

    /**
     * Calculate variance of responses
     */
    private function calculateVariance(array $values): float
    {
        if (count($values) <= 1) {
            return 0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);

        return array_sum($squaredDifferences) / count($values);
    }

    /**
     * Calculate percentile ranking compared to peers
     */
    private function calculatePercentileRanking(LearningStyleProfile $profile): array
    {
        $rankings = [];
        
        foreach (['visual_score', 'auditory_score', 'kinesthetic_score'] as $scoreField) {
            $totalCount = LearningStyleProfile::whereHas('student', function ($query) use ($profile) {
                $query->where('grade_level', $profile->student->grade_level);
            })->count();

            if ($totalCount > 0) {
                $lowerCount = LearningStyleProfile::whereHas('student', function ($query) use ($profile) {
                    $query->where('grade_level', $profile->student->grade_level);
                })
                ->where($scoreField, '<', $profile->{$scoreField})
                ->count();

                $percentile = ($lowerCount / $totalCount) * 100;
                $rankings[str_replace('_score', '', $scoreField)] = round($percentile, 1);
            } else {
                $rankings[str_replace('_score', '', $scoreField)] = 50;
            }
        }

        return $rankings;
    }
}