<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentPerformance;
use App\Models\HomeworkSubmission;
use App\Models\MonthlyReport;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\HomeworkAIService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class PerformanceController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.performance.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function dashboard(): View
    {
        $stats = [
            'total_students' => Student::active()->count(),
            'average_score' => HomeworkSubmission::graded()->avg('percentage') ?? 0,
            'pass_rate' => $this->calculatePassRate(),
            'reports_pending' => MonthlyReport::pendingSend()->count(),
        ];

        $topPerformers = $this->getTopPerformers(5);
        $needsAttention = $this->getStudentsNeedingAttention(5);

        return view($this->viewDirectory . 'dashboard', compact('stats', 'topPerformers', 'needsAttention'));
    }

    public function studentPerformance(int $studentId): View
    {
        $student = Student::with(['schoolClass', 'subjects'])->findOrFail($studentId);
        
        $performance = StudentPerformance::where('student_id', $studentId)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('subject.subject_name');

        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->with('homework.subject')
            ->where('status', 'graded')
            ->orderBy('graded_at', 'desc')
            ->take(10)
            ->get();

        $stats = $this->calculateStudentStats($studentId);

        return view($this->viewDirectory . 'student', compact('student', 'performance', 'submissions', 'stats'));
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
        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->where('status', 'graded')
            ->get();

        return [
            'total_submissions' => $submissions->count(),
            'average_score' => round($submissions->avg('percentage') ?? 0, 1),
            'highest_score' => $submissions->max('percentage') ?? 0,
            'lowest_score' => $submissions->min('percentage') ?? 0,
            'on_time_rate' => $submissions->count() > 0 
                ? round($submissions->where('is_late', false)->count() / $submissions->count() * 100, 1) 
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

