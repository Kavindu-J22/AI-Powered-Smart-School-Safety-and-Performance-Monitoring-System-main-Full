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
     * Generate questions from lesson content.
     * Uses a 5-minute timeout because the ML server may download model weights
     * from HuggingFace on first use (cold-start can take 2-4 minutes).
     */
    public function generateQuestions(array $lessonData, int $numMcq = 2, int $numShort = 2, int $numDescriptive = 1): array
    {
        // Use a longer timeout for question generation to accommodate ML model cold-starts
        $generateTimeout = config('services.homework_ai.generate_timeout', 300);

        try {
            $response = Http::timeout($generateTimeout)
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
     * Fallback question generation when AI service is unavailable.
     * Uses lesson content, learning outcomes and keywords to produce
     * content-relevant questions with randomised MCQ correct-answer positions.
     */
    protected function fallbackGenerateQuestions(array $lessonData, int $numMcq, int $numShort, int $numDescriptive): array
    {
        $questions = [];
        $topics           = $lessonData['topics']            ?? ['Topic'];
        $unit             = $lessonData['unit']              ?? 'Unit';
        $subject          = $lessonData['subject']           ?? 'General';
        $learningOutcomes = $lessonData['learning_outcomes'] ?? [];
        $content          = $lessonData['content']           ?? '';

        // Question-stem templates (varied to avoid repetition)
        $mcqStems   = [
            "What is the primary function of {topic}?",
            "Which of the following best describes {topic}?",
            "In the context of {unit}, {topic} is responsible for:",
            "Which statement about {topic} is correct?",
        ];
        $shortStems = [
            "Explain the process of {topic}.",
            "Describe the relationship between {topic} and {unit}.",
            "What are the key characteristics of {topic}?",
            "Why is {topic} important in {unit}?",
        ];
        $descStems  = [
            "Discuss in detail the role of {topic} in {unit}.",
            "Analyze {topic} and explain its significance in {unit}. Provide examples.",
            "Critically examine {topic} and its relationship to other concepts in {unit}.",
        ];

        $usedMcqStems  = [];
        $usedShortStems = [];
        $usedDescStems  = [];

        for ($i = 0; $i < $numMcq; $i++) {
            $topic = $topics[$i % count($topics)];

            // Pick an unused question stem
            $stem = $this->pickUnusedTemplate($mcqStems, $usedMcqStems);
            $questionText = str_replace(['{topic}', '{unit}'], [$topic, $unit], $stem);

            // Build 4 options: correct option first, then 3 distractors
            $options = $this->generateMcqOptions($topic, $unit, $subject);

            // Override correct option with a content-derived statement when possible
            $contentCorrect = $this->extractContentOption($topic, $content, $learningOutcomes);
            if ($contentCorrect) {
                $options[0] = $contentCorrect;
            }

            // SHUFFLE so the correct answer is not always option A
            $correctOption = $options[0];
            shuffle($options);
            $correctIdx    = array_search($correctOption, $options);
            $correctLetter = chr(65 + $correctIdx);

            $questions[] = [
                'question_type'  => 'MCQ',
                'question_text'  => $questionText,
                'options'        => array_values($options),
                'correct_answer' => $correctLetter,
                'explanation'    => "Option {$correctLetter} is correct: {$correctOption}",
                'marks'          => 1,
                'topic'          => $topic,
                'unit'           => $unit,
            ];
        }

        for ($i = 0; $i < $numShort; $i++) {
            $topic = $topics[$i % count($topics)];
            $stem  = $this->pickUnusedTemplate($shortStems, $usedShortStems);
            $questionText = str_replace(['{topic}', '{unit}'], [$topic, $unit], $stem);

            [$expectedAnswer, $keyPoints] = $this->buildExpectedAnswer(
                $topic,
                $unit,
                $content,
                $learningOutcomes,
                3
            );

            $questions[] = [
                'question_type'   => 'SHORT_ANSWER',
                'question_text'   => $questionText,
                'expected_answer' => $expectedAnswer,
                'key_points'      => $keyPoints,
                'marks'           => 3,
                'topic'           => $topic,
                'unit'            => $unit,
            ];
        }

        for ($i = 0; $i < $numDescriptive; $i++) {
            $topic = $topics[$i % count($topics)];
            $stem  = $this->pickUnusedTemplate($descStems, $usedDescStems);
            $questionText = str_replace(['{topic}', '{unit}'], [$topic, $unit], $stem);

            [$expectedAnswer, $keyPoints] = $this->buildExpectedAnswer(
                $topic,
                $unit,
                $content,
                $learningOutcomes,
                5
            );

            // Ensure at least 5 key points for descriptive questions
            $additions = [
                "Practical applications and real-world examples of {$topic}",
                "Critical analysis of {$topic} in the context of {$unit}",
                "Relevance of {$topic} to the Sri Lankan educational context",
            ];
            foreach ($additions as $addition) {
                if (count($keyPoints) >= 5) break;
                $keyPoints[] = $addition;
            }

            $questions[] = [
                'question_type'   => 'DESCRIPTIVE',
                'question_text'   => $questionText,
                'expected_answer' => $expectedAnswer,
                'key_points'      => $keyPoints,
                'marks'           => 5,
                'topic'           => $topic,
                'unit'            => $unit,
            ];
        }

        return $questions;
    }

    /**
     * Pick a template that has not been used yet (resets when all are exhausted).
     */
    protected function pickUnusedTemplate(array $templates, array &$used): string
    {
        $available = array_diff($templates, $used);
        if (empty($available)) {
            $used = [];
            $available = $templates;
        }
        $pick = $available[array_rand($available)];
        $used[] = $pick;
        return $pick;
    }

    /**
     * Extract a content-based correct option from lesson content / learning outcomes.
     * Returns null if no relevant content is found.
     */
    protected function extractContentOption(
        string $topic,
        string $content,
        array $learningOutcomes
    ): ?string {
        // 1. Try a relevant learning outcome
        foreach ($learningOutcomes as $outcome) {
            if (stripos($outcome, $topic) !== false) {
                $text = trim($outcome);
                return strlen($text) > 130 ? substr($text, 0, 127) . '...' : $text;
            }
        }

        // 2. Try a relevant sentence from content
        if ($content) {
            $sentences = preg_split('/(?<=[.!?])\s+/', $content);
            foreach ($sentences as $sent) {
                $sent = trim($sent);
                if (stripos($sent, $topic) !== false && strlen($sent) >= 20 && strlen($sent) <= 150) {
                    return $sent;
                }
            }
        }

        return null;
    }

    /**
     * Build a content-based expected answer and key points list.
     * Returns [expectedAnswer, keyPoints].
     */
    protected function buildExpectedAnswer(
        string $topic,
        string $unit,
        string $content,
        array $learningOutcomes,
        int $maxOutcomes
    ): array {
        // Split outcomes into relevant vs. other
        $relevant = [];
        $other    = [];
        foreach ($learningOutcomes as $outcome) {
            if (stripos($outcome, $topic) !== false) {
                $relevant[] = trim($outcome);
            } else {
                $other[] = trim($outcome);
            }
        }

        // Use relevant outcomes first, pad with others
        $selected = array_slice($relevant, 0, $maxOutcomes);
        if (count($selected) < 2) {
            $selected = array_merge($selected, array_slice($other, 0, $maxOutcomes - count($selected)));
        }

        $keyPoints = !empty($selected) ? $selected : [
            "Definition and meaning of {$topic}",
            "Relationship between {$topic} and {$unit}",
            "Practical application of {$topic} in {$unit}",
        ];

        // Build expected answer from outcomes + relevant content sentences
        $answerParts = [];
        if (!empty($selected)) {
            $answerParts[] = implode(' ', $selected);
        }

        if ($content) {
            $sentences = preg_split('/(?<=[.!?])\s+/', $content);
            foreach ($sentences as $sent) {
                $sent = trim($sent);
                if (stripos($sent, $topic) !== false && strlen($sent) >= 20) {
                    $answerParts[] = $sent;
                    if (count($answerParts) >= 4) break;
                }
            }
        }

        $expectedAnswer = !empty($answerParts)
            ? implode(' ', $answerParts)
            : "{$topic} is an important concept in {$unit}. It involves understanding the key principles and mechanisms related to {$unit} and applying them in practical contexts.";

        return [$expectedAnswer, array_values(array_slice($keyPoints, 0, $maxOutcomes))];
    }

    /**
     * Generate realistic MCQ options for fallback.
     * The first option is always the correct one; callers must shuffle.
     */
    protected function generateMcqOptions(string $topic, string $unit, string $subject): array
    {
        $subjectLower = strtolower($subject);

        // Subject-specific option templates (include $topic so all options are topic-specific)
        if (
            str_contains($subjectLower, 'science') || str_contains($subjectLower, 'biology') ||
            str_contains($subjectLower, 'chemistry') || str_contains($subjectLower, 'physics')
        ) {
            return [
                "{$topic} is a fundamental component that plays a key role in {$unit}",
                "{$topic} has no significant relationship with {$unit}",
                "{$topic} only occurs in extreme conditions unrelated to {$unit}",
                "{$topic} is a byproduct that does not affect {$unit}",
            ];
        } elseif (str_contains($subjectLower, 'history') || str_contains($subjectLower, 'social')) {
            return [
                "{$topic} significantly influenced the development of {$unit}",
                "{$topic} had minimal impact on {$unit}",
                "{$topic} occurred after the period of {$unit}",
                "{$topic} was unrelated to the key events in {$unit}",
            ];
        } elseif (str_contains($subjectLower, 'english') || str_contains($subjectLower, 'language')) {
            return [
                "{$topic} is an essential element that enhances understanding in {$unit}",
                "{$topic} is rarely applicable in {$unit}",
                "{$topic} contradicts the stylistic principles of {$unit}",
                "{$topic} is not applicable to the context of {$unit}",
            ];
        } elseif (
            str_contains($subjectLower, 'math') || str_contains($subjectLower, 'algebra') ||
            str_contains($subjectLower, 'geometry')
        ) {
            return [
                "{$topic} is a mathematical concept used to solve problems in {$unit}",
                "{$topic} cannot be applied to {$unit}",
                "{$topic} is only theoretical and not used in {$unit}",
                "{$topic} contradicts the core principles of {$unit}",
            ];
        } elseif (str_contains($subjectLower, 'health') || str_contains($subjectLower, 'medical')) {
            return [
                "{$topic} is important for maintaining proper function in {$unit}",
                "{$topic} has no measurable effect on {$unit}",
                "{$topic} only affects {$unit} in rare clinical cases",
                "{$topic} has been shown to be harmful in the context of {$unit}",
            ];
        }

        // Default general options
        return [
            "{$topic} is a key concept that is central to understanding {$unit}",
            "{$topic} is not directly related to {$unit}",
            "{$topic} only applies in specific cases outside {$unit}",
            "{$topic} contradicts the main principles of {$unit}",
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
