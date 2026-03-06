<?php

namespace Database\Seeders;

use App\Models\Mark;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StudentMarksAndAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::with('schoolClass', 'subjects')->where('is_active', true)->limit(50)->get();
        $academicYear = Carbon::now()->year;
        $currentTerm = $this->getCurrentTerm();

        foreach ($students as $student) {
            $subjects = $student->subjects;

            if ($subjects->isEmpty()) {
                continue;
            }

            foreach ($subjects as $subject) {
                // Create marks for each subject
                for ($i = 1; $i <= 3; $i++) {
                    $marks = rand(45, 95);
                    $totalMarks = 100;
                    $percentage = ($marks / $totalMarks) * 100;

                    try {
                        Mark::create([
                            'student_id' => $student->student_id,
                            'subject_id' => $subject->subject_id,
                            'grade_level' => $student->grade_level,
                            'academic_year' => $academicYear,
                            'term' => $i,
                            'marks' => $marks,
                            'total_marks' => $totalMarks,
                            'percentage' => $percentage,
                            'grade' => $this->calculateGrade($percentage),
                            'remarks' => $this->generateRemarks($percentage),
                            'entered_by' => 1, // Admin user
                        ]);
                    } catch (\Exception $e) {
                        // Skip duplicate entries
                        continue;
                    }
                }
            }

            // Create attendance records for the last 30 school days
            $this->createAttendanceRecords($student, 30);
        }

        $this->command->info('Student marks and attendance seeded successfully!');
    }

    /**
     * Create attendance records for a student
     */
    private function createAttendanceRecords(Student $student, int $days): void
    {
        $startDate = Carbon::now()->subDays($days);

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $status = random_int(0, 100) > 10 ? 'present' : 'absent'; // 90% present, 10% absent

            try {
                Attendance::create([
                    'student_id' => $student->student_id,
                    'attendance_date' => $date->format('Y-m-d'),
                    'check_in_time' => $date->copy()->setHour(8)->setMinute(random_int(0, 30))->format('H:i:s'),
                    'check_out_time' => $date->copy()->setHour(15)->setMinute(random_int(0, 59))->format('H:i:s'),
                    'status' => $status,
                    'nfc_tag_id' => 'NFC_' . $student->student_code,
                    'check_in_location' => 'Main Gate',
                    'check_out_location' => 'Main Gate',
                    'device_id' => 'DEVICE_001',
                    'temperature' => 36.5 + (random_int(-5, 5) / 10),
                    'is_auto_recorded' => true,
                    'recorded_by' => 1,
                ]);
            } catch (\Exception $e) {
                // Skip duplicate entries
                continue;
            }
        }
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        return 'F';
    }

    /**
     * Generate remarks based on percentage
     */
    private function generateRemarks(float $percentage): string
    {
        if ($percentage >= 90) return 'Excellent performance';
        if ($percentage >= 80) return 'Very good performance';
        if ($percentage >= 70) return 'Good performance';
        if ($percentage >= 60) return 'Satisfactory performance';
        if ($percentage >= 50) return 'Needs improvement';
        return 'Poor performance';
    }

    /**
     * Get current academic term
     */
    private function getCurrentTerm(): int
    {
        $month = Carbon::now()->month;

        if ($month >= 1 && $month <= 4) return 1;
        if ($month >= 5 && $month <= 8) return 2;
        return 3;
    }
}
