<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\SecurityStaff;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected string $directory = 'admin.pages.dashboard.';

    public function index(): View
    {
        // Get school statistics with optimized queries
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::active()->count(),
            'total_teachers' => Teacher::count(),
            'active_teachers' => Teacher::where('is_active', true)->count(),
            'total_security_staff' => SecurityStaff::count(),
            'active_security_staff' => SecurityStaff::where('is_active', true)->count(),
            'total_parents' => ParentModel::count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
        ];

        // Get recent enrollments (last 30 days)
        $recent_enrollments = Student::where('enrollment_date', '>=', Carbon::now()->subDays(30))->count();

        // Get grade distribution
        $grade_distribution = Student::select('grade_level', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // Get recent activities (last 10 students)
        $recent_students = Student::with(['user', 'schoolClass'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get classes with student counts
        $classes_with_counts = SchoolClass::withCount(['students' => function ($query) {
            $query->where('is_active', true);
        }])->orderBy('grade_level')->get();

        // Get settings for theme and school configuration
        $settings = Setting::first() ?? new Setting;

        return view($this->directory . 'index', compact(
            'stats',
            'recent_enrollments',
            'grade_distribution',
            'recent_students',
            'classes_with_counts',
            'settings'
        ));
    }
}
