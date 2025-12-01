<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentPerformance;
use App\Services\HomeworkAIService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HomeworkSubmissionController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.homework.submissions.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Homework $homework): View
    {
        $submissions = $homework->submissions()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);
        
        return view($this->viewDirectory . 'index', compact('homework', 'submissions'));
    }

    public function show(HomeworkSubmission $submission): View
    {
        $submission->load(['homework', 'student']);
        $questionResults = $submission->getQuestionResults();
        
        return view($this->viewDirectory . 'show', compact('submission', 'questionResults'));
    }

    /**
     * Student submits their homework answers
     */
    public function submit(Request $request, HomeworkSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_idx' => 'required|integer',
            'answers.*.answer' => 'required|string',
        ]);

        if ($submission->isSubmitted()) {
            return response()->json([
                'success' => false,
                'error' => 'Homework already submitted',
            ], 400);
        }

        $submission->answers = $validated['answers'];
        $submission->submitted_at = now();
        $submission->status = 'submitted';
        $submission->checkAndUpdateLateStatus();
        $submission->save();

        // Auto-grade the submission
        $this->autoGrade($submission);

        return response()->json([
            'success' => true,
            'message' => 'Homework submitted and graded successfully',
            'results' => $submission->fresh()->evaluation_results,
        ]);
    }

    /**
     * Auto-grade a submission using AI
     */
    public function autoGrade(HomeworkSubmission $submission): void
    {
        $homework = $submission->homework;
        $questions = $homework->questions;
        $answers = $submission->answers;

        try {
            $results = $this->aiService->evaluateSubmission($questions, $answers);
            
            $submission->evaluation_results = $results['results'] ?? [];
            $submission->marks_obtained = $results['summary']['marks_obtained'] ?? 0;
            $submission->percentage = $results['summary']['percentage'] ?? 0;
            $submission->grade = $results['summary']['grade'] ?? 'F';
            $submission->graded_at = now();
            $submission->status = 'graded';
            $submission->save();

            // Update student performance
            $this->updateStudentPerformance($submission);
        } catch (\Exception $e) {
            \Log::error('Auto-grading failed: ' . $e->getMessage());
        }
    }

    /**
     * Batch auto-grade all pending submissions for a homework
     */
    public function batchGrade(Homework $homework): JsonResponse
    {
        $pendingSubmissions = $homework->submissions()
            ->where('status', 'submitted')
            ->get();

        $graded = 0;
        foreach ($pendingSubmissions as $submission) {
            try {
                $this->autoGrade($submission);
                $graded++;
            } catch (\Exception $e) {
                \Log::error("Failed to grade submission {$submission->submission_id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'graded_count' => $graded,
            'total_pending' => $pendingSubmissions->count(),
        ]);
    }

    /**
     * Get detailed evaluation for a specific answer
     */
    public function evaluateAnswer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|array',
            'answer' => 'required|string',
        ]);

        try {
            $result = $this->aiService->evaluateSingleAnswer(
                $validated['question'],
                $validated['answer']
            );

            return response()->json([
                'success' => true,
                'evaluation' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get submission statistics for a student
     */
    public function studentStats(int $studentId): JsonResponse
    {
        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->where('status', 'graded')
            ->with('homework.subject')
            ->get();

        $stats = [
            'total_submissions' => $submissions->count(),
            'average_score' => $submissions->avg('percentage'),
            'highest_score' => $submissions->max('percentage'),
            'lowest_score' => $submissions->min('percentage'),
            'on_time_rate' => $submissions->where('is_late', false)->count() / max($submissions->count(), 1) * 100,
            'by_subject' => [],
        ];

        foreach ($submissions->groupBy('homework.subject.subject_name') as $subject => $subs) {
            $stats['by_subject'][$subject] = [
                'count' => $subs->count(),
                'average' => $subs->avg('percentage'),
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Update student performance after grading
     */
    protected function updateStudentPerformance(HomeworkSubmission $submission): void
    {
        $homework = $submission->homework;
        
        StudentPerformance::updateFromSubmissions(
            $submission->student_id,
            $homework->subject_id,
            $homework->academic_year,
            now()->month
        );
    }
}

