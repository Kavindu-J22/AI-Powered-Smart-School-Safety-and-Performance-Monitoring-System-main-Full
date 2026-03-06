<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Mark;
use App\Models\Attendance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PerformancePredictionService
{
    protected string $predictionApiUrl = 'http://127.0.0.1:5002';

    /**
     * Get performance prediction for a student
     * Returns prediction data with confidence intervals
     */
    public function getPrediction(int $studentId): array
    {
        $student = Student::find($studentId);

        if (!$student) {
            return [
                'error' => 'Student not found',
                'status' => 'error'
            ];
        }

        // Collect student data and marks
        $studentData = $this->buildStudentData($student);

        // Guard: no marks data available for this student
        if (empty($studentData['subjects'])) {
            return [
                'status'  => 'no_data',
                'message' => 'No marks data available for this student yet. Add marks to see the AI prediction.',
            ];
        }

        // Call the prediction API
        try {
            $response = Http::timeout(30)
                ->post($this->predictionApiUrl . '/predict', $studentData);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => 'Prediction API error: ' . $response->status(),
                'status' => 'error'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to connect to prediction service: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Build student data for prediction
     * Collects marks and attendance data for all subjects
     */
    protected function buildStudentData(Student $student): array
    {
        $marks = Mark::where('student_id', $student->student_id)
            ->with('subject')
            ->orderBy('term', 'desc')
            ->get()
            ->groupBy('subject_id');

        $attendance = $this->calculateAttendancePercentage($student->student_id);

        $subjects = [];
        foreach ($marks as $subjectId => $subjectMarks) {
            $latestMark = $subjectMarks->first();
            $subject = $latestMark->subject;

            $subjects[] = [
                'subject_name' => $subject->subject_name,
                'subject_id' => $subjectId,
                'marks' => (float) $latestMark->marks,
                'attendance' => $attendance[$subjectId] ?? 85.0,
                'trend' => $this->calculateMarksTrend($subjectMarks)
            ];
        }

        return [
            'student_id' => $student->student_id,
            'age' => $this->calculateAge($student->date_of_birth),
            'grade' => (int) $student->grade_level,
            'subjects' => $subjects
        ];
    }

    /**
     * Calculate attendance percentage per subject
     */
    protected function calculateAttendancePercentage(int $studentId): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $attendance = Attendance::where('student_id', $studentId)
            ->where('attendance_date', '>=', $thirtyDaysAgo)
            ->get();

        // Calculate overall attendance percentage
        $presentDays = $attendance->where('status', 'present')->count();
        $totalDays = $attendance->count();

        $overallAttendance = $totalDays > 0
            ? ($presentDays / $totalDays) * 100
            : 85.0;

        // For now, return the same attendance for all subjects
        // In a real scenario, you might have subject-specific attendance
        return array_fill_keys(range(1, 10), min($overallAttendance, 100));
    }

    /**
     * Calculate trend from marks history
     */
    protected function calculateMarksTrend(Collection $marks): string
    {
        if ($marks->count() < 2) {
            return 'stable';
        }

        $latestMarks = $marks->take(2);
        $difference = $latestMarks->first()->marks - $latestMarks->last()->marks;

        if ($difference > 5) return 'improving';
        if ($difference < -5) return 'declining';
        return 'stable';
    }

    /**
     * Calculate age from date of birth
     */
    protected function calculateAge($dateOfBirth): int
    {
        if (!$dateOfBirth) {
            return 15; // default age
        }

        return Carbon::parse($dateOfBirth)->age;
    }

    /**
     * Format prediction response for display
     */
    public function formatPredictionForDisplay(array $prediction): array
    {
        if (isset($prediction['error'])) {
            return $prediction;
        }

        if (!isset($prediction['predictions'])) {
            return [
                'error' => 'Invalid prediction response',
                'status' => 'error'
            ];
        }

        return [
            'student_id' => $prediction['student_id'] ?? null,
            'total_subjects' => $prediction['total_subjects'] ?? 0,
            'predictions' => collect($prediction['predictions'] ?? [])
                ->map(fn($pred) => [
                    'subject' => $pred['subject'] ?? 'Unknown',
                    'current_performance' => round($pred['current_performance'] ?? 0, 2),
                    'predicted_performance' => round($pred['predicted_performance'] ?? 0, 2),
                    'prediction_trend' => $pred['prediction_trend'] ?? 'stable',
                    'confidence' => round(($pred['confidence'] ?? 0) * 100, 1),
                    'confidence_interval' => [
                        'lower_bound' => round($pred['confidence_interval']['lower_bound'] ?? 0, 2),
                        'upper_bound' => round($pred['confidence_interval']['upper_bound'] ?? 0, 2),
                        'confidence_level' => ($pred['confidence_interval']['confidence_level'] ?? 0.95) * 100
                    ],
                    'improvement' => round(
                        ($pred['predicted_performance'] ?? 0) - ($pred['current_performance'] ?? 0),
                        2
                    )
                ])
                ->toArray(),
            'status' => 'success'
        ];
    }
}
