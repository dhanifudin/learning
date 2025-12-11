<?php

namespace App\Http\Controllers;

use App\Models\LearningStyleSurvey;
use App\Models\SurveyResponse;
use App\Models\Student;
use App\Services\LearningStyleClassifier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class LearningStyleSurveyController extends Controller
{
    protected LearningStyleClassifier $classifier;

    public function __construct(LearningStyleClassifier $classifier)
    {
        $this->classifier = $classifier;
    }

    /**
     * Display survey list for students
     */
    public function index(): Response
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Access denied. Students only.');
        }

        $availableSurveys = LearningStyleSurvey::active()
            ->published()
            ->forLanguage($student->preferred_language ?? 'id')
            ->with('creator')
            ->get();

        $completedSurveys = $student->surveyResponses()
            ->completed()
            ->with('survey')
            ->get();

        $inProgressSurvey = $student->surveyResponses()
            ->inProgress()
            ->with('survey')
            ->first();

        return Inertia::render('Survey/Index', [
            'availableSurveys' => $availableSurveys,
            'completedSurveys' => $completedSurveys,
            'inProgressSurvey' => $inProgressSurvey,
            'student' => $student->load('learningStyleProfile'),
        ]);
    }

    /**
     * Show specific survey for taking
     */
    public function show(LearningStyleSurvey $survey): Response
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Access denied. Students only.');
        }

        if (!$survey->is_active || !$survey->published_at || $survey->published_at > now()) {
            abort(404, 'Survey not available.');
        }

        // Check if student already completed this survey
        $existingResponse = $student->surveyResponses()
            ->where('survey_id', $survey->id)
            ->first();

        if ($existingResponse && $existingResponse->isCompleted()) {
            return redirect()->route('survey.results', $existingResponse->id)
                ->with('message', 'You have already completed this survey.');
        }

        // Get or create survey response for tracking progress
        $surveyResponse = $existingResponse ?: SurveyResponse::create([
            'survey_id' => $survey->id,
            'student_id' => $student->id,
            'responses' => [],
            'status' => 'started',
            'started_at' => now(),
            'session_id' => session()->getId(),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
            ],
        ]);

        return Inertia::render('Survey/Take', [
            'survey' => $survey,
            'surveyResponse' => $surveyResponse,
            'progress' => $surveyResponse->getCompletionPercentage(),
        ]);
    }

    /**
     * Store survey response (AJAX endpoint)
     */
    public function storeResponse(Request $request, LearningStyleSurvey $survey): JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'responses' => 'required|array',
            'responses.*' => 'required|integer|between:1,5',
            'is_complete' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $surveyResponse = SurveyResponse::where('survey_id', $survey->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$surveyResponse) {
            return response()->json(['error' => 'Survey response not found'], 404);
        }

        if ($surveyResponse->isCompleted()) {
            return response()->json(['error' => 'Survey already completed'], 409);
        }

        // Update survey response
        $updateData = [
            'responses' => $request->responses,
            'status' => $request->is_complete ? 'completed' : 'in_progress',
        ];

        if ($request->is_complete) {
            $updateData['completed_at'] = now();
            $updateData['time_spent_seconds'] = now()->diffInSeconds($surveyResponse->started_at);
        }

        $surveyResponse->update($updateData);

        // If completed, trigger AI analysis
        if ($request->is_complete) {
            try {
                $profile = $this->classifier->analyzeSurveyResponse($surveyResponse);
                
                return response()->json([
                    'message' => 'Survey completed successfully',
                    'redirect_url' => route('survey.results', $surveyResponse->id),
                    'profile' => $profile,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Survey saved but analysis failed. Please contact support.',
                    'details' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Progress saved',
            'completion_percentage' => $surveyResponse->getCompletionPercentage(),
        ]);
    }

    /**
     * Show survey results and learning style profile
     */
    public function showResults(SurveyResponse $surveyResponse): Response
    {
        $user = Auth::user();
        
        // Check authorization
        if ($surveyResponse->student->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        if (!$surveyResponse->isCompleted()) {
            return redirect()->route('survey.show', $surveyResponse->survey_id)
                ->with('message', 'Please complete the survey first.');
        }

        $profile = $surveyResponse->student->learningStyleProfile;
        
        // Generate recommendations
        $recommendations = $this->classifier->generateRecommendations($profile);
        
        // Get peer comparison data
        $peerComparison = $this->classifier->compareWithPeers($surveyResponse->student);

        return Inertia::render('Survey/Results', [
            'surveyResponse' => $surveyResponse->load('survey'),
            'profile' => $profile,
            'recommendations' => $recommendations,
            'peerComparison' => $peerComparison,
            'student' => $surveyResponse->student,
        ]);
    }

    /**
     * Get student's learning style evolution over time
     */
    public function getStyleEvolution(): JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $evolution = $this->classifier->getStyleEvolution($student);

        return response()->json([
            'evolution' => $evolution,
            'current_profile' => $student->learningStyleProfile,
        ]);
    }

    /**
     * Admin: Manage surveys
     */
    public function adminIndex(): Response
    {
        $this->authorize('viewAny', LearningStyleSurvey::class);

        $surveys = LearningStyleSurvey::with('creator')
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_surveys' => LearningStyleSurvey::count(),
            'active_surveys' => LearningStyleSurvey::active()->count(),
            'total_responses' => SurveyResponse::count(),
            'completed_responses' => SurveyResponse::completed()->count(),
        ];

        return Inertia::render('Admin/Surveys/Index', [
            'surveys' => $surveys,
            'stats' => $stats,
        ]);
    }

    /**
     * Admin: Create new survey
     */
    public function create(): Response
    {
        $this->authorize('create', LearningStyleSurvey::class);

        return Inertia::render('Admin/Surveys/Create');
    }

    /**
     * Admin: Store new survey
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LearningStyleSurvey::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|in:id,en',
            'questions' => 'required|array|min:3',
            'questions.*.id' => 'required|string',
            'questions.*.text' => 'required|string',
            'questions.*.category' => 'required|in:visual,auditory,kinesthetic',
            'time_limit_minutes' => 'required|integer|min:5|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $survey = LearningStyleSurvey::create([
            'title' => $request->title,
            'description' => $request->description,
            'language' => $request->language,
            'questions' => $request->questions,
            'scoring_rules' => $this->generateScoringRules($request->questions),
            'time_limit_minutes' => $request->time_limit_minutes,
            'is_active' => false, // Admin must manually activate
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Survey created successfully',
            'survey' => $survey,
            'redirect_url' => route('admin.surveys.show', $survey->id),
        ]);
    }

    /**
     * Admin: Toggle survey status
     */
    public function toggleStatus(LearningStyleSurvey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $survey->update([
            'is_active' => !$survey->is_active,
            'published_at' => !$survey->is_active ? null : ($survey->published_at ?? now()),
        ]);

        return response()->json([
            'message' => 'Survey status updated',
            'is_active' => $survey->is_active,
        ]);
    }

    /**
     * Generate scoring rules based on questions
     */
    private function generateScoringRules(array $questions): array
    {
        $categories = ['visual', 'auditory', 'kinesthetic'];
        $rules = [];

        foreach ($categories as $category) {
            $categoryQuestions = array_filter($questions, function ($q) use ($category) {
                return $q['category'] === $category;
            });
            
            $rules[$category] = [
                'question_ids' => array_column($categoryQuestions, 'id'),
                'weight' => 1.0,
                'max_score' => count($categoryQuestions) * 5, // 5-point Likert scale
            ];
        }

        return $rules;
    }
}