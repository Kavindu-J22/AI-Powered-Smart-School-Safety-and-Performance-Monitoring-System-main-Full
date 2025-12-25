<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Services\HomeworkAIService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    protected string $viewDirectory = 'student.pages.homework.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->middleware('auth');
        $this->aiService = $aiService;
    }

    /**
     * Get the current student
     */
    private function getStudent(): ?Student
    {
        return Student::where('user_id', Auth::id())->first();
    }

    /**
     * Display list of all homework for the student
     */
    public function index(): View
    {
        $student = $this->getStudent();

        if (!$student) {
            abort(403, 'No student profile found.');
        }

        // Get all homework submissions for this student
        $submissions = HomeworkSubmission::where('student_id', $student->student_id)
            ->with(['homework.subject', 'homework.assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Categorize submissions
        $pending = $submissions->filter(fn($s) => in_array($s->status, ['assigned', 'in_progress']));
        $submitted = $submissions->filter(fn($s) => $s->status === 'submitted');
        $graded = $submissions->filter(fn($s) => $s->status === 'graded');

        // Calculate statistics
        $stats = [
            'total' => $submissions->count(),
            'pending' => $pending->count(),
            'submitted' => $submitted->count(),
            'graded' => $graded->count(),
            'average_score' => $graded->avg('percentage') ?? 0,
            'on_time' => $graded->where('is_late', false)->count(),
            'late' => $graded->where('is_late', true)->count(),
        ];

        return view($this->viewDirectory . 'index', compact('student', 'pending', 'submitted', 'graded', 'stats'));
    }

    /**
     * Show homework details and answer form
     */
    public function show(HomeworkSubmission $submission): View
    {
        $student = $this->getStudent();

        if (!$student || $submission->student_id !== $student->student_id) {
            abort(403, 'Unauthorized access.');
        }

        $submission->load(['homework.subject', 'homework.assignedBy', 'homework.lesson']);
        $homework = $submission->homework;
        $questions = $homework->questions ?? [];

        // If already graded, show results
        if ($submission->status === 'graded') {
            return $this->showResults($submission);
        }

        // Mark as in progress if just assigned
        if ($submission->status === 'assigned') {
            $submission->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return view($this->viewDirectory . 'attempt', compact('student', 'submission', 'homework', 'questions'));
    }

    /**
     * Show graded results
     */
    public function showResults(HomeworkSubmission $submission): View
    {
        $student = $this->getStudent();

        if (!$student || $submission->student_id !== $student->student_id) {
            abort(403, 'Unauthorized access.');
        }

        $submission->load(['homework.subject', 'homework.assignedBy']);
        $homework = $submission->homework;
        $questions = $homework->questions ?? [];
        $answers = $submission->answers ?? [];
        $evaluationResults = $submission->evaluation_results ?? [];

        return view($this->viewDirectory . 'results', compact(
            'student',
            'submission',
            'homework',
            'questions',
            'answers',
            'evaluationResults'
        ));
    }

    /**
     * Submit homework answers
     */
    public function submit(Request $request, HomeworkSubmission $submission): JsonResponse
    {
        $student = $this->getStudent();

        if (!$student || $submission->student_id !== $student->student_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($submission->isSubmitted()) {
            return response()->json(['success' => false, 'error' => 'Already submitted'], 400);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_idx' => 'required|integer',
            'answers.*.answer' => 'required|string',
        ]);

        // Save answers and mark as submitted
        $submission->answers = $validated['answers'];
        $submission->submitted_at = now();
        $submission->status = 'submitted';
        $submission->checkAndUpdateLateStatus();
        $submission->save();

        // Auto-grade
        $this->autoGrade($submission);

        return response()->json([
            'success' => true,
            'message' => 'Homework submitted successfully!',
            'redirect' => route('student.homework.results', $submission->submission_id),
        ]);
    }

    /**
     * Auto-grade a submission using AI service
     */
    protected function autoGrade(HomeworkSubmission $submission): void
    {
        $homework = $submission->homework;
        $questions = $homework->questions;
        $answers = $submission->answers;

        try {
            $results = $this->aiService->evaluateSubmission($questions, $answers);

            // Transform results to be indexed by question_idx for easy access in views
            $transformedResults = $this->transformEvaluationResults($results['results'] ?? []);

            $submission->evaluation_results = $transformedResults;
            $submission->marks_obtained = $results['summary']['marks_obtained'] ?? 0;
            $submission->percentage = $results['summary']['percentage'] ?? 0;
            $submission->grade = $results['summary']['grade'] ?? 'F';
            $submission->feedback = $this->generateOverallFeedback($results);
            $submission->graded_at = now();
            $submission->status = 'graded';
            $submission->save();
        } catch (\Exception $e) {
            \Log::error('Auto-grading failed: ' . $e->getMessage());
            \Log::error('Error details: ' . $e->getTraceAsString());
            // Still mark as submitted even if grading fails
            $submission->status = 'submitted';
            $submission->save();
        }
    }

    /**
     * Transform evaluation results from API format to indexed array
     */
    protected function transformEvaluationResults(array $results): array
    {
        $transformed = [];

        foreach ($results as $result) {
            $questionIdx = $result['question_idx'] ?? 0;
            $evaluation = $result['evaluation'] ?? [];

            // Flatten the structure and add marks_awarded alias
            $transformed[$questionIdx] = array_merge($evaluation, [
                'marks_awarded' => $evaluation['marks_obtained'] ?? 0,
                'is_partial' => isset($evaluation['percentage']) &&
                    $evaluation['percentage'] > 0 &&
                    $evaluation['percentage'] < 100 &&
                    !($evaluation['is_correct'] ?? false)
            ]);
        }

        return $transformed;
    }

    /**
     * Generate overall feedback based on results
     */
    protected function generateOverallFeedback(array $results): string
    {
        $percentage = $results['summary']['percentage'] ?? 0;
        $grade = $results['summary']['grade'] ?? 'F';

        if ($percentage >= 90) {
            return "Excellent work! You've demonstrated a strong understanding of the material. Keep up the great effort!";
        } elseif ($percentage >= 75) {
            return "Good job! You have a solid grasp of the concepts. Review the areas where you lost marks to improve further.";
        } elseif ($percentage >= 60) {
            return "Satisfactory performance. Focus on the topics where you struggled and seek help if needed.";
        } elseif ($percentage >= 50) {
            return "You passed, but there's room for improvement. Consider reviewing the lesson material and practicing more.";
        } else {
            return "This assignment needs more work. Please review the lesson content carefully and don't hesitate to ask your teacher for help.";
        }
    }

    /**
     * Save progress without submitting
     */
    public function saveProgress(Request $request, HomeworkSubmission $submission): JsonResponse
    {
        $student = $this->getStudent();

        if (!$student || $submission->student_id !== $student->student_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($submission->isSubmitted()) {
            return response()->json(['success' => false, 'error' => 'Already submitted'], 400);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $submission->answers = $validated['answers'];
        $submission->save();

        return response()->json([
            'success' => true,
            'message' => 'Progress saved!',
        ]);
    }
}
