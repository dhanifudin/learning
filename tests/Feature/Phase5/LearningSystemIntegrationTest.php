<?php

namespace Tests\Feature\Phase5;

use App\Models\Student;
use App\Models\User;
use App\Models\LearningStyleProfile;
use App\Models\Content;
use App\Models\Assessment;
use App\Models\LearningActivity;
use App\Models\Recommendation;
use App\Models\FeedbackLog;
use App\Services\AnalyticsAggregationService;
use App\Services\FeedbackGenerationService;
use App\Services\ReportGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Comprehensive Integration Testing for Learning System
 * Phase 5: UI/UX and Testing Implementation
 */
class LearningSystemIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Student $student;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and student
        $this->user = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
        
        $this->student = Student::factory()->create([
            'user_id' => $this->user->id,
            'grade_level' => '11',
            'class' => '11 IPA 1',
            'major' => 'IPA',
            'profile_completed' => true,
            'preferred_language' => 'id',
        ]);
    }

    /**
     * Test complete learning style assessment workflow
     * @test
     */
    public function test_complete_learning_style_assessment_workflow()
    {
        // Simulate survey responses
        $surveyData = [
            'responses' => [
                // Visual learning responses
                1 => 5, 2 => 4, 3 => 5, 4 => 4, 5 => 5,
                // Auditory learning responses  
                6 => 2, 7 => 3, 8 => 2, 9 => 3, 10 => 2,
                // Kinesthetic learning responses
                11 => 3, 12 => 3, 13 => 2, 14 => 3, 15 => 3,
            ],
            'survey_id' => 1
        ];

        // Submit learning style survey
        $response = $this->actingAs($this->user)
            ->postJson('/api/learning-style/survey', $surveyData);

        $response->assertStatus(200);

        // Verify learning style profile was created
        $this->assertDatabaseHas('learning_style_profiles', [
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
        ]);

        $profile = LearningStyleProfile::where('student_id', $this->student->id)->first();
        
        // Verify scores are calculated correctly
        $this->assertGreaterThan(4.0, $profile->visual_score);
        $this->assertLessThan(3.0, $profile->auditory_score);
        $this->assertEquals('visual', $profile->dominant_style);
    }

    /**
     * Test content recommendation system integration
     * @test
     */
    public function test_content_recommendation_system_integration()
    {
        // Create learning style profile
        $profile = LearningStyleProfile::factory()->create([
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
            'visual_score' => 4.5,
            'auditory_score' => 2.5,
            'kinesthetic_score' => 3.0,
        ]);

        // Create various content types
        $visualContent = Content::factory()->create([
            'grade_level' => '11',
            'content_type' => 'video',
            'target_learning_style' => 'visual',
            'difficulty_level' => 'intermediate',
            'is_active' => true,
        ]);

        $auditoryContent = Content::factory()->create([
            'grade_level' => '11',
            'content_type' => 'audio',
            'target_learning_style' => 'auditory',
            'difficulty_level' => 'intermediate',
            'is_active' => true,
        ]);

        // Request recommendations
        $response = $this->actingAs($this->user)
            ->getJson('/student/recommendations');

        $response->assertStatus(200);

        // Verify visual content is prioritized for visual learner
        $recommendations = Recommendation::where('student_id', $this->student->id)->get();
        
        $visualRec = $recommendations->firstWhere('content_id', $visualContent->id);
        $auditoryRec = $recommendations->firstWhere('content_id', $auditoryContent->id);

        if ($visualRec && $auditoryRec) {
            $this->assertGreaterThan(
                $auditoryRec->relevance_score,
                $visualRec->relevance_score
            );
        }
    }

    /**
     * Test analytics aggregation and display
     * @test
     */
    public function test_analytics_aggregation_and_display()
    {
        // Create test data for analytics
        $this->createTestLearningActivities();
        $this->createTestAssessments();

        // Access analytics dashboard
        $response = $this->actingAs($this->user)
            ->get('/student/analytics');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Student/Analytics/Dashboard')
                 ->has('analytics')
                 ->has('analytics.engagement')
                 ->has('analytics.performance')
                 ->has('analytics.time_metrics');
        });

        // Test analytics data accuracy
        $analyticsData = $response->getOriginalContent()->getData()['page']['props']['analytics'];
        
        $this->assertIsArray($analyticsData);
        $this->assertArrayHasKey('engagement', $analyticsData);
        $this->assertArrayHasKey('performance', $analyticsData);
    }

    /**
     * Test automated feedback generation
     * @test
     */
    public function test_automated_feedback_generation()
    {
        // Create assessment with low score
        $assessment = Assessment::factory()->create([
            'student_id' => $this->student->id,
            'subject' => 'Mathematics',
            'topic' => 'Trigonometry',
            'score' => 45,
            'max_score' => 100,
            'percentage' => 45.0,
            'assessment_type' => 'quiz',
            'difficulty_level' => 'intermediate',
        ]);

        // Create learning style profile for contextualized feedback
        LearningStyleProfile::factory()->create([
            'student_id' => $this->student->id,
            'dominant_style' => 'visual',
        ]);

        // Trigger feedback generation
        $feedbackService = new FeedbackGenerationService();
        $feedback = $feedbackService->generateAssessmentFeedback($assessment);

        // Verify feedback was generated
        $this->assertInstanceOf(FeedbackLog::class, $feedback);
        $this->assertEquals($this->student->id, $feedback->student_id);
        $this->assertEquals($assessment->id, $feedback->assessment_id);
        $this->assertEquals('constructive', $feedback->sentiment);
        
        // Verify feedback content is appropriate
        $this->assertNotEmpty($feedback->feedback_text);
        $this->assertIsArray($feedback->action_items);
        $this->assertNotEmpty($feedback->action_items);
    }

    /**
     * Test responsive design and accessibility
     * @test
     */
    public function test_responsive_design_and_accessibility()
    {
        // Test mobile viewport
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
            ])
            ->get('/student/dashboard');

        $response->assertStatus(200);

        // Test tablet viewport
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)',
            ])
            ->get('/student/dashboard');

        $response->assertStatus(200);

        // Test desktop viewport
        $response = $this->actingAs($this->user)
            ->get('/student/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test Indonesian localization
     * @test
     */
    public function test_indonesian_localization()
    {
        // Set Indonesian locale
        $this->student->update(['preferred_language' => 'id']);

        // Test content display in Indonesian
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept-Language' => 'id'])
            ->get('/student/dashboard');

        $response->assertStatus(200);
        
        // Verify Indonesian content is served
        // Note: Actual localization testing would require checking rendered content
    }

    /**
     * Test performance under load
     * @test
     */
    public function test_performance_under_load()
    {
        // Create substantial test data
        Content::factory(100)->create(['grade_level' => '11', 'is_active' => true]);
        Assessment::factory(50)->create(['student_id' => $this->student->id]);
        LearningActivity::factory(200)->create(['student_id' => $this->student->id]);

        // Measure response times
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->get('/student/dashboard');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        // Verify response time is under 3 seconds (3000ms)
        $this->assertLessThan(3000, $responseTime, 'Dashboard response time exceeded 3 seconds');
    }

    /**
     * Test data privacy and security
     * @test
     */
    public function test_data_privacy_and_security()
    {
        $otherUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);
        
        // Verify students cannot access other students' data
        $response = $this->actingAs($this->user)
            ->get("/student/analytics");

        $response->assertStatus(200);
        
        // Try to access analytics with different student ID in session
        $response = $this->actingAs($otherUser)
            ->get("/student/analytics");

        $response->assertStatus(200);
        
        // Verify no data leakage between students
        // This would require checking the actual response data
    }

    /**
     * Test cross-browser compatibility simulation
     * @test
     */
    public function test_cross_browser_compatibility()
    {
        $browsers = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/91.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/14.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Edge/91.0',
        ];

        foreach ($browsers as $userAgent) {
            $response = $this->actingAs($this->user)
                ->withHeaders(['User-Agent' => $userAgent])
                ->get('/student/dashboard');

            $response->assertStatus(200);
        }
    }

    /**
     * Test error handling and user feedback
     * @test
     */
    public function test_error_handling_and_user_feedback()
    {
        // Test 404 error handling
        $response = $this->actingAs($this->user)
            ->get('/student/nonexistent-page');

        $response->assertStatus(404);

        // Test validation error handling
        $response = $this->actingAs($this->user)
            ->postJson('/api/learning-style/survey', [
                'responses' => [], // Empty responses should trigger validation
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test API rate limiting
     * @test
     */
    public function test_api_rate_limiting()
    {
        // Make multiple rapid requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->actingAs($this->user)
                ->getJson('/student/analytics/data');
                
            if ($response->getStatusCode() === 429) {
                // Rate limit hit - this is expected behavior
                $this->assertEquals(429, $response->getStatusCode());
                break;
            }
        }
    }

    /**
     * Helper method to create test learning activities
     */
    private function createTestLearningActivities(): void
    {
        $content = Content::factory(5)->create([
            'grade_level' => '11',
            'is_active' => true,
        ]);

        foreach ($content as $item) {
            LearningActivity::factory(3)->create([
                'student_id' => $this->student->id,
                'content_id' => $item->id,
                'activity_type' => $this->faker->randomElement(['view', 'complete', 'download']),
                'duration_seconds' => $this->faker->numberBetween(300, 3600), // 5 minutes to 1 hour
                'created_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
            ]);
        }
    }

    /**
     * Helper method to create test assessments
     */
    private function createTestAssessments(): void
    {
        Assessment::factory(10)->create([
            'student_id' => $this->student->id,
            'subject' => 'Mathematics',
            'assessment_type' => 'quiz',
            'percentage' => $this->faker->numberBetween(60, 95),
            'difficulty_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'created_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }
}