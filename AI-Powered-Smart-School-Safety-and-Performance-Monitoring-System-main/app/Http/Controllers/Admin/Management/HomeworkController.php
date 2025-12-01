<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\HomeworkAIService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HomeworkController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.homework.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(): View
    {
        $homework = Homework::with(['subject', 'assignedBy', 'schoolClass'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view($this->viewDirectory . 'index', compact('homework'));
    }

    public function dashboard(): View
    {
        $stats = [
            'total_homework' => Homework::count(),
            'active_homework' => Homework::active()->count(),
            'pending_submissions' => HomeworkSubmission::pending()->count(),
            'graded_today' => HomeworkSubmission::graded()->whereDate('graded_at', today())->count(),
        ];

        $recentHomework = Homework::with(['subject', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $overdueHomework = Homework::overdue()
            ->with(['subject', 'schoolClass'])
            ->take(5)
            ->get();

        // Data for modals
        $subjects = Subject::active()->orderBy('subject_name')->get();
        $classes = SchoolClass::orderBy('class_name')->get();
        $lessons = Lesson::published()->orderBy('title')->get();

        return view($this->viewDirectory . 'dashboard', compact(
            'stats',
            'recentHomework',
            'overdueHomework',
            'subjects',
            'classes',
            'lessons'
        ));
    }

    public function create(): View
    {
        $subjects = Subject::active()->orderBy('subject_name')->get();
        $classes = SchoolClass::orderBy('class_name')->get();
        $lessons = Lesson::published()->orderBy('title')->get();

        return view($this->viewDirectory . 'create', compact('subjects', 'classes', 'lessons'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Handle questions - could be JSON string or array
        $questionsData = $request->input('questions');
        if (is_string($questionsData)) {
            $questionsData = json_decode($questionsData, true);
            $request->merge(['questions' => $questionsData]);
        }

        // If no questions provided, create a default structure
        if (empty($questionsData)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['questions' => 'Please generate or add at least one question.']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:school_classes,id',
            'lesson_id' => 'nullable|exists:lessons,lesson_id',
            'grade_level' => 'required|integer|min:1|max:13',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after:today',
            'questions' => 'required|array|min:1',
        ]);

        $validated['assigned_by'] = auth()->user()->teacher->teacher_id ?? 1;
        $validated['assigned_date'] = now();
        $validated['total_marks'] = collect($validated['questions'])->sum('marks');
        $validated['status'] = 'active';
        $validated['academic_year'] = Homework::getCurrentAcademicYear();

        $homework = Homework::create($validated);

        return redirect()
            ->route('admin.management.homework.show', $homework->homework_id)
            ->with('success', 'Homework created successfully');
    }

    public function show(Homework $homework): View
    {
        $homework->load(['subject', 'assignedBy', 'schoolClass', 'lesson', 'submissions.student']);
        $submissionStats = $homework->getSubmissionStats();

        return view($this->viewDirectory . 'show', compact('homework', 'submissionStats'));
    }

    public function edit(Homework $homework): View
    {
        $subjects = Subject::orderBy('subject_name')->get();
        $classes = SchoolClass::orderBy('class_name')->get();

        return view($this->viewDirectory . 'edit', compact('homework', 'subjects', 'classes'));
    }

    public function update(Request $request, Homework $homework): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:school_classes,id',
            'grade_level' => 'required|integer|min:1|max:13',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:active,closed,draft',
            'questions' => 'nullable|array',
        ]);

        // Recalculate total marks if questions changed
        if (isset($validated['questions'])) {
            $validated['total_marks'] = collect($validated['questions'])->sum('marks');
        }

        $homework->update($validated);

        return redirect()
            ->route('admin.management.homework.show', $homework->homework_id)
            ->with('success', 'Homework updated successfully');
    }

    public function generateQuestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,lesson_id',
            'num_mcq' => 'integer|min:0|max:10',
            'num_short' => 'integer|min:0|max:10',
            'num_descriptive' => 'integer|min:0|max:5',
        ]);

        $lesson = Lesson::find($validated['lesson_id']);

        try {
            $questions = $this->aiService->generateQuestions(
                $lesson->getAIFormatted(),
                $validated['num_mcq'] ?? 2,
                $validated['num_short'] ?? 2,
                $validated['num_descriptive'] ?? 1
            );

            return response()->json([
                'success' => true,
                'questions' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function scheduleWeekly(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:school_classes,id',
            'lesson_id' => 'required|exists:lessons,lesson_id',
        ]);

        try {
            $lesson = Lesson::find($validated['lesson_id']);
            $schoolClass = SchoolClass::find($validated['class_id']);
            $today = now();
            $weekNumber = $today->weekOfYear;

            // Try AI service first, fall back to local generation if it fails
            $assignments = [];
            try {
                $assignments = $this->aiService->scheduleWeeklyHomework(
                    $lesson->getAIFormatted(),
                    $validated['subject_id'],
                    $validated['class_id']
                );
            } catch (\Exception $e) {
                // AI service not available, generate locally
                \Log::warning('AI service unavailable for scheduling, using local generation: ' . $e->getMessage());

                // Generate basic assignments locally
                $assignments = $this->generateLocalAssignments($lesson, $validated['subject_id'], $today);
            }

            // Save assignments to database
            $createdHomework = [];
            foreach ($assignments as $index => $assignment) {
                // Due dates from TODAY: Assignment 1 = +3 days, Assignment 2 = +6 days
                $dueDate = $index === 0
                    ? $today->copy()->addDays(3)
                    : $today->copy()->addDays(6);

                $homework = Homework::create([
                    'subject_id' => $validated['subject_id'],
                    'class_id' => $validated['class_id'],
                    'lesson_id' => $validated['lesson_id'],
                    'assigned_by' => auth()->user()->teacher->teacher_id ?? 1,
                    'grade_level' => $schoolClass->grade_level ?? 6,
                    'title' => $assignment['title'] ?? "Week $weekNumber Assignment " . ($index + 1),
                    'description' => $assignment['description'] ?? "Auto-generated homework for {$lesson->title}",
                    'questions' => $assignment['questions'] ?? [],
                    'total_marks' => $assignment['total_marks'] ?? 10,
                    'assigned_date' => $today,
                    'due_date' => $dueDate,
                    'status' => 'active',
                    'week_number' => $weekNumber,
                    'academic_year' => Homework::getCurrentAcademicYear(),
                ]);

                // Auto-assign to all students in the class
                $students = \App\Models\Student::where('class_id', $validated['class_id'])
                    ->where('is_active', true)
                    ->get();

                foreach ($students as $student) {
                    HomeworkSubmission::create([
                        'homework_id' => $homework->homework_id,
                        'student_id' => $student->student_id,
                        'status' => 'assigned',
                        'answers' => [],
                    ]);
                }

                $createdHomework[] = $homework;
            }

            return response()->json([
                'success' => true,
                'message' => count($createdHomework) . ' homework assignments scheduled for this week',
                'assignments' => $createdHomework,
            ]);
        } catch (\Exception $e) {
            \Log::error('Schedule weekly homework error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate local assignments when AI service is unavailable
     */
    private function generateLocalAssignments(Lesson $lesson, int $subjectId, $weekStart): array
    {
        $subject = Subject::find($subjectId);
        $subjectName = $subject->subject_name ?? 'Subject';
        $weekNumber = $weekStart->weekOfYear;

        return [
            [
                'title' => "$subjectName - Week $weekNumber Assignment 1",
                'description' => "Based on: {$lesson->title}",
                'questions' => [
                    [
                        'question_type' => 'MCQ',
                        'question' => "Review question about {$lesson->title}",
                        'options' => [
                            'A' => 'Option A - First choice',
                            'B' => 'Option B - Second choice',
                            'C' => 'Option C - Third choice',
                            'D' => 'Option D - Fourth choice',
                        ],
                        'correct_answer' => 'A',
                        'marks' => 1,
                    ],
                    [
                        'question_type' => 'SHORT_ANSWER',
                        'question' => "Briefly explain a key concept from {$lesson->title}",
                        'model_answer' => '',
                        'marks' => 3,
                    ],
                ],
                'total_marks' => 4,
            ],
            [
                'title' => "$subjectName - Week $weekNumber Assignment 2",
                'description' => "Based on: {$lesson->title}",
                'questions' => [
                    [
                        'question_type' => 'MCQ',
                        'question' => "Application question about {$lesson->title}",
                        'options' => [
                            'A' => 'Option A - First choice',
                            'B' => 'Option B - Second choice',
                            'C' => 'Option C - Third choice',
                            'D' => 'Option D - Fourth choice',
                        ],
                        'correct_answer' => 'A',
                        'marks' => 1,
                    ],
                    [
                        'question_type' => 'DESCRIPTIVE',
                        'question' => "Analyze and discuss the main themes of {$lesson->title}",
                        'model_answer' => '',
                        'marks' => 5,
                    ],
                ],
                'total_marks' => 6,
            ],
        ];
    }

    public function assignToStudents(Request $request, Homework $homework): JsonResponse
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,student_id',
        ]);

        $assigned = 0;
        foreach ($validated['student_ids'] as $studentId) {
            HomeworkSubmission::firstOrCreate([
                'homework_id' => $homework->homework_id,
                'student_id' => $studentId,
            ], [
                'answers' => [],
                'status' => 'assigned',
            ]);
            $assigned++;
        }

        return response()->json([
            'success' => true,
            'assigned_count' => $assigned,
        ]);
    }
}
