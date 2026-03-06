<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Services\SeatingArrangementService;
use Illuminate\Http\Request;

class SeatingArrangementController extends Controller
{
    protected SeatingArrangementService $service;

    public function __construct(SeatingArrangementService $service)
    {
        $this->service = $service;
    }

    /**
     * Main page: list all classes with seating status
     * GET /admin/management/seating
     */
    public function index()
    {
        $classes = $this->service->getClassesWithStudents();
        return view('admin.pages.management.seating.index', compact('classes'));
    }

    /**
     * Show / generate seating for one class
     * GET /admin/management/seating/{grade}/{section}
     */
    public function show(string $grade, string $section)
    {
        $saved    = $this->service->getArrangement((int)$grade, strtoupper($section));
        $students = \App\Models\Student::where('grade_level', $grade)
            ->where('section', strtoupper($section))
            ->where('is_active', true)
            ->get();

        return view('admin.pages.management.seating.show', compact('grade', 'section', 'saved', 'students'));
    }

    /**
     * Generate (or regenerate) seating arrangement via AJAX
     * POST /admin/management/seating/generate
     */
    public function generate(Request $request)
    {
        $request->validate([
            'grade'         => 'required|integer|min:1|max:13',
            'section'       => 'required|string|max:5',
            'seats_per_row' => 'nullable|integer|min:2|max:10',
            'total_rows'    => 'nullable|integer|min:2|max:15',
        ]);

        $grade   = (int) $request->grade;
        $section = strtoupper($request->section);

        $arrangement = $this->service->generateForClass(
            $grade,
            $section,
            $request->seats_per_row ?? 5,
            $request->total_rows    ?? 6
        );

        if (isset($arrangement['error'])) {
            return response()->json(['success' => false, 'message' => $arrangement['error']], 422);
        }

        // Persist the arrangement
        $studentsData = \App\Models\Student::where('grade_level', $grade)
            ->where('section', $section)
            ->where('is_active', true)
            ->get(['student_id', 'first_name', 'last_name', 'grade_level', 'section'])
            ->toArray();

        $this->service->saveArrangement($grade, $section, $arrangement, $studentsData, auth()->id());

        return response()->json([
            'success'     => true,
            'arrangement' => $arrangement,
            'message'     => "Seating for Grade {$grade}-{$section} generated successfully",
        ]);
    }

    /**
     * Get current saved arrangement for a class (AJAX)
     * GET /admin/management/seating/{grade}/{section}/data
     */
    public function data(string $grade, string $section)
    {
        $saved = $this->service->getArrangement((int)$grade, strtoupper($section));

        if (!$saved) {
            return response()->json(['success' => false, 'message' => 'No arrangement found'], 404);
        }

        return response()->json(['success' => true, 'data' => $saved]);
    }
}
