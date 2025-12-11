<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\AnalyticsReport;
use App\Models\ReportTemplate;
use App\Services\AnalyticsAggregationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportGenerationService
{
    protected AnalyticsAggregationService $analyticsService;

    public function __construct(AnalyticsAggregationService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Generate student progress report
     */
    public function generateStudentReport(Student $student, Carbon $startDate, Carbon $endDate, array $options = []): AnalyticsReport
    {
        $reportData = $this->buildStudentReportData($student, $startDate, $endDate);
        $template = $this->getReportTemplate('student', $options['template_id'] ?? null);
        
        $report = AnalyticsReport::create([
            'report_type' => 'student',
            'entity_id' => $student->id,
            'report_data' => $reportData,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'generated_by' => auth()->id() ?? 'system',
        ]);

        // Generate PDF file if requested
        if ($options['generate_pdf'] ?? false) {
            $pdfPath = $this->generateStudentReportPDF($student, $reportData, $template, $report);
            $report->update(['file_path' => $pdfPath]);
        }

        return $report;
    }

    /**
     * Generate teacher class report
     */
    public function generateClassReport(Teacher $teacher, string $className, Carbon $startDate, Carbon $endDate, array $options = []): AnalyticsReport
    {
        $students = Student::where('class', $className)->get();
        $reportData = $this->buildClassReportData($students, $startDate, $endDate);
        $template = $this->getReportTemplate('class', $options['template_id'] ?? null);
        
        $report = AnalyticsReport::create([
            'report_type' => 'class',
            'entity_id' => $teacher->id,
            'report_data' => $reportData,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'generated_by' => $teacher->user_id,
        ]);

        if ($options['generate_pdf'] ?? false) {
            $pdfPath = $this->generateClassReportPDF($className, $reportData, $template, $report);
            $report->update(['file_path' => $pdfPath]);
        }

        return $report;
    }

    /**
     * Generate teacher performance report
     */
    public function generateTeacherReport(Teacher $teacher, Carbon $startDate, Carbon $endDate, array $options = []): AnalyticsReport
    {
        $reportData = $this->buildTeacherReportData($teacher, $startDate, $endDate);
        $template = $this->getReportTemplate('teacher', $options['template_id'] ?? null);
        
        $report = AnalyticsReport::create([
            'report_type' => 'teacher',
            'entity_id' => $teacher->id,
            'report_data' => $reportData,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'generated_by' => auth()->id() ?? 'system',
        ]);

        if ($options['generate_pdf'] ?? false) {
            $pdfPath = $this->generateTeacherReportPDF($teacher, $reportData, $template, $report);
            $report->update(['file_path' => $pdfPath]);
        }

        return $report;
    }

    /**
     * Generate school-wide report
     */
    public function generateSchoolReport(Carbon $startDate, Carbon $endDate, array $options = []): AnalyticsReport
    {
        $reportData = $this->buildSchoolReportData($startDate, $endDate);
        $template = $this->getReportTemplate('school', $options['template_id'] ?? null);
        
        $report = AnalyticsReport::create([
            'report_type' => 'school',
            'entity_id' => 1, // School ID (assuming single school)
            'report_data' => $reportData,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'generated_by' => auth()->id() ?? 'system',
        ]);

        if ($options['generate_pdf'] ?? false) {
            $pdfPath = $this->generateSchoolReportPDF($reportData, $template, $report);
            $report->update(['file_path' => $pdfPath]);
        }

        return $report;
    }

    /**
     * Build student report data
     */
    private function buildStudentReportData(Student $student, Carbon $startDate, Carbon $endDate): array
    {
        $analytics = $this->analyticsService->getStudentAnalytics($student, $startDate, $endDate);
        
        $assessments = $student->assessments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        $feedback = $student->feedbackLogs()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $recommendations = $student->recommendations()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('content')
            ->get();

        return [
            'student_info' => [
                'name' => $student->user->name,
                'student_number' => $student->student_number,
                'grade_level' => $student->grade_level,
                'class' => $student->class,
                'learning_style' => $student->learningStyleProfile?->dominant_style,
            ],
            'period' => [
                'start_date' => $startDate->format('d/m/Y'),
                'end_date' => $endDate->format('d/m/Y'),
                'duration_days' => $startDate->diffInDays($endDate) + 1,
            ],
            'performance_summary' => [
                'total_assessments' => $assessments->count(),
                'average_score' => $assessments->avg('percentage') ?? 0,
                'highest_score' => $assessments->max('percentage') ?? 0,
                'lowest_score' => $assessments->min('percentage') ?? 0,
                'improvement_trend' => $this->calculateStudentImprovementTrend($assessments),
                'subject_breakdown' => $this->getSubjectBreakdown($assessments),
            ],
            'engagement_metrics' => $analytics['engagement'],
            'time_metrics' => $analytics['time_metrics'],
            'learning_progress' => [
                'completed_content' => $analytics['engagement']['completions'],
                'total_study_hours' => $analytics['time_metrics']['total_hours'],
                'consistency_score' => $this->calculateConsistencyScore($student, $startDate, $endDate),
                'goal_achievement' => $this->calculateGoalAchievement($student, $startDate, $endDate),
            ],
            'feedback_summary' => [
                'total_feedback' => $feedback->count(),
                'positive_feedback' => $feedback->where('sentiment', 'positive')->count(),
                'constructive_feedback' => $feedback->where('sentiment', 'constructive')->count(),
                'unread_feedback' => $feedback->where('is_read', false)->count(),
            ],
            'recommendations_used' => [
                'total_recommended' => $recommendations->count(),
                'completed_recommendations' => $recommendations->where('is_completed', true)->count(),
                'effectiveness_score' => $this->calculateRecommendationEffectiveness($recommendations),
            ],
            'trends' => $analytics['trends'],
            'strengths' => $this->identifyStudentStrengths($student, $analytics),
            'areas_for_improvement' => $this->identifyImprovementAreas($student, $analytics),
            'next_steps' => $this->generateNextSteps($student, $analytics),
        ];
    }

    /**
     * Build class report data
     */
    private function buildClassReportData(Collection $students, Carbon $startDate, Carbon $endDate): array
    {
        $classAnalytics = $this->analyticsService->getClassAnalytics($students, $startDate, $endDate);
        
        $allAssessments = collect();
        $allFeedback = collect();
        
        foreach ($students as $student) {
            $studentAssessments = $student->assessments()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            $allAssessments = $allAssessments->concat($studentAssessments);
            
            $studentFeedback = $student->feedbackLogs()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            $allFeedback = $allFeedback->concat($studentFeedback);
        }

        return [
            'class_info' => [
                'total_students' => $students->count(),
                'active_students' => $students->where('status', 'active')->count(),
                'period' => [
                    'start_date' => $startDate->format('d/m/Y'),
                    'end_date' => $endDate->format('d/m/Y'),
                ],
            ],
            'class_averages' => $classAnalytics['class_average'],
            'learning_style_distribution' => $classAnalytics['distribution'],
            'performance_overview' => [
                'total_assessments' => $allAssessments->count(),
                'class_average' => $allAssessments->avg('percentage') ?? 0,
                'top_performers' => $this->getTopPerformers($students, 5),
                'struggling_students' => $classAnalytics['at_risk_students'],
                'subject_performance' => $this->getClassSubjectPerformance($allAssessments),
            ],
            'engagement_overview' => [
                'highly_engaged' => $this->countStudentsByEngagement($students, 'high'),
                'moderately_engaged' => $this->countStudentsByEngagement($students, 'medium'),
                'low_engagement' => $this->countStudentsByEngagement($students, 'low'),
            ],
            'feedback_analytics' => [
                'total_feedback_given' => $allFeedback->count(),
                'sentiment_distribution' => $allFeedback->groupBy('sentiment')->map->count(),
                'response_rates' => $this->calculateFeedbackResponseRates($allFeedback),
            ],
            'content_effectiveness' => $this->analyzeContentEffectiveness($students),
            'recommendations' => [
                'teaching_strategies' => $this->generateTeachingStrategies($classAnalytics),
                'intervention_priorities' => $this->prioritizeInterventions($classAnalytics['at_risk_students']),
                'resource_recommendations' => $this->recommendResources($classAnalytics),
            ],
        ];
    }

    /**
     * Build teacher report data
     */
    private function buildTeacherReportData(Teacher $teacher, Carbon $startDate, Carbon $endDate): array
    {
        $teacherClasses = Student::where('class', 'like', '%' . $teacher->subject . '%')->get()->groupBy('class');
        $totalStudents = $teacherClasses->flatten()->count();
        
        $allAssessments = collect();
        $allFeedback = collect();
        
        foreach ($teacherClasses as $className => $students) {
            foreach ($students as $student) {
                $studentAssessments = $student->assessments()
                    ->where('subject', $teacher->subject)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $allAssessments = $allAssessments->concat($studentAssessments);
                
                $studentFeedback = $student->feedbackLogs()
                    ->where('feedback_type', 'teacher')
                    ->where('created_by', $teacher->user_id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                $allFeedback = $allFeedback->concat($studentFeedback);
            }
        }

        return [
            'teacher_info' => [
                'name' => $teacher->user->name,
                'subject' => $teacher->subject,
                'total_students' => $totalStudents,
                'classes_count' => $teacherClasses->count(),
            ],
            'teaching_effectiveness' => [
                'overall_class_performance' => $allAssessments->avg('percentage') ?? 0,
                'student_improvement_rate' => $this->calculateStudentImprovementRate($allAssessments),
                'engagement_improvement' => $this->calculateEngagementImprovement($teacherClasses->flatten()),
                'feedback_quality_score' => $this->calculateFeedbackQualityScore($allFeedback),
            ],
            'class_management' => [
                'classes_overview' => $teacherClasses->map(function ($students, $className) use ($startDate, $endDate) {
                    return [
                        'class_name' => $className,
                        'student_count' => $students->count(),
                        'average_performance' => $this->getClassAveragePerformance($students, $startDate, $endDate),
                        'engagement_level' => $this->getClassEngagementLevel($students),
                    ];
                }),
                'at_risk_students_across_classes' => $this->identifyAtRiskStudentsForTeacher($teacher),
            ],
            'content_delivery' => [
                'content_effectiveness' => $this->analyzeTeacherContentEffectiveness($teacher),
                'assessment_difficulty_analysis' => $this->analyzeAssessmentDifficulty($allAssessments),
                'student_feedback_on_teaching' => $this->getStudentFeedbackOnTeaching($teacher),
            ],
            'professional_development' => [
                'strengths' => $this->identifyTeachingStrengths($teacher, $allAssessments),
                'improvement_areas' => $this->identifyTeachingImprovementAreas($teacher, $allAssessments),
                'recommended_training' => $this->recommendProfessionalDevelopment($teacher),
            ],
            'analytics_summary' => [
                'total_feedback_given' => $allFeedback->count(),
                'response_time_average' => $this->calculateAverageResponseTime($allFeedback),
                'intervention_success_rate' => $this->calculateInterventionSuccessRate($teacher),
            ],
        ];
    }

    /**
     * Build school-wide report data
     */
    private function buildSchoolReportData(Carbon $startDate, Carbon $endDate): array
    {
        $allStudents = Student::where('status', 'active')->get();
        $allTeachers = Teacher::all();
        $schoolAnalytics = $this->analyticsService->getClassAnalytics($allStudents, $startDate, $endDate);
        
        return [
            'school_overview' => [
                'total_students' => $allStudents->count(),
                'total_teachers' => $allTeachers->count(),
                'grade_distribution' => $allStudents->groupBy('grade_level')->map->count(),
                'period' => [
                    'start_date' => $startDate->format('d/m/Y'),
                    'end_date' => $endDate->format('d/m/Y'),
                ],
            ],
            'academic_performance' => [
                'school_average_score' => $schoolAnalytics['class_average']['performance_score'],
                'performance_by_grade' => $this->getPerformanceByGrade($allStudents, $startDate, $endDate),
                'subject_performance' => $this->getSubjectPerformanceSchoolWide($startDate, $endDate),
                'improvement_trends' => $this->getSchoolImprovementTrends($startDate, $endDate),
            ],
            'engagement_metrics' => [
                'overall_engagement' => $schoolAnalytics['class_average']['engagement_score'],
                'engagement_by_grade' => $this->getEngagementByGrade($allStudents),
                'learning_style_distribution' => $schoolAnalytics['distribution'],
                'content_utilization' => $this->getContentUtilizationStats(),
            ],
            'ai_system_effectiveness' => [
                'recommendation_effectiveness' => $this->getSystemRecommendationEffectiveness(),
                'feedback_impact' => $this->getFeedbackImpactMetrics(),
                'prediction_accuracy' => $this->getPredictionAccuracyMetrics(),
                'user_satisfaction' => $this->getUserSatisfactionMetrics(),
            ],
            'resource_optimization' => [
                'content_effectiveness_ranking' => $this->rankContentEffectiveness(),
                'teacher_workload_analysis' => $this->analyzeTeacherWorkload($allTeachers),
                'system_usage_patterns' => $this->getSystemUsagePatterns(),
                'cost_effectiveness_analysis' => $this->getCostEffectivenessAnalysis(),
            ],
            'recommendations' => [
                'curriculum_adjustments' => $this->recommendCurriculumAdjustments(),
                'teacher_development_priorities' => $this->prioritizeTeacherDevelopment($allTeachers),
                'technology_improvements' => $this->recommendTechnologyImprovements(),
                'policy_recommendations' => $this->generatePolicyRecommendations($schoolAnalytics),
            ],
        ];
    }

    /**
     * Generate PDF report for student
     */
    private function generateStudentReportPDF(Student $student, array $reportData, ?ReportTemplate $template, AnalyticsReport $report): string
    {
        $viewData = [
            'student' => $student,
            'data' => $reportData,
            'template' => $template,
            'generated_at' => now(),
        ];
        
        $pdf = PDF::loadView('reports.student', $viewData);
        $filename = "student_report_{$student->student_number}_" . now()->format('Y_m_d') . ".pdf";
        $path = "reports/students/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Generate PDF report for class
     */
    private function generateClassReportPDF(string $className, array $reportData, ?ReportTemplate $template, AnalyticsReport $report): string
    {
        $viewData = [
            'className' => $className,
            'data' => $reportData,
            'template' => $template,
            'generated_at' => now(),
        ];
        
        $pdf = PDF::loadView('reports.class', $viewData);
        $filename = "class_report_" . str_replace(' ', '_', $className) . "_" . now()->format('Y_m_d') . ".pdf";
        $path = "reports/classes/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Get report template
     */
    private function getReportTemplate(string $type, ?int $templateId = null): ?ReportTemplate
    {
        if ($templateId) {
            return ReportTemplate::find($templateId);
        }
        
        return ReportTemplate::where('report_type', $type)
            ->where('is_active', true)
            ->first();
    }

    // Helper methods for calculations

    private function calculateStudentImprovementTrend(Collection $assessments): float
    {
        if ($assessments->count() < 2) {
            return 0;
        }
        
        $scores = $assessments->sortBy('created_at')->pluck('percentage');
        $trend = 0;
        
        for ($i = 1; $i < $scores->count(); $i++) {
            $trend += $scores[$i] - $scores[$i - 1];
        }
        
        return $trend / ($scores->count() - 1);
    }

    private function getSubjectBreakdown(Collection $assessments): array
    {
        return $assessments->groupBy('subject')->map(function ($subjectAssessments) {
            return [
                'count' => $subjectAssessments->count(),
                'average' => $subjectAssessments->avg('percentage'),
                'improvement' => $this->calculateStudentImprovementTrend($subjectAssessments),
            ];
        })->toArray();
    }

    private function calculateConsistencyScore(Student $student, Carbon $startDate, Carbon $endDate): float
    {
        $dailyActivities = $student->learningActivities()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as activity_count')
            ->groupBy('date')
            ->get();
        
        if ($dailyActivities->isEmpty()) {
            return 0;
        }
        
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $activeDays = $dailyActivities->count();
        
        return ($activeDays / $totalDays) * 100;
    }

    private function calculateGoalAchievement(Student $student, Carbon $startDate, Carbon $endDate): array
    {
        // This would integrate with a goal-setting system
        return [
            'weekly_hours_goal' => 10,
            'actual_hours' => 8.5,
            'achievement_percentage' => 85,
        ];
    }

    private function calculateRecommendationEffectiveness(Collection $recommendations): float
    {
        if ($recommendations->isEmpty()) {
            return 0;
        }
        
        $viewedRecommendations = $recommendations->where('is_viewed', true);
        $completedRecommendations = $recommendations->where('is_completed', true);
        
        $viewRate = ($viewedRecommendations->count() / $recommendations->count()) * 50;
        $completionRate = ($completedRecommendations->count() / $recommendations->count()) * 50;
        
        return $viewRate + $completionRate;
    }

    private function identifyStudentStrengths(Student $student, array $analytics): array
    {
        $strengths = [];
        
        if ($analytics['engagement']['score'] > 75) {
            $strengths[] = 'High engagement with learning materials';
        }
        
        if ($analytics['time_metrics']['total_hours'] > 8) {
            $strengths[] = 'Consistent study habits';
        }
        
        if ($analytics['performance']['improvement_trend'] > 5) {
            $strengths[] = 'Strong improvement trajectory';
        }
        
        return $strengths;
    }

    private function identifyImprovementAreas(Student $student, array $analytics): array
    {
        $areas = [];
        
        if ($analytics['engagement']['score'] < 50) {
            $areas[] = 'Increase engagement with learning materials';
        }
        
        if ($analytics['time_metrics']['total_hours'] < 4) {
            $areas[] = 'Establish more consistent study schedule';
        }
        
        if ($analytics['performance']['improvement_trend'] < -5) {
            $areas[] = 'Address declining performance trend';
        }
        
        return $areas;
    }

    private function generateNextSteps(Student $student, array $analytics): array
    {
        $nextSteps = [];
        
        if ($analytics['engagement']['score'] < 60) {
            $nextSteps[] = 'Schedule one-on-one session to discuss learning preferences';
        }
        
        if ($analytics['performance']['avg_score'] < 70) {
            $nextSteps[] = 'Provide additional practice materials for challenging topics';
        }
        
        $nextSteps[] = 'Continue monitoring progress and adjust recommendations weekly';
        
        return $nextSteps;
    }

    // Additional helper methods would continue here...
    // (For brevity, I'm showing the structure but not implementing every helper method)
}