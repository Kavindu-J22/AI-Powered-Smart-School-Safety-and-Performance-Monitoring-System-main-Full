<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating students for grades 1-13 with proper subject assignments...');

        // Generate students for each grade (1-13)
        for ($grade = 1; $grade <= 13; $grade++) {
            $this->createStudentsForGrade($grade);
        }
    }

    /**
     * Create students for a specific grade
     */
    private function createStudentsForGrade(int $grade): void
    {
        $sections = ['A', 'B'];
        $studentsPerSection = 2; // Create 2 students per section

        foreach ($sections as $section) {
            for ($i = 1; $i <= $studentsPerSection; $i++) {
                $this->createStudent($grade, $section, $i);
            }
        }
    }

    /**
     * Create a single student with proper subject assignment
     */
    private function createStudent(int $grade, string $section, int $index): void
    {
        $studentData = $this->generateStudentData($grade, $section, $index);

        // Create user account for student
        $user = User::create([
            'name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
            'email' => $studentData['email'],
            'password' => Hash::make('student123'), // Default password
            'email_verified_at' => now(),
        ]);

        // Assign student role
        $user->assignRole('Student');

        // Find the appropriate class based on grade and section
        $schoolClass = SchoolClass::where('grade_level', $grade)
            ->where('section', $section)
            ->first();

        // Create student record
        $student = Student::create([
            'user_id' => $user->id,
            'student_code' => Student::generateStudentCode(),
            'first_name' => $studentData['first_name'],
            'middle_name' => $studentData['middle_name'],
            'last_name' => $studentData['last_name'],
            'date_of_birth' => $studentData['date_of_birth'],
            'gender' => $studentData['gender'],
            'nationality' => $studentData['nationality'],
            'religion' => $studentData['religion'],
            'home_language' => $studentData['home_language'],
            'enrollment_date' => $studentData['enrollment_date'],
            'grade_level' => $grade,
            'class_id' => $schoolClass ? $schoolClass->id : null,
            'section' => $section,
            'is_active' => true,
            'address_line1' => $studentData['address_line1'],
            'address_line2' => $studentData['address_line2'] ?? null,
            'city' => $studentData['city'],
            'state' => $studentData['state'],
            'postal_code' => $studentData['postal_code'],
            'country' => $studentData['country'],
            'home_phone' => $studentData['home_phone'],
            'mobile_phone' => $studentData['mobile_phone'],
            'email' => $studentData['email'],
        ]);

        // Attach parent to student
        $parent = ParentModel::inRandomOrder()->first();
        if ($parent) {
            $student->parents()->attach($parent->parent_id, [
                'is_primary_contact' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attach subjects to student based on grade rules
        $this->attachSubjectsToStudent($student, $grade);

        $this->command->info("Created student: {$student->full_name} ({$student->student_code}) - Grade {$grade}{$section}");
    }

    /**
     * Attach subjects to student following grade-specific rules
     */
    private function attachSubjectsToStudent(Student $student, int $grade): void
    {
        $subjectsData = Subject::getSubjectsWithRules($grade);
        $subjects = $subjectsData['subjects'];
        $rules = $subjectsData['rules'];

        $subjectsToAttach = [];

        // Primary Education (Grades 1-5)
        if ($grade >= 1 && $grade <= 5) {
            // Add core subjects (auto-assigned)
            if (isset($subjects['core'])) {
                foreach ($subjects['core'] as $subject) {
                    $subjectsToAttach[] = $subject['id'];
                }
            }

            // Add first language (pick one)
            if (isset($subjects['first_language']) && $subjects['first_language']) {
                $subjectsToAttach[] = $subjects['first_language'][array_rand($subjects['first_language'])]['id'];
            }

            // Add religion (pick one)
            if (isset($subjects['religion']) && $subjects['religion']) {
                $subjectsToAttach[] = $subjects['religion'][array_rand($subjects['religion'])]['id'];
            }

            // Add aesthetic studies (pick one)
            if (isset($subjects['aesthetic']) && $subjects['aesthetic']) {
                $subjectsToAttach[] = $subjects['aesthetic'][array_rand($subjects['aesthetic'])]['id'];
            }
        }
        // Secondary Education (Grades 6-11)
        elseif ($grade >= 6 && $grade <= 11) {
            // Add core subjects (auto-assigned)
            if (isset($subjects['core'])) {
                foreach ($subjects['core'] as $subject) {
                    $subjectsToAttach[] = $subject['id'];
                }
            }

            // Add first language (pick one)
            if (isset($subjects['first_language']) && $subjects['first_language']) {
                $subjectsToAttach[] = $subjects['first_language'][array_rand($subjects['first_language'])]['id'];
            }

            // Add religion (pick one)
            if (isset($subjects['religion']) && $subjects['religion']) {
                $subjectsToAttach[] = $subjects['religion'][array_rand($subjects['religion'])]['id'];
            }

            // Add elective subjects (pick 3)
            if (isset($subjects['elective']) && $subjects['elective']) {
                $electiveCount = min(3, count($subjects['elective']));
                $electiveIndices = array_rand($subjects['elective'], $electiveCount);
                if ($electiveCount === 1) {
                    $electiveIndices = [$electiveIndices];
                }
                foreach ($electiveIndices as $index) {
                    $subjectsToAttach[] = $subjects['elective'][$index]['id'];
                }
            }
        }
        // Advanced Level (Grades 12-13)
        elseif ($grade >= 12 && $grade <= 13) {
            // Pick a stream and add its subjects
            if (isset($subjects['streams'])) {
                $availableStreams = array_keys($subjects['streams']);
                $selectedStream = $availableStreams[array_rand($availableStreams)];

                if (isset($subjects['streams'][$selectedStream]) && $subjects['streams'][$selectedStream]) {
                    $streamSubjectCount = min(3, count($subjects['streams'][$selectedStream]));
                    $streamIndices = array_rand($subjects['streams'][$selectedStream], $streamSubjectCount);
                    if ($streamSubjectCount === 1) {
                        $streamIndices = [$streamIndices];
                    }
                    foreach ($streamIndices as $index) {
                        $subjectsToAttach[] = $subjects['streams'][$selectedStream][$index]['id'];
                    }
                }
            }
        }

        // Attach all subjects to the student
        foreach (array_unique($subjectsToAttach) as $subjectId) {
            $student->subjects()->attach($subjectId, [
                'enrollment_date' => $student->enrollment_date,
                'grade' => $grade,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Generate student data
     */
    private function generateStudentData(int $grade, string $section, int $index): array
    {
        $firstNames = [
            'Amara',
            'Nimal',
            'Kumari',
            'Ruwan',
            'Anura',
            'Chamari',
            'Saman',
            'Nilmini',
            'Mohamed',
            'Tharindu',
            'Deepika',
            'Lakshan',
            'Priyanthi',
            'Dinesh',
            'Malini',
            'Kamal',
            'Sujatha',
            'Roshan',
            'Indika',
            'Nadeesha'
        ];
        $middleNames = [
            'Priyantha',
            'Kumudini',
            'Chaminda',
            'Priyadarshani',
            'Prabath',
            'Kumari',
            'Fazal',
            'Kumara',
            'Bandara',
            'Weerasinghe'
        ];
        $lastNames = [
            'Fernando',
            'Silva',
            'Perera',
            'Jayawardena',
            'Gunawardena',
            'Bandara',
            'Rajapaksa',
            'Hussain',
            'Weerasinghe',
            'Wickramasinghe',
            'Abeysinghe',
            'Dissanayake',
            'Senanayake',
            'Jayasinghe',
            'Mendis',
            'De Silva',
            'Fernando',
            'Rathnayake',
            'Samarasinghe',
            'Wijesinghe'
        ];

        $genders = ['M', 'F'];
        $religions = ['Christian', 'Catholic', 'Protestant', 'Baptist', 'Buddhist', 'Hindu', 'Muslim', 'Other'];

        // Calculate date of birth based on grade
        $currentYear = now()->year;
        $birthYear = $currentYear - (5 + $grade); // Approximate age
        $birthMonth = rand(1, 12);
        $birthDay = rand(1, 28);

        $firstName = $firstNames[($grade * 10 + $index + ord($section)) % count($firstNames)];
        $middleName = $middleNames[($grade + $index) % count($middleNames)];
        $lastName = $lastNames[($grade * 2 + $index) % count($lastNames)];
        $gender = $genders[($grade + $index) % 2];
        $religion = $religions[$grade % count($religions)];

        $email = strtolower($firstName . '.' . $lastName . $grade . $section . '@student.school.lk');

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'date_of_birth' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            'gender' => $gender,
            'nationality' => 'Sri Lankan',
            'religion' => $religion,
            'home_language' => 'Sinhala',
            'enrollment_date' => '2024-08-20',
            'address_line1' => ($index * 100 + $grade) . ' ' . $lastNames[$grade % count($lastNames)] . ' Road',
            'address_line2' => ($index % 2 == 0) ? 'House ' . ($index + 1) : null,
            'city' => ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Anuradhapura'][$grade % 5],
            'state' => ['Western Province', 'Central Province', 'Southern Province', 'Northern Province', 'North Central Province'][$grade % 5],
            'postal_code' => ['00300', '20000', '80000', '40000', '50000'][$grade % 5],
            'country' => 'Sri Lanka',
            'home_phone' => '+94-11-234-' . str_pad(($grade * 100 + $index), 4, '0', STR_PAD_LEFT),
            'mobile_phone' => '+94-77-123-' . str_pad(($grade * 100 + $index), 4, '0', STR_PAD_LEFT),
            'email' => $email,
        ];
    }
}
