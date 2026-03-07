<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentPerformance;
use App\Models\HomeworkSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\HomeworkAIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PerformanceController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.performance.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function dashboard(Request $request): View
    {
        $gradeLevel = $request->input('grade_level');
        $subjectId  = $request->input('subject_id');

        // Filter options
        $grades   = Student::active()->distinct()->orderBy('grade_level')->pluck('grade_level');
        $subjects = Subject::orderBy('subject_name')->get();

        // Students query with optional grade filter
        $query = Student::active()->with('schoolClass')->orderBy('grade_level')->orderBy('last_name');
        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }
        $students = $query->get();

        // Build per-student performance rows
        $studentPerformanceData = $students->map(function ($student) use ($subjectId) {
            $subQuery = HomeworkSubmission::where('student_id', $student->student_id)
                ->whereIn('status', ['submitted', 'graded']);
            if ($subjectId) {
                $subQuery->whereHas('homework', fn($q) => $q->where('subject_id', $subjectId));
            }
            $submissions = $subQuery->get();
            $graded      = $submissions->where('status', 'graded');
            $avg         = $graded->isNotEmpty() ? round($graded->avg('percentage'), 1) : null;
            return [
                'student'         => $student,
                'total_attempted' => $submissions->count(),
                'average_score'   => $avg,
                'grade'           => $avg !== null ? HomeworkSubmission::calculateGrade($avg) : '—',
            ];
        });

        // If subject filter, exclude students with no attempts in that subject
        if ($subjectId) {
            $studentPerformanceData = $studentPerformanceData
                ->filter(fn($row) => $row['total_attempted'] > 0)
                ->values();
        }

        // Aggregate stats
        $scores    = $studentPerformanceData->pluck('average_score')->filter();
        $totalSubs = $studentPerformanceData->sum('total_attempted');
        $studWithData = $studentPerformanceData->filter(fn($r) => $r['total_attempted'] > 0)->count();
        $passCount = $studentPerformanceData->filter(
            fn($r) => $r['average_score'] !== null && $r['average_score'] >= 40
        )->count();

        $stats = [
            'total_students'    => $studentPerformanceData->count(),
            'average_score'     => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
            'pass_rate'         => $studWithData > 0 ? round(($passCount / $studWithData) * 100, 1) : 0,
            'submissions_total' => $totalSubs,
        ];

        // Subject Performance bar chart — average score per subject (filtered)
        $subjectChartData = [];
        foreach ($subjects as $subject) {
            $subQ = HomeworkSubmission::where('status', 'graded')
                ->whereHas('homework', fn($q) => $q->where('subject_id', $subject->id));
            if ($gradeLevel) {
                $subQ->whereHas('student', fn($q) => $q->where('grade_level', $gradeLevel));
            }
            $avg = $subQ->avg('percentage');
            if ($avg !== null) {
                $subjectChartData[$subject->subject_name] = round($avg, 1);
            }
        }

        // Grade Distribution pie chart — count of students per letter grade
        $gradeDistribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        foreach ($studentPerformanceData as $row) {
            $g = $row['grade'];
            if (isset($gradeDistribution[$g])) {
                $gradeDistribution[$g]++;
            }
        }

        return view($this->viewDirectory . 'dashboard', compact(
            'stats',
            'studentPerformanceData',
            'grades',
            'subjects',
            'gradeLevel',
            'subjectId',
            'subjectChartData',
            'gradeDistribution'
        ));
    }

    public function downloadStudentPdf(int $studentId): Response
    {
        $student = Student::with('schoolClass')->findOrFail($studentId);

        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('homework.subject')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $graded = $submissions->where('status', 'graded');
        $avg    = $graded->isNotEmpty() ? round($graded->avg('percentage'), 1) : null;

        $subjectAverages = $graded
            ->filter(fn($s) => $s->homework?->subject)
            ->groupBy(fn($s) => $s->homework->subject->subject_name)
            ->map(fn($group) => round($group->avg('percentage'), 1));

        $stats = [
            'total_submissions' => $submissions->count(),
            'average_score'     => round($graded->avg('percentage') ?? 0, 1),
            'highest_score'     => $graded->max('percentage') ?? 0,
            'on_time_rate'      => $submissions->count() > 0
                ? round($submissions->where('is_late', false)->count() / $submissions->count() * 100, 1) : 0,
        ];

        $pdf = Pdf::loadView(
            'admin.pages.management.performance.pdf-student',
            compact('student', 'submissions', 'subjectAverages', 'stats', 'avg')
        )->setPaper('A4', 'portrait');

        return $pdf->download("student-report-{$student->student_code}-" . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadAllPdf(Request $request): Response
    {
        $gradeLevel = $request->input('grade_level');
        $subjectId  = $request->input('subject_id');

        $query = Student::active()->with('schoolClass')->orderBy('grade_level')->orderBy('last_name');
        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }
        $students = $query->get();

        $studentPerformanceData = $students->map(function ($student) use ($subjectId) {
            $subQuery = HomeworkSubmission::where('student_id', $student->student_id)
                ->whereIn('status', ['submitted', 'graded']);
            if ($subjectId) {
                $subQuery->whereHas('homework', fn($q) => $q->where('subject_id', $subjectId));
            }
            $submissions = $subQuery->with('homework.subject')->get();
            $graded      = $submissions->where('status', 'graded');
            $avg         = $graded->isNotEmpty() ? round($graded->avg('percentage'), 1) : null;
            return [
                'student'         => $student,
                'submissions'     => $submissions,
                'total_attempted' => $submissions->count(),
                'average_score'   => $avg,
                'grade'           => $avg !== null ? HomeworkSubmission::calculateGrade($avg) : '—',
            ];
        });

        if ($subjectId) {
            $studentPerformanceData = $studentPerformanceData
                ->filter(fn($row) => $row['total_attempted'] > 0)
                ->values();
        }

        $filterLabel = $gradeLevel ? "Grade {$gradeLevel}" : 'All Grades';
        if ($subjectId && $sub = Subject::find($subjectId)) {
            $filterLabel .= ' – ' . $sub->subject_name;
        }

        $pdf = Pdf::loadView(
            'admin.pages.management.performance.pdf-bulk',
            compact('studentPerformanceData', 'filterLabel')
        )->setPaper('A4', 'landscape');

        return $pdf->download('performance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function studentPerformance(int $studentId): View
    {
        $student = Student::with(['schoolClass', 'subjects'])->findOrFail($studentId);

        $performance = StudentPerformance::where('student_id', $studentId)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('subject.subject_name');

        // All attempted submissions (submitted + graded) so the teacher can
        // see every assignment the student has touched, with marks and grade.
        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->with(['homework.subject'])
            ->whereIn('status', ['submitted', 'graded'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        // Subject-level aggregate: average marks per subject for the chart.
        $subjectAverages = $submissions
            ->filter(fn($s) => $s->status === 'graded' && $s->homework?->subject)
            ->groupBy(fn($s) => $s->homework->subject->subject_name ?? 'Unknown')
            ->map(fn($group) => round($group->avg('percentage'), 1));

        $stats = $this->calculateStudentStats($studentId);

        return view(
            $this->viewDirectory . 'student',
            compact('student', 'performance', 'submissions', 'subjectAverages', 'stats')
        );
    }

    public function classPerformance(int $classId): View
    {
        $class = SchoolClass::findOrFail($classId);
        $students = Student::where('class_id', $classId)->get();

        $classStats = [
            'total_students' => $students->count(),
            'average_score' => 0,
            'pass_rate' => 0,
            'subject_averages' => [],
        ];

        $studentIds = $students->pluck('student_id');
        $submissions = HomeworkSubmission::whereIn('student_id', $studentIds)
            ->where('status', 'graded')
            ->with('homework.subject')
            ->get();

        if ($submissions->isNotEmpty()) {
            $classStats['average_score'] = round($submissions->avg('percentage'), 1);
            $classStats['pass_rate'] = round($submissions->where('percentage', '>=', 40)->count() / $submissions->count() * 100, 1);

            foreach ($submissions->groupBy('homework.subject.subject_name') as $subject => $subs) {
                $classStats['subject_averages'][$subject] = round($subs->avg('percentage'), 1);
            }
        }

        $studentPerformance = $this->getClassStudentPerformance($studentIds);

        return view($this->viewDirectory . 'class', compact('class', 'classStats', 'studentPerformance'));
    }

    public function trends(Request $request): JsonResponse
    {
        $studentId = $request->input('student_id');
        $classId = $request->input('class_id');
        $period = $request->input('period', 'month');

        $data = [];

        if ($studentId) {
            $data = $this->getStudentTrends($studentId, $period);
        } elseif ($classId) {
            $data = $this->getClassTrends($classId, $period);
        }

        return response()->json([
            'success' => true,
            'trends' => $data,
        ]);
    }

    public function heatmap(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');

        $data = $this->generateHeatmapData($classId, $subjectId);

        return response()->json([
            'success' => true,
            'heatmap' => $data,
        ]);
    }

    public function weakAreas(Request $request): JsonResponse
    {
        $studentId = $request->input('student_id');
        $classId = $request->input('class_id');

        $weakAreas = [];

        if ($studentId) {
            $weakAreas = $this->identifyStudentWeakAreas($studentId);
        } elseif ($classId) {
            $weakAreas = $this->identifyClassWeakAreas($classId);
        }

        return response()->json([
            'success' => true,
            'weak_areas' => $weakAreas,
        ]);
    }

    protected function calculatePassRate(): float
    {
        $total = HomeworkSubmission::graded()->count();
        if ($total === 0) return 0;

        $passed = HomeworkSubmission::graded()->where('percentage', '>=', 40)->count();
        return round(($passed / $total) * 100, 1);
    }

    protected function getTopPerformers(int $limit): array
    {
        return Student::select('students.*')
            ->join('homework_submissions', 'students.student_id', '=', 'homework_submissions.student_id')
            ->where('homework_submissions.status', 'graded')
            ->groupBy('students.student_id')
            ->orderByRaw('AVG(homework_submissions.percentage) DESC')
            ->take($limit)
            ->get()
            ->map(function ($student) {
                $avg = HomeworkSubmission::where('student_id', $student->student_id)
                    ->where('status', 'graded')
                    ->avg('percentage');
                return [
                    'student' => $student,
                    'average' => round($avg, 1),
                ];
            })
            ->toArray();
    }

    protected function getStudentsNeedingAttention(int $limit): array
    {
        return Student::select('students.*')
            ->join('homework_submissions', 'students.student_id', '=', 'homework_submissions.student_id')
            ->where('homework_submissions.status', 'graded')
            ->groupBy('students.student_id')
            ->havingRaw('AVG(homework_submissions.percentage) < 50')
            ->orderByRaw('AVG(homework_submissions.percentage) ASC')
            ->take($limit)
            ->get()
            ->map(function ($student) {
                $avg = HomeworkSubmission::where('student_id', $student->student_id)
                    ->where('status', 'graded')
                    ->avg('percentage');
                return [
                    'student' => $student,
                    'average' => round($avg, 1),
                ];
            })
            ->toArray();
    }

    protected function calculateStudentStats(int $studentId): array
    {
        // All attempted assignments (includes submitted but not yet graded)
        $attempted = HomeworkSubmission::where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'graded'])
            ->get();

        // Only graded ones have percentage/marks
        $graded = $attempted->filter(fn($s) => $s->status === 'graded');

        return [
            'total_submissions' => $attempted->count(),
            'average_score'     => round($graded->avg('percentage') ?? 0, 1),
            'highest_score'     => $graded->max('percentage') ?? 0,
            'lowest_score'      => $graded->min('percentage') ?? 0,
            'on_time_rate'      => $attempted->count() > 0
                ? round($attempted->where('is_late', false)->count() / $attempted->count() * 100, 1)
                : 0,
        ];
    }

    protected function getClassStudentPerformance($studentIds): array
    {
        return Student::whereIn('student_id', $studentIds)
            ->get()
            ->map(function ($student) {
                $avg = HomeworkSubmission::where('student_id', $student->student_id)
                    ->where('status', 'graded')
                    ->avg('percentage');
                return [
                    'student' => $student,
                    'average' => round($avg ?? 0, 1),
                    'grade' => HomeworkSubmission::calculateGrade($avg ?? 0),
                ];
            })
            ->sortByDesc('average')
            ->values()
            ->toArray();
    }

    protected function getStudentTrends(int $studentId, string $period): array
    {
        // Implementation for student trends
        return [];
    }

    protected function getClassTrends(int $classId, string $period): array
    {
        // Implementation for class trends
        return [];
    }

    protected function generateHeatmapData($classId, $subjectId): array
    {
        // Implementation for heatmap data
        return [];
    }

    protected function identifyStudentWeakAreas(int $studentId): array
    {
        // Implementation for identifying weak areas
        return [];
    }

    protected function identifyClassWeakAreas(int $classId): array
    {
        // Implementation for identifying class weak areas
        return [];
    }
}
