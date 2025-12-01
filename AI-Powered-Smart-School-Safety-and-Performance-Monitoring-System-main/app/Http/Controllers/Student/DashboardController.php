<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\HomeworkSubmission;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected string $directory = 'student.pages.dashboard.';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            abort(403, 'No student profile found for this user.');
        }

        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStats($student);

        // Get recent marks
        $recentMarks = Mark::where('student_id', $student->student_id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending homework
        $pendingHomework = HomeworkSubmission::where('student_id', $student->student_id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['homework.subject'])
            ->whereHas('homework')
            ->get()
            ->sortBy(fn($s) => $s->homework->due_date)
            ->take(5);

        // Get today's timetable
        // Note: day_of_week in model uses 1=Monday to 7=Sunday, Carbon uses 0=Sunday to 6=Saturday
        $dayOfWeek = Carbon::now()->dayOfWeekIso; // 1=Monday to 7=Sunday
        $todayTimetable = Timetable::where('school_class_id', $student->class_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'active')
            ->with(['subject', 'teacher', 'timeSlot'])
            ->get()
            ->sortBy(fn($t) => $t->timeSlot?->start_time);

        // Get overall performance
        $overallPerformance = $this->calculateOverallPerformance($student);

        return view($this->directory . 'index', compact(
            'student',
            'attendanceStats',
            'recentMarks',
            'pendingHomework',
            'todayTimetable',
            'overallPerformance'
        ));
    }

    private function getAttendanceStats(Student $student): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyAttendance = Attendance::where('student_id', $student->student_id)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalDays = $monthlyAttendance->count();
        $presentDays = $monthlyAttendance->where('status', 'present')->count() + $monthlyAttendance->where('status', 'late')->count();
        $absentDays = $monthlyAttendance->where('status', 'absent')->count();
        $lateDays = $monthlyAttendance->where('status', 'late')->count();

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'attendance_percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
        ];
    }

    private function calculateOverallPerformance(Student $student): array
    {
        $marks = Mark::where('student_id', $student->student_id)->get();

        if ($marks->isEmpty()) {
            return [
                'average' => 0,
                'highest' => 0,
                'lowest' => 0,
                'total_exams' => 0,
            ];
        }

        return [
            'average' => round($marks->avg('marks'), 1),
            'highest' => $marks->max('marks'),
            'lowest' => $marks->min('marks'),
            'total_exams' => $marks->count(),
        ];
    }
}
