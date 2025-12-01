<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TimeSlot;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableViewerController extends Controller
{
    /**
     * Display timetable viewer
     */
    public function index(Request $request): View
    {
        $classes = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $selectedClassId = $request->get('class_id');
        $selectedWeek = $request->get('week', 'current');

        $timetable = [];
        $timeSlots = [];

        if ($selectedClassId) {
            // Get regular time slots (preset schedule only)
            $timeSlots = TimeSlot::where('slot_type', 'regular')
                ->orderBy('start_time')
                ->get();

            // Get break time
            $breakSlot = TimeSlot::where('slot_type', 'break')->first();

            // Build timetable structure
            $days = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday'
            ];

            foreach ($days as $dayNum => $dayName) {
                $timetable[$dayNum] = [
                    'name' => $dayName,
                    'periods' => []
                ];

                foreach ($timeSlots as $slot) {
                    $entry = Timetable::with(['subject', 'teacher.user'])
                        ->where('school_class_id', $selectedClassId)
                        ->where('day_of_week', $dayNum)
                        ->where('time_slot_id', $slot->id)
                        ->where('is_active', true)
                        ->first();

                    $timetable[$dayNum]['periods'][] = [
                        'time_slot' => $slot,
                        'entry' => $entry,
                        'is_break' => false
                    ];

                    // Add break after period 4
                    if ($slot->period_number == 4 && $breakSlot) {
                        $timetable[$dayNum]['periods'][] = [
                            'time_slot' => $breakSlot,
                            'entry' => null,
                            'is_break' => true
                        ];
                    }
                }
            }
        }

        return view('admin.pages.timetable-viewer.index', compact(
            'classes',
            'timetable',
            'timeSlots',
            'selectedClassId',
            'selectedWeek'
        ));
    }

    /**
     * Export timetable as PDF
     */
    public function exportPdf(Request $request)
    {
        // Implementation for PDF export can be added later
        return back()->with('info', 'PDF export feature coming soon!');
    }

    /**
     * Get timetable data for AJAX requests
     */
    public function getTimetableData(Request $request)
    {
        $classId = $request->get('class_id');

        if (!$classId) {
            return response()->json(['error' => 'Class ID required'], 400);
        }

        $timeSlots = TimeSlot::where('slot_type', 'regular')
            ->orderBy('start_time')
            ->get();

        $breakSlot = TimeSlot::where('slot_type', 'break')->first();

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
        $timetableData = [];

        foreach ($days as $dayNum => $dayName) {
            $periods = [];

            foreach ($timeSlots as $slot) {
                $entry = Timetable::with(['subject', 'teacher.user'])
                    ->where('school_class_id', $classId)
                    ->where('day_of_week', $dayNum)
                    ->where('time_slot_id', $slot->id)
                    ->where('is_active', true)
                    ->first();

                $periods[] = [
                    'period' => $slot->period_number,
                    'time' => $slot->start_time->format('H:i') . ' - ' . $slot->end_time->format('H:i'),
                    'subject' => $entry ? $entry->subject->subject_name : '',
                    'teacher' => $entry ? $entry->teacher->user->name : '',
                    'is_break' => false
                ];

                // Add break after period 4
                if ($slot->period_number == 4 && $breakSlot) {
                    $periods[] = [
                        'period' => 'Break',
                        'time' => $breakSlot->start_time->format('H:i') . ' - ' . $breakSlot->end_time->format('H:i'),
                        'subject' => 'Break Time',
                        'teacher' => '',
                        'is_break' => true
                    ];
                }
            }

            $timetableData[$dayName] = $periods;
        }

        return response()->json([
            'success' => true,
            'timetable' => $timetableData
        ]);
    }
}
