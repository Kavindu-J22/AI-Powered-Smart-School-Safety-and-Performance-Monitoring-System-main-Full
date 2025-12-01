<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PlaceholderController extends Controller
{
    /**
     * Generic index method for placeholder routes
     */
    public function index($module = null)
    {
        return view('admin.pages.placeholder.index', [
            'module' => ucfirst($module ?? 'Feature'),
            'message' => 'This feature is coming soon! Our development team is working on implementing this functionality.',
        ]);
    }

    // Academic Operations
    public function assignments()
    {
        return $this->index('assignments');
    }

    public function grades()
    {
        return $this->index('grades');
    }

    public function attendance()
    {
        return $this->index('attendance');
    }

    public function timetable()
    {
        return $this->index('timetable');
    }

    // Security
    public function visitors()
    {
        return $this->index('visitors');
    }

    public function incidents()
    {
        return $this->index('incidents');
    }

    // Reports
    public function studentReports()
    {
        return $this->index('student reports');
    }

    public function academicReports()
    {
        return $this->index('academic reports');
    }

    public function attendanceReports()
    {
        return $this->index('attendance reports');
    }

    // Communication
    public function announcements()
    {
        return $this->index('announcements');
    }

    public function messages()
    {
        return $this->index('messages');
    }

    // System Setup
    public function schoolInfo()
    {
        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting();
        return view('admin.pages.setup.school.index', compact('setting'));
    }

    public function gradeLevels()
    {
        return $this->index('grade levels');
    }

    public function academicYear()
    {
        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting();
        return view('admin.pages.setup.academic-year.index', compact('setting'));
    }

    public function roles()
    {
        return $this->index('roles & permissions');
    }

    public function users()
    {
        return $this->index('users');
    }
}
