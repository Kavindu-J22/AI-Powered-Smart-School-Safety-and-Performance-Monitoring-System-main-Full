<?php

namespace App\Console\Commands;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyHomework extends Command
{
    protected $signature   = 'homework:generate-weekly';
    protected $description = 'Auto-generate 2 homework assignments per week for every grade+subject that has published lessons';

    public function handle(): int
    {
        $setting = Setting::first();

        if (! $setting || ! $setting->auto_homework_enabled) {
            $this->info('Auto homework generation is disabled. Skipping.');
            return Command::SUCCESS;
        }

        $today       = now();
        $weekNumber  = $today->weekOfYear;
        $academicYear = Homework::getCurrentAcademicYear();

        // Get a fallback teacher (first available)
        $fallbackTeacherId = Teacher::orderBy('teacher_id')->value('teacher_id') ?? 1;

        // Get all distinct grade_level + subject_id combos from published lessons
        $combos = Lesson::published()
            ->select('grade_level', 'subject_id')
            ->distinct()
            ->get();

        if ($combos->isEmpty()) {
            $this->info('No published lessons found. Nothing to generate.');
            return Command::SUCCESS;
        }

        $totalCreated = 0;

        foreach ($combos as $combo) {
            $gradeLevel = $combo->grade_level;
            $subjectId  = $combo->subject_id;

            // Skip if we already auto-generated 2 assignments this week for this combo
            $existingCount = Homework::where('subject_id', $subjectId)
                ->where('grade_level', $gradeLevel)
                ->where('auto_generated', true)
                ->where('week_number', $weekNumber)
                ->where('academic_year', $academicYear)
                ->count();

            if ($existingCount >= 2) {
                $this->line("  [SKIP] Grade {$gradeLevel} / Subject {$subjectId} — already has {$existingCount} auto homework this week.");
                continue;
            }

            $needed = 2 - $existingCount;

            // Fetch published lessons for this combo
            $allLessons = Lesson::published()
                ->where('grade_level', $gradeLevel)
                ->where('subject_id', $subjectId)
                ->orderByDesc('created_at')
                ->get();

            if ($allLessons->isEmpty()) {
                continue;
            }

            // Separate new lessons (created in the last 7 days) from older ones
            $newLessons = $allLessons->filter(fn($l) => $l->created_at->gte($today->copy()->subDays(7)));
            $oldLessons = $allLessons->diff($newLessons);

            // Select lessons with priority: new first, then old
            $selectedLessons = collect();
            if ($newLessons->count() >= $needed) {
                $selectedLessons = $newLessons->random($needed);
            } elseif ($newLessons->count() > 0) {
                $selectedLessons = $newLessons;
                $remaining = $needed - $newLessons->count();
                if ($oldLessons->count() > 0) {
                    $selectedLessons = $selectedLessons->merge(
                        $oldLessons->count() >= $remaining ? $oldLessons->random($remaining) : $oldLessons
                    );
                }
            } else {
                $selectedLessons = $allLessons->count() >= $needed ? $allLessons->random($needed) : $allLessons;
            }

            // Get all classes for this grade level
            $classes = SchoolClass::where('grade_level', $gradeLevel)->get();

            foreach ($selectedLessons as $index => $lesson) {
                $teacherId  = $lesson->teacher_id ?? $fallbackTeacherId;
                $subject    = Subject::find($subjectId);
                $subjectName = $subject->subject_name ?? 'Subject';
                $dueDate    = $today->copy()->addDays(($index + 1) * 3);
                $assignmentNum = $existingCount + $index + 1;

                foreach ($classes as $schoolClass) {
                    try {
                        $homework = Homework::create([
                            'lesson_id'      => $lesson->lesson_id,
                            'subject_id'     => $subjectId,
                            'class_id'       => $schoolClass->id,
                            'assigned_by'    => $teacherId,
                            'grade_level'    => $gradeLevel,
                            'title'          => "{$subjectName} — Week {$weekNumber} Auto Assignment {$assignmentNum}",
                            'description'    => "Auto-generated weekly assignment based on: {$lesson->title}",
                            'questions'      => $this->buildQuestions($lesson, $subjectName),
                            'total_marks'    => 10,
                            'assigned_date'  => $today->toDateString(),
                            'due_date'       => $dueDate->toDateString(),
                            'status'         => 'active',
                            'week_number'    => $weekNumber,
                            'academic_year'  => $academicYear,
                            'auto_generated' => true,
                        ]);

                        // Create submissions for all active students in this class
                        $students = Student::where('class_id', $schoolClass->id)
                            ->where('is_active', true)
                            ->get();

                        foreach ($students as $student) {
                            HomeworkSubmission::firstOrCreate(
                                ['homework_id' => $homework->homework_id, 'student_id' => $student->student_id],
                                ['status' => 'assigned', 'answers' => []]
                            );
                        }

                        $totalCreated++;
                        $this->line("  [OK] Created: {$homework->title} for class {$schoolClass->class_name}");
                    } catch (\Throwable $e) {
                        Log::error("GenerateWeeklyHomework error: " . $e->getMessage());
                        $this->error("  [ERR] " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Done. Total homework created: {$totalCreated}");
        Log::info("GenerateWeeklyHomework: created {$totalCreated} assignments for week {$weekNumber}.");
        return Command::SUCCESS;
    }

    private function buildQuestions(Lesson $lesson, string $subjectName): array
    {
        return [
            [
                'question_type' => 'MCQ',
                'question'      => "Which of the following best describes a key concept from \"{$lesson->title}\"?",
                'options'       => [
                    'A' => 'The first key idea from the lesson',
                    'B' => 'An unrelated concept',
                    'C' => 'A secondary topic covered',
                    'D' => 'None of the above',
                ],
                'correct_answer' => 'A',
                'marks'          => 4,
            ],
            [
                'question_type' => 'SHORT_ANSWER',
                'question'      => "Briefly explain one important concept you learned in \"{$lesson->title}\".",
                'model_answer'  => '',
                'marks'         => 6,
            ],
        ];
    }
}

