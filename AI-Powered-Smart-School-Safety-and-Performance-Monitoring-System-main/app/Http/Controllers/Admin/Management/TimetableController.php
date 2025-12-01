<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class TimetableController extends Controller
{
    protected string $parentViewPath = 'admin.pages.management.timetables.';
    protected string $parentRoutePath = 'admin.management.timetables.';

    /**
     * Display a listing of timetables
     */
    public function index(Request $request): View
    {
        $classes = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $selectedClassId = $request->get('class_id');
        $selectedDay = $request->get('day', 1); // Default to Monday

        $timetables = collect();

        if ($selectedClassId) {
            $timetables = Timetable::with(['schoolClass', 'subject', 'teacher', 'timeSlot'])
                ->where('school_class_id', $selectedClassId)
                ->where('day_of_week', $selectedDay)
                ->where('status', 'active')
                ->orderBy('time_slot_id')
                ->get();
        }

        $timeSlots = TimeSlot::orderBy('start_time')
            ->get();

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday'
        ];

        // Get subjects and teachers for quick assignment
        $subjects = Subject::where('status', 'active')
            ->orderBy('subject_name')
            ->get();

        $teachers = Teacher::with('user')
            ->where('is_active', true)
            ->get();

        return view($this->parentViewPath . 'index', compact(
            'classes',
            'timetables',
            'timeSlots',
            'days',
            'selectedClassId',
            'selectedDay',
            'subjects',
            'teachers'
        ));
    }

    /**
     * Show the form for creating a new timetable entry
     */
    public function create(Request $request): View
    {
        $classes = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $subjects = Subject::where('status', 'active')
            ->orderBy('subject_name')
            ->get();

        $teachers = Teacher::where('is_active', true)
            ->with('user')
            ->get();

        $timeSlots = TimeSlot::orderBy('start_time')
            ->get();

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday'
        ];

        // Pre-select values if passed via request
        $preselected = [
            'class_id' => $request->get('class_id'),
            'day_of_week' => $request->get('day_of_week', 1),
            'time_slot_id' => $request->get('time_slot_id')
        ];

        return view($this->parentViewPath . 'form', compact(
            'classes',
            'subjects',
            'teachers',
            'timeSlots',
            'days',
            'preselected'
        ));
    }

    /**
     * Store a newly created timetable entry
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'day_of_week' => 'required|integer|between:1,5',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'notes' => 'nullable|string',
            'teacher_notes' => 'nullable|string',
            'alternative_teacher_id' => 'nullable|exists:teachers,teacher_id',
        ]);

        // Check for conflicts
        $conflict = $this->checkForConflicts($validated);
        if ($conflict) {
            return back()->withErrors(['conflict' => $conflict])->withInput();
        }

        $validated['is_active'] = true;

        Timetable::create($validated);

        return redirect()->route($this->parentRoutePath . 'index', [
            'class_id' => $validated['school_class_id'],
            'day' => $validated['day_of_week']
        ])->with('success', 'Timetable entry created successfully!');
    }

    /**
     * Display the specified timetable entry
     */
    public function show(Timetable $timetable): View
    {
        $timetable->load(['schoolClass', 'subject', 'teacher.user', 'timeSlot']);

        return view($this->parentViewPath . 'show', compact('timetable'));
    }

    /**
     * Show the form for editing the specified timetable entry
     */
    public function edit(Timetable $timetable): View
    {
        $classes = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $subjects = Subject::where('status', 'active')
            ->orderBy('subject_name')
            ->get();

        $teachers = Teacher::where('is_active', true)
            ->with('user')
            ->get();

        $timeSlots = TimeSlot::orderBy('start_time')
            ->get();

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday'
        ];

        return view($this->parentViewPath . 'form', compact(
            'timetable',
            'classes',
            'subjects',
            'teachers',
            'timeSlots',
            'days'
        ));
    }

    /**
     * Update the specified timetable entry
     */
    public function update(Request $request, Timetable $timetable): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'day_of_week' => 'required|integer|between:1,5',
            'academic_year' => 'required|string',
            'semester' => 'nullable|string',
            'room_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Check for conflicts (exclude current timetable)
        $conflict = $this->checkForConflicts($validated, $timetable->id);
        if ($conflict) {
            return back()->withErrors(['conflict' => $conflict])->withInput();
        }

        $timetable->update($validated);

        return redirect()->route($this->parentRoutePath . 'index', [
            'class_id' => $validated['school_class_id'],
            'day' => $validated['day_of_week']
        ])->with('success', 'Timetable entry updated successfully!');
    }

    /**
     * Remove the specified timetable entry
     */
    public function destroy(Timetable $timetable): RedirectResponse
    {
        $classId = $timetable->school_class_id;
        $day = $timetable->day_of_week;

        $timetable->delete();

        return redirect()->route($this->parentRoutePath . 'index', [
            'class_id' => $classId,
            'day' => $day
        ])->with('success', 'Timetable entry deleted successfully!');
    }

    /**
     * Get timetable for a specific class and day (AJAX)
     */
    public function getTimetable(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        $day = $request->get('day', 1);

        $timetables = Timetable::with(['subject', 'teacher.user', 'timeSlot'])
            ->where('school_class_id', $classId)
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->orderBy('time_slot_id')
            ->get();

        return response()->json([
            'success' => true,
            'timetables' => $timetables
        ]);
    }

    /**
     * Create additional time slot (Only allowed after 1:30 PM)
     */
    public function createTimeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i|after_or_equal:13:30',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_name' => 'required|string|max:255',
        ], [
            'start_time.after_or_equal' => 'Additional time slots can only be created after 1:30 PM. The preset schedule (8:00 AM - 1:30 PM) cannot be modified.',
        ]);

        // Ensure no overlap with existing slots
        $overlap = TimeSlot::where(function ($query) use ($validated) {
            $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                ->orWhere(function ($q) use ($validated) {
                    $q->where('start_time', '<=', $validated['start_time'])
                        ->where('end_time', '>=', $validated['end_time']);
                });
        })->exists();

        if ($overlap) {
            return back()->withErrors(['start_time' => 'This time slot overlaps with an existing slot.'])->withInput();
        }

        // Get next slot number
        $lastSlot = TimeSlot::orderBy('slot_number', 'desc')->first();
        $slotNumber = $lastSlot ? $lastSlot->slot_number + 1 : 1;

        // Create additional time slot
        TimeSlot::create([
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'label' => $validated['slot_name'],
            'is_break' => false,
            'slot_number' => $slotNumber,
            'slot_type' => 'additional',
            'day_of_week' => null,
            'period_number' => null,
            'description' => 'Additional slot created by admin after 1:30 PM',
            'status' => 'active'
        ]);

        return back()->with('success', 'Additional time slot created successfully after 1:30 PM!');
    }

    /**
     * Show bulk assignment form
     */
    public function bulkAssignForm(): View
    {
        $classes = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $subjects = Subject::where('status', 'active')
            ->orderBy('subject_name')
            ->get();

        $teachers = Teacher::where('is_active', true)
            ->with('user')
            ->get();

        $timeSlots = TimeSlot::where('slot_type', 'regular')
            ->orderBy('start_time')
            ->get();

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday'
        ];

        return view($this->parentViewPath . 'bulk-assign', compact(
            'classes',
            'subjects',
            'teachers',
            'timeSlots',
            'days'
        ));
    }

    /**
     * Process bulk assignment
     */
    public function bulkAssignStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'assignments' => 'required|array',
            'assignments.*.day_of_week' => 'required|integer|between:1,5',
            'assignments.*.time_slot_id' => 'required|exists:time_slots,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.teacher_id' => 'required|exists:teachers,teacher_id',
        ]);

        $createdCount = 0;
        $conflicts = [];

        foreach ($validated['assignments'] as $assignment) {
            $assignment['school_class_id'] = $validated['school_class_id'];
            $assignment['is_active'] = true;
            $assignment['effective_date'] = now()->format('Y-m-d');

            // Check for conflicts
            $conflict = $this->checkForConflicts($assignment);
            if ($conflict) {
                $timeSlot = TimeSlot::find($assignment['time_slot_id']);
                $conflicts[] = "Day {$assignment['day_of_week']}, {$timeSlot->label}: {$conflict}";
                continue;
            }

            Timetable::create($assignment);
            $createdCount++;
        }

        $message = "Successfully created {$createdCount} timetable entries.";
        if (!empty($conflicts)) {
            $message .= " Conflicts found: " . implode('; ', $conflicts);
        }

        return redirect()->route($this->parentRoutePath . 'index', [
            'class_id' => $validated['school_class_id']
        ])->with($conflicts ? 'warning' : 'success', $message);
    }

    /**
     * Quick assign subject and teacher to a time slot (AJAX)
     */
    public function quickAssign(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'day_of_week' => 'required|integer|between:1,5',
            'time_slot_id' => 'required|exists:time_slots,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Check for conflicts
        $conflict = $this->checkForConflicts($validated);
        if ($conflict) {
            return back()->withErrors(['conflict' => $conflict])->withInput();
        }

        $validated['is_active'] = true;
        $validated['effective_date'] = now()->format('Y-m-d');

        $timetable = Timetable::create($validated);

        // If it's an AJAX request, return JSON
        if ($request->expectsJson()) {
            $timetable->load(['subject', 'teacher.user', 'timeSlot']);
            return response()->json([
                'success' => true,
                'message' => 'Timetable entry created successfully!',
                'timetable' => $timetable
            ]);
        }

        // Regular form submission - redirect back with success
        return redirect()->route('admin.management.timetables.index', [
            'class_id' => $validated['school_class_id'],
            'day' => $validated['day_of_week']
        ])->with('success', 'Class added successfully to the timetable!');
    }

    /**
     * Bulk delete timetable entries
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'timetable_ids' => 'required|array',
            'timetable_ids.*' => 'exists:timetables,id'
        ]);

        $deletedCount = Timetable::whereIn('id', $validated['timetable_ids'])->delete();

        return back()->with('success', "Successfully deleted {$deletedCount} timetable entries.");
    }

    /**
     * Delete a time slot (Only additional slots after 1:30 PM)
     */
    public function deleteTimeSlot(TimeSlot $timeSlot): RedirectResponse
    {
        // Ensure only additional slots after 1:30 PM can be deleted
        if ($timeSlot->slot_type !== 'additional' || $timeSlot->start_time->format('H:i') < '13:30') {
            return back()->withErrors(['error' => 'Only additional time slots after 1:30 PM can be deleted. The preset schedule cannot be modified.']);
        }

        // Check if any timetable entries exist for this slot
        $timetableCount = Timetable::where('time_slot_id', $timeSlot->id)->count();
        if ($timetableCount > 0) {
            return back()->withErrors(['error' => "Cannot delete time slot. It has {$timetableCount} timetable entries. Please delete or reassign them first."]);
        }

        $slotName = $timeSlot->label;
        $timeSlot->delete();

        return back()->with('success', "Time slot '{$slotName}' deleted successfully!");
    }

    /**
     * Check for scheduling conflicts
     */
    private function checkForConflicts(array $data, int $excludeId = null): ?string
    {
        // Check if class is already scheduled at this time
        $classConflict = Timetable::where('school_class_id', $data['school_class_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($classConflict) {
            return 'This class is already scheduled for this time slot.';
        }

        // Check if teacher is already scheduled at this time
        $teacherConflict = Timetable::where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($teacherConflict) {
            return 'This teacher is already scheduled for this time slot.';
        }

        return null;
    }
}
