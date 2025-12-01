<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sri Lankan School Education System Subjects
     */
    public function run(): void
    {
        $subjects = [
            // ==========================================
            // PRIMARY EDUCATION (GRADES 1-5)
            // ==========================================
            [
                'subject_code' => 'PE-SIN-01',
                'subject_name' => 'Sinhala (First Language)',
                'grade_level' => '1-5',
                'description' => 'Primary Sinhala language development - reading, writing, and communication skills',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-TAM-01',
                'subject_name' => 'Tamil (First Language)',
                'grade_level' => '1-5',
                'description' => 'Primary Tamil language development - reading, writing, and communication skills',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-ENG-01',
                'subject_name' => 'English Language',
                'grade_level' => '1-5',
                'description' => 'Primary English language skills development',
                'credits' => 4,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-MATH-01',
                'subject_name' => 'Mathematics',
                'grade_level' => '1-5',
                'description' => 'Foundation mathematics - numbers, operations, and problem solving',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-ENV-01',
                'subject_name' => 'Environmental Studies',
                'grade_level' => '1-5',
                'description' => 'Basic understanding of natural and social environment',
                'credits' => 4,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-BUD-01',
                'subject_name' => 'Religion - Buddhism',
                'grade_level' => '1-5',
                'description' => 'Buddhist religious education and moral values',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-HIN-01',
                'subject_name' => 'Religion - Hinduism',
                'grade_level' => '1-5',
                'description' => 'Hindu religious education and moral values',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-ISL-01',
                'subject_name' => 'Religion - Islam',
                'grade_level' => '1-5',
                'description' => 'Islamic religious education and moral values',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-CHR-01',
                'subject_name' => 'Religion - Christianity',
                'grade_level' => '1-5',
                'description' => 'Christian religious education and moral values',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-AES-01',
                'subject_name' => 'Aesthetic Studies',
                'grade_level' => '1-5',
                'description' => 'Art, Music, Dance, and Drama education',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-HPE-01',
                'subject_name' => 'Health and Physical Education',
                'grade_level' => '1-5',
                'description' => 'Physical fitness, health awareness, and sports skills',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'PE-LCE-01',
                'subject_name' => 'Life Competencies and Civic Education',
                'grade_level' => '1-5',
                'description' => 'Life skills and basic civic education',
                'credits' => 2,
                'type' => 'Core',
                'status' => 'active',
            ],

            // ==========================================
            // SECONDARY EDUCATION - CORE (GRADES 6-11)
            // ==========================================
            [
                'subject_code' => 'SE-SIN-01',
                'subject_name' => 'Sinhala (First Language)',
                'grade_level' => '6-11',
                'description' => 'Advanced Sinhala language and literature for O/L',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-TAM-01',
                'subject_name' => 'Tamil (First Language)',
                'grade_level' => '6-11',
                'description' => 'Advanced Tamil language and literature for O/L',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-ENG-01',
                'subject_name' => 'English Language',
                'grade_level' => '6-11',
                'description' => 'English language proficiency for O/L examination',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-MATH-01',
                'subject_name' => 'Mathematics',
                'grade_level' => '6-11',
                'description' => 'Comprehensive mathematics for O/L examination',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-SCI-01',
                'subject_name' => 'Science',
                'grade_level' => '6-11',
                'description' => 'Integrated science covering Physics, Chemistry, and Biology',
                'credits' => 5,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-HIS-01',
                'subject_name' => 'History',
                'grade_level' => '6-11',
                'description' => 'Sri Lankan and world history',
                'credits' => 4,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-BUD-01',
                'subject_name' => 'Religion - Buddhism',
                'grade_level' => '6-11',
                'description' => 'Buddhist philosophy and teachings for O/L',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-HIN-01',
                'subject_name' => 'Religion - Hinduism',
                'grade_level' => '6-11',
                'description' => 'Hindu philosophy and teachings for O/L',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-ISL-01',
                'subject_name' => 'Religion - Islam',
                'grade_level' => '6-11',
                'description' => 'Islamic studies and teachings for O/L',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-CHR-01',
                'subject_name' => 'Religion - Christianity',
                'grade_level' => '6-11',
                'description' => 'Christian studies and teachings for O/L',
                'credits' => 3,
                'type' => 'Core',
                'status' => 'active',
            ],

            // ==========================================
            // SECONDARY EDUCATION - ELECTIVE (GRADES 6-11)
            // ==========================================
            [
                'subject_code' => 'SE-GEO-01',
                'subject_name' => 'Geography',
                'grade_level' => '6-11',
                'description' => 'Physical and human geography studies',
                'credits' => 4,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-CIV-01',
                'subject_name' => 'Civic Education',
                'grade_level' => '6-11',
                'description' => 'Civic awareness and citizenship education',
                'credits' => 3,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-BUS-01',
                'subject_name' => 'Business and Accounting Studies',
                'grade_level' => '6-11',
                'description' => 'Introduction to business and accounting principles',
                'credits' => 4,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-ICT-01',
                'subject_name' => 'Information and Communication Technology (ICT)',
                'grade_level' => '6-11',
                'description' => 'Computer literacy and ICT skills',
                'credits' => 4,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-AGR-01',
                'subject_name' => 'Agriculture and Food Technology',
                'grade_level' => '6-11',
                'description' => 'Agricultural science and food technology',
                'credits' => 4,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-HPE-01',
                'subject_name' => 'Health and Physical Education',
                'grade_level' => '6-11',
                'description' => 'Advanced physical education and health studies',
                'credits' => 3,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-SL2-01',
                'subject_name' => 'Second Language (Tamil/Sinhala)',
                'grade_level' => '6-11',
                'description' => 'Second language proficiency development',
                'credits' => 3,
                'type' => 'Elective',
                'status' => 'active',
            ],
            [
                'subject_code' => 'SE-AES-01',
                'subject_name' => 'Aesthetic Studies',
                'grade_level' => '6-11',
                'description' => 'Advanced Art, Music, Dance, and Drama',
                'credits' => 3,
                'type' => 'Elective',
                'status' => 'active',
            ],

            // ==========================================
            // ADVANCED LEVEL - ARTS STREAM (GRADES 12-13)
            // ==========================================
            [
                'subject_code' => 'AL-ART-SIN-01',
                'subject_name' => 'Sinhala Literature',
                'grade_level' => '12-13',
                'description' => 'Advanced study of Sinhala literature and language',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-TAM-01',
                'subject_name' => 'Tamil Literature',
                'grade_level' => '12-13',
                'description' => 'Advanced study of Tamil literature and language',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-ENG-01',
                'subject_name' => 'English Literature',
                'grade_level' => '12-13',
                'description' => 'Critical study of English literature',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-HIS-01',
                'subject_name' => 'History',
                'grade_level' => '12-13',
                'description' => 'In-depth study of historical periods and events',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-GEO-01',
                'subject_name' => 'Geography',
                'grade_level' => '12-13',
                'description' => 'Advanced geographical studies and analysis',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-POL-01',
                'subject_name' => 'Political Science',
                'grade_level' => '12-13',
                'description' => 'Study of political systems, theory, and governance',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-LOG-01',
                'subject_name' => 'Logic and Scientific Method',
                'grade_level' => '12-13',
                'description' => 'Logical reasoning and scientific methodology',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-ECO-01',
                'subject_name' => 'Economics',
                'grade_level' => '12-13',
                'description' => 'Economic theory and principles',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-BUD-01',
                'subject_name' => 'Buddhism',
                'grade_level' => '12-13',
                'description' => 'Advanced Buddhist philosophy and civilization',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-HIN-01',
                'subject_name' => 'Hinduism',
                'grade_level' => '12-13',
                'description' => 'Advanced Hindu philosophy and civilization',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-ISL-01',
                'subject_name' => 'Islam',
                'grade_level' => '12-13',
                'description' => 'Advanced Islamic studies and civilization',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-CHR-01',
                'subject_name' => 'Christianity',
                'grade_level' => '12-13',
                'description' => 'Advanced Christian studies and civilization',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-AES-01',
                'subject_name' => 'Aesthetic Studies',
                'grade_level' => '12-13',
                'description' => 'Advanced Art, Music, Dance, and Drama studies',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-MED-01',
                'subject_name' => 'Media Studies',
                'grade_level' => '12-13',
                'description' => 'Study of media, communication, and journalism',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-ART-IT-01',
                'subject_name' => 'Information Technology',
                'grade_level' => '12-13',
                'description' => 'IT systems and application development',
                'credits' => 5,
                'type' => 'Arts Stream',
                'status' => 'active',
            ],

            // ==========================================
            // ADVANCED LEVEL - COMMERCE STREAM (GRADES 12-13)
            // ==========================================
            [
                'subject_code' => 'AL-COM-BUS-01',
                'subject_name' => 'Business Studies',
                'grade_level' => '12-13',
                'description' => 'Comprehensive business management and entrepreneurship',
                'credits' => 5,
                'type' => 'Commerce Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-COM-ACC-01',
                'subject_name' => 'Accounting',
                'grade_level' => '12-13',
                'description' => 'Advanced accounting principles and practices',
                'credits' => 5,
                'type' => 'Commerce Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-COM-ECO-01',
                'subject_name' => 'Economics',
                'grade_level' => '12-13',
                'description' => 'Microeconomics and macroeconomics',
                'credits' => 5,
                'type' => 'Commerce Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-COM-IT-01',
                'subject_name' => 'Information Technology',
                'grade_level' => '12-13',
                'description' => 'Business IT systems and applications',
                'credits' => 5,
                'type' => 'Commerce Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-COM-ENT-01',
                'subject_name' => 'Entrepreneurship Studies',
                'grade_level' => '12-13',
                'description' => 'Entrepreneurial skills and business development',
                'credits' => 5,
                'type' => 'Commerce Stream',
                'status' => 'active',
            ],

            // ==========================================
            // ADVANCED LEVEL - SCIENCE STREAM (GRADES 12-13)
            // ==========================================
            [
                'subject_code' => 'AL-SCI-PHY-01',
                'subject_name' => 'Physics',
                'grade_level' => '12-13',
                'description' => 'Advanced physics including mechanics, electricity, and quantum physics',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-SCI-CHE-01',
                'subject_name' => 'Chemistry',
                'grade_level' => '12-13',
                'description' => 'Advanced chemistry including organic, inorganic, and physical chemistry',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-SCI-BIO-01',
                'subject_name' => 'Biology',
                'grade_level' => '12-13',
                'description' => 'Advanced biology including molecular biology, genetics, and ecology',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-SCI-CMATH-01',
                'subject_name' => 'Combined Mathematics',
                'grade_level' => '12-13',
                'description' => 'Advanced mathematics including calculus, algebra, and statistics',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-SCI-AGR-01',
                'subject_name' => 'Agriculture',
                'grade_level' => '12-13',
                'description' => 'Agricultural science and biotechnology',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-SCI-IT-01',
                'subject_name' => 'Information Technology',
                'grade_level' => '12-13',
                'description' => 'Computer science and IT applications',
                'credits' => 5,
                'type' => 'Science Stream',
                'status' => 'active',
            ],

            // ==========================================
            // ADVANCED LEVEL - TECHNOLOGY STREAM (GRADES 12-13)
            // ==========================================
            [
                'subject_code' => 'AL-TEC-SCT-01',
                'subject_name' => 'Science for Technology',
                'grade_level' => '12-13',
                'description' => 'Applied science for technological applications',
                'credits' => 5,
                'type' => 'Technology Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-TEC-ENG-01',
                'subject_name' => 'Engineering Technology',
                'grade_level' => '12-13',
                'description' => 'Engineering principles and applications',
                'credits' => 5,
                'type' => 'Technology Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-TEC-BIO-01',
                'subject_name' => 'Bio-systems Technology',
                'grade_level' => '12-13',
                'description' => 'Biological systems and agricultural technology',
                'credits' => 5,
                'type' => 'Technology Stream',
                'status' => 'active',
            ],
            [
                'subject_code' => 'AL-TEC-ICT-01',
                'subject_name' => 'Information and Communication Technology',
                'grade_level' => '12-13',
                'description' => 'Advanced ICT systems and programming',
                'credits' => 5,
                'type' => 'Technology Stream',
                'status' => 'active',
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create($subjectData);
            $this->command->info("Created subject: {$subjectData['subject_name']} ({$subjectData['subject_code']})");
        }

        $this->command->info("\n✓ Successfully seeded " . count($subjects) . " subjects for Sri Lankan education system");
        $this->command->info("  - Primary Education (Grades 1-5): 12 subjects");
        $this->command->info("  - Secondary Education (Grades 6-11): 18 subjects");
        $this->command->info("  - A/L Arts Stream (Grades 12-13): 15 subjects");
        $this->command->info("  - A/L Commerce Stream (Grades 12-13): 5 subjects");
        $this->command->info("  - A/L Science Stream (Grades 12-13): 6 subjects");
        $this->command->info("  - A/L Technology Stream (Grades 12-13): 4 subjects");
    }
}
