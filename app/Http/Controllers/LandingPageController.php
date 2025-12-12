<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\DemoSession;
use App\Mail\ContactFormSubmission;
use App\Mail\NewsletterSubscription;
use App\Services\GeminiAIService;
use Laravel\Fortify\Features;

/**
 * Landing Page Controller for AI-Powered Learning Platform
 * 
 * Handles all landing page routes, lead generation, demo interactions,
 * and marketing-related functionality for user acquisition.
 */
class LandingPageController extends Controller
{
    protected GeminiAIService $geminiService;

    public function __construct(GeminiAIService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Display the main landing page
     */
    public function index(Request $request)
    {
        // Get visitor analytics data
        $visitorData = $this->getVisitorAnalytics($request);
        
        // Cache frequently requested data
        $landingData = Cache::remember('landing_page_data', 3600, function () {
            return [
                'student_testimonials' => $this->getStudentTestimonials(),
                'teacher_testimonials' => $this->getTeacherTestimonials(),
                'platform_statistics' => $this->getPlatformStatistics(),
                'feature_highlights' => $this->getFeatureHighlights(),
                'success_stories' => $this->getSuccessStories(),
            ];
        });

        return Inertia::render('Landing/Index', [
            'canRegister' => Features::enabled(Features::registration()),
            'auth' => [
                'user' => $request->user()
            ],
            'visitor' => $visitorData,
            'testimonials' => [
                'students' => $landingData['student_testimonials'],
                'teachers' => $landingData['teacher_testimonials'],
            ],
            'statistics' => $landingData['platform_statistics'],
            'features' => $landingData['feature_highlights'],
            'successStories' => $landingData['success_stories'],
            'meta' => [
                'title' => 'Platform Pembelajaran AI - Belajar Personal untuk Siswa SMA/SMK Indonesia',
                'description' => 'Sistem pembelajaran berbasis AI yang menganalisis gaya belajar dan memberikan konten personal untuk siswa SMA/SMK. Tingkatkan nilai dengan pembelajaran yang tepat sasaran.',
                'keywords' => 'pembelajaran AI, gaya belajar, SMA, SMK, matematika, Indonesia, personalisasi',
            ],
        ]);
    }

    /**
     * Display interactive demo page
     */
    public function demo(Request $request)
    {
        // Create demo session for tracking
        $demoSession = DemoSession::create([
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'started_at' => now(),
        ]);

        return Inertia::render('Landing/Demo', [
            'demoSession' => $demoSession->id,
            'demoQuestions' => $this->getDemoQuestions(),
            'sampleContent' => $this->getSampleContent(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    /**
     * Process demo learning style assessment
     */
    public function demoAssessment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'demo_session_id' => 'required|exists:demo_sessions,id',
            'responses' => 'required|array|min:5',
            'responses.*' => 'required|integer|min:1|max:5',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Process demo assessment with simplified AI analysis
        $responses = $request->get('responses');
        $analysis = $this->processDemoAssessment($responses);

        // Update demo session
        $demoSession = DemoSession::find($request->get('demo_session_id'));
        $demoSession->update([
            'responses' => $responses,
            'analysis_result' => $analysis,
            'completed_at' => now(),
            'lead_name' => $request->get('name'),
            'lead_email' => $request->get('email'),
        ]);

        // Create lead if email provided
        if ($request->get('email')) {
            $this->createLead($request, $analysis, 'demo_completion');
        }

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'recommendations' => $this->getDemoRecommendations($analysis['dominant_style']),
            'nextSteps' => $this->getNextSteps(),
        ]);
    }

    /**
     * Handle contact form submissions
     */
    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:student,teacher,school,general',
            'school_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create lead record
        $lead = $this->createLead($request, [], 'contact_form');

        // Send notification email
        Mail::to(config('mail.contact_email', 'info@learning.example.com'))
            ->send(new ContactFormSubmission($request->validated(), $lead));

        // Send auto-response to user
        $this->sendAutoResponse($request->get('email'), $request->get('name'), $request->get('type'));

        return back()->with('success', 'Terima kasih! Pesan Anda telah dikirim. Tim kami akan menghubungi Anda dalam 1-2 hari kerja.');
    }

    /**
     * Handle newsletter subscriptions
     */
    public function newsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:leads,email',
            'interests' => 'nullable|array',
            'interests.*' => 'string|in:student_tips,teacher_resources,platform_updates,ai_insights',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar atau tidak valid.',
            ], 422);
        }

        // Create newsletter lead
        $lead = Lead::create([
            'email' => $request->get('email'),
            'source' => 'newsletter',
            'type' => 'newsletter_subscription',
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'metadata' => [
                'interests' => $request->get('interests', []),
                'subscribed_at' => now()->toISOString(),
            ],
        ]);

        // Send welcome email
        Mail::to($lead->email)->send(new NewsletterSubscription($lead));

        return response()->json([
            'success' => true,
            'message' => 'Berhasil berlangganan newsletter! Periksa email Anda untuk konfirmasi.',
        ]);
    }

    /**
     * Display features page
     */
    public function features(Request $request)
    {
        return Inertia::render('Landing/Features', [
            'features' => $this->getDetailedFeatures(),
            'comparisons' => $this->getFeatureComparisons(),
            'integrations' => $this->getIntegrationPartners(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }


    /**
     * Display about page
     */
    public function about(Request $request)
    {
        return Inertia::render('Landing/About', [
            'team' => $this->getTeamMembers(),
            'mission' => $this->getMissionStatement(),
            'timeline' => $this->getCompanyTimeline(),
            'awards' => $this->getAwardsAndRecognitions(),
            'partners' => $this->getEducationalPartners(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    /**
     * Get visitor analytics data
     */
    protected function getVisitorAnalytics(Request $request): array
    {
        return [
            'is_mobile' => $request->header('User-Agent') && preg_match('/Mobile|Android|iPhone/', $request->header('User-Agent')),
            'referrer' => $request->header('Referer'),
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'session_id' => $request->session()->getId(),
        ];
    }

    /**
     * Get student testimonials
     */
    protected function getStudentTestimonials(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Ahmad Ridwan',
                'grade' => '11 IPA',
                'school' => 'SMAN 1 Jakarta',
                'photo' => '/images/testimonials/ahmad.jpg',
                'quote' => 'Nilai matematika saya naik dari 70 menjadi 85 setelah menggunakan platform ini. AI-nya benar-benar memahami cara belajar saya!',
                'improvement' => '+15 poin',
                'learning_style' => 'Visual',
                'verified' => true,
            ],
            [
                'id' => 2,
                'name' => 'Siti Nurhaliza',
                'grade' => '12 IPA',
                'school' => 'SMAN 3 Bandung',
                'photo' => '/images/testimonials/siti.jpg',
                'quote' => 'Platform ini membantu saya memahami konsep trigonometri yang susah. Video dan simulasi interaktifnya sangat membantu.',
                'improvement' => '+20 poin',
                'learning_style' => 'Kinesthetic',
                'verified' => true,
            ],
            [
                'id' => 3,
                'name' => 'Budi Santoso',
                'grade' => '10 IPA',
                'school' => 'SMAN 2 Surabaya',
                'photo' => '/images/testimonials/budi.jpg',
                'quote' => 'Fitur analitik membantu saya melihat progress belajar dan area yang perlu diperbaiki. Sangat motivating!',
                'improvement' => '+18 poin',
                'learning_style' => 'Auditory',
                'verified' => true,
            ],
        ];
    }

    /**
     * Get teacher testimonials
     */
    protected function getTeacherTestimonials(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Dr. Indira Sari, M.Pd',
                'position' => 'Guru Matematika Senior',
                'school' => 'SMAN 1 Jakarta',
                'photo' => '/images/testimonials/indira.jpg',
                'quote' => 'Platform ini membantu saya memahami gaya belajar setiap siswa dan menyesuaikan metode mengajar. Hasilnya luar biasa!',
                'experience' => '15 tahun mengajar',
                'students_helped' => '500+',
                'verified' => true,
            ],
            [
                'id' => 2,
                'name' => 'Pak Joko Widodo, S.Pd',
                'position' => 'Kepala Sekolah',
                'school' => 'SMAN 5 Yogyakarta',
                'photo' => '/images/testimonials/joko.jpg',
                'quote' => 'Analitik yang disediakan membantu kami mengidentifikasi siswa yang membutuhkan bantuan tambahan lebih awal.',
                'experience' => '20 tahun di bidang pendidikan',
                'students_helped' => '1000+',
                'verified' => true,
            ],
        ];
    }

    /**
     * Get platform statistics
     */
    protected function getPlatformStatistics(): array
    {
        return [
            'active_students' => 25000,
            'partner_schools' => 150,
            'content_items' => 5000,
            'assessments_completed' => 100000,
            'average_improvement' => 22.5,
            'teacher_satisfaction' => 94,
            'student_engagement' => 87,
            'success_rate' => 89,
        ];
    }

    /**
     * Get feature highlights for landing page
     */
    protected function getFeatureHighlights(): array
    {
        return [
            [
                'icon' => 'brain-circuit',
                'title' => 'Analisis Gaya Belajar AI',
                'description' => 'Kecerdasan buatan menganalisis preferensi belajar dan memberikan konten yang disesuaikan',
                'benefits' => ['Pembelajaran lebih efektif', 'Waktu belajar lebih efisien', 'Hasil yang terukur'],
            ],
            [
                'icon' => 'target-arrow',
                'title' => 'Konten Personal',
                'description' => 'Materi pembelajaran yang disesuaikan dengan gaya belajar dan tingkat kemampuan',
                'benefits' => ['Video untuk visual learner', 'Audio untuk auditory learner', 'Simulasi untuk kinesthetic learner'],
            ],
            [
                'icon' => 'chart-analytics',
                'title' => 'Analytics Mendalam',
                'description' => 'Pantau progress belajar dengan analitik komprehensif dan insight yang actionable',
                'benefits' => ['Progress tracking real-time', 'Identifikasi kelemahan', 'Rekomendasi improvement'],
            ],
            [
                'icon' => 'graduation-cap',
                'title' => 'Kurikulum Indonesia',
                'description' => 'Konten sesuai kurikulum SMA/SMK Indonesia dengan fokus pada Mathematics',
                'benefits' => ['Materi sesuai kurikulum', 'Persiapan ujian nasional', 'Bahasa Indonesia'],
            ],
        ];
    }

    /**
     * Process demo learning style assessment
     */
    protected function processDemoAssessment(array $responses): array
    {
        // Simplified analysis for demo (without full AI processing)
        $visualQuestions = [1, 2, 3];
        $auditoryQuestions = [4, 5, 6];
        $kinestheticQuestions = [7, 8, 9];

        $visualScore = 0;
        $auditoryScore = 0;
        $kinestheticScore = 0;

        foreach ($responses as $questionId => $score) {
            if (in_array($questionId, $visualQuestions)) {
                $visualScore += $score;
            } elseif (in_array($questionId, $auditoryQuestions)) {
                $auditoryScore += $score;
            } elseif (in_array($questionId, $kinestheticQuestions)) {
                $kinestheticScore += $score;
            }
        }

        // Convert to 5-point scale
        $visualScore = round($visualScore / count($visualQuestions), 1);
        $auditoryScore = round($auditoryScore / count($auditoryQuestions), 1);
        $kinestheticScore = round($kinestheticScore / count($kinestheticQuestions), 1);

        // Determine dominant style
        $scores = [
            'visual' => $visualScore,
            'auditory' => $auditoryScore,
            'kinesthetic' => $kinestheticScore,
        ];

        $dominantStyle = array_keys($scores, max($scores))[0];

        return [
            'dominant_style' => $dominantStyle,
            'visual_score' => $visualScore,
            'auditory_score' => $auditoryScore,
            'kinesthetic_score' => $kinestheticScore,
            'confidence_score' => 0.85, // Demo confidence
            'analysis_type' => 'demo',
        ];
    }

    /**
     * Get demo questions for learning style assessment
     */
    protected function getDemoQuestions(): array
    {
        return [
            [
                'id' => 1,
                'category' => 'visual',
                'text' => 'Saya lebih mudah memahami konsep matematika ketika dijelaskan menggunakan diagram atau grafik',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 2,
                'category' => 'visual',
                'text' => 'Saya suka membuat catatan berwarna-warni saat belajar',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 3,
                'category' => 'visual',
                'text' => 'Video pembelajaran lebih efektif untuk saya daripada membaca teks',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 4,
                'category' => 'auditory',
                'text' => 'Saya belajar lebih baik saat mendengarkan penjelasan guru daripada membaca sendiri',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 5,
                'category' => 'auditory',
                'text' => 'Saya suka berdiskusi dengan teman saat memecahkan soal matematika',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 6,
                'category' => 'auditory',
                'text' => 'Saya mengingat rumus matematika lebih baik setelah mengulanginya dengan suara keras',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 7,
                'category' => 'kinesthetic',
                'text' => 'Saya lebih suka praktik langsung daripada hanya teori saat belajar matematika',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 8,
                'category' => 'kinesthetic',
                'text' => 'Saya lebih fokus belajar sambil bergerak atau berjalan-jalan',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
            [
                'id' => 9,
                'category' => 'kinesthetic',
                'text' => 'Saya memahami konsep matematika lebih baik dengan menggunakan alat peraga',
                'scale' => [1, 2, 3, 4, 5],
                'labels' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
            ],
        ];
    }

    /**
     * Create lead record
     */
    protected function createLead(Request $request, array $analysisData, string $source): Lead
    {
        return Lead::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'source' => $source,
            'type' => $request->get('type', 'general'),
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'metadata' => array_merge($analysisData, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'form_data' => $request->except(['_token', '_method']),
                'created_at' => now()->toISOString(),
            ]),
        ]);
    }

    // Additional methods for getting various data...
    // (These would be implemented based on specific requirements)
    
    protected function getSampleContent(): array { return []; }
    protected function getDemoRecommendations(string $style): array { return []; }
    protected function getNextSteps(): array { return []; }
    protected function sendAutoResponse(string $email, string $name, string $type): void {}
    protected function getSuccessStories(): array { return []; }
    protected function getDetailedFeatures(): array { return []; }
    protected function getFeatureComparisons(): array { return []; }
    protected function getIntegrationPartners(): array { return []; }
    protected function getTeamMembers(): array { return []; }
    protected function getMissionStatement(): array { return []; }
    protected function getCompanyTimeline(): array { return []; }
    protected function getAwardsAndRecognitions(): array { return []; }
    protected function getEducationalPartners(): array { return []; }
}