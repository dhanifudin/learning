<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    /**
     * Generate content using Gemini AI
     */
    public function generateContent(string $prompt): ?string
    {
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);
            return $response->text();
        } catch (\Exception $e) {
            Log::error('Gemini AI Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyze learning style from survey responses
     */
    public function analyzeLearningStyle(array $surveyResponses, array $studentContext = []): array
    {
        $prompt = $this->buildLearningStylePrompt($surveyResponses, $studentContext);
        
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);

            $analysisText = $response->text();
            
            // Parse the response to extract structured data
            return $this->parseLearningStyleResponse($analysisText);
        } catch (\Exception $e) {
            Log::error('Learning Style Analysis Error: ' . $e->getMessage());
            throw new \Exception('Failed to analyze learning style: ' . $e->getMessage());
        }
    }

    /**
     * Generate personalized learning recommendations
     */
    public function generateRecommendations(array $learningProfile, array $contentLibrary = []): array
    {
        $prompt = $this->buildRecommendationPrompt($learningProfile, $contentLibrary);
        
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);

            $recommendationText = $response->text();
            
            return $this->parseRecommendationResponse($recommendationText);
        } catch (\Exception $e) {
            Log::error('Recommendation Generation Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate automated feedback for assessments
     */
    public function generateFeedback(array $assessmentData, array $studentProfile): string
    {
        $prompt = $this->buildFeedbackPrompt($assessmentData, $studentProfile);
        
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);

            return $response->text();
        } catch (\Exception $e) {
            Log::error('Feedback Generation Error: ' . $e->getMessage());
            return 'Unable to generate feedback at this time. Please try again later.';
        }
    }

    /**
     * Build learning style analysis prompt
     */
    private function buildLearningStylePrompt(array $responses, array $context): string
    {
        $contextStr = !empty($context) ? 
            "Student context: Grade {$context['grade']}, Interests: " . implode(', ', $context['interests'] ?? []) . "\n" : 
            '';

        $responsesStr = '';
        foreach ($responses as $questionId => $response) {
            $responsesStr .= "Question {$questionId}: {$response}\n";
        }

        return "
Analyze this Indonesian high school student's learning style based on their survey responses.

{$contextStr}

Survey Responses:
{$responsesStr}

Please analyze and provide a JSON response with the following structure:
{
    \"visual_score\": 0-100,
    \"auditory_score\": 0-100,
    \"kinesthetic_score\": 0-100,
    \"dominant_style\": \"visual|auditory|kinesthetic|mixed\",
    \"confidence_score\": 0-100,
    \"reasoning\": \"Brief explanation of the analysis\",
    \"recommendations\": [\"List of study method recommendations\"]
}

Focus on Indonesian educational context and cultural learning preferences.
";
    }

    /**
     * Build recommendation prompt
     */
    private function buildRecommendationPrompt(array $profile, array $content): string
    {
        $profileStr = json_encode($profile, JSON_PRETTY_PRINT);
        $contentStr = !empty($content) ? json_encode($content, JSON_PRETTY_PRINT) : 'No specific content provided';

        return "
Generate personalized learning content recommendations for an Indonesian high school student.

Student Learning Profile:
{$profileStr}

Available Content Library:
{$contentStr}

Please provide recommendations in JSON format:
{
    \"recommended_content\": [
        {
            \"content_id\": \"id\",
            \"relevance_score\": 0-100,
            \"reason\": \"Why this content matches the student's learning style\"
        }
    ],
    \"study_strategies\": [\"List of personalized study strategies\"],
    \"learning_tips\": [\"Specific tips based on learning style\"]
}
";
    }

    /**
     * Build feedback prompt
     */
    private function buildFeedbackPrompt(array $assessment, array $profile): string
    {
        $assessmentStr = json_encode($assessment, JSON_PRETTY_PRINT);
        $profileStr = json_encode($profile, JSON_PRETTY_PRINT);

        return "
Generate constructive, personalized feedback for an Indonesian high school student's assessment.

Assessment Data:
{$assessmentStr}

Student Profile:
{$profileStr}

Please provide encouraging, actionable feedback that:
1. Acknowledges their efforts and progress
2. Identifies specific areas for improvement
3. Suggests concrete next steps based on their learning style
4. Uses appropriate tone for Indonesian high school students
5. Includes motivational elements

Keep feedback positive, specific, and culturally appropriate.
";
    }

    /**
     * Parse learning style analysis response
     */
    private function parseLearningStyleResponse(string $response): array
    {
        // Try to extract JSON from the response
        preg_match('/\{.*\}/s', $response, $matches);
        
        if (!empty($matches[0])) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        // Fallback if JSON parsing fails
        return [
            'visual_score' => 33.33,
            'auditory_score' => 33.33,
            'kinesthetic_score' => 33.33,
            'dominant_style' => 'mixed',
            'confidence_score' => 50,
            'reasoning' => 'Analysis could not be completed',
            'recommendations' => ['Try various learning methods to find what works best']
        ];
    }

    /**
     * Parse recommendation response
     */
    private function parseRecommendationResponse(string $response): array
    {
        preg_match('/\{.*\}/s', $response, $matches);
        
        if (!empty($matches[0])) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return [
            'recommended_content' => [],
            'study_strategies' => ['Use a variety of learning materials'],
            'learning_tips' => ['Practice regularly and seek help when needed']
        ];
    }
}