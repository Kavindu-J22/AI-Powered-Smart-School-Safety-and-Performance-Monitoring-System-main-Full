<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeworkAIService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.homework_ai.base_url', 'http://localhost:5001');
        $this->timeout = config('services.homework_ai.timeout', 30);
    }

    /**
     * Generate questions from lesson content
     */
    public function generateQuestions(array $lessonData, int $numMcq = 2, int $numShort = 2, int $numDescriptive = 1): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/lessons/generate-questions", [
                    'lesson_data' => $lessonData,
                    'num_mcq' => $numMcq,
                    'num_short' => $numShort,
                    'num_descriptive' => $numDescriptive,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['questions'] ?? [];
            }

            Log::error('AI Service error: ' . $response->body());
            throw new \Exception('Failed to generate questions: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Service connection error: ' . $e->getMessage());
            // Fallback to basic question generation
            return $this->fallbackGenerateQuestions($lessonData, $numMcq, $numShort, $numDescriptive);
        }
    }

    /**
     * Evaluate a complete homework submission
     */
    public function evaluateSubmission(array $questions, array $answers): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/evaluation/evaluate", [
                    'questions' => $questions,
                    'answers' => $answers,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to evaluate submission');
        } catch (\Exception $e) {
            Log::error('AI evaluation error: ' . $e->getMessage());
            return $this->fallbackEvaluate($questions, $answers);
        }
    }

    /**
     * Evaluate a single answer
     */
    public function evaluateSingleAnswer(array $question, string $answer): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/evaluation/evaluate-single", [
                    'question' => $question,
                    'answer' => $answer,
                ]);

            if ($response->successful()) {
                return $response->json()['evaluation'] ?? [];
            }

            throw new \Exception('Failed to evaluate answer');
        } catch (\Exception $e) {
            Log::error('AI single evaluation error: ' . $e->getMessage());
            return $this->fallbackEvaluateSingle($question, $answer);
        }
    }

    /**
     * Schedule weekly homework assignments
     */
    public function scheduleWeeklyHomework(array $lessonData, int $subjectId, int $classId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/homework/schedule-weekly", [
                    'lesson_data' => $lessonData,
                    'subject' => $lessonData['subject'] ?? '',
                    'grade' => $lessonData['grade'] ?? 6,
                    'class_id' => $classId,
                    'week_start' => now()->startOfWeek()->format('Y-m-d'),
                ]);

            if ($response->successful()) {
                return $response->json()['assignments'] ?? [];
            }

            throw new \Exception('Failed to schedule homework');
        } catch (\Exception $e) {
            Log::error('AI scheduling error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student performance data
     */
    public function getStudentPerformance(int $studentId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/performance/student/{$studentId}");

            if ($response->successful()) {
                return $response->json()['performance'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('AI performance fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate monthly report
     */
    public function generateMonthlyReport(int $studentId, int $month, int $year): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/reports/monthly/student/{$studentId}", [
                    'month' => $month,
                    'year' => $year,
                ]);

            if ($response->successful()) {
                return $response->json()['report'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('AI report generation error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fallback question generation when AI service is unavailable
     */
    protected function fallbackGenerateQuestions(array $lessonData, int $numMcq, int $numShort, int $numDescriptive): array
    {
        $questions = [];
        $topics = $lessonData['topics'] ?? ['Topic'];
        $unit = $lessonData['unit'] ?? 'Unit';
        $subject = $lessonData['subject'] ?? 'General';

        for ($i = 0; $i < $numMcq; $i++) {
            $topic = $topics[$i % count($topics)];
            $options = $this->generateMcqOptions($topic, $unit, $subject);

            $questions[] = [
                'question_type' => 'MCQ',
                'question_text' => "What is the primary function of {$topic}?",
                'options' => $options,
                'correct_answer' => 'A',
                'explanation' => "Option A is correct because {$topic} is a fundamental component in {$unit}. It plays a crucial role in the processes and mechanisms that define this area of study.",
                'marks' => 1,
                'topic' => $topic,
                'unit' => $unit,
            ];
        }

        for ($i = 0; $i < $numShort; $i++) {
            $topic = $topics[$i % count($topics)];
            $questions[] = [
                'question_type' => 'SHORT_ANSWER',
                'question_text' => "Explain the process of {$topic}.",
                'expected_answer' => "A comprehensive explanation of {$topic} including its key aspects, relevance to {$unit}, and practical applications.",
                'key_points' => ["Definition of {$topic}", "Relationship to {$unit}", "Practical application or example"],
                'marks' => 3,
                'topic' => $topic,
                'unit' => $unit,
            ];
        }

        for ($i = 0; $i < $numDescriptive; $i++) {
            $topic = $topics[$i % count($topics)];
            $questions[] = [
                'question_type' => 'DESCRIPTIVE',
                'question_text' => "Discuss in detail the scientific principles of {$topic}.",
                'expected_answer' => "A comprehensive analysis of {$topic} covering theoretical understanding, practical applications, examples from Sri Lankan context, and critical evaluation.",
                'key_points' => [
                    "Theoretical foundation of {$topic}",
                    "Practical applications and examples",
                    "Analysis and critical thinking",
                    "Relevance to Sri Lankan context",
                    "Conclusions and recommendations"
                ],
                'marks' => 5,
                'topic' => $topic,
                'unit' => $unit,
            ];
        }

        return $questions;
    }

    /**
     * Generate realistic MCQ options for fallback
     */
    protected function generateMcqOptions(string $topic, string $unit, string $subject): array
    {
        $subjectLower = strtolower($subject);

        // Subject-specific option templates
        if (
            str_contains($subjectLower, 'science') || str_contains($subjectLower, 'biology') ||
            str_contains($subjectLower, 'chemistry') || str_contains($subjectLower, 'physics')
        ) {
            return [
                "It is a fundamental component that plays a key role in {$unit}",
                "It has no significant relationship with {$unit}",
                "It only occurs in extreme conditions unrelated to {$unit}",
                "It is a byproduct that doesn't affect {$unit}"
            ];
        } elseif (str_contains($subjectLower, 'history') || str_contains($subjectLower, 'social')) {
            return [
                "It significantly influenced the development of {$unit}",
                "It had minimal impact on {$unit}",
                "It occurred after the period of {$unit}",
                "It was unrelated to the events in {$unit}"
            ];
        } elseif (str_contains($subjectLower, 'english') || str_contains($subjectLower, 'language')) {
            return [
                "It is an essential element used to enhance {$unit}",
                "It is rarely used in {$unit}",
                "It contradicts the principles of {$unit}",
                "It is not applicable to {$unit}"
            ];
        } elseif (
            str_contains($subjectLower, 'math') || str_contains($subjectLower, 'algebra') ||
            str_contains($subjectLower, 'geometry')
        ) {
            return [
                "It is a mathematical concept that helps solve problems in {$unit}",
                "It cannot be applied to {$unit}",
                "It is only theoretical and not used in {$unit}",
                "It contradicts the principles of {$unit}"
            ];
        } elseif (str_contains($subjectLower, 'health') || str_contains($subjectLower, 'medical')) {
            return [
                "It is important for maintaining proper function in {$unit}",
                "It has no effect on {$unit}",
                "It only affects {$unit} in rare cases",
                "It is harmful to {$unit}"
            ];
        }

        // Default general options
        return [
            "It is a key concept that is central to understanding {$unit}",
            "It is not directly related to {$unit}",
            "It only applies in specific cases outside {$unit}",
            "It contradicts the main principles of {$unit}"
        ];
    }

    protected function fallbackEvaluate(array $questions, array $answers): array
    {
        // Simple fallback evaluation
        $results = [];
        $totalMarks = 0;
        $marksObtained = 0;

        foreach ($answers as $answer) {
            $idx = $answer['question_idx'] ?? 0;
            $question = $questions[$idx] ?? null;

            if ($question) {
                $eval = $this->fallbackEvaluateSingle($question, $answer['answer'] ?? '');
                $totalMarks += $eval['max_marks'];
                $marksObtained += $eval['marks_obtained'];
                $results[] = ['question_idx' => $idx, 'evaluation' => $eval];
            }
        }

        $percentage = $totalMarks > 0 ? ($marksObtained / $totalMarks) * 100 : 0;

        return [
            'results' => $results,
            'summary' => [
                'total_marks' => $totalMarks,
                'marks_obtained' => $marksObtained,
                'percentage' => round($percentage, 1),
                'grade' => $this->calculateGrade($percentage),
            ],
        ];
    }

    protected function fallbackEvaluateSingle(array $question, string $answer): array
    {
        $type = $question['question_type'] ?? 'MCQ';
        $maxMarks = $question['marks'] ?? 1;

        if ($type === 'MCQ') {
            $correct = strtoupper(trim($answer)) === strtoupper(trim($question['correct_answer'] ?? 'A'));
            $correctAnswer = $question['correct_answer'] ?? 'A';

            return [
                'question_type' => 'MCQ',
                'is_correct' => $correct,
                'marks_obtained' => $correct ? $maxMarks : 0,
                'max_marks' => $maxMarks,
                'percentage' => $correct ? 100 : 0,
                'feedback' => $correct ? 'Correct!' : "Incorrect. The correct answer is {$correctAnswer}.",
                'correct_answer' => $correctAnswer,
                'student_answer' => strtoupper(trim($answer)),
                'explanation' => $question['explanation'] ?? ''
            ];
        }

        // For SHORT_ANSWER and DESCRIPTIVE, use basic keyword matching
        $expectedAnswer = $question['expected_answer'] ?? '';
        $keyPoints = $question['key_points'] ?? [];

        // Calculate score based on answer length and keyword presence
        $answerLength = strlen(trim($answer));
        $wordCount = str_word_count($answer);

        // Minimum word requirements
        $minWords = $type === 'DESCRIPTIVE' ? 50 : 15;
        $optimalWords = $type === 'DESCRIPTIVE' ? 150 : 50;

        // Length score
        if ($wordCount < $minWords) {
            $lengthScore = $wordCount / $minWords;
        } elseif ($wordCount > $optimalWords * 2) {
            $lengthScore = 0.7; // Too long
        } else {
            $lengthScore = 1.0;
        }

        // Keyword matching score
        $keywordScore = $this->calculateKeywordScore($answer, $expectedAnswer, $keyPoints);

        // Combined score (40% length, 60% keywords)
        $combinedScore = ($lengthScore * 0.4) + ($keywordScore * 0.6);

        $marksObtained = round($combinedScore * $maxMarks, 1);
        $percentage = round($combinedScore * 100, 1);

        // Generate feedback
        $feedback = $this->generateSubjectiveFeedback($combinedScore, $wordCount, $minWords, $keyPoints);

        return [
            'question_type' => $type,
            'is_correct' => $combinedScore >= 0.6,
            'marks_obtained' => $marksObtained,
            'max_marks' => $maxMarks,
            'percentage' => $percentage,
            'feedback' => $feedback,
            'word_count' => $wordCount,
            'min_words' => $minWords
        ];
    }

    /**
     * Calculate keyword matching score
     */
    protected function calculateKeywordScore(string $answer, string $expectedAnswer, array $keyPoints): float
    {
        $answerLower = strtolower($answer);
        $score = 0;
        $totalKeywords = 0;

        // Extract keywords from expected answer
        $expectedWords = preg_split('/\s+/', strtolower($expectedAnswer));
        $expectedWords = array_filter($expectedWords, function ($word) {
            return strlen($word) > 4; // Only consider words longer than 4 characters
        });

        foreach ($expectedWords as $word) {
            $totalKeywords++;
            if (str_contains($answerLower, $word)) {
                $score++;
            }
        }

        // Check key points
        foreach ($keyPoints as $point) {
            $totalKeywords++;
            $pointLower = strtolower($point);
            // Check if any significant words from the key point are in the answer
            $pointWords = preg_split('/\s+/', $pointLower);
            $pointWords = array_filter($pointWords, function ($word) {
                return strlen($word) > 4;
            });

            $pointMatches = 0;
            foreach ($pointWords as $word) {
                if (str_contains($answerLower, $word)) {
                    $pointMatches++;
                }
            }

            if ($pointMatches > 0) {
                $score += ($pointMatches / max(1, count($pointWords)));
            }
        }

        return $totalKeywords > 0 ? min(1.0, $score / $totalKeywords) : 0.5;
    }

    /**
     * Generate feedback for subjective answers
     */
    protected function generateSubjectiveFeedback(float $score, int $wordCount, int $minWords, array $keyPoints): string
    {
        if ($score >= 0.9) {
            return "Excellent answer! You've covered the key points comprehensively.";
        } elseif ($score >= 0.75) {
            return "Good answer! You've addressed most of the important points.";
        } elseif ($score >= 0.6) {
            return "Satisfactory answer. Consider adding more details about the key concepts.";
        } elseif ($wordCount < $minWords) {
            return "Your answer is too brief. Please provide more details and explanation (minimum {$minWords} words).";
        } else {
            $missingPoints = count($keyPoints) > 0 ? " Make sure to cover: " . implode(', ', array_slice($keyPoints, 0, 2)) : "";
            return "Your answer needs improvement.{$missingPoints}";
        }
    }

    protected function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D+';
        if ($percentage >= 40) return 'D';
        return 'F';
    }
}
