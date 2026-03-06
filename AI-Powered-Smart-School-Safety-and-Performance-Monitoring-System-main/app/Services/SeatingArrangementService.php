<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Mark;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeatingArrangementService
{
    protected string $apiUrl = 'http://127.0.0.1:5003';

    /**
     * Generate seating arrangement for a class
     */
    public function generateForClass(int $gradeLevel, string $section, ?int $seatsPerRow = 5, ?int $totalRows = 6): array
    {
        $students = Student::where('grade_level', $gradeLevel)
            ->where('section', $section)
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            return ['error' => 'No students found for this class', 'status' => 'error'];
        }

        $studentsData = $students->map(function ($student) use ($gradeLevel) {
            $avg = Mark::where('student_id', $student->student_id)->avg('marks');
            return [
                'student_id' => (string) $student->student_id,
                'name'        => $student->full_name,
                'average_marks' => $avg ? round((float) $avg, 2) : 60.0,
                'grade'       => (string) $gradeLevel,
                'section'     => $student->section,
            ];
        })->values()->all();

        try {
            $response = Http::timeout(30)->post($this->apiUrl . '/generate-seating', [
                'grade'        => (string) $gradeLevel,
                'section'      => $section,
                'students'     => $studentsData,
                'seats_per_row' => $seatsPerRow,
                'total_rows'   => $totalRows,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['success']) && $result['success']) {
                    return $this->normalizeArrangement($result['data'], $seatsPerRow ?? 5, $totalRows ?? 6);
                }
                return ['error' => $result['message'] ?? 'Generation failed', 'status' => 'error'];
            }

            return ['error' => 'Seating API error: ' . $response->status(), 'status' => 'error'];
        } catch (\Exception $e) {
            return ['error' => 'Failed to connect to seating service: ' . $e->getMessage(), 'status' => 'error'];
        }
    }

    /**
     * Convert the Python API's flat arrangement list into a 2-D rows[][] grid.
     * Also renames student_name → name for consistency with the JS renderer.
     */
    protected function normalizeArrangement(array $data, int $seatsPerRow, int $totalRows): array
    {
        $flat         = $data['arrangement'] ?? [];
        $seatsPerRow  = (int) ($data['seats_per_row'] ?? $seatsPerRow);
        $totalRows    = (int) ($data['total_rows']    ?? $totalRows);

        // Build an empty 2D grid
        $grid = [];
        for ($r = 0; $r < $totalRows; $r++) {
            $grid[$r] = array_fill(0, $seatsPerRow, null);
        }

        // Place each student seat into the grid
        foreach ($flat as $seat) {
            $r = (int) ($seat['row']    ?? 1) - 1;
            $c = (int) ($seat['column'] ?? 1) - 1;
            if ($r >= 0 && $r < $totalRows && $c >= 0 && $c < $seatsPerRow) {
                $grid[$r][$c] = [
                    'student_id'       => $seat['student_id'],
                    'name'             => $seat['student_name'] ?? '',
                    'seat_label'       => $seat['seat_label'] ?? ('R' . ($r + 1) . 'S' . ($c + 1)),
                    'average_marks'    => $seat['average_marks'] ?? null,
                    'performance_level' => $seat['performance_level'] ?? null,
                    'row'              => $r + 1,
                    'column'           => $c + 1,
                ];
            }
        }

        // Fill remaining positions with empty-seat placeholders
        for ($r = 0; $r < $totalRows; $r++) {
            for ($c = 0; $c < $seatsPerRow; $c++) {
                if ($grid[$r][$c] === null) {
                    $grid[$r][$c] = [
                        'student_id'    => null,
                        'name'          => null,
                        'seat_label'    => 'R' . ($r + 1) . 'S' . ($c + 1),
                        'average_marks' => null,
                        'row'           => $r + 1,
                        'column'        => $c + 1,
                    ];
                }
            }
        }

        return [
            'rows'           => array_values($grid),
            'total_students' => (int) ($data['total_students'] ?? count($flat)),
            'seats_per_row'  => $seatsPerRow,
            'total_rows'     => $totalRows,
        ];
    }

    /**
     * Save a generated seating arrangement to the database
     */
    public function saveArrangement(int $gradeLevel, string $section, array $arrangement, array $studentsData, int $generatedBy): object
    {
        return (object) DB::table('seating_arrangements')->updateOrInsert(
            ['grade_level' => $gradeLevel, 'section' => $section],
            [
                'arrangement'   => json_encode($arrangement),
                'students_data' => json_encode($studentsData),
                'generated_at'  => now(),
                'generated_by'  => $generatedBy,
                'is_active'     => true,
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );
    }

    /**
     * Get the latest saved arrangement for a class
     */
    public function getArrangement(int $gradeLevel, string $section): ?array
    {
        $row = DB::table('seating_arrangements')
            ->where('grade_level', $gradeLevel)
            ->where('section', $section)
            ->where('is_active', true)
            ->latest('generated_at')
            ->first();

        if (!$row) return null;

        return [
            'arrangement'   => json_decode($row->arrangement, true),
            'students_data' => json_decode($row->students_data, true),
            'generated_at'  => $row->generated_at,
        ];
    }

    /**
     * Find a student's seat in a saved arrangement
     */
    public function findStudentSeat(int $studentId, int $gradeLevel, string $section): ?array
    {
        $saved = $this->getArrangement($gradeLevel, $section);
        if (!$saved || empty($saved['arrangement'])) return null;

        $arrangement = $saved['arrangement'];
        $rows = $arrangement['rows'] ?? [];

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $seatIndex => $seat) {
                if (isset($seat['student_id']) && (string)$seat['student_id'] === (string)$studentId) {
                    return [
                        'row'          => $rowIndex + 1,
                        'seat'         => $seatIndex + 1,
                        'seat_label'   => $seat['seat_label'] ?? ('R' . ($rowIndex + 1) . 'S' . ($seatIndex + 1)),
                        'generated_at' => $saved['generated_at'],
                        'total_rows'   => count($rows),
                        'total_seats'  => isset($rows[0]) ? count($rows[0]) : 5,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Get all classes (grade + section) that have students
     */
    public function getClassesWithStudents(): array
    {
        return Student::where('is_active', true)
            ->select('grade_level', 'section', DB::raw('count(*) as student_count'))
            ->groupBy('grade_level', 'section')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get()
            ->map(function ($row) {
                $saved = DB::table('seating_arrangements')
                    ->where('grade_level', $row->grade_level)
                    ->where('section', $row->section)
                    ->where('is_active', true)
                    ->exists();
                return [
                    'grade_level'    => $row->grade_level,
                    'section'        => $row->section,
                    'student_count'  => $row->student_count,
                    'has_arrangement' => $saved,
                ];
            })->all();
    }
}
