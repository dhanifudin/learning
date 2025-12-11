<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Assessment;
use App\Models\FeedbackLog;
use App\Models\LearningActivity;
use App\Services\GeminiAIService;
use Illuminate\Support\Collection;

class FeedbackGenerationService
{
    protected GeminiAIService $geminiService;
    protected AnalyticsAggregationService $analyticsService;

    public function __construct(GeminiAIService $geminiService, AnalyticsAggregationService $analyticsService)
    {
        $this->geminiService = $geminiService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Generate feedback for an assessment
     */
    public function generateAssessmentFeedback(Assessment $assessment): FeedbackLog
    {
        $student = $assessment->student;
        $learningStyle = $student->learningStyleProfile;
        
        $performanceContext = $this->buildPerformanceContext($student, $assessment);
        $feedbackData = $this->generateAIFeedback($assessment, $student, $performanceContext);
        
        return FeedbackLog::create([
            'student_id' => $student->id,
            'assessment_id' => $assessment->id,
            'feedback_type' => 'auto',
            'feedback_text' => $feedbackData['feedback_text'],
            'action_items' => $feedbackData['action_items'],
            'sentiment' => $feedbackData['sentiment'],
            'is_read' => false,
        ]);
    }

    /**
     * Generate personalized study recommendations
     */
    public function generateStudyRecommendations(Student $student): FeedbackLog
    {
        $analyticsData = $this->buildStudyAnalyticsContext($student);
        $recommendations = $this->generateStudyFeedback($student, $analyticsData);
        
        return FeedbackLog::create([
            'student_id' => $student->id,
            'feedback_type' => 'system',
            'feedback_text' => $recommendations['feedback_text'],
            'action_items' => $recommendations['action_items'],
            'sentiment' => $recommendations['sentiment'],
            'is_read' => false,
        ]);
    }

    /**
     * Generate motivational feedback based on engagement
     */
    public function generateMotivationalFeedback(Student $student): ?FeedbackLog
    {
        $recentEngagement = $this->calculateRecentEngagement($student);
        
        // Only generate motivational feedback if engagement is low or declining
        if ($recentEngagement['score'] > 70 && $recentEngagement['trend'] >= 0) {
            return null;
        }
        
        $motivationalData = $this->generateMotivationalMessage($student, $recentEngagement);
        
        return FeedbackLog::create([
            'student_id' => $student->id,
            'feedback_type' => 'system',
            'feedback_text' => $motivationalData['feedback_text'],
            'action_items' => $motivationalData['action_items'],
            'sentiment' => 'constructive',
            'is_read' => false,
        ]);
    }

    /**
     * Build performance context for AI feedback generation
     */
    private function buildPerformanceContext(Student $student, Assessment $assessment): array
    {
        $recentAssessments = Assessment::where('student_id', $student->id)
            ->where('subject', $assessment->subject)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $previousAverage = $recentAssessments->where('id', '!=', $assessment->id)->avg('percentage') ?? 0;
        
        return [
            'current_score' => $assessment->percentage,
            'previous_average' => $previousAverage,
            'improvement' => $assessment->percentage - $previousAverage,
            'subject' => $assessment->subject,
            'topic' => $assessment->topic,
            'difficulty' => $assessment->difficulty_level,
            'time_taken' => $assessment->time_taken_seconds,
            'learning_style' => $student->learningStyleProfile?->dominant_style,
            'recent_performance_trend' => $this->calculatePerformanceTrend($recentAssessments),
        ];
    }

    /**
     * Build study analytics context
     */
    private function buildStudyAnalyticsContext(Student $student): array
    {
        $weeklyAnalytics = $this->analyticsService->getStudentAnalytics(
            $student,
            now()->subWeek(),
            now()
        );
        
        $recentActivities = LearningActivity::where('student_id', $student->id)
            ->where('created_at', '>=', now()->subWeek())
            ->get();
            
        return [
            'weekly_study_hours' => $weeklyAnalytics['time_metrics']['total_hours'],
            'engagement_score' => $weeklyAnalytics['engagement']['score'],
            'completion_rate' => $this->calculateCompletionRate($recentActivities),
            'preferred_study_times' => $this->identifyPreferredStudyTimes($recentActivities),
            'challenging_topics' => $this->identifyChallengingTopics($student),
            'learning_style' => $student->learningStyleProfile?->dominant_style,
        ];
    }

    /**
     * Generate AI-powered feedback using Gemini
     */
    private function generateAIFeedback(Assessment $assessment, Student $student, array $context): array
    {
        $locale = $student->preferred_language ?? 'id';
        $prompt = $this->buildAssessmentFeedbackPrompt($assessment, $context, $locale);
        
        try {
            $response = $this->geminiService->generateContent($prompt);
            return $this->parseAIFeedbackResponse(['candidates' => [['content' => ['parts' => [['text' => $response]]]]]]);
        } catch (\Exception $e) {
            // Fallback to rule-based feedback if AI fails
            return $this->generateRuleBasedFeedback($assessment, $context, $locale);
        }
    }

    /**
     * Generate study feedback using AI
     */
    private function generateStudyFeedback(Student $student, array $analyticsData): array
    {
        $locale = $student->preferred_language ?? 'id';
        $prompt = $this->buildStudyRecommendationPrompt($student, $analyticsData, $locale);
        
        try {
            $response = $this->geminiService->generateContent($prompt);
            return $this->parseAIFeedbackResponse(['candidates' => [['content' => ['parts' => [['text' => $response]]]]]]);
        } catch (\Exception $e) {
            return $this->generateRuleBasedStudyFeedback($analyticsData, $locale);
        }
    }

    /**
     * Build assessment feedback prompt for AI
     */
    private function buildAssessmentFeedbackPrompt(Assessment $assessment, array $context, string $locale): string
    {
        $templates = [
            'id' => "Seorang siswa kelas {grade} baru saja menyelesaikan penilaian {subject} dengan hasil:\n" .
                   "- Skor: {score}%\n" .
                   "- Rata-rata sebelumnya: {prev_avg}%\n" .
                   "- Topik: {topic}\n" .
                   "- Tingkat kesulitan: {difficulty}\n" .
                   "- Waktu pengerjaan: {time} menit\n" .
                   "- Gaya belajar: {learning_style}\n" .
                   "- Tren performa: {trend}\n\n" .
                   "Buatkan feedback yang:\n" .
                   "1. Mengakui pencapaian dan memberikan dorongan\n" .
                   "2. Mengidentifikasi area yang perlu diperbaiki\n" .
                   "3. Memberikan 3-5 saran konkret yang disesuaikan dengan gaya belajar\n" .
                   "4. Menggunakan bahasa yang positif dan memotivasi\n" .
                   "5. Menyertakan rencana tindak lanjut yang spesifik\n\n" .
                   "Berikan respon dalam format JSON dengan struktur:\n" .
                   "{\n" .
                   "  \"feedback_text\": \"teks feedback utama\",\n" .
                   "  \"sentiment\": \"positive|neutral|constructive\",\n" .
                   "  \"action_items\": [\n" .
                   "    {\n" .
                   "      \"priority\": \"high|medium|low\",\n" .
                   "      \"action\": \"deskripsi tindakan\",\n" .
                   "      \"resources\": [\"sumber 1\", \"sumber 2\"]\n" .
                   "    }\n" .
                   "  ]\n" .
                   "}",
            
            'en' => "A grade {grade} student has just completed a {subject} assessment with the following results:\n" .
                   "- Score: {score}%\n" .
                   "- Previous average: {prev_avg}%\n" .
                   "- Topic: {topic}\n" .
                   "- Difficulty level: {difficulty}\n" .
                   "- Time taken: {time} minutes\n" .
                   "- Learning style: {learning_style}\n" .
                   "- Performance trend: {trend}\n\n" .
                   "Generate feedback that:\n" .
                   "1. Acknowledges achievement and provides encouragement\n" .
                   "2. Identifies areas for improvement\n" .
                   "3. Provides 3-5 concrete suggestions adapted to learning style\n" .
                   "4. Uses positive and motivating language\n" .
                   "5. Includes specific follow-up action plan\n\n" .
                   "Respond in JSON format with structure:\n" .
                   "{\n" .
                   "  \"feedback_text\": \"main feedback text\",\n" .
                   "  \"sentiment\": \"positive|neutral|constructive\",\n" .
                   "  \"action_items\": [\n" .
                   "    {\n" .
                   "      \"priority\": \"high|medium|low\",\n" .
                   "      \"action\": \"action description\",\n" .
                   "      \"resources\": [\"resource 1\", \"resource 2\"]\n" .
                   "    }\n" .
                   "  ]\n" .
                   "}"
        ];

        $template = $templates[$locale] ?? $templates['en'];
        
        return strtr($template, [
            '{grade}' => $assessment->student->grade_level,
            '{subject}' => $context['subject'],
            '{score}' => number_format($context['current_score'], 1),
            '{prev_avg}' => number_format($context['previous_average'], 1),
            '{topic}' => $context['topic'],
            '{difficulty}' => $context['difficulty'],
            '{time}' => round($context['time_taken'] / 60),
            '{learning_style}' => $context['learning_style'] ?? 'belum diketahui',
            '{trend}' => $context['recent_performance_trend'] > 0 ? 'meningkat' : ($context['recent_performance_trend'] < 0 ? 'menurun' : 'stabil'),
        ]);
    }

    /**
     * Build study recommendation prompt for AI
     */
    private function buildStudyRecommendationPrompt(Student $student, array $analyticsData, string $locale): string
    {
        $templates = [
            'id' => "Seorang siswa dengan profil belajar sebagai berikut:\n" .
                   "- Gaya belajar: {learning_style}\n" .
                   "- Jam belajar minggu ini: {study_hours} jam\n" .
                   "- Skor keterlibatan: {engagement}%\n" .
                   "- Tingkat penyelesaian: {completion}%\n" .
                   "- Waktu belajar optimal: {preferred_times}\n" .
                   "- Topik yang menantang: {challenging_topics}\n\n" .
                   "Buatkan rekomendasi belajar yang:\n" .
                   "1. Mengoptimalkan waktu dan metode belajar\n" .
                   "2. Meningkatkan keterlibatan dan motivasi\n" .
                   "3. Mengatasi kesulitan pada topik tertentu\n" .
                   "4. Disesuaikan dengan gaya belajar dominan\n" .
                   "5. Praktis dan dapat ditindaklanjuti\n\n" .
                   "Format JSON yang sama seperti sebelumnya.",
            
            'en' => "A student with the following learning profile:\n" .
                   "- Learning style: {learning_style}\n" .
                   "- Study hours this week: {study_hours} hours\n" .
                   "- Engagement score: {engagement}%\n" .
                   "- Completion rate: {completion}%\n" .
                   "- Optimal study times: {preferred_times}\n" .
                   "- Challenging topics: {challenging_topics}\n\n" .
                   "Generate study recommendations that:\n" .
                   "1. Optimize study time and methods\n" .
                   "2. Increase engagement and motivation\n" .
                   "3. Address difficulties with specific topics\n" .
                   "4. Are adapted to dominant learning style\n" .
                   "5. Are practical and actionable\n\n" .
                   "Same JSON format as before."
        ];

        $template = $templates[$locale] ?? $templates['en'];
        
        return strtr($template, [
            '{learning_style}' => $analyticsData['learning_style'] ?? 'unknown',
            '{study_hours}' => number_format($analyticsData['weekly_study_hours'], 1),
            '{engagement}' => number_format($analyticsData['engagement_score'], 1),
            '{completion}' => number_format($analyticsData['completion_rate'], 1),
            '{preferred_times}' => implode(', ', $analyticsData['preferred_study_times']),
            '{challenging_topics}' => implode(', ', array_slice($analyticsData['challenging_topics'], 0, 3)),
        ]);
    }

    /**
     * Parse AI response and extract structured data
     */
    private function parseAIFeedbackResponse(array $response): array
    {
        // Extract JSON from AI response
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Try to extract JSON from the response
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $jsonData = json_decode($matches[0], true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'feedback_text' => $jsonData['feedback_text'] ?? '',
                    'sentiment' => $jsonData['sentiment'] ?? 'neutral',
                    'action_items' => $jsonData['action_items'] ?? [],
                ];
            }
        }
        
        // Fallback if JSON parsing fails
        return [
            'feedback_text' => $content,
            'sentiment' => 'neutral',
            'action_items' => [],
        ];
    }

    /**
     * Generate rule-based feedback as fallback
     */
    private function generateRuleBasedFeedback(Assessment $assessment, array $context, string $locale): array
    {
        $score = $context['current_score'];
        $improvement = $context['improvement'];
        
        $templates = [
            'id' => [
                'excellent' => "Luar biasa! Anda berhasil meraih skor {score}%. Ini menunjukkan pemahaman yang sangat baik terhadap materi {topic}.",
                'good' => "Bagus! Anda mendapat skor {score}% untuk {topic}. Ada beberapa area yang bisa ditingkatkan.",
                'needs_improvement' => "Skor Anda adalah {score}% untuk {topic}. Jangan khawatir, mari kita bersama-sama meningkatkan pemahaman Anda.",
            ],
            'en' => [
                'excellent' => "Excellent! You achieved a score of {score}%. This shows very good understanding of {topic}.",
                'good' => "Good! You scored {score}% on {topic}. There are some areas that can be improved.",
                'needs_improvement' => "Your score is {score}% for {topic}. Don't worry, let's work together to improve your understanding.",
            ],
        ];
        
        $level = $score >= 80 ? 'excellent' : ($score >= 60 ? 'good' : 'needs_improvement');
        $sentiment = $score >= 80 ? 'positive' : ($score >= 60 ? 'neutral' : 'constructive');
        
        $feedback = strtr($templates[$locale][$level], [
            '{score}' => number_format($score, 1),
            '{topic}' => $context['topic'],
        ]);
        
        $actionItems = $this->generateRuleBasedActionItems($context, $locale);
        
        return [
            'feedback_text' => $feedback,
            'sentiment' => $sentiment,
            'action_items' => $actionItems,
        ];
    }

    /**
     * Generate rule-based study feedback as fallback
     */
    private function generateRuleBasedStudyFeedback(array $analyticsData, string $locale): array
    {
        $engagementScore = $analyticsData['engagement_score'] ?? 0;
        $studyHours = $analyticsData['weekly_study_hours'] ?? 0;
        
        $templates = [
            'id' => [
                'low_engagement' => "Kami melihat tingkat keterlibatan belajar Anda adalah {$engagementScore}%. Mari tingkatkan dengan jadwal yang lebih konsisten.",
                'low_hours' => "Waktu belajar Anda hanya {$studyHours} jam minggu ini. Cobalah targetkan 10-15 jam per minggu.",
                'general' => "Terus pertahankan semangat belajar Anda! Ada beberapa area yang bisa ditingkatkan.",
            ],
            'en' => [
                'low_engagement' => "We see your learning engagement is {$engagementScore}%. Let's improve with a more consistent schedule.",
                'low_hours' => "Your study time was only {$studyHours} hours this week. Try to target 10-15 hours per week.",
                'general' => "Keep up your learning enthusiasm! There are some areas that can be improved.",
            ],
        ];
        
        $feedbackKey = $engagementScore < 50 ? 'low_engagement' : ($studyHours < 5 ? 'low_hours' : 'general');
        $feedback = $templates[$locale][$feedbackKey] ?? $templates['en'][$feedbackKey];
        
        $actionItems = [
            [
                'priority' => 'high',
                'action' => $locale === 'id' ? 'Tetapkan jadwal belajar yang konsisten' : 'Set a consistent study schedule',
                'resources' => [$locale === 'id' ? 'Aplikasi pengingat' : 'Reminder apps'],
            ],
        ];
        
        return [
            'feedback_text' => $feedback,
            'sentiment' => 'constructive',
            'action_items' => $actionItems,
        ];
    }

    /**
     * Generate rule-based action items
     */
    private function generateRuleBasedActionItems(array $context, string $locale): array
    {
        $actions = [];
        $score = $context['current_score'];
        $learningStyle = $context['learning_style'];
        
        if ($score < 70) {
            $actions[] = [
                'priority' => 'high',
                'action' => $locale === 'id' ? 'Ulangi materi dasar ' . $context['topic'] : 'Review basic materials for ' . $context['topic'],
                'resources' => [$locale === 'id' ? 'Video pembelajaran' : 'Learning videos'],
            ];
        }
        
        if ($learningStyle === 'visual') {
            $actions[] = [
                'priority' => 'medium',
                'action' => $locale === 'id' ? 'Gunakan diagram dan mind map untuk memahami konsep' : 'Use diagrams and mind maps to understand concepts',
                'resources' => [$locale === 'id' ? 'Materi visual' : 'Visual materials'],
            ];
        }
        
        return $actions;
    }

    /**
     * Calculate performance trend from recent assessments
     */
    private function calculatePerformanceTrend(Collection $assessments): float
    {
        if ($assessments->count() < 2) {
            return 0;
        }
        
        $scores = $assessments->pluck('percentage');
        $trend = 0;
        
        for ($i = 1; $i < $scores->count(); $i++) {
            $trend += $scores[$i] - $scores[$i - 1];
        }
        
        return $trend / ($scores->count() - 1);
    }

    /**
     * Calculate recent engagement metrics
     */
    private function calculateRecentEngagement(Student $student): array
    {
        $weeklyAnalytics = $this->analyticsService->getStudentAnalytics(
            $student,
            now()->subWeek(),
            now()
        );
        
        $previousWeekAnalytics = $this->analyticsService->getStudentAnalytics(
            $student,
            now()->subWeeks(2),
            now()->subWeek()
        );
        
        $currentScore = $weeklyAnalytics['engagement']['score'];
        $previousScore = $previousWeekAnalytics['engagement']['score'];
        
        return [
            'score' => $currentScore,
            'trend' => $currentScore - $previousScore,
            'sessions' => $weeklyAnalytics['engagement']['sessions'],
            'completions' => $weeklyAnalytics['engagement']['completions'],
        ];
    }

    /**
     * Generate motivational message for low engagement
     */
    private function generateMotivationalMessage(Student $student, array $engagement): array
    {
        $locale = $student->preferred_language ?? 'id';
        
        $messages = [
            'id' => [
                'low_engagement' => "Kami perhatikan aktivitas belajar Anda menurun dalam beberapa hari terakhir. Jangan khawatir, ini normal terjadi! Mari kita buat rencana untuk kembali semangat belajar.",
                'declining_trend' => "Semangat belajar Anda sepertinya sedang menurun. Ayo kita cari cara untuk membuat belajar lebih menyenangkan dan menarik lagi!"
            ],
            'en' => [
                'low_engagement' => "We noticed your learning activity has decreased in recent days. Don't worry, this is normal! Let's create a plan to get back on track.",
                'declining_trend' => "Your learning momentum seems to be declining. Let's find ways to make learning more enjoyable and engaging again!"
            ]
        ];
        
        $messageType = $engagement['score'] < 30 ? 'low_engagement' : 'declining_trend';
        
        return [
            'feedback_text' => $messages[$locale][$messageType],
            'sentiment' => 'constructive',
            'action_items' => $this->generateMotivationalActionItems($locale),
        ];
    }

    /**
     * Generate motivational action items
     */
    private function generateMotivationalActionItems(string $locale): array
    {
        $items = [
            'id' => [
                [
                    'priority' => 'high',
                    'action' => 'Tetapkan target belajar harian yang realistis (15-30 menit)',
                    'resources' => ['Timer belajar', 'Aplikasi pengingat'],
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Pilih topik yang menarik minat Anda untuk dipelajari terlebih dahulu',
                    'resources' => ['Konten interaktif', 'Video pembelajaran'],
                ],
            ],
            'en' => [
                [
                    'priority' => 'high',
                    'action' => 'Set realistic daily study goals (15-30 minutes)',
                    'resources' => ['Study timer', 'Reminder app'],
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Choose topics that interest you to study first',
                    'resources' => ['Interactive content', 'Learning videos'],
                ],
            ],
        ];
        
        return $items[$locale] ?? $items['en'];
    }

    /**
     * Calculate completion rate from activities
     */
    private function calculateCompletionRate(Collection $activities): float
    {
        $totalViews = $activities->where('activity_type', 'view')->count();
        $completions = $activities->where('activity_type', 'complete')->count();
        
        return $totalViews > 0 ? ($completions / $totalViews) * 100 : 0;
    }

    /**
     * Identify preferred study times based on activities
     */
    private function identifyPreferredStudyTimes(Collection $activities): array
    {
        $hourCounts = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('H');
        })->map->count()->sortDesc()->take(3);
        
        return $hourCounts->keys()->map(function ($hour) {
            return sprintf('%02d:00', $hour);
        })->toArray();
    }

    /**
     * Identify challenging topics for the student
     */
    private function identifyChallengingTopics(Student $student): array
    {
        $poorAssessments = Assessment::where('student_id', $student->id)
            ->where('percentage', '<', 70)
            ->where('created_at', '>=', now()->subMonth())
            ->get()
            ->groupBy('topic')
            ->map(function ($assessments) {
                return [
                    'topic' => $assessments->first()->topic,
                    'avg_score' => $assessments->avg('percentage'),
                    'count' => $assessments->count(),
                ];
            })
            ->sortBy('avg_score')
            ->take(5);
        
        return $poorAssessments->pluck('topic')->toArray();
    }
}