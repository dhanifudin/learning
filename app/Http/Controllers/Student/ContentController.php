<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Services\ContentService;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService,
        private RecommendationEngine $recommendationEngine
    ) {}

    /**
     * Simple version for debugging.
     */
    public function simple(Request $request)
    {
        try {
            $student = Auth::user()->student;
            $filters = $request->only(['subject', 'content_type', 'difficulty_level', 'search']);
            $filters['grade_level'] = $student->grade_level;

            $content = $this->contentService->getFilteredContent($filters, 12);
            $completedCount = $student->learningActivities()
                ->where('activity_type', 'complete')
                ->distinct('content_id')
                ->count();

            return Inertia::render('Student/Content/Simple', [
                'content' => $content,
                'filters' => $filters,
                'statistics' => [
                    'total' => $content->total(),
                    'completed' => $completedCount,
                ],
                'subjects' => ['Mathematics', 'Physics', 'Chemistry', 'Biology'],
                'contentTypes' => ['video', 'pdf', 'audio', 'interactive', 'text'],
                'difficultyLevels' => ['beginner', 'intermediate', 'advanced'],
                'recommendations' => [],
                'completedContent' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Test method for debugging.
     */
    public function test()
    {
        try {
            $user = Auth::user();
            $student = $user->student ?? null;
            
            $content = null;
            if ($student) {
                $filters = ['grade_level' => $student->grade_level];
                $content = $this->contentService->getFilteredContent($filters, 5);
            }
            
            return Inertia::render('Student/Content/Test', [
                'message' => 'Content system test page',
                'user' => $user,
                'student' => $student,
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Student/Content/Test', [
                'message' => 'Error: ' . $e->getMessage(),
                'user' => Auth::user(),
                'student' => null,
                'content' => null,
            ]);
        }
    }

    /**
     * Display content library for students.
     */
    public function index(Request $request)
    {
        try {
            $student = Auth::user()->student;
            
            if (!$student) {
                return redirect()->route('dashboard')
                    ->with('error', 'Student profile not found. Please contact administrator.');
            }

            $filters = $request->only(['subject', 'content_type', 'difficulty_level', 'search']);
            $filters['grade_level'] = $student->grade_level;

            $content = $this->contentService->getFilteredContent($filters, 12);

            $completedCount = $student->learningActivities()
                ->where('activity_type', 'complete')
                ->distinct('content_id')
                ->count();

            return Inertia::render('Student/Content/Index', [
                'content' => $content,
                'filters' => $filters,
                'statistics' => [
                    'total' => $content->total(),
                    'completed' => $completedCount,
                ],
                'subjects' => ['Mathematics', 'Physics', 'Chemistry', 'Biology'],
                'contentTypes' => ['video', 'pdf', 'audio', 'interactive', 'text'],
                'difficultyLevels' => ['beginner', 'intermediate', 'advanced'],
                'recommendations' => [],
                'completedContent' => [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Content index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Student/Content/Test', [
                'message' => 'Error loading content: ' . $e->getMessage(),
                'user' => Auth::user(),
                'student' => Auth::user()->student ?? null,
                'content' => null,
            ]);
        }
    }

    /**
     * Display specific content item.
     */
    public function show(int $id)
    {
        $student = Auth::user()->student;
        $content = $this->contentService->getContentForViewing($id, $student);

        if (!$content) {
            return redirect()->route('student.content.index')
                ->with('error', __('Content not found.'));
        }

        // Check if content is suitable for student's grade
        if ($content->grade_level !== $student->grade_level) {
            return redirect()->route('student.content.index')
                ->with('warning', __('This content is designed for Grade :grade students.', [
                    'grade' => $content->grade_level
                ]));
        }

        // Get related content
        $relatedContent = $this->contentService->getContentByTopic(
            $content->topic,
            $student->grade_level
        )->take(4);

        // Check if this content was recommended
        $isRecommended = $student->recommendations()
            ->where('content_id', $content->id)
            ->exists();

        // Mark recommendation as viewed if it exists
        if ($isRecommended) {
            $this->recommendationEngine->markAsViewed($student, $content);
        }

        return Inertia::render('Student/Content/Show', [
            'content' => $content->load(['creator']),
            'relatedContent' => $relatedContent,
            'isRecommended' => $isRecommended,
            'hasCompleted' => $student->learningActivities()
                ->where('content_id', $content->id)
                ->where('activity_type', 'complete')
                ->exists(),
        ]);
    }

    /**
     * Mark content as completed.
     */
    public function markComplete(int $id, Request $request)
    {
        $student = Auth::user()->student;
        $content = Content::findOrFail($id);

        // Record completion activity
        $this->contentService->recordActivity(
            $student,
            $content,
            'complete',
            $request->only(['rating', 'feedback'])
        );

        // Mark recommendation as completed if it exists
        $this->recommendationEngine->markAsCompleted($student, $content);

        // Rate content if rating provided
        if ($request->has('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $this->contentService->rateContent($content, $request->rating);
        }

        return back()->with('success', __('Content marked as completed!'));
    }

    /**
     * Get recommendations for student.
     */
    public function recommendations()
    {
        $student = Auth::user()->student;
        
        // Get fresh recommendations
        $recommendations = $this->recommendationEngine->generateRecommendations($student, 15);
        
        // Get recommendation effectiveness metrics
        $metrics = $this->recommendationEngine->getEffectivenessMetrics($student);

        return Inertia::render('Student/Content/Recommendations', [
            'recommendations' => $recommendations,
            'metrics' => $metrics,
            'learningStyle' => $student->learningStyleProfile?->dominant_style,
        ]);
    }

    /**
     * Search content.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $student = Auth::user()->student;
        $query = $request->input('q');
        
        $filters = [
            'grade_level' => $student->grade_level,
            'search' => $query,
        ];

        $results = $this->contentService->getFilteredContent($filters, 20);

        return Inertia::render('Student/Content/SearchResults', [
            'results' => $results,
            'query' => $query,
            'suggestions' => $this->getSuggestions($query, $student),
        ]);
    }

    /**
     * Get content by subject.
     */
    public function bySubject(string $subject)
    {
        $student = Auth::user()->student;
        
        $content = $this->contentService->getContentBySubjectAndGrade(
            $subject,
            $student->grade_level,
            ['is_active' => true]
        );

        return Inertia::render('Student/Content/BySubject', [
            'content' => $content,
            'subject' => $subject,
            'learningStyleContent' => $this->contentService->getContentForLearningStyle(
                $student->learningStyleProfile?->dominant_style ?? 'visual',
                $student->grade_level,
                6
            ),
        ]);
    }

    /**
     * Get recently added content.
     */
    public function recent()
    {
        $student = Auth::user()->student;
        
        $recentContent = $this->contentService->getRecentContent($student->grade_level, 20);

        return Inertia::render('Student/Content/Recent', [
            'content' => $recentContent,
            'gradeLevel' => $student->grade_level,
        ]);
    }

    /**
     * Download content file (for PDFs, documents, etc.).
     */
    public function download(int $id)
    {
        $student = Auth::user()->student;
        $content = Content::findOrFail($id);

        // Check access permissions
        if ($content->grade_level !== $student->grade_level) {
            abort(403, 'You do not have permission to download this content.');
        }

        if (!$content->hasMediaFile()) {
            return back()->with('error', __('No downloadable file available.'));
        }

        // Record download activity
        $this->contentService->recordActivity($student, $content, 'download');

        // Return file download or redirect to external URL
        if ($content->file_url) {
            $filePath = str_replace('/storage/', '', $content->file_url);
            return response()->download(storage_path('app/public/' . $filePath));
        } else {
            return redirect($content->external_url);
        }
    }

    /**
     * Track content interaction (clicks, views, etc.).
     */
    public function track(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:click,view,scroll,pause,resume',
            'timestamp' => 'required|integer',
            'metadata' => 'array',
        ]);

        $student = Auth::user()->student;
        $content = Content::findOrFail($id);

        $this->contentService->recordActivity(
            $student,
            $content,
            $request->action,
            array_merge($request->metadata ?? [], [
                'timestamp' => $request->timestamp,
                'user_agent' => $request->userAgent(),
            ])
        );

        return response()->json(['status' => 'tracked']);
    }

    /**
     * Get content suggestions based on search query.
     */
    private function getSuggestions(string $query, $student): array
    {
        // Simple suggestion logic - in production, you might use Elasticsearch or similar
        $suggestions = Content::active()
            ->gradeLevel($student->grade_level)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('topic', 'LIKE', "%{$query}%");
            })
            ->distinct('topic')
            ->limit(5)
            ->pluck('topic')
            ->toArray();

        return $suggestions;
    }
}