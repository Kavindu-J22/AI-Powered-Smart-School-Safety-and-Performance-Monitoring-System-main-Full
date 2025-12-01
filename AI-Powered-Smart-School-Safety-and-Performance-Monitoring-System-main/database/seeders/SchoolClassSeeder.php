<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating school classes for grades 1-13 with sections A, B, C...');

        $sections = ['A', 'B', 'C'];
        $academicYear = '2024-2025';
        $classCounter = 1;

        // Create classes for grades 1-13
        for ($grade = 1; $grade <= 13; $grade++) {
            foreach ($sections as $section) {
                $classCode = 'CL-' . str_pad($classCounter, 3, '0', STR_PAD_LEFT);
                $className = "Grade {$grade}{$section}";

                // Determine capacity based on grade level
                $capacity = $this->getCapacityByGrade($grade);

                // Determine room number
                $roomNumber = $this->getRoomNumber($grade, $section);

                // Create class description
                $description = $this->getClassDescription($grade, $section);

                $classData = [
                    'class_code' => $classCode,
                    'class_name' => $className,
                    'grade_level' => $grade,
                    'academic_year' => $academicYear,
                    'section' => $section,
                    'room_number' => $roomNumber,
                    'capacity' => $capacity,
                    'description' => $description,
                    'status' => 'active',
                ];

                $schoolClass = SchoolClass::create($classData);
                $this->command->info("Created class: {$schoolClass->class_name} ({$schoolClass->class_code})");

                $classCounter++;
            }
        }

        // After all teachers are created, assign class teachers
        $this->assignClassTeachers();
    }

    /**
     * Get capacity based on grade level
     */
    private function getCapacityByGrade(int $grade): int
    {
        if ($grade <= 2) {
            return 25; // Smaller classes for younger students
        } elseif ($grade <= 5) {
            return 30; // Standard elementary capacity
        } elseif ($grade <= 8) {
            return 32; // Middle school capacity
        } else {
            return 35; // High school capacity
        }
    }

    /**
     * Get room number based on grade and section
     */
    private function getRoomNumber(int $grade, string $section): string
    {
        $sectionNumber = ord($section) - ord('A') + 1; // A=1, B=2, C=3
        return $grade . str_pad($sectionNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get class description based on grade and section
     */
    private function getClassDescription(int $grade, string $section): string
    {
        $descriptions = [
            'A' => 'Section A with focus on comprehensive curriculum and academic excellence',
            'B' => 'Section B with emphasis on creative learning and practical applications',
            'C' => 'Section C with integration of technology and project-based learning'
        ];

        $gradeLevel = $this->getGradeLevelName($grade);

        return "{$gradeLevel} class section {$section}. " . $descriptions[$section];
    }

    /**
     * Get grade level descriptive name
     */
    private function getGradeLevelName(int $grade): string
    {
        if ($grade <= 5) {
            return "Elementary grade {$grade}";
        } elseif ($grade <= 8) {
            return "Middle school grade {$grade}";
        } else {
            return "High school grade {$grade}";
        }
    }

    /**
     * Assign class teachers to classes after teachers are created
     */
    private function assignClassTeachers(): void
    {
        // This will be called after TeacherSeeder runs
        // We'll update classes with class teachers in a separate method
    }
}
