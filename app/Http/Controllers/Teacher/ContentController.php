<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ContentRequest;
use App\Models\Content;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService
    ) {}

    /**
     * Display content management interface for teachers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        
        // Check if teacher profile exists
        if (!$teacher) {
            return Inertia::render('Teacher/Content/Setup', [
                'user' => $user,
                'message' => 'Your teacher profile is not set up yet. Please contact the administrator to set up your teacher profile.',
            ]);
        }
        
        $filters = $request->only(['grade_level', 'content_type', 'is_active', 'search']);
        
        $content = $this->contentService->getTeacherContent($teacher->subject, $filters, 15);
        $statistics = $this->contentService->getStatistics();

        return Inertia::render('Teacher/Content/Index', [
            'content' => $content,
            'filters' => $filters,
            'statistics' => $statistics,
            'teacher' => $teacher,
            'gradeLevels' => ['10', '11', '12'],
            'contentTypes' => ['video', 'pdf', 'audio', 'interactive', 'text'],
            'difficultyLevels' => ['beginner', 'intermediate', 'advanced'],
            'targetLearningStyles' => ['visual', 'auditory', 'kinesthetic', 'all'],
        ]);
    }

    /**
     * Show form for creating new content.
     */
    public function create()
    {
        $teacher = Auth::user()->teacher;

        return Inertia::render('Teacher/Content/Create', [
            'teacher' => $teacher,
            'gradeLevels' => ['10', '11', '12'],
            'contentTypes' => [
                'video' => __('Video'),
                'pdf' => __('PDF Document'),
                'audio' => __('Audio'),
                'interactive' => __('Interactive'),
                'text' => __('Text/Article'),
            ],
            'difficultyLevels' => [
                'beginner' => __('Beginner'),
                'intermediate' => __('Intermediate'),
                'advanced' => __('Advanced'),
            ],
            'targetLearningStyles' => [
                'visual' => __('Visual Learners'),
                'auditory' => __('Auditory Learners'),
                'kinesthetic' => __('Kinesthetic Learners'),
                'all' => __('All Learning Styles'),
            ],
        ]);
    }

    /**
     * Store newly created content.
     */
    public function store(ContentRequest $request)
    {
        $teacher = Auth::user()->teacher;
        
        $data = $request->validated();
        $data['subject'] = $teacher->subject;

        $content = $this->contentService->createContent(
            $data,
            $request->file('content_file'),
            $request->file('thumbnail')
        );

        return redirect()->route('teacher.content.index')
            ->with('success', __('Content created successfully!'));
    }

    /**
     * Display specific content item.
     */
    public function show(int $id)
    {
        $teacher = Auth::user()->teacher;
        $content = Content::with(['creator', 'learningActivities.student.user'])
            ->findOrFail($id);

        // Check if teacher has permission to view this content
        if ($content->subject !== $teacher->subject && $content->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to view this content.');
        }

        // Get engagement statistics
        $engagementStats = [
            'total_views' => $content->learningActivities()->where('activity_type', 'view')->count(),
            'unique_viewers' => $content->learningActivities()->where('activity_type', 'view')->distinct('student_id')->count(),
            'completions' => $content->learningActivities()->where('activity_type', 'complete')->count(),
            'average_rating' => $content->rating,
        ];

        // Get recent activity
        $recentActivity = $content->learningActivities()
            ->with(['student.user'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Teacher/Content/Show', [
            'content' => $content,
            'engagementStats' => $engagementStats,
            'recentActivity' => $recentActivity,
            'canEdit' => $content->created_by === Auth::id(),
        ]);
    }

    /**
     * Show form for editing content.
     */
    public function edit(int $id)
    {
        $teacher = Auth::user()->teacher;
        $content = Content::findOrFail($id);

        // Check permissions
        if ($content->created_by !== Auth::id()) {
            abort(403, 'You can only edit content you created.');
        }

        return Inertia::render('Teacher/Content/Edit', [
            'content' => $content,
            'teacher' => $teacher,
            'gradeLevels' => ['10', '11', '12'],
            'contentTypes' => [
                'video' => __('Video'),
                'pdf' => __('PDF Document'),
                'audio' => __('Audio'),
                'interactive' => __('Interactive'),
                'text' => __('Text/Article'),
            ],
            'difficultyLevels' => [
                'beginner' => __('Beginner'),
                'intermediate' => __('Intermediate'),
                'advanced' => __('Advanced'),
            ],
            'targetLearningStyles' => [
                'visual' => __('Visual Learners'),
                'auditory' => __('Auditory Learners'),
                'kinesthetic' => __('Kinesthetic Learners'),
                'all' => __('All Learning Styles'),
            ],
        ]);
    }

    /**
     * Update existing content.
     */
    public function update(ContentRequest $request, int $id)
    {
        $content = Content::findOrFail($id);

        // Check permissions
        if ($content->created_by !== Auth::id()) {
            abort(403, 'You can only edit content you created.');
        }

        $data = $request->validated();

        $this->contentService->updateContent(
            $content,
            $data,
            $request->file('content_file'),
            $request->file('thumbnail')
        );

        return redirect()->route('teacher.content.show', $content->id)
            ->with('success', __('Content updated successfully!'));
    }

    /**
     * Remove content.
     */
    public function destroy(int $id)
    {
        $content = Content::findOrFail($id);

        // Check permissions
        if ($content->created_by !== Auth::id()) {
            abort(403, 'You can only delete content you created.');
        }

        // Check if content has been used by students
        $hasActivity = $content->learningActivities()->exists();
        
        if ($hasActivity) {
            // Soft delete by marking as inactive instead of hard delete
            $this->contentService->toggleContentStatus($content);
            $message = __('Content has been deactivated (students have interacted with it).');
        } else {
            // Hard delete if no student activity
            $this->contentService->deleteContent($content);
            $message = __('Content deleted successfully!');
        }

        return redirect()->route('teacher.content.index')
            ->with('success', $message);
    }

    /**
     * Duplicate content.
     */
    public function duplicate(int $id)
    {
        $content = Content::findOrFail($id);
        $teacher = Auth::user()->teacher;

        // Check if content belongs to same subject
        if ($content->subject !== $teacher->subject) {
            abort(403, 'You can only duplicate content from your subject.');
        }

        $duplicatedContent = $this->contentService->duplicateContent($content);

        return redirect()->route('teacher.content.edit', $duplicatedContent->id)
            ->with('success', __('Content duplicated successfully! Please review and activate.'));
    }

    /**
     * Toggle content active status.
     */
    public function toggleStatus(int $id)
    {
        $content = Content::findOrFail($id);

        // Check permissions
        if ($content->created_by !== Auth::id()) {
            abort(403, 'You can only modify content you created.');
        }

        $this->contentService->toggleContentStatus($content);

        $status = $content->fresh()->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', __('Content :status successfully!', ['status' => $status]));
    }

    /**
     * Get content analytics.
     */
    public function analytics(int $id)
    {
        $content = Content::with(['learningActivities.student.user'])->findOrFail($id);
        $teacher = Auth::user()->teacher;

        // Check permissions
        if ($content->subject !== $teacher->subject && $content->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to view analytics for this content.');
        }

        $analytics = [
            'views_over_time' => $this->getViewsOverTime($content),
            'completion_rate' => $this->getCompletionRate($content),
            'engagement_by_learning_style' => $this->getEngagementByLearningStyle($content),
            'performance_metrics' => $this->getPerformanceMetrics($content),
            'student_feedback' => $this->getStudentFeedback($content),
        ];

        return Inertia::render('Teacher/Content/Analytics', [
            'content' => $content,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Get views over time data.
     */
    private function getViewsOverTime(Content $content): array
    {
        $viewsData = $content->learningActivities()
            ->where('activity_type', 'view')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();

        return $viewsData->map(function ($item) {
            return [
                'date' => $item->date,
                'views' => $item->views,
            ];
        })->toArray();
    }

    /**
     * Get completion rate.
     */
    private function getCompletionRate(Content $content): array
    {
        $totalViews = $content->learningActivities()->where('activity_type', 'view')->distinct('student_id')->count();
        $totalCompletions = $content->learningActivities()->where('activity_type', 'complete')->count();

        return [
            'total_views' => $totalViews,
            'total_completions' => $totalCompletions,
            'completion_rate' => $totalViews > 0 ? ($totalCompletions / $totalViews) * 100 : 0,
        ];
    }

    /**
     * Get engagement by learning style.
     */
    private function getEngagementByLearningStyle(Content $content): array
    {
        $engagementData = $content->learningActivities()
            ->join('students', 'learning_activities.student_id', '=', 'students.id')
            ->join('learning_style_profiles', 'students.id', '=', 'learning_style_profiles.student_id')
            ->selectRaw('learning_style_profiles.dominant_style, COUNT(*) as count')
            ->groupBy('learning_style_profiles.dominant_style')
            ->get();

        return $engagementData->pluck('count', 'dominant_style')->toArray();
    }

    /**
     * Get performance metrics.
     */
    private function getPerformanceMetrics(Content $content): array
    {
        return [
            'average_time_spent' => $content->learningActivities()
                ->where('activity_type', 'complete')
                ->avg('duration_seconds'),
            'bounce_rate' => $this->calculateBounceRate($content),
            'rating' => $content->rating,
            'total_ratings' => $content->learningActivities()
                ->whereNotNull('metadata->rating')
                ->count(),
        ];
    }

    /**
     * Get student feedback.
     */
    private function getStudentFeedback(Content $content): array
    {
        $feedback = $content->learningActivities()
            ->whereNotNull('metadata->feedback')
            ->with(['student.user'])
            ->latest()
            ->limit(10)
            ->get();

        return $feedback->map(function ($activity) {
            return [
                'student_name' => $activity->student->user->name,
                'feedback' => $activity->metadata['feedback'] ?? '',
                'rating' => $activity->metadata['rating'] ?? null,
                'created_at' => $activity->created_at,
            ];
        })->toArray();
    }

    /**
     * Calculate bounce rate.
     */
    private function calculateBounceRate(Content $content): float
    {
        $totalSessions = $content->learningActivities()
            ->where('activity_type', 'view')
            ->distinct(['student_id', 'session_id'])
            ->count();

        $bouncedSessions = $content->learningActivities()
            ->where('activity_type', 'view')
            ->where('duration_seconds', '<', 30) // Less than 30 seconds considered bounce
            ->distinct(['student_id', 'session_id'])
            ->count();

        return $totalSessions > 0 ? ($bouncedSessions / $totalSessions) * 100 : 0;
    }
}