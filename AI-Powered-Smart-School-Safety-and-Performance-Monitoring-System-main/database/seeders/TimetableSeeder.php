<?php

namespace Database\Seeders;

use App\Models\Timetable;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating timetables for all classes...');

        $academicYear = '2024-2025';
        $workingDays = [1, 2, 3, 4, 5]; // Monday to Friday

        // Get all active classes, subjects, teachers, and regular time slots
        $classes = SchoolClass::where('status', 'active')->get();
        $subjects = Subject::where('status', 'active')->get();
        $teachers = Teacher::where('is_active', true)->get();
        $regularTimeSlots = TimeSlot::where('slot_type', 'regular')
            ->where('status', 'active')
            ->whereNotIn('slot_name', ['Short Break', 'Lunch Break'])
            ->orderBy('start_time')
            ->get();

        if ($classes->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty() || $regularTimeSlots->isEmpty()) {
            $this->command->error('Missing required data: classes, subjects, teachers, or time slots');
            return;
        }

        foreach ($classes as $class) {
            $this->command->info("Creating timetable for {$class->class_name}...");

            // Get subjects appropriate for this grade level
            $gradeSubjects = $this->getSubjectsForGrade($subjects, $class->grade_level);

            foreach ($workingDays as $day) {
                $this->createDayTimetable($class, $gradeSubjects, $teachers, $regularTimeSlots, $day, $academicYear);
            }
        }

        $this->command->info('Timetables created successfully!');
    }

    /**
     * Create timetable for a specific day
     */
    private function createDayTimetable($class, $subjects, $teachers, $timeSlots, $day, $academicYear): void
    {
        $assignedPeriods = 0;
        $maxPeriodsPerDay = min(6, $timeSlots->count()); // Max 6 periods per day

        // Shuffle subjects for variety
        $shuffledSubjects = $subjects->shuffle();
        $subjectIndex = 0;

        foreach ($timeSlots as $timeSlot) {
            if ($assignedPeriods >= $maxPeriodsPerDay) {
                break;
            }

            // Skip if this time slot is already assigned to this class on this day
            $existingEntry = Timetable::where('school_class_id', $class->id)
                ->where('day_of_week', $day)
                ->where('time_slot_id', $timeSlot->id)
                ->first();

            if ($existingEntry) {
                continue;
            }

            // Get subject for this period
            $subject = $shuffledSubjects[$subjectIndex % $shuffledSubjects->count()];

            // Find a suitable teacher for this subject
            $teacher = $this->findAvailableTeacher($teachers, $subject, $timeSlot, $day);

            if ($teacher) {
                Timetable::create([
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->teacher_id,
                    'time_slot_id' => $timeSlot->id,
                    'day_of_week' => $day,
                    'academic_year' => $academicYear,
                    'semester' => '1st Semester',
                    'room_number' => $class->room_number,
                    'notes' => "Regular class period for {$subject->subject_name}",
                    'status' => 'active',
                ]);

                $assignedPeriods++;
                $subjectIndex++;
            }
        }
    }

    /**
     * Get subjects appropriate for a specific grade level
     */
    private function getSubjectsForGrade($allSubjects, $gradeLevel): \Illuminate\Database\Eloquent\Collection
    {
        // For simplicity, we'll assign core subjects to all grades
        // In a real system, you might have grade-specific subjects
        $coreSubjects = ['Mathematics', 'English', 'Science', 'Social Studies', 'Physical Education'];

        return $allSubjects->filter(function ($subject) use ($coreSubjects) {
            return in_array($subject->subject_name, $coreSubjects) ||
                str_contains(strtolower($subject->subject_name), 'language') ||
                str_contains(strtolower($subject->subject_name), 'art') ||
                str_contains(strtolower($subject->subject_name), 'music');
        })->take(8); // Limit to 8 subjects per grade
    }

    /**
     * Find an available teacher for a subject at a specific time
     */
    private function findAvailableTeacher($teachers, $subject, $timeSlot, $day)
    {
        // First, try to find a teacher who teaches this subject
        $subjectTeachers = $teachers->filter(function ($teacher) use ($subject) {
            // This would ideally check a teacher-subject relationship
            // For now, we'll use a simple approach
            return true; // All teachers can teach any subject for demo purposes
        });

        foreach ($subjectTeachers as $teacher) {
            // Check if teacher is already scheduled at this time
            $conflictingSchedule = Timetable::where('teacher_id', $teacher->teacher_id)
                ->where('day_of_week', $day)
                ->where('time_slot_id', $timeSlot->id)
                ->first();

            if (!$conflictingSchedule) {
                return $teacher;
            }
        }

        // If no specific teacher found, return any available teacher
        foreach ($teachers as $teacher) {
            $conflictingSchedule = Timetable::where('teacher_id', $teacher->teacher_id)
                ->where('day_of_week', $day)
                ->where('time_slot_id', $timeSlot->id)
                ->first();

            if (!$conflictingSchedule) {
                return $teacher;
            }
        }

        return null; // No available teacher found
    }
}
