<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassTeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign class teachers to classes
        $classTeacherAssignments = [
            ['class_code' => 'CL-001', 'teacher_specialization' => 'Mathematics'],
            ['class_code' => 'CL-002', 'teacher_specialization' => 'English Language Arts'],
            ['class_code' => 'CL-003', 'teacher_specialization' => 'Social Studies'],
            ['class_code' => 'CL-004', 'teacher_specialization' => 'Library Science'],
            ['class_code' => 'CL-005', 'teacher_specialization' => 'Mathematics'],
            ['class_code' => 'CL-006', 'teacher_specialization' => 'English Language Arts'],
            ['class_code' => 'CL-007', 'teacher_specialization' => 'Social Studies'],
            ['class_code' => 'CL-008', 'teacher_specialization' => 'Library Science'],
            ['class_code' => 'CL-009', 'teacher_specialization' => 'Mathematics'],
            ['class_code' => 'CL-010', 'teacher_specialization' => 'English Language Arts'],
        ];

        foreach ($classTeacherAssignments as $assignment) {
            $class = SchoolClass::where('class_code', $assignment['class_code'])->first();
            $teacher = Teacher::where('specialization', $assignment['teacher_specialization'])
                ->where('is_active', true)
                ->first();

            if ($class && $teacher) {
                $class->update(['class_teacher_id' => $teacher->teacher_id]);
                $this->command->info("Assigned {$teacher->full_name} as class teacher for {$class->class_name}");
            }
        }

        // Assign subjects to classes based on grade level
        $this->assignSubjectsToClasses();
    }

    /**
     * Assign subjects to classes based on grade level
     */
    private function assignSubjectsToClasses(): void
    {
        // Grade 1 classes (basic subjects)
        $grade1Subjects = ['Mathematics', 'English Language Arts', 'Science', 'Physical Education'];
        $this->assignSubjectsToGrade('1', $grade1Subjects);

        // Grade 2 classes (add social studies)
        $grade2Subjects = ['Mathematics', 'English Language Arts', 'Science', 'Social Studies', 'Physical Education'];
        $this->assignSubjectsToGrade('2', $grade2Subjects);

        // Grade 3 classes (add arts)
        $grade3Subjects = ['Mathematics', 'English Language Arts', 'Science', 'Social Studies', 'Visual Arts', 'Music', 'Physical Education', 'Library Skills'];
        $this->assignSubjectsToGrade('3', $grade3Subjects);

        // Grade 4 classes (add technology)
        $grade4Subjects = ['Mathematics', 'English Language Arts', 'Science', 'Social Studies', 'Visual Arts', 'Music', 'Technology Education', 'Physical Education', 'Library Skills'];
        $this->assignSubjectsToGrade('4', $grade4Subjects);

        // Grade 5 classes (add Spanish)
        $grade5Subjects = ['Mathematics', 'English Language Arts', 'Science', 'Social Studies', 'Visual Arts', 'Music', 'Spanish Language', 'Technology Education', 'Physical Education', 'Library Skills'];
        $this->assignSubjectsToGrade('5', $grade5Subjects);
    }

    /**
     * Assign subjects to classes for a specific grade
     */
    private function assignSubjectsToGrade(string $gradeLevel, array $subjectNames): void
    {
        $classes = SchoolClass::where('grade_level', $gradeLevel)->get();

        foreach ($classes as $class) {
            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('subject_name', $subjectName)->first();
                if ($subject && ! $class->subjects()->where('subject_id', $subject->id)->exists()) {
                    $class->subjects()->attach($subject->id);
                }
            }
            $this->command->info("Assigned subjects to {$class->class_name}: " . implode(', ', $subjectNames));
        }
    }
}
