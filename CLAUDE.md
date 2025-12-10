# Requirement Analysis: AI-Powered Personalized Learning System
## Web Application using Laravel + Vue.js

---

## 1. Executive Summary

### 1.1 Project Overview
Development of a web-based personalized learning system that leverages Google Gemini AI and data analytics to provide customized educational experiences for students. The system analyzes learning styles, academic performance, and behavioral patterns to deliver tailored content recommendations and automated feedback.

**Target Audience:** High School Students (Grades 10, 11, 12 / SMA/SMK in Indonesia)
**Initial Subject Focus:** Mathematics
**Language Support:** Indonesian (Bahasa Indonesia) and English
**AI Provider:** Google Gemini AI for intelligent content recommendation, learning style analysis, and personalized feedback generation

### 1.2 Technology Stack
- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Vue.js 3 (Composition API) with Inertia.js
- **Database**: MySQL 8.0+ / PostgreSQL 13+
- **AI Provider**: Google Gemini AI (Gemini 2.0 Flash/Pro)
- **Localization**: Laravel Localization + Vue I18n (Indonesian & English)
- **Authentication**: Laravel Breeze with Inertia
- **Real-time**: Laravel Echo + Pusher / Socket.io
- **Visualization**: Chart.js / ApexCharts
- **UI Framework**: Tailwind CSS

---

## 2. System Architecture

### 2.1 High-Level Architecture
```
┌─────────────────────────────────────────────────────────┐
│                     Frontend Layer                       │
│         Vue.js 3 + Inertia.js + Vue I18n (id/en)        │
└────────────────────┬────────────────────────────────────┘
                     │ Inertia Protocol (No API needed)
┌────────────────────▼────────────────────────────────────┐
│                   Backend Layer                          │
│           Laravel with Inertia (Controllers,             │
│            Services, Repositories)                       │
└────────┬────────────────────────────┬───────────────────┘
         │                            │
┌────────▼────────┐          ┌───────▼──────────────────┐
│   PostgreSQL    │          │   Google Gemini AI       │
│   Database      │          │   (Gemini 2.0 Flash/Pro) │
│   (with i18n)   │          │   Multi-language Support │
└─────────────────┘          └──────────────────────────┘
```

### 2.2 Laravel Application Structure
```
app/
├── Models/
│   ├── User.php
│   ├── Student.php
│   ├── Teacher.php
│   ├── LearningStyleProfile.php
│   ├── LearningActivity.php
│   ├── Assessment.php
│   ├── Content.php
│   └── Recommendation.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── StudentController.php
│   │   │   ├── TeacherController.php
│   │   │   ├── LearningStyleController.php
│   │   │   ├── RecommendationController.php
│   │   │   ├── AnalyticsController.php
│   │   │   └── ContentController.php
│   ├── Middleware/
│   │   ├── RoleMiddleware.php
│   │   └── ActivityLogMiddleware.php
│   └── Requests/
│       ├── LearningStyleSurveyRequest.php
│       └── AssessmentSubmissionRequest.php
├── Services/
│   ├── LearningStyleClassifier.php
│   ├── RecommendationEngine.php
│   ├── AnalyticsService.php
│   ├── AIServiceClient.php
│   └── FeedbackGenerator.php
├── Repositories/
│   ├── StudentRepository.php
│   ├── ContentRepository.php
│   └── AnalyticsRepository.php
└── Jobs/
    ├── ProcessLearningStyleAnalysis.php
    ├── GenerateRecommendations.php
    └── GenerateAnalyticsReport.php
```

---

## 3. Database Schema Design

### 3.1 Core Tables

#### users
```sql
id: bigint (PK)
name: varchar(255)
email: varchar(255) UNIQUE
password: varchar(255)
role: enum('student', 'teacher', 'admin')
email_verified_at: timestamp
created_at, updated_at: timestamp
```

#### students
```sql
id: bigint (PK)
user_id: bigint (FK -> users)
student_number: varchar(50) UNIQUE
grade_level: enum('10', '11', '12') -- SMA/SMK grades
class: varchar(50) -- e.g., '10 IPA 1', '11 IPS 2'
major: varchar(100) -- e.g., 'IPA' (Science), 'IPS' (Social)
learning_interests: json
enrollment_year: year
status: enum('active', 'inactive', 'graduated')
profile_completed: boolean DEFAULT false
preferred_language: enum('id', 'en') DEFAULT 'id'
created_at, updated_at: timestamp
```

#### teachers
```sql
id: bigint (PK)
user_id: bigint (FK -> users)
teacher_number: varchar(50) UNIQUE
subject: varchar(100)
department: varchar(100)
created_at, updated_at: timestamp
```

#### learning_style_profiles
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
visual_score: decimal(5,2)
auditory_score: decimal(5,2)
kinesthetic_score: decimal(5,2)
dominant_style: enum('visual', 'auditory', 'kinesthetic', 'mixed')
survey_data: json
analysis_date: timestamp
ai_confidence_score: decimal(5,2)
created_at, updated_at: timestamp
```

#### learning_activities
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
content_id: bigint (FK -> contents)
activity_type: enum('view', 'click', 'download', 'complete')
duration_seconds: int
session_id: varchar(100)
device_type: varchar(50)
ip_address: varchar(45)
metadata: json
created_at: timestamp
```

#### assessments
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
subject: varchar(100)
topic: varchar(255)
assessment_type: enum('quiz', 'exam', 'assignment')
score: decimal(5,2)
max_score: decimal(5,2)
percentage: decimal(5,2)
difficulty_level: enum('easy', 'medium', 'hard')
time_taken_seconds: int
submission_data: json
graded_by: bigint (FK -> users) NULL
graded_at: timestamp NULL
created_at, updated_at: timestamp
```

#### contents
```sql
id: bigint (PK)
title: varchar(255) -- Default title (can be overridden by translations)
description: text
subject: varchar(100) -- e.g., 'Mathematics'
topic: varchar(255) -- e.g., 'Trigonometry', 'Calculus'
grade_level: enum('10', '11', '12')
content_type: enum('video', 'pdf', 'audio', 'interactive', 'text')
target_learning_style: enum('visual', 'auditory', 'kinesthetic', 'all')
difficulty_level: enum('beginner', 'intermediate', 'advanced')
file_url: varchar(500)
external_url: varchar(500)
thumbnail_url: varchar(500)
duration_minutes: int
metadata: json
views_count: int DEFAULT 0
rating: decimal(3,2) DEFAULT 0
is_active: boolean DEFAULT true
created_by: bigint (FK -> users)
created_at, updated_at: timestamp
```

#### recommendations
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
content_id: bigint (FK -> contents)
recommendation_type: enum('learning_style', 'performance', 'hybrid')
relevance_score: decimal(5,2)
reason: text
algorithm_version: varchar(50)
is_viewed: boolean DEFAULT false
is_completed: boolean DEFAULT false
viewed_at: timestamp NULL
created_at, updated_at: timestamp
```

#### feedback_logs
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
assessment_id: bigint (FK -> assessments) NULL
feedback_type: enum('auto', 'teacher', 'system')
feedback_text: text
action_items: json
sentiment: enum('positive', 'neutral', 'constructive')
is_read: boolean DEFAULT false
read_at: timestamp NULL
created_by: bigint (FK -> users) NULL
created_at, updated_at: timestamp
```

#### analytics_reports
```sql
id: bigint (PK)
report_type: enum('student', 'class', 'school', 'teacher')
entity_id: bigint
report_data: json
period_start: date
period_end: date
generated_by: varchar(50) -- 'system' or 'user_id'
file_path: varchar(500) NULL
created_at: timestamp
```

#### competency_maps
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
subject: varchar(100)
competency_name: varchar(255)
current_level: decimal(5,2)
target_level: decimal(5,2)
progress_percentage: decimal(5,2)
last_assessment_date: date
achievements: json
created_at, updated_at: timestamp
```

### 3.2 Supporting Tables

#### learning_style_surveys
```sql
id: bigint (PK)
title: varchar(255)
description: text
questions: json -- Array of questions with Likert scale options
version: varchar(20)
is_active: boolean
created_at, updated_at: timestamp
```

#### grade_subjects
```sql
id: bigint (PK)
grade_level: enum('10', '11', '12')
subject_code: varchar(20) -- e.g., 'MTK' (Matematika)
subject_name_id: varchar(255) -- Indonesian name
subject_name_en: varchar(255) -- English name
category: enum('wajib', 'peminatan', 'lintas_minat') -- Required, Specialization, Cross-interest
is_active: boolean DEFAULT true
display_order: int
created_at, updated_at: timestamp
```

#### student_report_grades
```sql
id: bigint (PK)
student_id: bigint (FK -> students)
grade_subject_id: bigint (FK -> grade_subjects)
semester: enum('1', '2')
academic_year: varchar(9) -- e.g., '2024/2025'
knowledge_score: decimal(5,2) -- Nilai Pengetahuan
skill_score: decimal(5,2) -- Nilai Keterampilan
final_score: decimal(5,2) -- Nilai Akhir
grade_letter: varchar(2) -- A, B, C, D
description: text -- Teacher's note from Rapor
created_at, updated_at: timestamp
```

#### content_translations
```sql
id: bigint (PK)
content_id: bigint (FK -> contents)
locale: enum('id', 'en')
title: varchar(255)
description: text
metadata: json
created_at, updated_at: timestamp
UNIQUE(content_id, locale)
```

#### survey_responses
```sql
id: bigint (PK)
survey_id: bigint (FK -> learning_style_surveys)
student_id: bigint (FK -> students)
responses: json
completed_at: timestamp
created_at: timestamp
```

#### teacher_observations
```sql
id: bigint (PK)
teacher_id: bigint (FK -> teachers)
student_id: bigint (FK -> students)
observation_date: date
observation_text: text
tags: json
validation_for_ai: boolean DEFAULT false
created_at, updated_at: timestamp
```

---

## 4. Inertia.js Routes & Controllers

### 4.1 Authentication Routes
```php
// routes/web.php (using Inertia)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
```

### 4.2 First-Time User Profile Setup Routes
```php
Route::middleware(['auth', 'profile.incomplete'])->group(function () {
    Route::get('/profile/setup', [ProfileSetupController::class, 'show'])->name('profile.setup');
    Route::post('/profile/setup/biodata', [ProfileSetupController::class, 'storeBiodata'])->name('profile.setup.biodata');
    Route::post('/profile/setup/grades', [ProfileSetupController::class, 'storeGrades'])->name('profile.setup.grades');
    Route::get('/profile/setup/learning-style', [ProfileSetupController::class, 'showLearningStyle'])->name('profile.setup.learning-style');
    Route::post('/profile/setup/learning-style', [ProfileSetupController::class, 'storeLearningStyle'])->name('profile.setup.learning-style.store');
    Route::get('/profile/setup/quiz', [ProfileSetupController::class, 'showQuiz'])->name('profile.setup.quiz');
    Route::post('/profile/setup/quiz', [ProfileSetupController::class, 'submitQuiz'])->name('profile.setup.quiz.submit');
});
```

### 4.3 Student Routes
```php
Route::middleware(['auth', 'role:student', 'profile.complete'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::get('/learning-profile', [LearningProfileController::class, 'show'])->name('learning-profile');
    Route::get('/progress', [StudentProgressController::class, 'index'])->name('progress');
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations');
    Route::get('/content/{id}', [ContentViewController::class, 'show'])->name('content.show');
    Route::post('/content/{id}/view', [ContentViewController::class, 'logView'])->name('content.log-view');
    Route::get('/assessments', [StudentAssessmentController::class, 'index'])->name('assessments');
    Route::get('/assessments/{id}', [StudentAssessmentController::class, 'show'])->name('assessments.show');
    Route::post('/assessments/{id}/submit', [StudentAssessmentController::class, 'submit'])->name('assessments.submit');
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback');
    Route::post('/feedback/{id}/read', [FeedbackController::class, 'markAsRead'])->name('feedback.read');
});
```

### 4.4 Teacher Routes
```php
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes');
    Route::get('/classes/{id}', [TeacherClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{id}/analytics', [ClassAnalyticsController::class, 'show'])->name('classes.analytics');
    Route::get('/students/{id}', [TeacherStudentController::class, 'show'])->name('students.show');
    Route::post('/observations', [ObservationController::class, 'store'])->name('observations.store');
    Route::get('/content', [TeacherContentController::class, 'index'])->name('content.index');
    Route::get('/content/create', [TeacherContentController::class, 'create'])->name('content.create');
    Route::post('/content', [TeacherContentController::class, 'store'])->name('content.store');
    Route::get('/content/{id}/edit', [TeacherContentController::class, 'edit'])->name('content.edit');
    Route::put('/content/{id}', [TeacherContentController::class, 'update'])->name('content.update');
    Route::delete('/content/{id}', [TeacherContentController::class, 'destroy'])->name('content.destroy');
});
```

### 4.5 Admin Routes
```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');
    Route::resource('users', UserController::class);
    Route::resource('grade-subjects', GradeSubjectController::class);
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
```

### 4.6 Shared/API Routes (for AJAX calls)
```php
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/grade-subjects/{gradeLevel}', [GradeSubjectController::class, 'getByGrade'])->name('grade-subjects.by-grade');
    Route::post('/gemini/analyze-learning-style', [GeminiController::class, 'analyzeLearningStyle'])->name('gemini.analyze');
    Route::post('/gemini/generate-recommendations', [GeminiController::class, 'generateRecommendations'])->name('gemini.recommendations');
    Route::post('/gemini/generate-feedback', [GeminiController::class, 'generateFeedback'])->name('gemini.feedback');
    Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');
});
```

---

## 5. Google Gemini AI Integration

### 5.1 Gemini AI Service Architecture

**Integration Approach:**
- Direct integration from Laravel using Google AI PHP SDK or HTTP client
- Gemini 2.0 Flash for quick operations (learning style classification, quick recommendations)
- Gemini Pro for complex analysis (detailed feedback, essay grading)
- Multi-language prompt engineering for Indonesian and English support

**Service Class Structure:**
```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected string $model = 'gemini-2.0-flash-exp'; // or gemini-pro
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }
    
    public function generateContent(string $prompt, array $options = []): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => $options['config'] ?? [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
            ]);
        
        return $response->json();
    }
    
    public function analyzeLearningStyle(array $surveyData, string $locale = 'id'): array
    {
        $prompt = $this->buildLearningStylePrompt($surveyData, $locale);
        $response = $this->generateContent($prompt);
        
        return $this->parseLearningStyleResponse($response);
    }
    
    public function generateRecommendations(array $studentProfile, array $contents, string $locale = 'id'): array
    {
        $prompt = $this->buildRecommendationPrompt($studentProfile, $contents, $locale);
        $response = $this->generateContent($prompt);
        
        return $this->parseRecommendationResponse($response);
    }
    
    public function generateFeedback(array $assessmentData, string $locale = 'id'): array
    {
        $prompt = $this->buildFeedbackPrompt($assessmentData, $locale);
        $response = $this->generateContent($prompt);
        
        return $this->parseFeedbackResponse($response);
    }
    
    private function buildLearningStylePrompt(array $surveyData, string $locale): string
    {
        if ($locale === 'id') {
            return "Analisis hasil survei gaya belajar berikut dan tentukan gaya belajar dominan siswa (Visual, Auditori, atau Kinestetik).\n\n" .
                   "Data survei:\n" . json_encode($surveyData, JSON_PRETTY_PRINT) . "\n\n" .
                   "Berikan hasil dalam format JSON dengan struktur:\n" .
                   "{\n" .
                   "  \"dominant_style\": \"visual|auditory|kinesthetic\",\n" .
                   "  \"confidence_score\": 0.0-1.0,\n" .
                   "  \"visual_score\": 0.0-5.0,\n" .
                   "  \"auditory_score\": 0.0-5.0,\n" .
                   "  \"kinesthetic_score\": 0.0-5.0,\n" .
                   "  \"characteristics\": [\"karakteristik 1\", \"karakteristik 2\"],\n" .
                   "  \"study_recommendations\": [\"rekomendasi 1\", \"rekomendasi 2\"]\n" .
                   "}";
        } else {
            return "Analyze the following learning style survey results and determine the student's dominant learning style (Visual, Auditory, or Kinesthetic).\n\n" .
                   "Survey data:\n" . json_encode($surveyData, JSON_PRETTY_PRINT) . "\n\n" .
                   "Provide results in JSON format with structure:\n" .
                   "{\n" .
                   "  \"dominant_style\": \"visual|auditory|kinesthetic\",\n" .
                   "  \"confidence_score\": 0.0-1.0,\n" .
                   "  \"visual_score\": 0.0-5.0,\n" .
                   "  \"auditory_score\": 0.0-5.0,\n" .
                   "  \"kinesthetic_score\": 0.0-5.0,\n" .
                   "  \"characteristics\": [\"characteristic 1\", \"characteristic 2\"],\n" .
                   "  \"study_recommendations\": [\"recommendation 1\", \"recommendation 2\"]\n" .
                   "}";
        }
    }
    
    private function buildRecommendationPrompt(array $studentProfile, array $contents, string $locale): string
    {
        $styleMap = [
            'visual' => $locale === 'id' ? 'visual' : 'visual',
            'auditory' => $locale === 'id' ? 'auditori' : 'auditory',
            'kinesthetic' => $locale === 'id' ? 'kinestetik' : 'kinesthetic',
        ];
        
        if ($locale === 'id') {
            return "Seorang siswa kelas {$studentProfile['grade_level']} dengan gaya belajar {$styleMap[$studentProfile['learning_style']]} " .
                   "dan rata-rata nilai {$studentProfile['avg_score']}%.\n\n" .
                   "Berikan rekomendasi 5 konten pembelajaran terbaik dari daftar berikut:\n" .
                   json_encode($contents, JSON_PRETTY_PRINT) . "\n\n" .
                   "Format JSON:\n" .
                   "[\n" .
                   "  {\n" .
                   "    \"content_id\": 1,\n" .
                   "    \"relevance_score\": 0.0-1.0,\n" .
                   "    \"reason\": \"alasan rekomendasi\"\n" .
                   "  }\n" .
                   "]";
        } else {
            return "A grade {$studentProfile['grade_level']} student with {$styleMap[$studentProfile['learning_style']]} learning style " .
                   "and average score of {$studentProfile['avg_score']}%.\n\n" .
                   "Recommend the top 5 learning contents from the following list:\n" .
                   json_encode($contents, JSON_PRETTY_PRINT) . "\n\n" .
                   "JSON format:\n" .
                   "[\n" .
                   "  {\n" .
                   "    \"content_id\": 1,\n" .
                   "    \"relevance_score\": 0.0-1.0,\n" .
                   "    \"reason\": \"recommendation reason\"\n" .
                   "  }\n" .
                   "]";
        }
    }
}
```

### 5.2 Learning Style Classification with Gemini

**Process Flow:**
1. Student completes learning style survey (Likert scale 1-5)
2. Survey responses sent to Gemini for analysis
3. Gemini analyzes patterns and determines dominant style
4. System stores profile with confidence scores
5. If confidence < 70%, suggest retaking survey

**Example Gemini Prompt (Indonesian):**
```
Analisis hasil survei gaya belajar berikut:

Preferensi Visual:
- Saya belajar lebih baik dengan diagram dan grafik: 5
- Saya lebih suka menonton video daripada membaca teks: 4
- Saya mengingat informasi lebih baik saat melihatnya tertulis: 5
- Saya suka menggunakan warna dan stabilo saat belajar: 4

Preferensi Auditori:
- Saya belajar paling baik saat ada yang menjelaskan secara verbal: 3
- Saya lebih suka mendengarkan kuliah daripada membaca: 2
- Saya mengingat lebih baik saat mengatakan sesuatu dengan keras: 3
- Saya menikmati diskusi kelompok dan penjelasan verbal: 3

Preferensi Kinestetik:
- Saya belajar paling baik dengan melakukan dan mempraktikkan: 3
- Saya lebih suka aktivitas langsung dan eksperimen: 3
- Saya mengingat lebih baik saat bisa bergerak sambil belajar: 2
- Saya suka membangun atau membuat sesuatu untuk memahami konsep: 3

Tentukan gaya belajar dominan dan berikan rekomendasi metode belajar yang sesuai.
```

**Gemini Response Structure:**
```json
{
  "dominant_style": "visual",
  "confidence_score": 0.92,
  "visual_score": 4.5,
  "auditory_score": 2.75,
  "kinesthetic_score": 2.75,
  "characteristics": [
    "Belajar paling efektif melalui diagram, grafik, dan representasi visual",
    "Mengingat informasi lebih baik saat melihat catatan tertulis",
    "Lebih suka video tutorial daripada penjelasan verbal",
    "Mendapat manfaat dari penggunaan warna dan mind map"
  ],
  "study_recommendations": [
    "Gunakan mind map dan diagram untuk merangkum materi",
    "Tonton video pembelajaran dan catat poin-poin penting",
    "Buat catatan visual dengan warna berbeda untuk setiap konsep",
    "Gunakan flashcard dengan gambar untuk menghapal",
    "Visualisasikan konsep abstrak dengan gambar atau diagram"
  ]
}
```

### 5.3 Content Recommendation with Gemini

**Hybrid Recommendation Approach:**
1. **Rule-based filtering** (Laravel): Filter by grade, subject, difficulty
2. **Gemini AI scoring**: Analyze semantic relevance and personalization
3. **Ranking**: Combine scores for final recommendations

**Example Implementation:**
```php
class RecommendationEngine
{
    protected GeminiService $gemini;
    
    public function generateRecommendations(Student $student, int $limit = 10): Collection
    {
        // Step 1: Rule-based filtering
        $candidateContents = $this->getFilteredContents($student);
        
        // Step 2: Prepare student profile for Gemini
        $studentProfile = [
            'grade_level' => $student->grade_level,
            'learning_style' => $student->learningStyleProfile->dominant_style,
            'avg_score' => $student->getAverageScore(),
            'interests' => $student->learning_interests,
            'recent_topics' => $student->getRecentTopics(),
            'weak_areas' => $student->getWeakAreas(),
        ];
        
        // Step 3: Get Gemini recommendations
        $locale = $student->preferred_language ?? 'id';
        $geminiRecommendations = $this->gemini->generateRecommendations(
            $studentProfile,
            $candidateContents->toArray(),
            $locale
        );
        
        // Step 4: Map and save recommendations
        return $this->saveRecommendations($student, $geminiRecommendations, $limit);
    }
    
    private function getFilteredContents(Student $student): Collection
    {
        $query = Content::with('translations')
            ->where('is_active', true)
            ->where('grade_level', $student->grade_level);
        
        // Filter by learning style
        $profile = $student->learningStyleProfile;
        if ($profile) {
            $query->where(function($q) use ($profile) {
                $q->where('target_learning_style', $profile->dominant_style)
                  ->orWhere('target_learning_style', 'all');
            });
        }
        
        // Exclude completed content
        $completedIds = $student->learningActivities()
            ->where('activity_type', 'complete')
            ->pluck('content_id');
        $query->whereNotIn('id', $completedIds);
        
        return $query->limit(30)->get(); // Get candidates for Gemini to rank
    }
}
```

### 5.4 Automated Feedback Generation with Gemini

**Feedback Types:**
1. **Post-assessment feedback**: After quiz/test completion
2. **Progress feedback**: Weekly/monthly progress reports
3. **Motivational feedback**: For low engagement students
4. **Achievement feedback**: Celebrating milestones

**Example Prompt for Post-Assessment Feedback (Indonesian):**
```php
private function buildFeedbackPrompt(array $assessmentData, string $locale): string
{
    if ($locale === 'id') {
        return "Seorang siswa kelas {$assessmentData['grade_level']} baru saja menyelesaikan ujian Matematika " .
               "topik {$assessmentData['topic']} dengan hasil:\n" .
               "- Skor: {$assessmentData['score']} / {$assessmentData['max_score']} ({$assessmentData['percentage']}%)\n" .
               "- Waktu pengerjaan: {$assessmentData['time_taken']} menit\n" .
               "- Gaya belajar: {$assessmentData['learning_style']}\n" .
               "- Soal yang salah: " . json_encode($assessmentData['wrong_answers']) . "\n\n" .
               "Buatkan feedback yang:\n" .
               "1. Memotivasi dan mendorong semangat belajar\n" .
               "2. Mengidentifikasi area yang perlu diperbaiki\n" .
               "3. Memberikan 3-5 saran konkret untuk meningkatkan pemahaman\n" .
               "4. Disesuaikan dengan gaya belajar siswa\n" .
               "5. Menggunakan bahasa yang ramah dan mendukung\n\n" .
               "Format JSON:\n" .
               "{\n" .
               "  \"feedback_text\": \"teks feedback utama\",\n" .
               "  \"sentiment\": \"positive|neutral|constructive\",\n" .
               "  \"action_items\": [\n" .
               "    {\n" .
               "      \"priority\": \"high|medium|low\",\n" .
               "      \"action\": \"deskripsi tindakan\",\n" .
               "      \"resources\": [\"sumber 1\", \"sumber 2\"]\n" .
               "    }\n" .
               "  ],\n" .
               "  \"encouragement\": \"pesan motivasi tambahan\"\n" .
               "}";
    }
    // English version...
}
```

### 5.5 Gemini API Configuration

**Environment Variables:**
```env
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_TEMPERATURE=0.7
GEMINI_MAX_TOKENS=2048
```

**Configuration File (config/services.php):**
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    'max_tokens' => env('GEMINI_MAX_TOKENS', 2048),
    'cache_ttl' => 3600, // 1 hour cache for similar prompts
],
```

### 5.6 Cost Optimization Strategies

1. **Prompt Caching**: Cache similar prompts/responses
2. **Batch Processing**: Group similar requests
3. **Use Flash model**: For quick operations (cheaper)
4. **Rate Limiting**: Prevent excessive API calls
5. **Fallback Logic**: If Gemini fails, use rule-based system

```php
class GeminiService
{
    public function generateContentWithCache(string $cacheKey, string $prompt): array
    {
        return Cache::remember($cacheKey, config('services.gemini.cache_ttl'), function() use ($prompt) {
            return $this->generateContent($prompt);
        });
    }
}
```

---

## 6. Vue.js with Inertia.js Frontend Structure

### 6.1 Project Structure with Inertia

```
resources/
├── js/
│   ├── app.js                    // Main Inertia app setup
│   ├── ssr.js                    // SSR setup (optional)
│   ├── Pages/                    // Inertia pages (replaces views/)
│   │   ├── Auth/
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   └── ResetPassword.vue
│   │   ├── ProfileSetup/
│   │   │   ├── Index.vue         // Multi-step profile setup
│   │   │   ├── Biodata.vue
│   │   │   ├── ReportGrades.vue
│   │   │   ├── LearningStyle.vue
│   │   │   └── Quiz.vue
│   │   ├── Student/
│   │   │   ├── Dashboard.vue
│   │   │   ├── LearningProfile.vue
│   │   │   ├── Progress.vue
│   │   │   ├── Recommendations.vue
│   │   │   ├── ContentView.vue
│   │   │   ├── Assessments/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Show.vue
│   │   │   └── Feedback.vue
│   │   ├── Teacher/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Classes/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Show.vue
│   │   │   │   └── Analytics.vue
│   │   │   ├── Students/
│   │   │   │   └── Show.vue
│   │   │   └── Content/
│   │   │       ├── Index.vue
│   │   │       ├── Create.vue
│   │   │       └── Edit.vue
│   │   ├── Admin/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Analytics.vue
│   │   │   ├── Users/
│   │   │   └── Settings.vue
│   │   └── Shared/
│   │       └── Layout.vue        // Main layout component
│   ├── Components/               // Reusable Vue components
│   │   ├── Common/
│   │   │   ├── Navbar.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── Card.vue
│   │   │   ├── DataTable.vue
│   │   │   ├── Modal.vue
│   │   │   ├── Alert.vue
│   │   │   └── LanguageSwitcher.vue
│   │   ├── Charts/
│   │   │   ├── RadarChart.vue
│   │   │   ├── BarChart.vue
│   │   │   ├── LineChart.vue
│   │   │   └── PieChart.vue
│   │   ├── Student/
│   │   │   ├── LearningStyleCard.vue
│   │   │   ├── RecommendationCard.vue
│   │   │   ├── ProgressTracker.vue
│   │   │   └── FeedbackNotification.vue
│   │   ├── Teacher/
│   │   │   ├── StudentPerformanceCard.vue
│   │   │   ├── ClassDistributionChart.vue
│   │   │   └── ObservationForm.vue
│   │   └── ProfileSetup/
│   │       ├── StepIndicator.vue
│   │       ├── BiodataForm.vue
│   │       ├── GradeInputTable.vue
│   │       └── LearningStyleSelector.vue
│   ├── Composables/              // Composition API composables
│   │   ├── useAuth.js
│   │   ├── useLocale.js
│   │   ├── useNotification.js
│   │   ├── useGemini.js
│   │   └── useAnalytics.js
│   ├── Utils/
│   │   ├── helpers.js
│   │   ├── validators.js
│   │   └── constants.js
│   └── Locales/                  // Vue I18n translations
│       ├── id.json
│       └── en.json
├── css/
│   └── app.css                   // Tailwind CSS
└── views/
    └── app.blade.php             // Main Inertia app layout
```

### 6.2 Inertia.js Setup

**app.js:**
```javascript
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { createI18n } from 'vue-i18n';

// Import translations
import id from './Locales/id.json';
import en from './Locales/en.json';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

// Setup i18n
const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('locale') || 'id',
    fallbackLocale: 'en',
    messages: {
        id,
        en
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

**app.blade.php (Inertia Root Template):**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
```

### 6.3 Key Inertia Page Examples

#### Profile Setup Page (Multi-step)
```vue
<!-- resources/js/Pages/ProfileSetup/Index.vue -->
<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Layout from '@/Pages/Shared/Layout.vue';
import StepIndicator from '@/Components/ProfileSetup/StepIndicator.vue';
import BiodataForm from '@/Components/ProfileSetup/BiodataForm.vue';
import GradeInputTable from '@/Components/ProfileSetup/GradeInputTable.vue';
import LearningStyleSelector from '@/Components/ProfileSetup/LearningStyleSelector.vue';

const { t } = useI18n();

const props = defineProps({
    currentStep: Number,
    gradeSubjects: Array,
    learningStyleSurvey: Object,
    student: Object,
});

const step = ref(props.currentStep || 1);
const totalSteps = 4;

const steps = computed(() => [
    { number: 1, name: t('profile_setup.biodata'), completed: step.value > 1 },
    { number: 2, name: t('profile_setup.grades'), completed: step.value > 2 },
    { number: 3, name: t('profile_setup.learning_style'), completed: step.value > 3 },
    { number: 4, name: t('profile_setup.complete'), completed: step.value > 4 },
]);

const handleBiodataSubmit = (data) => {
    router.post(route('profile.setup.biodata'), data, {
        onSuccess: () => step.value = 2
    });
};

const handleGradesSubmit = (data) => {
    router.post(route('profile.setup.grades'), data, {
        onSuccess: () => step.value = 3
    });
};

const handleLearningStyleSubmit = (data) => {
    router.post(route('profile.setup.learning-style.store'), data, {
        onSuccess: () => {
            router.visit(route('student.dashboard'));
        }
    });
};

const goToQuiz = () => {
    router.visit(route('profile.setup.quiz'));
};
</script>

<template>
    <Layout>
        <div class="max-w-4xl mx-auto py-8">
            <h1 class="text-3xl font-bold mb-8">{{ t('profile_setup.title') }}</h1>
            
            <StepIndicator :steps="steps" :current-step="step" />
            
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <BiodataForm 
                    v-if="step === 1"
                    :student="student"
                    @submit="handleBiodataSubmit"
                />
                
                <GradeInputTable
                    v-else-if="step === 2"
                    :subjects="gradeSubjects"
                    :student="student"
                    @submit="handleGradesSubmit"
                />
                
                <LearningStyleSelector
                    v-else-if="step === 3"
                    :survey="learningStyleSurvey"
                    @submit="handleLearningStyleSubmit"
                    @take-quiz="goToQuiz"
                />
            </div>
        </div>
    </Layout>
</template>
```

#### Student Dashboard with Inertia
```vue
<!-- resources/js/Pages/Student/Dashboard.vue -->
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Layout from '@/Pages/Shared/Layout.vue';
import LearningStyleCard from '@/Components/Student/LearningStyleCard.vue';
import RecommendationCard from '@/Components/Student/RecommendationCard.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import StatCard from '@/Components/Common/StatCard.vue';

const { t } = useI18n();

const props = defineProps({
    learningProfile: Object,
    recommendations: Array,
    stats: Object,
    progressData: Array,
    recentFeedback: Array,
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>
    <Layout>
        <div class="space-y-6">
            <h1 class="text-3xl font-bold">{{ t('dashboard.welcome') }}</h1>
            
            <!-- Learning Style Profile -->
            <LearningStyleCard 
                :profile="learningProfile"
            />
            
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <StatCard 
                    :title="t('dashboard.completed_modules')" 
                    :value="stats.completed" 
                />
                <StatCard 
                    :title="t('dashboard.study_hours')" 
                    :value="stats.hours" 
                />
                <StatCard 
                    :title="t('dashboard.average_score')" 
                    :value="`${stats.avgScore}%`" 
                />
            </div>
            
            <!-- Recommendations -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">{{ t('dashboard.recommended_for_you') }}</h2>
                    <Link :href="route('student.recommendations')" class="text-blue-600 hover:underline">
                        {{ t('dashboard.view_all') }}
                    </Link>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <RecommendationCard 
                        v-for="content in recommendations" 
                        :key="content.id"
                        :content="content"
                    />
                </div>
            </section>
            
            <!-- Progress Chart -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">{{ t('dashboard.my_progress') }}</h2>
                <div class="h-64">
                    <LineChart :data="progressData" :options="chartOptions" />
                </div>
            </section>
        </div>
    </Layout>
</template>
```

### 6.4 Localization Composable

```javascript
// resources/js/Composables/useLocale.js
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';

export function useLocale() {
    const { locale, t } = useI18n();
    const currentLocale = computed(() => locale.value);
    
    const availableLocales = [
        { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' },
        { code: 'en', name: 'English', flag: '🇺🇸' },
    ];
    
    const switchLocale = async (newLocale) => {
        locale.value = newLocale;
        localStorage.setItem('locale', newLocale);
        
        // Notify backend
        await router.post(route('api.language.switch'), {
            locale: newLocale
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };
    
    return {
        currentLocale,
        availableLocales,
        switchLocale,
        t,
    };
}
```

### 6.5 Gemini Integration Composable

```javascript
// resources/js/Composables/useGemini.js
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

export function useGemini() {
    const isProcessing = ref(false);
    const error = ref(null);
    
    const analyzeLearningStyle = async (surveyData) => {
        isProcessing.value = true;
        error.value = null;
        
        try {
            const response = await axios.post(route('api.gemini.analyze'), {
                survey_data: surveyData
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Analysis failed';
            throw err;
        } finally {
            isProcessing.value = false;
        }
    };
    
    const generateRecommendations = async () => {
        isProcessing.value = true;
        error.value = null;
        
        try {
            const response = await axios.post(route('api.gemini.recommendations'));
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Recommendation generation failed';
            throw err;
        } finally {
            isProcessing.value = false;
        }
    };
    
    return {
        isProcessing,
        error,
        analyzeLearningStyle,
        generateRecommendations,
    };
}
```

---

## 7. Database Seeders

### 7.1 Seeder Overview

The system includes comprehensive seeders to populate initial data for:
- User roles and permissions
- Grade levels and subjects (Mathematics for Grades 10-12)
- Learning style survey questions
- Sample mathematics content
- Sample students and teachers

### 7.2 Grade Subjects Seeder

**Indonesian High School Grade System:**
- **Grade 10**: Foundation year (all students take same subjects)
- **Grade 11-12**: Specialization tracks (IPA/Science, IPS/Social, Bahasa/Language)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeSubject;

class GradeSubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            // Grade 10 - Common subjects (Mata Pelajaran Wajib)
            [
                'grade_level' => '10',
                'subject_code' => 'MTK',
                'subject_name_id' => 'Matematika',
                'subject_name_en' => 'Mathematics',
                'category' => 'wajib',
                'display_order' => 1,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'BIN',
                'subject_name_id' => 'Bahasa Indonesia',
                'subject_name_en' => 'Indonesian Language',
                'category' => 'wajib',
                'display_order' => 2,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'BING',
                'subject_name_id' => 'Bahasa Inggris',
                'subject_name_en' => 'English',
                'category' => 'wajib',
                'display_order' => 3,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'FIS',
                'subject_name_id' => 'Fisika',
                'subject_name_en' => 'Physics',
                'category' => 'wajib',
                'display_order' => 4,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'KIM',
                'subject_name_id' => 'Kimia',
                'subject_name_en' => 'Chemistry',
                'category' => 'wajib',
                'display_order' => 5,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'BIO',
                'subject_name_id' => 'Biologi',
                'subject_name_en' => 'Biology',
                'category' => 'wajib',
                'display_order' => 6,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'SEJ',
                'subject_name_id' => 'Sejarah Indonesia',
                'subject_name_en' => 'Indonesian History',
                'category' => 'wajib',
                'display_order' => 7,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'GEO',
                'subject_name_id' => 'Geografi',
                'subject_name_en' => 'Geography',
                'category' => 'wajib',
                'display_order' => 8,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'EKO',
                'subject_name_id' => 'Ekonomi',
                'subject_name_en' => 'Economics',
                'category' => 'wajib',
                'display_order' => 9,
            ],
            [
                'grade_level' => '10',
                'subject_code' => 'PKN',
                'subject_name_id' => 'Pendidikan Pancasila dan Kewarganegaraan',
                'subject_name_en' => 'Civic Education',
                'category' => 'wajib',
                'display_order' => 10,
            ],
            
            // Grade 11 - IPA (Science Track)
            [
                'grade_level' => '11',
                'subject_code' => 'MTK',
                'subject_name_id' => 'Matematika (Peminatan)',
                'subject_name_en' => 'Mathematics (Specialization)',
                'category' => 'peminatan',
                'display_order' => 1,
            ],
            [
                'grade_level' => '11',
                'subject_code' => 'FIS',
                'subject_name_id' => 'Fisika (Peminatan)',
                'subject_name_en' => 'Physics (Specialization)',
                'category' => 'peminatan',
                'display_order' => 2,
            ],
            [
                'grade_level' => '11',
                'subject_code' => 'KIM',
                'subject_name_id' => 'Kimia (Peminatan)',
                'subject_name_en' => 'Chemistry (Specialization)',
                'category' => 'peminatan',
                'display_order' => 3,
            ],
            [
                'grade_level' => '11',
                'subject_code' => 'BIO',
                'subject_name_id' => 'Biologi (Peminatan)',
                'subject_name_en' => 'Biology (Specialization)',
                'category' => 'peminatan',
                'display_order' => 4,
            ],
            
            // Grade 12 - Same subjects as Grade 11
            [
                'grade_level' => '12',
                'subject_code' => 'MTK',
                'subject_name_id' => 'Matematika (Peminatan)',
                'subject_name_en' => 'Mathematics (Specialization)',
                'category' => 'peminatan',
                'display_order' => 1,
            ],
            [
                'grade_level' => '12',
                'subject_code' => 'FIS',
                'subject_name_id' => 'Fisika (Peminatan)',
                'subject_name_en' => 'Physics (Specialization)',
                'category' => 'peminatan',
                'display_order' => 2,
            ],
            [
                'grade_level' => '12',
                'subject_code' => 'KIM',
                'subject_name_id' => 'Kimia (Peminatan)',
                'subject_name_en' => 'Chemistry (Specialization)',
                'category' => 'peminatan',
                'display_order' => 3,
            ],
            [
                'grade_level' => '12',
                'subject_code' => 'BIO',
                'subject_name_id' => 'Biologi (Peminatan)',
                'subject_name_en' => 'Biology (Specialization)',
                'category' => 'peminatan',
                'display_order' => 4,
            ],
        ];
        
        foreach ($subjects as $subject) {
            GradeSubject::create($subject);
        }
    }
}
```

### 7.3 Mathematics Content Seeder

**Mathematics Topics by Grade:**

**Grade 10:**
- Persamaan dan Pertidaksamaan Linear (Linear Equations and Inequalities)
- Sistem Persamaan Linear (Systems of Linear Equations)
- Fungsi (Functions)
- Persamaan dan Fungsi Kuadrat (Quadratic Equations and Functions)
- Trigonometri Dasar (Basic Trigonometry)

**Grade 11:**
- Trigonometri Lanjutan (Advanced Trigonometry)
- Matriks (Matrices)
- Program Linear (Linear Programming)
- Barisan dan Deret (Sequences and Series)
- Limit Fungsi (Limits)

**Grade 12:**
- Turunan (Derivatives)
- Integral (Integrals)
- Statistika (Statistics)
- Peluang (Probability)
- Dimensi Tiga (Three-Dimensional Geometry)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;
use App\Models\ContentTranslation;
use App\Models\User;

class MathematicsContentSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::where('role', 'admin')->first();
        
        $contents = [
            // Grade 10 Mathematics
            [
                'title' => 'Persamaan Linear',
                'subject' => 'Mathematics',
                'topic' => 'Linear Equations',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://www.youtube.com/watch?v=example1',
                'thumbnail_url' => '/images/content/linear-equations.jpg',
                'duration_minutes' => 15,
                'translations' => [
                    'id' => [
                        'title' => 'Persamaan Linear Satu Variabel',
                        'description' => 'Video pembelajaran tentang konsep dasar persamaan linear satu variabel dengan contoh soal dan pembahasan lengkap.',
                    ],
                    'en' => [
                        'title' => 'Linear Equations in One Variable',
                        'description' => 'Learning video about the basic concepts of linear equations in one variable with examples and complete solutions.',
                    ],
                ],
            ],
            [
                'title' => 'Sistem Persamaan Linear',
                'subject' => 'Mathematics',
                'topic' => 'Systems of Linear Equations',
                'grade_level' => '10',
                'content_type' => 'pdf',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_url' => '/storage/content/sistem-persamaan-linear.pdf',
                'thumbnail_url' => '/images/content/systems.jpg',
                'duration_minutes' => 30,
                'translations' => [
                    'id' => [
                        'title' => 'Sistem Persamaan Linear Dua Variabel (SPLDV)',
                        'description' => 'Materi lengkap SPLDV dengan metode eliminasi, substitusi, dan grafik disertai latihan soal.',
                    ],
                    'en' => [
                        'title' => 'Systems of Linear Equations in Two Variables',
                        'description' => 'Complete material on systems of linear equations with elimination, substitution, and graphical methods with practice problems.',
                    ],
                ],
            ],
            [
                'title' => 'Fungsi Matematika',
                'subject' => 'Mathematics',
                'topic' => 'Functions',
                'grade_level' => '10',
                'content_type' => 'interactive',
                'target_learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://www.geogebra.org/m/example',
                'thumbnail_url' => '/images/content/functions.jpg',
                'duration_minutes' => 20,
                'translations' => [
                    'id' => [
                        'title' => 'Pengenalan Fungsi dan Grafiknya',
                        'description' => 'Simulasi interaktif untuk memahami konsep fungsi, domain, kodomain, dan cara menggambar grafik fungsi.',
                    ],
                    'en' => [
                        'title' => 'Introduction to Functions and Their Graphs',
                        'description' => 'Interactive simulation to understand the concept of functions, domain, codomain, and how to graph functions.',
                    ],
                ],
            ],
            [
                'title' => 'Persamaan Kuadrat',
                'subject' => 'Mathematics',
                'topic' => 'Quadratic Equations',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.youtube.com/watch?v=example2',
                'thumbnail_url' => '/images/content/quadratic.jpg',
                'duration_minutes' => 25,
                'translations' => [
                    'id' => [
                        'title' => 'Persamaan Kuadrat dan Cara Penyelesaiannya',
                        'description' => 'Pembahasan lengkap persamaan kuadrat dengan metode pemfaktoran, melengkapkan kuadrat sempurna, dan rumus ABC.',
                    ],
                    'en' => [
                        'title' => 'Quadratic Equations and Solution Methods',
                        'description' => 'Complete discussion of quadratic equations with factoring, completing the square, and quadratic formula methods.',
                    ],
                ],
            ],
            [
                'title' => 'Trigonometri Dasar',
                'subject' => 'Mathematics',
                'topic' => 'Basic Trigonometry',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'all',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://docs.google.com/presentation/d/1PtwhBkUxPSYgB8BTze_el_wrHdKk3g-r',
                'thumbnail_url' => '/images/content/trigonometry.jpg',
                'duration_minutes' => 30,
                'translations' => [
                    'id' => [
                        'title' => 'Pengenalan Trigonometri: Sin, Cos, Tan',
                        'description' => 'Materi dasar trigonometri meliputi perbandingan sisi segitiga siku-siku dan penggunaan sin, cos, tan.',
                    ],
                    'en' => [
                        'title' => 'Introduction to Trigonometry: Sin, Cos, Tan',
                        'description' => 'Basic trigonometry material covering ratios of right triangle sides and the use of sin, cos, tan.',
                    ],
                ],
            ],
            
            // Grade 11 Mathematics
            [
                'title' => 'Irisan Kerucut',
                'subject' => 'Mathematics',
                'topic' => 'Conic Sections',
                'grade_level' => '11',
                'content_type' => 'interactive',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'external_url' => 'https://www.canva.com/design/DAGSFuKqc78/WW3qfXsgAidg_n37TnNDWQ/edit',
                'thumbnail_url' => '/images/content/conic-sections.jpg',
                'duration_minutes' => 40,
                'translations' => [
                    'id' => [
                        'title' => 'Irisan Kerucut: Lingkaran, Elips, Parabola, Hiperbola',
                        'description' => 'Pembelajaran interaktif tentang irisan kerucut menggunakan Mr. GG dengan visualisasi 3D.',
                    ],
                    'en' => [
                        'title' => 'Conic Sections: Circle, Ellipse, Parabola, Hyperbola',
                        'description' => 'Interactive learning about conic sections using Mr. GG with 3D visualization.',
                    ],
                ],
            ],
            [
                'title' => 'Matriks',
                'subject' => 'Mathematics',
                'topic' => 'Matrices',
                'grade_level' => '11',
                'content_type' => 'pdf',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_url' => 'https://drive.google.com/file/d/1pYrOhgp2gsD_FPaB-c4t4TYFr-btySDv',
                'thumbnail_url' => '/images/content/matrices.jpg',
                'duration_minutes' => 35,
                'translations' => [
                    'id' => [
                        'title' => 'Operasi Matriks dan Determinan',
                        'description' => 'Materi lengkap tentang penjumlahan, pengurangan, perkalian matriks, dan cara mencari determinan.',
                    ],
                    'en' => [
                        'title' => 'Matrix Operations and Determinants',
                        'description' => 'Complete material on matrix addition, subtraction, multiplication, and how to find determinants.',
                    ],
                ],
            ],
            [
                'title' => 'Program Linear',
                'subject' => 'Mathematics',
                'topic' => 'Linear Programming',
                'grade_level' => '11',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.youtube.com/watch?v=example3',
                'thumbnail_url' => '/images/content/linear-programming.jpg',
                'duration_minutes' => 30,
                'translations' => [
                    'id' => [
                        'title' => 'Program Linear: Optimasi dengan Pertidaksamaan',
                        'description' => 'Pembelajaran tentang program linear untuk menyelesaikan masalah optimasi menggunakan grafik dan metode simpleks.',
                    ],
                    'en' => [
                        'title' => 'Linear Programming: Optimization with Inequalities',
                        'description' => 'Learning about linear programming to solve optimization problems using graphs and simplex method.',
                    ],
                ],
            ],
            [
                'title' => 'Barisan dan Deret',
                'subject' => 'Mathematics',
                'topic' => 'Sequences and Series',
                'grade_level' => '11',
                'content_type' => 'audio',
                'target_learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_url' => '/storage/content/barisan-deret-audio.mp3',
                'thumbnail_url' => '/images/content/sequences.jpg',
                'duration_minutes' => 25,
                'translations' => [
                    'id' => [
                        'title' => 'Barisan dan Deret Aritmetika & Geometri',
                        'description' => 'Podcast edukatif membahas barisan dan deret aritmetika serta geometri dengan contoh aplikasi.',
                    ],
                    'en' => [
                        'title' => 'Arithmetic and Geometric Sequences & Series',
                        'description' => 'Educational podcast discussing arithmetic and geometric sequences and series with application examples.',
                    ],
                ],
            ],
            [
                'title' => 'Limit Fungsi',
                'subject' => 'Mathematics',
                'topic' => 'Limits',
                'grade_level' => '11',
                'content_type' => 'video',
                'target_learning_style' => 'all',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.youtube.com/watch?v=example4',
                'thumbnail_url' => '/images/content/limits.jpg',
                'duration_minutes' => 35,
                'translations' => [
                    'id' => [
                        'title' => 'Konsep Limit dan Teknik Penyelesaian',
                        'description' => 'Pembahasan konsep limit fungsi, limit tak hingga, dan berbagai teknik untuk menyelesaikan soal limit.',
                    ],
                    'en' => [
                        'title' => 'Concept of Limits and Solution Techniques',
                        'description' => 'Discussion of limit concepts, limits at infinity, and various techniques for solving limit problems.',
                    ],
                ],
            ],
            
            // Grade 12 Mathematics
            [
                'title' => 'Turunan Fungsi',
                'subject' => 'Mathematics',
                'topic' => 'Derivatives',
                'grade_level' => '12',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'external_url' => 'https://www.youtube.com/watch?v=example5',
                'thumbnail_url' => '/images/content/derivatives.jpg',
                'duration_minutes' => 40,
                'translations' => [
                    'id' => [
                        'title' => 'Turunan: Definisi dan Aplikasi',
                        'description' => 'Materi lengkap tentang turunan fungsi, aturan rantai, dan aplikasi turunan dalam menyelesaikan masalah.',
                    ],
                    'en' => [
                        'title' => 'Derivatives: Definition and Applications',
                        'description' => 'Complete material on function derivatives, chain rule, and applications of derivatives in problem solving.',
                    ],
                ],
            ],
            [
                'title' => 'Integral',
                'subject' => 'Mathematics',
                'topic' => 'Integrals',
                'grade_level' => '12',
                'content_type' => 'pdf',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'file_url' => '/storage/content/integral-materi.pdf',
                'thumbnail_url' => '/images/content/integrals.jpg',
                'duration_minutes' => 45,
                'translations' => [
                    'id' => [
                        'title' => 'Integral Tak Tentu dan Integral Tentu',
                        'description' => 'Pembahasan integral tak tentu, integral tentu, dan penerapannya untuk menghitung luas daerah dan volume benda putar.',
                    ],
                    'en' => [
                        'title' => 'Indefinite and Definite Integrals',
                        'description' => 'Discussion of indefinite integrals, definite integrals, and their applications to calculate area and volume of solids of revolution.',
                    ],
                ],
            ],
            [
                'title' => 'Statistika',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'grade_level' => '12',
                'content_type' => 'interactive',
                'target_learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.geogebra.org/m/statistics',
                'thumbnail_url' => '/images/content/statistics.jpg',
                'duration_minutes' => 30,
                'translations' => [
                    'id' => [
                        'title' => 'Statistika: Mean, Median, Modus, dan Diagram',
                        'description' => 'Simulasi interaktif untuk memahami ukuran pemusatan data dan penyajian data dalam bentuk diagram.',
                    ],
                    'en' => [
                        'title' => 'Statistics: Mean, Median, Mode, and Charts',
                        'description' => 'Interactive simulation to understand measures of central tendency and data presentation in chart form.',
                    ],
                ],
            ],
            [
                'title' => 'Peluang',
                'subject' => 'Mathematics',
                'topic' => 'Probability',
                'grade_level' => '12',
                'content_type' => 'video',
                'target_learning_style' => 'all',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.youtube.com/watch?v=example6',
                'thumbnail_url' => '/images/content/probability.jpg',
                'duration_minutes' => 35,
                'translations' => [
                    'id' => [
                        'title' => 'Teori Peluang dan Kaidah Pencacahan',
                        'description' => 'Materi peluang meliputi permutasi, kombinasi, dan perhitungan peluang kejadian majemuk.',
                    ],
                    'en' => [
                        'title' => 'Probability Theory and Counting Rules',
                        'description' => 'Probability material covering permutations, combinations, and compound event probability calculations.',
                    ],
                ],
            ],
            [
                'title' => 'Dimensi Tiga',
                'subject' => 'Mathematics',
                'topic' => 'Three-Dimensional Geometry',
                'grade_level' => '12',
                'content_type' => 'interactive',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'external_url' => 'https://www.geogebra.org/m/3d-geometry',
                'thumbnail_url' => '/images/content/3d-geometry.jpg',
                'duration_minutes' => 40,
                'translations' => [
                    'id' => [
                        'title' => 'Geometri Ruang: Jarak dan Sudut',
                        'description' => 'Visualisasi 3D untuk memahami konsep jarak titik ke garis, titik ke bidang, dan sudut antar bidang.',
                    ],
                    'en' => [
                        'title' => 'Spatial Geometry: Distance and Angles',
                        'description' => '3D visualization to understand concepts of point-to-line distance, point-to-plane distance, and angles between planes.',
                    ],
                ],
            ],
        ];
        
        foreach ($contents as $contentData) {
            $translations = $contentData['translations'];
            unset($contentData['translations']);
            
            $contentData['created_by'] = $adminUser->id;
            $contentData['is_active'] = true;
            
            $content = Content::create($contentData);
            
            // Create translations
            foreach ($translations as $locale => $translation) {
                ContentTranslation::create([
                    'content_id' => $content->id,
                    'locale' => $locale,
                    'title' => $translation['title'],
                    'description' => $translation['description'],
                ]);
            }
        }
    }
}
```

### 7.4 Learning Style Survey Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningStyleSurvey;

class LearningStyleSurveySeeder extends Seeder
{
    public function run()
    {
        LearningStyleSurvey::create([
            'title' => 'Learning Style Assessment',
            'description' => 'A comprehensive survey to determine your dominant learning style',
            'version' => '1.0',
            'is_active' => true,
            'questions' => [
                // Visual Learning Questions
                [
                    'id' => 1,
                    'category' => 'visual',
                    'text_id' => 'Saya belajar lebih baik saat informasi disajikan dengan diagram dan grafik',
                    'text_en' => 'I learn better when information is presented with diagrams and charts',
                    'scale' => [1, 2, 3, 4, 5],
                    'scale_labels_id' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Netral', 'Setuju', 'Sangat Setuju'],
                    'scale_labels_en' => ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'],
                ],
                [
                    'id' => 2,
                    'category' => 'visual',
                    'text_id' => 'Saya lebih suka menonton video daripada membaca teks',
                    'text_en' => 'I prefer watching videos over reading text',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 3,
                    'category' => 'visual',
                    'text_id' => 'Saya mengingat informasi lebih baik saat melihatnya tertulis',
                    'text_en' => 'I remember information better when I see it written down',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 4,
                    'category' => 'visual',
                    'text_id' => 'Saya suka menggunakan warna dan stabilo saat belajar',
                    'text_en' => 'I like using colors and highlighters when studying',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 5,
                    'category' => 'visual',
                    'text_id' => 'Saya lebih mudah memahami dengan gambar daripada penjelasan lisan',
                    'text_en' => 'I understand better with pictures than verbal explanations',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                
                // Auditory Learning Questions
                [
                    'id' => 6,
                    'category' => 'auditory',
                    'text_id' => 'Saya belajar paling baik saat ada yang menjelaskan secara verbal',
                    'text_en' => 'I learn best when someone explains things verbally',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 7,
                    'category' => 'auditory',
                    'text_id' => 'Saya lebih suka mendengarkan kuliah daripada membaca',
                    'text_en' => 'I prefer listening to lectures over reading',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 8,
                    'category' => 'auditory',
                    'text_id' => 'Saya mengingat lebih baik saat mengatakan sesuatu dengan keras',
                    'text_en' => 'I remember things better when I say them out loud',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 9,
                    'category' => 'auditory',
                    'text_id' => 'Saya menikmati diskusi kelompok dan penjelasan verbal',
                    'text_en' => 'I enjoy group discussions and verbal explanations',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 10,
                    'category' => 'auditory',
                    'text_id' => 'Saya belajar dengan baik melalui podcast atau rekaman audio',
                    'text_en' => 'I learn well through podcasts or audio recordings',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                
                // Kinesthetic Learning Questions
                [
                    'id' => 11,
                    'category' => 'kinesthetic',
                    'text_id' => 'Saya belajar paling baik dengan melakukan dan mempraktikkan',
                    'text_en' => 'I learn best by doing and practicing',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 12,
                    'category' => 'kinesthetic',
                    'text_id' => 'Saya lebih suka aktivitas langsung dan eksperimen',
                    'text_en' => 'I prefer hands-on activities and experiments',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 13,
                    'category' => 'kinesthetic',
                    'text_id' => 'Saya mengingat lebih baik saat bisa bergerak sambil belajar',
                    'text_en' => 'I remember better when I can move while learning',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 14,
                    'category' => 'kinesthetic',
                    'text_id' => 'Saya suka membangun atau membuat sesuatu untuk memahami konsep',
                    'text_en' => 'I like to build or create things to understand concepts',
                    'scale' => [1, 2, 3, 4, 5],
                ],
                [
                    'id' => 15,
                    'category' => 'kinesthetic',
                    'text_id' => 'Saya merasa lebih fokus saat melakukan aktivitas fisik',
                    'text_en' => 'I feel more focused when doing physical activities',
                    'scale' => [1, 2, 3, 4, 5],
                ],
            ],
        ]);
    }
}
```

### 7.5 User and Student Sample Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        $admin = User::create([
            'name' => 'Admin System',
            'email' => 'admin@school.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Teacher User
        $teacher = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.teacher@school.id',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
        
        Teacher::create([
            'user_id' => $teacher->id,
            'teacher_number' => 'TCH001',
            'subject' => 'Mathematics',
            'department' => 'Science',
        ]);
        
        // Sample Students
        $students = [
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.student@school.id',
                'grade_level' => '10',
                'class' => '10 IPA 1',
                'student_number' => 'STD2024001',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.student@school.id',
                'grade_level' => '11',
                'class' => '11 IPA 2',
                'student_number' => 'STD2024002',
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.student@school.id',
                'grade_level' => '12',
                'class' => '12 IPA 1',
                'student_number' => 'STD2024003',
            ],
        ];
        
        foreach ($students as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
            
            Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'grade_level' => $studentData['grade_level'],
                'class' => $studentData['class'],
                'major' => 'IPA',
                'enrollment_year' => now()->year,
                'status' => 'active',
                'profile_completed' => false,
                'preferred_language' => 'id',
            ]);
        }
    }
}
```

### 7.6 Running Seeders

```bash
# Run all seeders
php artisan db:seed

# Or run specific seeders
php artisan db:seed --class=GradeSubjectSeeder
php artisan db:seed --class=MathematicsContentSeeder
php artisan db:seed --class=LearningStyleSurveySeeder
php artisan db:seed --class=UserSeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

---

## 8. Key Features Implementation

### 8.1 First-Time Student Profile Setup

When a student logs in for the first time (`profile_completed = false`), they must complete a multi-step profile setup process.

**Profile Setup Flow:**
1. **Step 1: Biodata** - Basic information (name, student number, grade, class)
2. **Step 2: Report Card Grades (Rapor)** - Input grades from last semester's report
3. **Step 3: Learning Style** - Select or take quiz to determine learning style
4. **Step 4: Complete** - Profile setup finished, redirect to dashboard

*[Full implementation details for Profile Setup would be inserted here with Middleware, Controllers, and Vue components as outlined in the recorded information]*

### 8.2 Learning Style Survey System

**Survey Structure (Likert Scale 1-5):**

Questions designed to identify learning preferences:

**Visual Category:**
1. I learn better when information is presented with diagrams and charts
2. I prefer watching videos over reading text
3. I remember information better when I see it written down
4. I like using colors and highlighters when studying

**Auditory Category:**
1. I learn best when someone explains things verbally
2. I prefer listening to lectures over reading
3. I remember things better when I say them out loud
4. I enjoy group discussions and verbal explanations

**Kinesthetic Category:**
1. I learn best by doing and practicing
2. I prefer hands-on activities and experiments
3. I remember things better when I can move while learning
4. I like to build or create things to understand concepts

**Implementation:**
```php
// Laravel Controller
class LearningStyleController extends Controller
{
    public function submitSurvey(LearningStyleSurveyRequest $request)
    {
        $student = auth()->user()->student;
        
        // Save survey response
        $surveyResponse = SurveyResponse::create([
            'student_id' => $student->id,
            'survey_id' => $request->survey_id,
            'responses' => $request->responses,
            'completed_at' => now()
        ]);
        
        // Dispatch job to process with AI
        ProcessLearningStyleAnalysis::dispatch($student->id, $surveyResponse->id);
        
        return response()->json([
            'message' => 'Survey submitted successfully',
            'status' => 'processing'
        ]);
    }
    
    public function getProfile($studentId)
    {
        $profile = LearningStyleProfile::where('student_id', $studentId)
            ->with('recommendations')
            ->latest()
            ->first();
            
        if (!$profile) {
            return response()->json([
                'message' => 'No learning style profile found. Please complete the survey.'
            ], 404);
        }
        
        return response()->json($profile);
    }
}
```

### 7.2 Content Recommendation Engine

**Recommendation Logic:**
```php
class RecommendationEngine
{
    public function generateRecommendations(Student $student, int $limit = 10): Collection
    {
        $profile = $student->learningStyleProfile;
        
        if (!$profile) {
            // Return popular content if no profile exists
            return Content::where('is_active', true)
                ->orderBy('rating', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit($limit)
                ->get();
        }
        
        // Build query based on learning style
        $query = Content::where('is_active', true);
        
        // Filter by learning style
        $query->where(function($q) use ($profile) {
            $q->where('target_learning_style', $profile->dominant_style)
              ->orWhere('target_learning_style', 'all');
        });
        
        // Get student's recent assessments to determine difficulty
        $avgScore = $student->assessments()
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('percentage');
        
        $difficulty = $this->determineDifficultyLevel($avgScore);
        $query->where('difficulty_level', $difficulty);
        
        // Get student's subjects of interest
        $subjects = $student->learning_interests ?? [];
        if (!empty($subjects)) {
            $query->whereIn('subject', $subjects);
        }
        
        // Exclude already completed content
        $completedContentIds = $student->learningActivities()
            ->where('activity_type', 'complete')
            ->pluck('content_id');
        $query->whereNotIn('id', $completedContentIds);
        
        $contents = $query->limit($limit * 2)->get();
        
        // Calculate relevance scores using AI service
        $scoredContents = $this->calculateRelevanceScores($student, $contents);
        
        // Save recommendations
        $this->saveRecommendations($student, $scoredContents);
        
        return $scoredContents->take($limit);
    }
    
    private function calculateRelevanceScores(Student $student, Collection $contents): Collection
    {
        // Call AI service to score each content
        $aiClient = new AIServiceClient();
        
        return $contents->map(function($content) use ($student, $aiClient) {
            $score = $aiClient->scoreContentRelevance([
                'student_profile' => $student->toArray(),
                'content' => $content->toArray()
            ]);
            
            $content->relevance_score = $score['relevance_score'];
            $content->recommendation_reason = $score['reason'];
            
            return $content;
        })->sortByDesc('relevance_score');
    }
    
    private function determineDifficultyLevel(float $avgScore): string
    {
        if ($avgScore >= 80) return 'advanced';
        if ($avgScore >= 60) return 'intermediate';
        return 'beginner';
    }
}
```

### 7.3 Automated Feedback System

**Feedback Generator:**
```php
class FeedbackGenerator
{
    public function generateFeedback(Assessment $assessment): FeedbackLog
    {
        $student = $assessment->student;
        $percentage = $assessment->percentage;
        
        // Determine feedback tone
        $sentiment = $percentage >= 75 ? 'positive' : 
                    ($percentage >= 60 ? 'neutral' : 'constructive');
        
        // Generate personalized feedback text
        $feedbackText = $this->generateFeedbackText($assessment);
        
        // Generate action items using AI
        $actionItems = $this->generateActionItems($assessment);
        
        return FeedbackLog::create([
            'student_id' => $student->id,
            'assessment_id' => $assessment->id,
            'feedback_type' => 'auto',
            'feedback_text' => $feedbackText,
            'action_items' => $actionItems,
            'sentiment' => $sentiment
        ]);
    }
    
    private function generateFeedbackText(Assessment $assessment): string
    {
        $percentage = $assessment->percentage;
        $topic = $assessment->topic;
        
        $templates = [
            'high' => "Excellent work on {topic}! You scored {percentage}%. Your understanding is solid. Keep up the great work!",
            'medium' => "Good effort on {topic}. You scored {percentage}%. You're making progress, but there's room for improvement in some areas.",
            'low' => "Thank you for completing the assessment on {topic}. You scored {percentage}%. Don't worry - let's work together to strengthen your understanding."
        ];
        
        $level = $percentage >= 75 ? 'high' : ($percentage >= 60 ? 'medium' : 'low');
        
        return str_replace(
            ['{topic}', '{percentage}'],
            [$topic, number_format($percentage, 1)],
            $templates[$level]
        );
    }
    
    private function generateActionItems(Assessment $assessment): array
    {
        $items = [];
        $percentage = $assessment->percentage;
        $profile = $assessment->student->learningStyleProfile;
        
        if ($percentage < 60) {
            $items[] = [
                'priority' => 'high',
                'action' => "Review the basics of {$assessment->topic}",
                'resources' => $this->getRelevantResources($assessment, 'beginner')
            ];
        }
        
        if ($percentage < 75) {
            // Get learning style specific suggestions
            if ($profile && $profile->dominant_style === 'visual') {
                $items[] = [
                    'priority' => 'medium',
                    'action' => 'Watch video tutorials on the challenging concepts',
                    'resources' => $this->getRelevantResources($assessment, 'video')
                ];
            }
            
            $items[] = [
                'priority' => 'medium',
                'action' => "Practice more problems on {$assessment->topic}",
                'resources' => $this->getPracticeProblems($assessment)
            ];
        }
        
        return $items;
    }
}
```

### 7.4 Analytics Dashboard Implementation

**Analytics Service:**
```php
class AnalyticsService
{
    public function getStudentAnalytics(int $studentId, array $options = []): array
    {
        $student = Student::findOrFail($studentId);
        $period = $options['period'] ?? 30; // days
        
        return [
            'learning_style' => $this->getLearningStyleData($student),
            'performance' => $this->getPerformanceMetrics($student, $period),
            'activity' => $this->getActivityMetrics($student, $period),
            'progress' => $this->getProgressData($student, $period),
            'recommendations_effectiveness' => $this->getRecommendationEffectiveness($student),
            'competency_map' => $this->getCompetencyMap($student)
        ];
    }
    
    private function getLearningStyleData(Student $student): array
    {
        $profile = $student->learningStyleProfile;
        
        if (!$profile) {
            return ['status' => 'not_assessed'];
        }
        
        return [
            'dominant_style' => $profile->dominant_style,
            'distribution' => [
                'visual' => $profile->visual_score,
                'auditory' => $profile->auditory_score,
                'kinesthetic' => $profile->kinesthetic_score
            ],
            'confidence' => $profile->ai_confidence_score,
            'assessed_at' => $profile->analysis_date
        ];
    }
    
    private function getPerformanceMetrics(Student $student, int $days): array
    {
        $assessments = $student->assessments()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
        
        return [
            'average_score' => $assessments->avg('percentage'),
            'total_assessments' => $assessments->count(),
            'improvement_trend' => $this->calculateTrend($assessments),
            'by_subject' => $assessments->groupBy('subject')->map(function($items) {
                return [
                    'count' => $items->count(),
                    'avg_score' => $items->avg('percentage')
                ];
            }),
            'by_difficulty' => $assessments->groupBy('difficulty_level')->map(function($items) {
                return [
                    'count' => $items->count(),
                    'avg_score' => $items->avg('percentage')
                ];
            })
        ];
    }
    
    private function getActivityMetrics(Student $student, int $days): array
    {
        $activities = $student->learningActivities()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
        
        return [
            'total_time_seconds' => $activities->sum('duration_seconds'),
            'total_time_formatted' => $this->formatDuration($activities->sum('duration_seconds')),
            'sessions_count' => $activities->pluck('session_id')->unique()->count(),
            'most_active_days' => $this->getMostActiveDays($activities),
            'activity_by_type' => $activities->groupBy('activity_type')->map->count(),
            'engagement_score' => $this->calculateEngagementScore($activities)
        ];
    }
    
    private function getCompetencyMap(Student $student): array
    {
        return $student->competencyMaps()
            ->get()
            ->groupBy('subject')
            ->map(function($competencies) {
                return $competencies->map(function($comp) {
                    return [
                        'name' => $comp->competency_name,
                        'current' => $comp->current_level,
                        'target' => $comp->target_level,
                        'progress' => $comp->progress_percentage
                    ];
                });
            })
            ->toArray();
    }
}
```

---

## 8. User Interface Design Specifications

### 8.1 Student Interface

**Dashboard Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│  Header: Welcome, [Student Name]              [Notifications]│
├─────────────────────────────────────────────────────────────┤
│  Sidebar:                    Main Content Area               │
│  ├ Dashboard                ┌──────────────────────────────┐│
│  ├ My Learning Profile      │ Learning Style Profile Card  ││
│  ├ Recommendations          │  [Visual/Auditory/Kinesthetic]│
│  ├ My Progress              │  [Radar Chart Visualization] ││
│  ├ Assessments              └──────────────────────────────┘│
│  ├ Content Library          ┌──────────────────────────────┐│
│  └ Feedback                 │ Quick Stats                  ││
│                             │ [Completed] [Hours] [Avg Score]│
│                             └──────────────────────────────┘│
│                             ┌──────────────────────────────┐│
│                             │ Recommended For You          ││
│                             │ [Content Cards Grid]         ││
│                             └──────────────────────────────┘│
│                             ┌──────────────────────────────┐│
│                             │ My Progress Chart            ││
│                             │ [Line Chart: Performance]    ││
│                             └──────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

**Learning Profile Page:**
- Prominent display of dominant learning style with icon
- Radar chart showing distribution across all three styles
- "What this means for you" section with personalized tips
- Recommended study methods based on style
- "Retake Survey" button
- Historical profile changes timeline

**Recommendations Page:**
- Filterable content cards (by subject, type, difficulty)
- Each card shows:
  - Content title and thumbnail
  - Content type icon (video/audio/interactive)
  - Estimated duration
  - Relevance percentage
  - "Why recommended" tooltip
  - View/Start button
- Mark as completed functionality
- Rating system after completion

### 8.2 Teacher Interface

**Class Analytics Dashboard:**
```
┌─────────────────────────────────────────────────────────────┐
│  Class: Grade 10A - Mathematics               [Export Report]│
├─────────────────────────────────────────────────────────────┤
│ ┌──────────────────┐  ┌─────────────────────────────────┐  │
│ │ Learning Style   │  │ Class Performance Metrics       │  │
│ │ Distribution     │  │ Avg Score: 75%  Active: 28/30  │  │
│ │ [Pie Chart]      │  │ Completion Rate: 85%            │  │
│ └──────────────────┘  └─────────────────────────────────┘  │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Students Requiring Attention                           │  │
│ │ [Table: Name | Style | Avg Score | Last Active | Action] │
│ └────────────────────────────────────────────────────────┘  │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Performance Trends Over Time [Line Chart]              │  │
│ └────────────────────────────────────────────────────────┘  │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Recommended Teaching Approaches                        │  │
│ │ • 45% Visual learners - Increase video content        │  │
│ │ • Consider more hands-on activities for kinesthetic   │  │
│ └────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

**Student Detail View:**
- Complete learning profile
- Performance history with trends
- Activity timeline
- Assigned content and completion status
- Teacher's observations and notes
- Direct messaging capability

### 8.3 Admin Interface

**School-Wide Dashboard:**
- Total students, teachers, classes overview
- System-wide learning style distribution
- Overall performance metrics
- Platform usage statistics
- Content library statistics
- AI system performance metrics
- User management interface
- Report generation tools

---

## 9. Non-Functional Requirements

### 9.1 Performance Requirements
- Page load time: < 2 seconds
- API response time: < 500ms for most endpoints
- AI processing: < 30 seconds for learning style analysis
- Recommendation generation: < 5 seconds
- Support 500+ concurrent users
- Database queries optimized with indexes

### 9.2 Security Requirements
- Password hashing: bcrypt (Laravel default)
- API authentication: Laravel Sanctum with token expiration
- Role-based access control (RBAC)
- Input validation on all forms
- SQL injection protection (Laravel ORM)
- XSS protection
- CSRF token validation
- HTTPS required for production
- Regular security audits
- Data backup: daily automated backups

### 9.3 Scalability
- Horizontal scaling capability
- Database read replicas for analytics
- Redis caching for frequently accessed data
- CDN for static assets
- Queue workers for background jobs
- Microservice architecture for AI components

### 9.4 Availability
- 99.5% uptime target
- Graceful error handling
- Fallback mechanisms for AI service failures
- Database replication for redundancy
- Monitoring and alerting system

### 9.5 Usability
- Responsive design (mobile, tablet, desktop)
- Intuitive navigation
- Consistent UI components
- Clear error messages
- Loading indicators for async operations
- Help documentation and tooltips
- Multi-language support (future consideration)

---

## 10. Data Privacy and Compliance

### 10.1 Student Data Protection
- Collect only necessary personal information
- Anonymize data for AI training
- Secure storage of sensitive information
- Data retention policies
- Right to access personal data
- Right to delete account and associated data

### 10.2 Teacher and Admin Access Controls
- Teachers can only view students in their assigned classes
- Role-based data access restrictions
- Audit logs for sensitive operations
- Secure handling of observational notes

---

## 11. Testing Strategy

### 11.1 Backend Testing (Laravel)
- **Unit Tests**: Test models, services, repositories
- **Feature Tests**: Test API endpoints, authentication
- **Integration Tests**: Test Laravel-Python integration
- Use PHPUnit
- Target: 80% code coverage

### 11.2 Frontend Testing (Vue.js)
- **Unit Tests**: Test Vue components, utilities
- **Component Tests**: Test component behavior
- **E2E Tests**: Test user workflows
- Use Jest + Vue Test Utils
- Cypress for E2E testing

### 11.3 AI/ML Testing
- Validate classification accuracy
- Test recommendation relevance
- Performance benchmarking
- A/B testing for recommendation algorithms

---

## 12. Deployment Architecture

### 12.1 Production Environment
```
┌────────────────────────────────────────┐
│         Load Balancer (Nginx)          │
└────────────┬───────────────────────────┘
             │
    ┌────────┴─────────┐
    │                  │
┌───▼───────┐    ┌────▼──────┐
│ Laravel   │    │ Laravel   │
│ Instance 1│    │ Instance 2│
└───┬───────┘    └────┬──────┘
    │                 │
    └────────┬────────┘
             │
    ┌────────▼────────┐
    │   MySQL Master  │
    │  (Write/Read)   │
    └────────┬────────┘
             │
    ┌────────▼────────┐
    │  MySQL Replica  │
    │   (Read Only)   │
    └─────────────────┘

┌──────────────────┐    ┌──────────────┐
│  Redis Cache     │    │ Queue Workers│
└──────────────────┘    └──────────────┘

┌──────────────────────────────────────┐
│  Python AI Service (Flask/FastAPI)   │
│  (Separate server/container)         │
└──────────────────────────────────────┘
```

### 12.2 Development Workflow
- Version control: Git (GitHub/GitLab)
- CI/CD: GitHub Actions / GitLab CI
- Staging environment for testing
- Automated deployment scripts
- Database migrations management

### 12.3 Monitoring and Logging
- Application monitoring: Laravel Telescope
- Error tracking: Sentry / Bugsnag
- Performance monitoring: New Relic / DataDog
- Log aggregation: ELK Stack
- Uptime monitoring: Pingdom / UptimeRobot

---

## 13. Implementation Phases

### Phase 1: Foundation (Weeks 1-4)
- Setup development environment
- Database schema implementation
- Authentication and authorization
- Basic CRUD operations for core entities
- User management system

### Phase 2: Learning Style System (Weeks 5-8)
- Learning style survey implementation
- AI classification service development
- Learning profile management
- Initial analytics dashboard

### Phase 3: Content and Recommendations (Weeks 9-12)
- Content management system
- Recommendation engine development
- Content delivery system
- Activity tracking implementation

### Phase 4: Analytics and Feedback (Weeks 13-16)
- Advanced analytics dashboard
- Automated feedback system
- Teacher analytics interface
- Reporting system

### Phase 5: UI/UX and Testing (Weeks 17-20)
- Frontend refinement
- Comprehensive testing
- Performance optimization
- User acceptance testing

### Phase 6: Deployment and Training (Weeks 21-24)
- Production deployment
- User training materials
- Documentation
- Go-live and monitoring

---

## 14. Maintenance and Support

### 14.1 Regular Maintenance Tasks
- Database optimization and cleanup
- Security updates and patches
- Performance monitoring and tuning
- Backup verification
- Log rotation and cleanup

### 14.2 AI Model Maintenance
- Periodic model retraining with new data
- Algorithm performance evaluation
- Accuracy monitoring
- Feature engineering improvements

### 14.3 Support Structure
- User documentation and FAQs
- Technical support ticket system
- Bug tracking and resolution
- Feature request management
- Regular user feedback collection

---

## 15. Future Enhancements

### 15.1 Planned Features
- Mobile applications (iOS/Android)
- Gamification elements (badges, achievements)
- Social learning features (study groups, forums)
- Parent portal for progress monitoring
- Integration with external LMS platforms
- Advanced AI features (NLP for essay grading)
- Predictive analytics for early intervention
- Virtual tutor chatbot
- Augmented reality learning experiences

### 15.2 Scalability Considerations
- Multi-school support
- White-label capabilities
- API for third-party integrations
- Advanced reporting and BI tools
- Machine learning pipeline automation

---

## 16. Technical Dependencies

### 16.1 Laravel Backend
```json
{
  "php": "^8.1",
  "laravel/framework": "^10.0",
  "laravel/sanctum": "^3.2",
  "laravel/telescope": "^4.14",
  "inertiajs/inertia-laravel": "^0.6",
  "guzzlehttp/guzzle": "^7.5",
  "predis/predis": "^2.1",
  "maatwebsite/excel": "^3.1",
  "spatie/laravel-translatable": "^6.5"
}
```

### 16.2 Vue.js Frontend
```json
{
  "vue": "^3.3",
  "@inertiajs/vue3": "^1.0",
  "vue-i18n": "^9.6",
  "axios": "^1.4",
  "chart.js": "^4.3",
  "vue-chartjs": "^5.2",
  "tailwindcss": "^3.3",
  "@vitejs/plugin-vue": "^4.4",
  "laravel-vite-plugin": "^0.8"
}
```

### 16.3 Google Gemini AI
```
Google AI PHP SDK or HTTP Client
API Key from Google AI Studio
Gemini 2.0 Flash / Gemini Pro access
```

### 16.4 Additional Tools
```
- Redis for caching and queues
- PostgreSQL/MySQL for database
- Node.js & npm for frontend build
- Composer for PHP dependencies
```

---

## 17. Conclusion

This comprehensive requirement analysis provides the foundation for developing a robust AI-powered personalized learning system using Laravel and Vue.js. The system addresses the key challenges in modern education by leveraging machine learning to understand individual learning styles and deliver customized educational experiences.

**Key Success Factors:**
- Accurate learning style classification
- Relevant content recommendations
- Actionable analytics for teachers
- User-friendly interface for all stakeholders
- Scalable and maintainable architecture
- Strong data privacy and security measures

**Expected Outcomes:**
- Improved student engagement and learning outcomes
- Data-driven teaching strategies
- Efficient identification of at-risk students
- Personalized learning paths for every student
- Enhanced teacher effectiveness through analytics

This document serves as a living blueprint that should be updated as requirements evolve and new insights are gained during the development process.

---

**Document Version:** 1.0  
**Last Updated:** December 11, 2025  
**Prepared For:** AI-Powered Learning System Development Team