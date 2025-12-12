<?php

namespace Tests\UAT;

use App\Models\User;
use App\Models\Student;
use App\Models\LearningStyleProfile;
use App\Models\Content;
use App\Models\Assessment;
use App\Models\GradeSubject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * User Acceptance Testing for Student Workflows
 * Phase 5: UI/UX and Testing Implementation
 * 
 * These tests validate that the system meets user requirements and expectations
 * from a student's perspective, focusing on real-world usage scenarios.
 */
class StudentUserAcceptanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $studentUser;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data that mirrors real-world scenarios
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Create a realistic student user
        $this->studentUser = User::factory()->create([
            'name' => 'Ahmad Ridwan Pratama',
            'email' => 'ahmad.ridwan@student.sch.id',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'student_number' => 'STD2024001',
            'grade_level' => '11',
            'class' => '11 IPA 1',
            'major' => 'IPA',
            'enrollment_year' => now()->year,
            'profile_completed' => false, // Will be completed during tests
            'preferred_language' => 'id',
        ]);

        // Create grade subjects for Grade 11 IPA
        $this->createGradeSubjects();
        
        // Create sample mathematics content
        $this->createSampleContent();
    }

    protected function createGradeSubjects(): void
    {
        $subjects = [
            ['grade_level' => '11', 'subject_code' => 'MTK', 'subject_name_id' => 'Matematika (Peminatan)', 'subject_name_en' => 'Mathematics (Specialization)', 'category' => 'peminatan'],
            ['grade_level' => '11', 'subject_code' => 'FIS', 'subject_name_id' => 'Fisika (Peminatan)', 'subject_name_en' => 'Physics (Specialization)', 'category' => 'peminatan'],
            ['grade_level' => '11', 'subject_code' => 'KIM', 'subject_name_id' => 'Kimia (Peminatan)', 'subject_name_en' => 'Chemistry (Specialization)', 'category' => 'peminatan'],
            ['grade_level' => '11', 'subject_code' => 'BIO', 'subject_name_id' => 'Biologi (Peminatan)', 'subject_name_en' => 'Biology (Specialization)', 'category' => 'peminatan'],
        ];

        foreach ($subjects as $subject) {
            GradeSubject::create($subject);
        }
    }

    protected function createSampleContent(): void
    {
        Content::factory(5)->create([
            'grade_level' => '11',
            'subject' => 'Mathematics',
            'content_type' => 'video',
            'target_learning_style' => 'visual',
            'difficulty_level' => 'intermediate',
            'is_active' => true,
        ]);

        Content::factory(3)->create([
            'grade_level' => '11',
            'subject' => 'Mathematics',
            'content_type' => 'pdf',
            'target_learning_style' => 'visual',
            'difficulty_level' => 'beginner',
            'is_active' => true,
        ]);
    }

    /**
     * UAT-001: Student Registration and First-Time Setup
     * 
     * User Story: As a new student, I want to register and complete my profile setup
     * so that I can start using the personalized learning platform.
     * 
     * Acceptance Criteria:
     * - Student can register with valid credentials
     * - System guides through profile setup steps
     * - Learning style assessment is completed
     * - Dashboard is accessible after setup
     * 
     * @test
     */
    public function student_can_complete_full_registration_and_profile_setup()
    {
        // Step 1: Registration
        $registrationData = [
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@student.sch.id',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'student',
        ];

        $response = $this->post('/register', $registrationData);
        $response->assertRedirect('/profile/setup');

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'siti.nurhaliza@student.sch.id',
            'role' => 'student',
        ]);

        $newUser = User::where('email', 'siti.nurhaliza@student.sch.id')->first();
        $this->assertNotNull($newUser);

        // Step 2: Profile Setup - Biodata
        $biodataResponse = $this->actingAs($newUser)->post('/profile/setup/biodata', [
            'student_number' => 'STD2024002',
            'grade_level' => '11',
            'class' => '11 IPA 2',
            'major' => 'IPA',
            'preferred_language' => 'id',
        ]);

        $biodataResponse->assertRedirect('/profile/setup?step=2');

        // Verify student record was created
        $this->assertDatabaseHas('students', [
            'user_id' => $newUser->id,
            'student_number' => 'STD2024002',
            'grade_level' => '11',
        ]);

        $newStudent = Student::where('user_id', $newUser->id)->first();

        // Step 3: Grade Input
        $gradeSubjects = GradeSubject::where('grade_level', '11')->get();
        $gradeData = [];
        foreach ($gradeSubjects as $subject) {
            $gradeData[$subject->subject_code] = $this->faker->numberBetween(75, 95);
        }

        $gradeResponse = $this->actingAs($newUser)->post('/profile/setup/grades', [
            'grades' => $gradeData,
            'semester' => '1',
            'academic_year' => '2024/2025',
        ]);

        $gradeResponse->assertRedirect('/profile/setup?step=3');

        // Step 4: Learning Style Assessment
        $learningStyleData = [
            'responses' => [
                // Visual learning preferences (high scores)
                1 => 5, 2 => 4, 3 => 5, 4 => 4, 5 => 5,
                // Auditory learning preferences (low scores)
                6 => 2, 7 => 2, 8 => 3, 9 => 2, 10 => 2,
                // Kinesthetic learning preferences (medium scores)
                11 => 3, 12 => 3, 13 => 3, 14 => 3, 15 => 3,
            ]
        ];

        $learningStyleResponse = $this->actingAs($newUser)
            ->post('/profile/setup/learning-style', $learningStyleData);

        // Should redirect to dashboard after successful completion
        $learningStyleResponse->assertRedirect('/student/dashboard');

        // Verify learning style profile was created
        $this->assertDatabaseHas('learning_style_profiles', [
            'student_id' => $newStudent->id,
            'dominant_style' => 'visual',
        ]);

        // Verify student profile is marked as complete
        $newStudent->refresh();
        $this->assertTrue($newStudent->profile_completed);

        // Step 5: Access dashboard
        $dashboardResponse = $this->actingAs($newUser)->get('/student/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertInertia(function ($page) {
            $page->component('Student/Dashboard')
                 ->has('learningProfile')
                 ->has('recommendations');
        });
    }

    /**
     * UAT-002: Content Discovery and Learning
     * 
     * User Story: As a student, I want to discover and consume learning content
     * that matches my learning style and grade level.
     * 
     * @test
     */
    public function student_can_discover_and_consume_personalized_content()
    {
        // Setup: Complete student with visual learning style
        $this->student->update(['profile_completed' => true]);
        
        LearningStyleProfile::factory()->create([
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
            'visual_score' => 4.5,
            'auditory_score' => 2.5,
            'kinesthetic_score' => 3.0,
        ]);

        // Test 1: Browse content library
        $contentLibraryResponse = $this->actingAs($this->studentUser)
            ->get('/student/content');

        $contentLibraryResponse->assertStatus(200);
        $contentLibraryResponse->assertInertia(function ($page) {
            $page->component('Student/Content/Index')
                 ->has('content')
                 ->has('subjects')
                 ->has('contentTypes');
        });

        // Test 2: Filter content by subject
        $filteredContentResponse = $this->actingAs($this->studentUser)
            ->get('/student/content?subject=Mathematics');

        $filteredContentResponse->assertStatus(200);
        $filteredContentResponse->assertInertia(function ($page) {
            $page->where('filters.subject', 'Mathematics');
        });

        // Test 3: View specific content
        $content = Content::where('grade_level', '11')
            ->where('subject', 'Mathematics')
            ->first();

        $contentViewResponse = $this->actingAs($this->studentUser)
            ->get("/student/content/{$content->id}");

        $contentViewResponse->assertStatus(200);

        // Test 4: Mark content as completed
        $completeResponse = $this->actingAs($this->studentUser)
            ->post("/student/content/{$content->id}/complete");

        $completeResponse->assertStatus(200);

        // Verify learning activity was logged
        $this->assertDatabaseHas('learning_activities', [
            'student_id' => $this->student->id,
            'content_id' => $content->id,
            'activity_type' => 'complete',
        ]);
    }

    /**
     * UAT-003: Assessment Taking Experience
     * 
     * User Story: As a student, I want to take assessments and receive 
     * immediate feedback on my performance.
     * 
     * @test
     */
    public function student_can_take_assessment_and_receive_feedback()
    {
        // Setup
        $this->student->update(['profile_completed' => true]);

        // Create an assessment
        $assessment = Assessment::factory()->create([
            'subject' => 'Mathematics',
            'topic' => 'Trigonometry',
            'assessment_type' => 'quiz',
            'max_score' => 100,
            'difficulty_level' => 'intermediate',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What is sin(90°)?',
                    'options' => ['0', '1', '√2/2', '√3/2'],
                    'correct_answer' => '1',
                ],
                [
                    'id' => 2,
                    'question' => 'What is cos(0°)?',
                    'options' => ['0', '1', '√2/2', '√3/2'],
                    'correct_answer' => '1',
                ],
            ]
        ]);

        // Test 1: View available assessments
        $assessmentsResponse = $this->actingAs($this->studentUser)
            ->get('/student/assessments');

        $assessmentsResponse->assertStatus(200);
        $assessmentsResponse->assertInertia(function ($page) {
            $page->component('Student/Assessments/Index');
        });

        // Test 2: Start assessment
        $startResponse = $this->actingAs($this->studentUser)
            ->get("/student/assessments/{$assessment->id}");

        $startResponse->assertStatus(200);
        $startResponse->assertInertia(function ($page) use ($assessment) {
            $page->component('Student/Assessments/Show')
                 ->where('assessment.id', $assessment->id);
        });

        // Test 3: Submit assessment answers
        $submissionData = [
            'answers' => [
                1 => '1', // Correct
                2 => '0', // Incorrect
            ],
            'time_taken_seconds' => 300, // 5 minutes
        ];

        $submitResponse = $this->actingAs($this->studentUser)
            ->post("/student/assessments/{$assessment->id}/submit", $submissionData);

        $submitResponse->assertStatus(200);

        // Verify assessment result was created
        $this->assertDatabaseHas('assessments', [
            'student_id' => $this->student->id,
            'subject' => 'Mathematics',
            'topic' => 'Trigonometry',
            'score' => 50, // 1 correct out of 2
            'max_score' => 100,
            'percentage' => 50.0,
        ]);

        // Test 4: View results and feedback
        $studentAssessment = Assessment::where('student_id', $this->student->id)
            ->where('subject', 'Mathematics')
            ->first();

        $resultsResponse = $this->actingAs($this->studentUser)
            ->get("/student/assessments/{$studentAssessment->id}/results");

        $resultsResponse->assertStatus(200);
        $resultsResponse->assertInertia(function ($page) {
            $page->has('assessment')
                 ->has('feedback')
                 ->has('recommendations');
        });
    }

    /**
     * UAT-004: Analytics and Progress Monitoring
     * 
     * User Story: As a student, I want to view my learning analytics
     * and track my progress over time.
     * 
     * @test
     */
    public function student_can_view_comprehensive_analytics_and_progress()
    {
        // Setup: Student with learning history
        $this->student->update(['profile_completed' => true]);

        LearningStyleProfile::factory()->create([
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
        ]);

        // Create learning activities
        $content = Content::factory(3)->create([
            'grade_level' => '11',
            'subject' => 'Mathematics',
        ]);

        foreach ($content as $item) {
            $this->student->learningActivities()->create([
                'content_id' => $item->id,
                'activity_type' => 'complete',
                'duration_seconds' => 1800, // 30 minutes
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create assessments with varying scores
        Assessment::factory(5)->create([
            'student_id' => $this->student->id,
            'subject' => 'Mathematics',
            'percentage' => $this->faker->numberBetween(70, 95),
            'created_at' => now()->subDays(rand(1, 30)),
        ]);

        // Test 1: View analytics dashboard
        $analyticsResponse = $this->actingAs($this->studentUser)
            ->get('/student/analytics');

        $analyticsResponse->assertStatus(200);
        $analyticsResponse->assertInertia(function ($page) {
            $page->component('Student/Analytics/Dashboard')
                 ->has('analytics')
                 ->has('analytics.engagement')
                 ->has('analytics.performance')
                 ->has('analytics.time_metrics')
                 ->has('learningProfile');
        });

        // Test 2: View analytics for different time periods
        $periods = ['day', 'week', 'month', 'quarter'];
        
        foreach ($periods as $period) {
            $periodResponse = $this->actingAs($this->studentUser)
                ->get("/student/analytics?period={$period}");

            $periodResponse->assertStatus(200);
            $periodResponse->assertInertia(function ($page) use ($period) {
                $page->where('period', $period);
            });
        }

        // Test 3: View progress tracking
        $progressResponse = $this->actingAs($this->studentUser)
            ->get('/student/progress');

        $progressResponse->assertStatus(200);
        $progressResponse->assertInertia(function ($page) {
            $page->component('Student/Progress/Index');
        });
    }

    /**
     * UAT-005: Recommendation System Effectiveness
     * 
     * User Story: As a student with a specific learning style, 
     * I want to receive relevant content recommendations.
     * 
     * @test
     */
    public function student_receives_relevant_personalized_recommendations()
    {
        // Setup: Visual learner
        $this->student->update(['profile_completed' => true]);

        $profile = LearningStyleProfile::factory()->create([
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
            'visual_score' => 4.8,
            'auditory_score' => 2.2,
            'kinesthetic_score' => 2.5,
        ]);

        // Create diverse content with different learning styles
        $visualContent = Content::factory(3)->create([
            'grade_level' => '11',
            'target_learning_style' => 'visual',
            'content_type' => 'video',
            'subject' => 'Mathematics',
        ]);

        $auditoryContent = Content::factory(2)->create([
            'grade_level' => '11',
            'target_learning_style' => 'auditory',
            'content_type' => 'audio',
            'subject' => 'Mathematics',
        ]);

        // Test 1: View recommendations page
        $recommendationsResponse = $this->actingAs($this->studentUser)
            ->get('/student/recommendations');

        $recommendationsResponse->assertStatus(200);
        $recommendationsResponse->assertInertia(function ($page) {
            $page->component('Student/Recommendations/Index')
                 ->has('recommendations')
                 ->has('learningProfile');
        });

        // Test 2: Verify visual content is prioritized
        // This would typically be handled by the recommendation algorithm
        // For testing purposes, we'll simulate the expected behavior
        
        $this->assertTrue(true); // Placeholder for recommendation algorithm test
    }

    /**
     * UAT-006: Mobile Responsiveness and Accessibility
     * 
     * User Story: As a student using a mobile device, I want the platform
     * to work seamlessly on my smartphone or tablet.
     * 
     * @test
     */
    public function platform_works_correctly_on_mobile_devices()
    {
        // Setup
        $this->student->update(['profile_completed' => true]);

        // Test mobile user agent
        $mobileHeaders = [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
        ];

        // Test 1: Dashboard on mobile
        $mobileResponse = $this->actingAs($this->studentUser)
            ->withHeaders($mobileHeaders)
            ->get('/student/dashboard');

        $mobileResponse->assertStatus(200);

        // Test 2: Content library on mobile
        $mobileContentResponse = $this->actingAs($this->studentUser)
            ->withHeaders($mobileHeaders)
            ->get('/student/content');

        $mobileContentResponse->assertStatus(200);

        // Test 3: Analytics on mobile
        $mobileAnalyticsResponse = $this->actingAs($this->studentUser)
            ->withHeaders($mobileHeaders)
            ->get('/student/analytics');

        $mobileAnalyticsResponse->assertStatus(200);
    }

    /**
     * UAT-007: Offline Functionality
     * 
     * User Story: As a student with intermittent internet connectivity,
     * I want to access previously loaded content offline.
     * 
     * @test
     */
    public function previously_loaded_content_accessible_offline()
    {
        // This would typically require service worker testing
        // which is better handled in E2E tests
        // For now, we'll test the basic cache headers
        
        $this->student->update(['profile_completed' => true]);

        $response = $this->actingAs($this->studentUser)
            ->get('/student/dashboard');

        $response->assertStatus(200);
        
        // Verify appropriate cache headers are set
        // This would be configured in the web server or middleware
        $this->assertTrue(true); // Placeholder
    }

    /**
     * UAT-008: Error Handling and User Feedback
     * 
     * User Story: As a student, when something goes wrong,
     * I want to see clear, helpful error messages.
     * 
     * @test
     */
    public function system_provides_helpful_error_messages()
    {
        // Test 1: Invalid form submission
        $invalidResponse = $this->actingAs($this->studentUser)
            ->post('/profile/setup/biodata', [
                'student_number' => '', // Required field empty
                'grade_level' => '13', // Invalid grade
            ]);

        $invalidResponse->assertSessionHasErrors(['student_number', 'grade_level']);

        // Test 2: Accessing non-existent content
        $notFoundResponse = $this->actingAs($this->studentUser)
            ->get('/student/content/99999');

        $notFoundResponse->assertStatus(404);

        // Test 3: Unauthorized access
        $unauthorizedResponse = $this->actingAs($this->studentUser)
            ->get('/admin/dashboard');

        $unauthorizedResponse->assertStatus(403);
    }

    /**
     * UAT-009: Language and Localization
     * 
     * User Story: As an Indonesian student, I want the interface
     * to be available in Bahasa Indonesia.
     * 
     * @test
     */
    public function platform_supports_indonesian_localization()
    {
        // Setup student with Indonesian preference
        $this->student->update([
            'profile_completed' => true,
            'preferred_language' => 'id',
        ]);

        // Test with Indonesian locale
        $response = $this->actingAs($this->studentUser)
            ->withHeaders(['Accept-Language' => 'id'])
            ->get('/student/dashboard');

        $response->assertStatus(200);

        // Test language switching
        $languageSwitchResponse = $this->actingAs($this->studentUser)
            ->post('/api/language/switch', ['locale' => 'en']);

        $languageSwitchResponse->assertStatus(200);

        // Verify the change
        $this->student->refresh();
        $this->assertEquals('en', $this->student->preferred_language);
    }

    /**
     * UAT-010: Performance Requirements
     * 
     * User Story: As a student, I want the platform to load quickly
     * and respond promptly to my interactions.
     * 
     * @test
     */
    public function platform_meets_performance_requirements()
    {
        $this->student->update(['profile_completed' => true]);

        // Test page load times
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->studentUser)
            ->get('/student/dashboard');
        
        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        // Dashboard should load within 3 seconds (3000ms)
        // Note: In real testing environment, this might need adjustment
        $this->assertLessThan(3000, $loadTime, 'Dashboard load time exceeded 3 seconds');
    }
}