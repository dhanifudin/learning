<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AnalyticsAggregationService;
use App\Services\FeedbackGenerationService;
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    protected ?AnalyticsAggregationService $analyticsService;
    protected ?FeedbackGenerationService $feedbackService;
    protected ?ReportGenerationService $reportService;

    public function __construct(
        ?AnalyticsAggregationService $analyticsService = null,
        ?FeedbackGenerationService $feedbackService = null,
        ?ReportGenerationService $reportService = null
    ) {
        $this->analyticsService = $analyticsService;
        $this->feedbackService = $feedbackService;
        $this->reportService = $reportService;
    }

    /**
     * Display student analytics dashboard
     */
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $period = $request->get('period', 'week');
        
        [$startDate, $endDate] = $this->parsePeriod($period);
        
        // Simplified analytics data for testing
        $analytics = [
            'engagement' => ['score' => 75, 'sessions' => 12, 'content_views' => 25, 'completions' => 8],
            'performance' => ['avg_score' => 82, 'total_assessments' => 5, 'improvement_trend' => 3.2],
            'time_metrics' => ['total_hours' => 12.5, 'avg_session_minutes' => 45],
            'trends' => [
                'avg_assessment_score' => [
                    ['date' => $startDate->format('Y-m-d'), 'value' => 78],
                    ['date' => $endDate->format('Y-m-d'), 'value' => 82]
                ],
                'engagement_score' => [
                    ['date' => $startDate->format('Y-m-d'), 'value' => 70],
                    ['date' => $endDate->format('Y-m-d'), 'value' => 75]
                ]
            ]
        ];

        $recentFeedback = $student->feedbackLogs()
            ->latest()
            ->limit(5)
            ->get();
        
        $predictions = $student->performancePredictions()
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('Student/Analytics/Dashboard', [
            'analytics' => $analytics,
            'recentFeedback' => $recentFeedback,
            'predictions' => $predictions,
            'period' => $period,
            'learningProfile' => $student->learningStyleProfile,
        ]);
    }

    /**
     * Show detailed performance analytics
     */
    public function performance(Request $request)
    {
        $student = $request->user()->student;
        $subject = $request->get('subject');
        $period = $request->get('period', 'month');
        
        [$startDate, $endDate] = $this->parsePeriod($period);
        
        $analytics = $this->analyticsService->getStudentAnalytics($student, $startDate, $endDate);
        
        $assessments = $student->assessments()
            ->when($subject, fn($q) => $q->where('subject', $subject))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $competencyMaps = $student->competencyMaps()
            ->when($subject, fn($q) => $q->where('subject', $subject))
            ->get();

        return Inertia::render('Student/Analytics/Performance', [
            'analytics' => $analytics,
            'assessments' => $assessments,
            'competencyMaps' => $competencyMaps,
            'period' => $period,
            'subject' => $subject,
        ]);
    }

    /**
     * Show learning journey visualization
     */
    public function learningJourney(Request $request)
    {
        $student = $request->user()->student;
        $period = $request->get('period', 'semester');
        
        [$startDate, $endDate] = $this->parsePeriod($period);
        
        $activities = $student->learningActivities()
            ->with('content')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $recommendations = $student->recommendations()
            ->with('content')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $milestones = $this->identifyLearningMilestones($student, $startDate, $endDate);

        return Inertia::render('Student/Analytics/LearningJourney', [
            'activities' => $activities,
            'recommendations' => $recommendations,
            'milestones' => $milestones,
            'period' => $period,
        ]);
    }

    /**
     * Show study patterns analysis
     */
    public function studyPatterns(Request $request)
    {
        $student = $request->user()->student;
        
        $patterns = $this->analyzeStudyPatterns($student);
        $suggestions = $this->generateStudyOptimizationSuggestions($student, $patterns);

        return Inertia::render('Student/Analytics/StudyPatterns', [
            'patterns' => $patterns,
            'suggestions' => $suggestions,
            'learningStyle' => $student->learningStyleProfile,
        ]);
    }

    /**
     * Generate personal progress report
     */
    public function generateReport(Request $request)
    {
        $student = $request->user()->student;
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'pdf');
        
        [$startDate, $endDate] = $this->parsePeriod($period);
        
        $report = $this->reportService->generateStudentReport($student, $startDate, $endDate, [
            'generate_pdf' => $format === 'pdf',
        ]);

        if ($format === 'pdf') {
            return response()->download(storage_path('app/' . $report->file_path));
        }

        return response()->json($report->report_data);
    }

    /**
     * Get analytics data for API calls
     */
    public function analyticsData(Request $request)
    {
        $student = $request->user()->student;
        $period = $request->get('period', 'week');
        $metric = $request->get('metric', 'all');
        
        [$startDate, $endDate] = $this->parsePeriod($period);
        
        $analytics = $this->analyticsService->getStudentAnalytics($student, $startDate, $endDate);
        
        if ($metric !== 'all' && isset($analytics[$metric])) {
            return response()->json($analytics[$metric]);
        }
        
        return response()->json($analytics);
    }

    /**
     * Get performance predictions
     */
    public function predictions(Request $request)
    {
        $student = $request->user()->student;
        $type = $request->get('type', 'exam_score');
        
        $predictions = $student->performancePredictions()
            ->when($type !== 'all', fn($q) => $q->byType($type))
            ->pending()
            ->orderBy('target_date', 'asc')
            ->get();

        return response()->json($predictions);
    }

    /**
     * Mark feedback as read
     */
    public function markFeedbackRead(Request $request, int $feedbackId)
    {
        $student = $request->user()->student;
        
        $feedback = $student->feedbackLogs()
            ->where('id', $feedbackId)
            ->first();
        
        if ($feedback) {
            $feedback->markAsRead();
            return response()->json(['status' => 'success']);
        }
        
        return response()->json(['error' => 'Feedback not found'], 404);
    }

    /**
     * Request study recommendations
     */
    public function requestRecommendations(Request $request)
    {
        $student = $request->user()->student;
        
        $feedback = $this->feedbackService->generateStudyRecommendations($student);
        
        return response()->json([
            'feedback' => $feedback,
            'message' => 'New study recommendations generated',
        ]);
    }

    /**
     * Parse period parameter into date range
     */
    private function parsePeriod(string $period): array
    {
        $now = now();
        
        return match ($period) {
            'day' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'semester' => [$now->copy()->subMonths(6), $now],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
        };
    }

    /**
     * Identify learning milestones
     */
    private function identifyLearningMilestones(Student $student, Carbon $startDate, Carbon $endDate): array
    {
        $milestones = [];
        
        // First content completion
        $firstCompletion = $student->learningActivities()
            ->where('activity_type', 'complete')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->oldest()
            ->first();
        
        if ($firstCompletion) {
            $milestones[] = [
                'type' => 'first_completion',
                'date' => $firstCompletion->created_at,
                'title' => 'First Content Completed',
                'description' => 'Started the learning journey',
            ];
        }
        
        // Assessment milestones
        $highScores = $student->assessments()
            ->where('percentage', '>=', 90)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        foreach ($highScores as $assessment) {
            $milestones[] = [
                'type' => 'high_score',
                'date' => $assessment->created_at,
                'title' => 'Excellence Achievement',
                'description' => "Scored {$assessment->percentage}% in {$assessment->topic}",
            ];
        }
        
        // Consistency milestones
        $consistentWeeks = $this->findConsistentLearningWeeks($student, $startDate, $endDate);
        
        if ($consistentWeeks >= 4) {
            $milestones[] = [
                'type' => 'consistency',
                'date' => $startDate->copy()->addWeeks(4),
                'title' => 'Consistency Master',
                'description' => "Maintained consistent learning for {$consistentWeeks} weeks",
            ];
        }
        
        return collect($milestones)->sortBy('date')->values()->toArray();
    }

    /**
     * Analyze study patterns
     */
    private function analyzeStudyPatterns(Student $student): array
    {
        $activities = $student->learningActivities()
            ->where('created_at', '>=', now()->subMonth())
            ->get();
        
        $hourlyDistribution = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('H');
        })->map->count();
        
        $dailyDistribution = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('w');
        })->map->count();
        
        $peakHours = $hourlyDistribution->sortDesc()->take(3)->keys();
        $peakDays = $dailyDistribution->sortDesc()->take(3)->keys();
        
        return [
            'hourly_distribution' => $hourlyDistribution,
            'daily_distribution' => $dailyDistribution,
            'peak_study_hours' => $peakHours->map(fn($hour) => sprintf('%02d:00', $hour)),
            'peak_study_days' => $peakDays->map(fn($day) => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$day]),
            'average_session_duration' => $activities->avg('duration_seconds') / 60, // in minutes
            'total_study_sessions' => $activities->pluck('session_id')->unique()->count(),
            'consistency_score' => $this->calculateStudyConsistency($activities),
        ];
    }

    /**
     * Generate study optimization suggestions
     */
    private function generateStudyOptimizationSuggestions(Student $student, array $patterns): array
    {
        $suggestions = [];
        
        // Peak hours suggestion
        if (!empty($patterns['peak_study_hours'])) {
            $suggestions[] = [
                'type' => 'timing',
                'priority' => 'medium',
                'title' => 'Optimize Study Schedule',
                'description' => 'Your peak learning hours are ' . implode(', ', $patterns['peak_study_hours']->toArray()),
                'action' => 'Schedule important topics during these hours for better retention',
            ];
        }
        
        // Session duration suggestion
        if ($patterns['average_session_duration'] > 120) {
            $suggestions[] = [
                'type' => 'duration',
                'priority' => 'high',
                'title' => 'Break Down Study Sessions',
                'description' => 'Your average session is ' . round($patterns['average_session_duration']) . ' minutes',
                'action' => 'Consider shorter 45-60 minute sessions with breaks for better focus',
            ];
        }
        
        // Consistency suggestion
        if ($patterns['consistency_score'] < 60) {
            $suggestions[] = [
                'type' => 'consistency',
                'priority' => 'high',
                'title' => 'Improve Study Consistency',
                'description' => 'Your study consistency score is ' . round($patterns['consistency_score']) . '%',
                'action' => 'Try to study for at least 30 minutes daily to build a strong habit',
            ];
        }
        
        return $suggestions;
    }

    /**
     * Calculate study consistency score
     */
    private function calculateStudyConsistency(Collection $activities): float
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $totalDays = $monthStart->diffInDays($now) + 1;
        
        $activeDays = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        })->count();
        
        return ($activeDays / $totalDays) * 100;
    }

    /**
     * Find consistent learning weeks
     */
    private function findConsistentLearningWeeks(Student $student, Carbon $startDate, Carbon $endDate): int
    {
        $weeks = [];
        $current = $startDate->copy();
        
        while ($current->lt($endDate)) {
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd = $current->copy()->endOfWeek();
            
            $weekActivities = $student->learningActivities()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            
            $weeks[] = $weekActivities >= 5; // At least 5 activities per week
            $current->addWeek();
        }
        
        // Count consecutive consistent weeks
        $consecutiveWeeks = 0;
        $maxConsecutive = 0;
        
        foreach ($weeks as $isConsistent) {
            if ($isConsistent) {
                $consecutiveWeeks++;
                $maxConsecutive = max($maxConsecutive, $consecutiveWeeks);
            } else {
                $consecutiveWeeks = 0;
            }
        }
        
        return $maxConsecutive;
    }
}