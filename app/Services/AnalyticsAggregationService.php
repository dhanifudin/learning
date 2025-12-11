<?php

namespace App\Services;

use App\Models\Student;
use App\Models\LearningAnalytic;
use App\Models\LearningActivity;
use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsAggregationService
{
    /**
     * Calculate and store daily analytics for a student
     */
    public function calculateDailyAnalytics(Student $student, ?Carbon $date = null): void
    {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Calculate engagement metrics
        $this->calculateEngagementMetrics($student, $startOfDay, $endOfDay);
        
        // Calculate performance metrics
        $this->calculatePerformanceMetrics($student, $startOfDay, $endOfDay);
        
        // Calculate time-based metrics
        $this->calculateTimeMetrics($student, $startOfDay, $endOfDay);
    }

    /**
     * Calculate engagement metrics for a student on a specific day
     */
    private function calculateEngagementMetrics(Student $student, Carbon $startDate, Carbon $endDate): void
    {
        $activities = LearningActivity::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $engagementScore = $this->calculateEngagementScore($activities);
        $sessionCount = $activities->pluck('session_id')->unique()->count();
        $contentViews = $activities->where('activity_type', 'view')->count();
        $completions = $activities->where('activity_type', 'complete')->count();

        $this->storeAnalytic($student, 'engagement_score', $engagementScore, $startDate->toDateString());
        $this->storeAnalytic($student, 'session_count', $sessionCount, $startDate->toDateString());
        $this->storeAnalytic($student, 'content_views', $contentViews, $startDate->toDateString());
        $this->storeAnalytic($student, 'content_completions', $completions, $startDate->toDateString());
    }

    /**
     * Calculate performance metrics for a student on a specific day
     */
    private function calculatePerformanceMetrics(Student $student, Carbon $startDate, Carbon $endDate): void
    {
        $assessments = Assessment::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        if ($assessments->isNotEmpty()) {
            $avgScore = $assessments->avg('percentage');
            $assessmentCount = $assessments->count();
            $improvementTrend = $this->calculateImprovementTrend($student, $endDate);

            $this->storeAnalytic($student, 'avg_assessment_score', $avgScore, $startDate->toDateString());
            $this->storeAnalytic($student, 'assessment_count', $assessmentCount, $startDate->toDateString());
            $this->storeAnalytic($student, 'improvement_trend', $improvementTrend, $startDate->toDateString());
        }
    }

    /**
     * Calculate time-based metrics for a student on a specific day
     */
    private function calculateTimeMetrics(Student $student, Carbon $startDate, Carbon $endDate): void
    {
        $totalTime = LearningActivity::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('duration_seconds');

        $avgSessionDuration = $this->calculateAverageSessionDuration($student, $startDate, $endDate);

        $this->storeAnalytic($student, 'total_study_time', $totalTime / 3600, $startDate->toDateString()); // in hours
        $this->storeAnalytic($student, 'avg_session_duration', $avgSessionDuration / 60, $startDate->toDateString()); // in minutes
    }

    /**
     * Calculate engagement score based on activities
     */
    private function calculateEngagementScore(Collection $activities): float
    {
        if ($activities->isEmpty()) {
            return 0.0;
        }

        $weights = [
            'view' => 1.0,
            'click' => 1.5,
            'download' => 2.0,
            'complete' => 3.0,
        ];

        $totalWeight = 0;
        $maxPossibleWeight = $activities->count() * 3.0; // Maximum if all were completions

        foreach ($activities as $activity) {
            $totalWeight += $weights[$activity->activity_type] ?? 1.0;
        }

        return $maxPossibleWeight > 0 ? ($totalWeight / $maxPossibleWeight) * 100 : 0.0;
    }

    /**
     * Calculate improvement trend based on recent assessments
     */
    private function calculateImprovementTrend(Student $student, Carbon $endDate): float
    {
        $recentAssessments = Assessment::where('student_id', $student->id)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentAssessments->count() < 2) {
            return 0.0;
        }

        $scores = $recentAssessments->pluck('percentage')->reverse()->values();
        $trend = 0.0;

        for ($i = 1; $i < $scores->count(); $i++) {
            $trend += $scores[$i] - $scores[$i - 1];
        }

        return $trend / ($scores->count() - 1);
    }

    /**
     * Calculate average session duration
     */
    private function calculateAverageSessionDuration(Student $student, Carbon $startDate, Carbon $endDate): float
    {
        $sessionData = LearningActivity::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('session_id, SUM(duration_seconds) as session_duration')
            ->groupBy('session_id')
            ->get();

        return $sessionData->isNotEmpty() ? $sessionData->avg('session_duration') : 0.0;
    }

    /**
     * Store an analytic metric
     */
    private function storeAnalytic(Student $student, string $metricType, float $value, string $date): void
    {
        LearningAnalytic::updateOrCreate([
            'student_id' => $student->id,
            'metric_type' => $metricType,
            'calculation_date' => $date,
            'aggregation_period' => 'daily',
        ], [
            'metric_value' => $value,
            'context_data' => [
                'calculated_at' => now(),
                'version' => '1.0',
            ],
        ]);
    }

    /**
     * Get student analytics for a date range
     */
    public function getStudentAnalytics(Student $student, Carbon $startDate, Carbon $endDate): array
    {
        $analytics = LearningAnalytic::forStudent($student->id)
            ->inDateRange($startDate, $endDate)
            ->byPeriod('daily')
            ->get()
            ->groupBy('metric_type');

        return [
            'engagement' => [
                'score' => $analytics->get('engagement_score', collect())->pluck('metric_value')->avg() ?? 0,
                'sessions' => $analytics->get('session_count', collect())->sum('metric_value'),
                'content_views' => $analytics->get('content_views', collect())->sum('metric_value'),
                'completions' => $analytics->get('content_completions', collect())->sum('metric_value'),
            ],
            'performance' => [
                'avg_score' => $analytics->get('avg_assessment_score', collect())->pluck('metric_value')->avg() ?? 0,
                'total_assessments' => $analytics->get('assessment_count', collect())->sum('metric_value'),
                'improvement_trend' => $analytics->get('improvement_trend', collect())->pluck('metric_value')->avg() ?? 0,
            ],
            'time_metrics' => [
                'total_hours' => $analytics->get('total_study_time', collect())->sum('metric_value'),
                'avg_session_minutes' => $analytics->get('avg_session_duration', collect())->pluck('metric_value')->avg() ?? 0,
            ],
            'trends' => $this->buildTrendData($analytics, $startDate, $endDate),
        ];
    }

    /**
     * Build trend data for charts
     */
    private function buildTrendData(Collection $analytics, Carbon $startDate, Carbon $endDate): array
    {
        $period = $startDate->diffInDays($endDate);
        $dates = [];
        
        for ($i = 0; $i <= $period; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->toDateString();
        }

        $trendMetrics = ['engagement_score', 'avg_assessment_score', 'total_study_time'];
        $trends = [];

        foreach ($trendMetrics as $metric) {
            $metricData = $analytics->get($metric, collect())->keyBy('calculation_date');
            
            $trends[$metric] = array_map(function ($date) use ($metricData) {
                return [
                    'date' => $date,
                    'value' => $metricData->get($date)?->metric_value ?? 0,
                ];
            }, $dates);
        }

        return $trends;
    }

    /**
     * Calculate weekly aggregations from daily data
     */
    public function calculateWeeklyAnalytics(Student $student, Carbon $weekStart): void
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $dailyAnalytics = LearningAnalytic::forStudent($student->id)
            ->byPeriod('daily')
            ->inDateRange($weekStart, $weekEnd)
            ->get()
            ->groupBy('metric_type');

        foreach ($dailyAnalytics as $metricType => $metrics) {
            $aggregatedValue = $this->aggregateWeeklyValue($metricType, $metrics);
            
            LearningAnalytic::updateOrCreate([
                'student_id' => $student->id,
                'metric_type' => $metricType,
                'calculation_date' => $weekStart->toDateString(),
                'aggregation_period' => 'weekly',
            ], [
                'metric_value' => $aggregatedValue,
                'context_data' => [
                    'calculated_at' => now(),
                    'period_end' => $weekEnd->toDateString(),
                    'data_points' => $metrics->count(),
                ],
            ]);
        }
    }

    /**
     * Aggregate daily values into weekly values
     */
    private function aggregateWeeklyValue(string $metricType, Collection $metrics): float
    {
        $sumMetrics = ['session_count', 'content_views', 'content_completions', 'assessment_count', 'total_study_time'];
        $avgMetrics = ['engagement_score', 'avg_assessment_score', 'improvement_trend', 'avg_session_duration'];

        if (in_array($metricType, $sumMetrics)) {
            return $metrics->sum('metric_value');
        } elseif (in_array($metricType, $avgMetrics)) {
            return $metrics->avg('metric_value') ?? 0;
        }

        return $metrics->sum('metric_value');
    }

    /**
     * Get class analytics aggregation
     */
    public function getClassAnalytics(Collection $students, Carbon $startDate, Carbon $endDate): array
    {
        $classData = [];
        
        foreach ($students as $student) {
            $studentAnalytics = $this->getStudentAnalytics($student, $startDate, $endDate);
            $classData[] = array_merge($studentAnalytics, ['student_id' => $student->id]);
        }

        return [
            'class_average' => $this->calculateClassAverages($classData),
            'distribution' => $this->calculateClassDistribution($students),
            'performance_ranking' => $this->calculatePerformanceRanking($classData),
            'at_risk_students' => $this->identifyAtRiskStudents($classData),
        ];
    }

    /**
     * Calculate class averages
     */
    private function calculateClassAverages(array $classData): array
    {
        if (empty($classData)) {
            return [
                'engagement_score' => 0,
                'performance_score' => 0,
                'study_hours' => 0,
                'completion_rate' => 0,
            ];
        }

        return [
            'engagement_score' => collect($classData)->avg('engagement.score'),
            'performance_score' => collect($classData)->avg('performance.avg_score'),
            'study_hours' => collect($classData)->avg('time_metrics.total_hours'),
            'completion_rate' => $this->calculateClassCompletionRate($classData),
        ];
    }

    /**
     * Calculate class completion rate
     */
    private function calculateClassCompletionRate(array $classData): float
    {
        $totalViews = collect($classData)->sum('engagement.content_views');
        $totalCompletions = collect($classData)->sum('engagement.completions');

        return $totalViews > 0 ? ($totalCompletions / $totalViews) * 100 : 0;
    }

    /**
     * Calculate learning style distribution for class
     */
    private function calculateClassDistribution(Collection $students): array
    {
        $distribution = $students->load('learningStyleProfile')
            ->pluck('learningStyleProfile.dominant_style')
            ->filter()
            ->countBy()
            ->toArray();

        $total = array_sum($distribution);

        return array_map(function ($count) use ($total) {
            return $total > 0 ? ($count / $total) * 100 : 0;
        }, $distribution);
    }

    /**
     * Calculate performance ranking
     */
    private function calculatePerformanceRanking(array $classData): array
    {
        return collect($classData)
            ->sortByDesc('performance.avg_score')
            ->values()
            ->map(function ($student, $index) {
                return [
                    'student_id' => $student['student_id'],
                    'rank' => $index + 1,
                    'score' => $student['performance']['avg_score'],
                ];
            })
            ->toArray();
    }

    /**
     * Identify at-risk students
     */
    private function identifyAtRiskStudents(array $classData): array
    {
        $riskThresholds = [
            'engagement_score' => 30, // Below 30% engagement
            'performance_score' => 60, // Below 60% performance
            'study_hours' => 2, // Less than 2 hours per week
        ];

        return collect($classData)
            ->filter(function ($student) use ($riskThresholds) {
                return $student['engagement']['score'] < $riskThresholds['engagement_score'] ||
                       $student['performance']['avg_score'] < $riskThresholds['performance_score'] ||
                       $student['time_metrics']['total_hours'] < $riskThresholds['study_hours'];
            })
            ->map(function ($student) use ($riskThresholds) {
                $riskFactors = [];
                
                if ($student['engagement']['score'] < $riskThresholds['engagement_score']) {
                    $riskFactors[] = 'low_engagement';
                }
                
                if ($student['performance']['avg_score'] < $riskThresholds['performance_score']) {
                    $riskFactors[] = 'low_performance';
                }
                
                if ($student['time_metrics']['total_hours'] < $riskThresholds['study_hours']) {
                    $riskFactors[] = 'insufficient_study_time';
                }

                return [
                    'student_id' => $student['student_id'],
                    'risk_factors' => $riskFactors,
                    'risk_level' => count($riskFactors) >= 2 ? 'high' : 'medium',
                ];
            })
            ->values()
            ->toArray();
    }
}