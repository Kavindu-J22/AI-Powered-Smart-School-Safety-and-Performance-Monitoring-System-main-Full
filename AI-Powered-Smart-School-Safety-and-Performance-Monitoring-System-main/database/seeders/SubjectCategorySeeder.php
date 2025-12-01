<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing subjects
        DB::table('subjects')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Primary Education (Grades 1-5) Subjects
        $primarySubjects = [
            // First Language (Choice - must select 1)
            ['subject_code' => 'PRI-SIN-001', 'subject_name' => 'Sinhala (First Language)', 'grade_level' => '1-5', 'category' => 'First Language', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-TAM-001', 'subject_name' => 'Tamil (First Language)', 'grade_level' => '1-5', 'category' => 'First Language', 'is_required' => false, 'credits' => 1],

            // Core Subjects (Auto-assigned)
            ['subject_code' => 'PRI-ENG-001', 'subject_name' => 'English Language', 'grade_level' => '1-5', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'PRI-MAT-001', 'subject_name' => 'Mathematics', 'grade_level' => '1-5', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'PRI-ENV-001', 'subject_name' => 'Environmental Studies', 'grade_level' => '1-5', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'PRI-HPE-001', 'subject_name' => 'Health and Physical Education', 'grade_level' => '1-5', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'PRI-LCE-001', 'subject_name' => 'Life Competencies and Civic Education', 'grade_level' => '1-5', 'category' => 'Core', 'is_required' => true, 'credits' => 1],

            // Religion (Choice - must select 1)
            ['subject_code' => 'PRI-BUD-001', 'subject_name' => 'Buddhism', 'grade_level' => '1-5', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-HIN-001', 'subject_name' => 'Hinduism', 'grade_level' => '1-5', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-ISL-001', 'subject_name' => 'Islam', 'grade_level' => '1-5', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-CHR-001', 'subject_name' => 'Christianity', 'grade_level' => '1-5', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],

            // Aesthetic Studies (Choice - must select 1)
            ['subject_code' => 'PRI-ART-001', 'subject_name' => 'Art', 'grade_level' => '1-5', 'category' => 'Aesthetic Studies', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-MUS-001', 'subject_name' => 'Music', 'grade_level' => '1-5', 'category' => 'Aesthetic Studies', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-DAN-001', 'subject_name' => 'Dance', 'grade_level' => '1-5', 'category' => 'Aesthetic Studies', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'PRI-DRA-001', 'subject_name' => 'Drama', 'grade_level' => '1-5', 'category' => 'Aesthetic Studies', 'is_required' => false, 'credits' => 1],
        ];

        // Secondary Education (Grades 6-11) Subjects
        $secondarySubjects = [
            // First Language (Choice - must select 1)
            ['subject_code' => 'SEC-SIN-001', 'subject_name' => 'Sinhala (First Language)', 'grade_level' => '6-11', 'category' => 'First Language', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-TAM-001', 'subject_name' => 'Tamil (First Language)', 'grade_level' => '6-11', 'category' => 'First Language', 'is_required' => false, 'credits' => 1],

            // Core Subjects (Auto-assigned)
            ['subject_code' => 'SEC-ENG-001', 'subject_name' => 'English Language', 'grade_level' => '6-11', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'SEC-MAT-001', 'subject_name' => 'Mathematics', 'grade_level' => '6-11', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'SEC-SCI-001', 'subject_name' => 'Science', 'grade_level' => '6-11', 'category' => 'Core', 'is_required' => true, 'credits' => 1],
            ['subject_code' => 'SEC-HIS-001', 'subject_name' => 'History', 'grade_level' => '6-11', 'category' => 'Core', 'is_required' => true, 'credits' => 1],

            // Religion (Choice - must select 1)
            ['subject_code' => 'SEC-BUD-001', 'subject_name' => 'Buddhism', 'grade_level' => '6-11', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-HIN-001', 'subject_name' => 'Hinduism', 'grade_level' => '6-11', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-ISL-001', 'subject_name' => 'Islam', 'grade_level' => '6-11', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-CHR-001', 'subject_name' => 'Christianity', 'grade_level' => '6-11', 'category' => 'Religion', 'is_required' => false, 'credits' => 1],

            // Elective Subjects (Must select exactly 3)
            ['subject_code' => 'SEC-GEO-001', 'subject_name' => 'Geography', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-CIV-001', 'subject_name' => 'Civic Education', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-BUS-001', 'subject_name' => 'Business and Accounting Studies', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-ICT-001', 'subject_name' => 'Information and Communication Technology (ICT)', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-AGR-001', 'subject_name' => 'Agriculture and Food Technology', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-HPE-001', 'subject_name' => 'Health and Physical Education', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-SL2-001', 'subject_name' => 'Second Language', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'SEC-AES-001', 'subject_name' => 'Aesthetic Studies', 'grade_level' => '6-11', 'category' => 'Elective', 'is_required' => false, 'credits' => 1],
        ];

        // Advanced Level (Grades 12-13) - Arts Stream
        $artsStreamSubjects = [
            ['subject_code' => 'AL-ART-SIN-001', 'subject_name' => 'Sinhala Literature', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-TAM-001', 'subject_name' => 'Tamil Literature', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-ENG-001', 'subject_name' => 'English Literature', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-HIS-001', 'subject_name' => 'History', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-GEO-001', 'subject_name' => 'Geography', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-POL-001', 'subject_name' => 'Political Science', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-LOG-001', 'subject_name' => 'Logic and Scientific Method', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-ECO-001', 'subject_name' => 'Economics', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-BUD-001', 'subject_name' => 'Buddhism', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-HIN-001', 'subject_name' => 'Hinduism', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-ISL-001', 'subject_name' => 'Islam', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-CHR-001', 'subject_name' => 'Christianity', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-AES-001', 'subject_name' => 'Aesthetic Studies', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-MED-001', 'subject_name' => 'Media Studies', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-ART-IT-001', 'subject_name' => 'Information Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Arts', 'is_required' => false, 'credits' => 1],
        ];

        // Commerce Stream
        $commerceStreamSubjects = [
            ['subject_code' => 'AL-COM-BUS-001', 'subject_name' => 'Business Studies', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Commerce', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-COM-ACC-001', 'subject_name' => 'Accounting', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Commerce', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-COM-ECO-001', 'subject_name' => 'Economics', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Commerce', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-COM-IT-001', 'subject_name' => 'Information Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Commerce', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-COM-ENT-001', 'subject_name' => 'Entrepreneurship Studies', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Commerce', 'is_required' => false, 'credits' => 1],
        ];

        // Science Stream
        $scienceStreamSubjects = [
            ['subject_code' => 'AL-SCI-PHY-001', 'subject_name' => 'Physics', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-SCI-CHE-001', 'subject_name' => 'Chemistry', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-SCI-BIO-001', 'subject_name' => 'Biology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-SCI-MAT-001', 'subject_name' => 'Combined Mathematics', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-SCI-AGR-001', 'subject_name' => 'Agriculture', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-SCI-IT-001', 'subject_name' => 'Information Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Science', 'is_required' => false, 'credits' => 1],
        ];

        // Technology Stream
        $technologyStreamSubjects = [
            ['subject_code' => 'AL-TEC-SCI-001', 'subject_name' => 'Science for Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Technology', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-TEC-ENG-001', 'subject_name' => 'Engineering Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Technology', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-TEC-BIO-001', 'subject_name' => 'Bio-systems Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Technology', 'is_required' => false, 'credits' => 1],
            ['subject_code' => 'AL-TEC-ICT-001', 'subject_name' => 'Information and Communication Technology', 'grade_level' => '12-13', 'category' => 'Core', 'stream' => 'Technology', 'is_required' => false, 'credits' => 1],
        ];

        // Merge all subjects
        $allSubjects = array_merge(
            $primarySubjects,
            $secondarySubjects,
            $artsStreamSubjects,
            $commerceStreamSubjects,
            $scienceStreamSubjects,
            $technologyStreamSubjects
        );

        // Add timestamps and status to all subjects
        foreach ($allSubjects as &$subject) {
            $subject['status'] = 'active';
            $subject['type'] = 'Core'; // Default type
            if (!isset($subject['stream'])) {
                $subject['stream'] = null;
            }
            $subject['created_at'] = now();
            $subject['updated_at'] = now();
        }

        // Insert all subjects
        DB::table('subjects')->insert($allSubjects);

        $this->command->info('Subject categories seeded successfully!');
        $this->command->info('Total subjects created: ' . count($allSubjects));
    }
}
