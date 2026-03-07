<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\MonthlyReport;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Services\HomeworkAIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MonthlyReportController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.reports.';
    protected HomeworkAIService $aiService;

    public function __construct(HomeworkAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(): View
    {
        $reports = MonthlyReport::with('student')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        $classes = SchoolClass::orderBy('class_name')->get();

        return view($this->viewDirectory . 'index', compact('reports', 'classes'));
    }

    public function show(MonthlyReport $report): View
    {
        $report->load('student');

        return view($this->viewDirectory . 'show', compact('report'));
    }

    /**
     * Generate reports for all students in a class
     */
    public function generateForClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $students = Student::where('class_id', $validated['class_id'])->get();
        $generated = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                MonthlyReport::generateForStudent(
                    $student->student_id,
                    $validated['year'],
                    $validated['month']
                );
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Student {$student->student_id}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'generated' => $generated,
            'total_students' => $students->count(),
            'errors' => $errors,
        ]);
    }

    /**
     * Generate report for a single student
     */
    public function generateForStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            $report = MonthlyReport::generateForStudent(
                $validated['student_id'],
                $validated['year'],
                $validated['month']
            );

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send reports to parents
     */
    public function sendToParents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:monthly_reports,report_id',
        ]);

        $sent = 0;
        foreach ($validated['report_ids'] as $reportId) {
            $report = MonthlyReport::find($reportId);
            if ($report && $report->status === 'generated') {
                // In a real implementation, this would send email/notification
                $report->status = 'sent_to_parents';
                $report->sent_to_parents_at = now();
                $report->save();
                $sent++;
            }
        }

        return response()->json([
            'success' => true,
            'sent_count' => $sent,
        ]);
    }

    /**
     * Mark report as acknowledged by parent
     */
    public function markAcknowledged(MonthlyReport $report): JsonResponse
    {
        $report->status = 'acknowledged';
        $report->parent_acknowledged_at = now();
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Report marked as acknowledged',
        ]);
    }

    /**
     * Download a single student report as PDF.
     */
    public function downloadPdf(MonthlyReport $report): Response
    {
        $report->load('student');

        $pdf = Pdf::loadView('admin.pages.management.reports.pdf', compact('report'))
            ->setPaper('a4', 'portrait');

        $studentCode = $report->student->student_code ?? $report->student_id;
        $filename    = "report_{$studentCode}_{$report->year}_{$report->month}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download a combined PDF for all students in a class for a given month/year.
     *
     * Route: GET /reports/class/{classId}/{year}/{month}/download
     */
    public function downloadClassPdf(int $classId, int $year, int $month): Response
    {
        $class = SchoolClass::findOrFail($classId);

        // Fetch all generated reports for this class + period
        $reports = MonthlyReport::with('student')
            ->whereHas('student', fn($q) => $q->where('class_id', $classId))
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('overall_average', 'desc')
            ->get();

        $pdf = Pdf::loadView('admin.pages.management.reports.class-pdf', compact('reports', 'class', 'year', 'month'))
            ->setPaper('a4', 'portrait');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $filename  = "class_report_{$class->class_name}_{$monthName}_{$year}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Get report statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $reports = MonthlyReport::where('year', $year)
            ->where('month', $month)
            ->get();

        $stats = [
            'total_reports' => $reports->count(),
            'generated' => $reports->where('status', 'generated')->count(),
            'sent' => $reports->where('status', 'sent_to_parents')->count(),
            'acknowledged' => $reports->where('status', 'acknowledged')->count(),
            'average_score' => round($reports->avg('overall_average') ?? 0, 1),
            'grade_distribution' => $this->getGradeDistribution($reports),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    protected function getGradeDistribution($reports): array
    {
        $distribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];

        foreach ($reports as $report) {
            $grade = strtoupper(substr($report->overall_grade ?? 'F', 0, 1));
            if (isset($distribution[$grade])) {
                $distribution[$grade]++;
            }
        }

        return $distribution;
    }
}
