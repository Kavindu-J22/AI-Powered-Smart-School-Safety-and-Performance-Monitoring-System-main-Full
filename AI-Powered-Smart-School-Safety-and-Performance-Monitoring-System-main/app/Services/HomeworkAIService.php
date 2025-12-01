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

        for ($i = 0; $i < $numMcq; $i++) {
            $topic = $topics[$i % count($topics)];
            $questions[] = [
                'question_type' => 'MCQ',
                'question_text' => "What is the primary function of {$topic}?",
                'options' => ["Correct answer about {$topic}", "Option B", "Option C", "Option D"],
                'correct_answer' => 'A',
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
                'expected_answer' => "Explanation of {$topic}",
                'key_points' => ["Definition", "Process", "Application"],
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
                'expected_answer' => "Detailed analysis of {$topic}",
                'key_points' => ["Theory", "Application", "Examples", "Conclusion"],
                'marks' => 5,
                'topic' => $topic,
                'unit' => $unit,
            ];
        }

        return $questions;
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
            $correct = strtoupper($answer) === strtoupper($question['correct_answer'] ?? 'A');
            return [
                'is_correct' => $correct,
                'marks_obtained' => $correct ? $maxMarks : 0,
                'max_marks' => $maxMarks,
                'percentage' => $correct ? 100 : 0,
                'feedback' => $correct ? 'Correct!' : 'Incorrect.',
            ];
        }

        // For subjective answers, give partial marks based on length
        $answerLength = strlen(trim($answer));
        $score = min(1, $answerLength / 100);
        
        return [
            'is_correct' => $score > 0.5,
            'marks_obtained' => round($score * $maxMarks, 1),
            'max_marks' => $maxMarks,
            'percentage' => round($score * 100, 1),
            'feedback' => $score > 0.5 ? 'Good attempt.' : 'Needs more detail.',
        ];
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

