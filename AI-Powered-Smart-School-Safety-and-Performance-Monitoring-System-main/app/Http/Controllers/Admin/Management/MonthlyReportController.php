<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\MonthlyReport;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Services\HomeworkAIService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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
        
        return view($this->viewDirectory . 'index', compact('reports'));
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
     * Download report as PDF
     */
    public function downloadPdf(MonthlyReport $report)
    {
        $report->load('student');
        
        // Generate PDF content
        $pdf = $this->generatePdfContent($report);
        
        $filename = "report_{$report->student->student_code}_{$report->year}_{$report->month}.pdf";
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
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

    protected function generatePdfContent(MonthlyReport $report): string
    {
        // Basic PDF generation - in production, use a proper PDF library
        return "PDF content for {$report->student->first_name} {$report->student->last_name}";
    }
}

