<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TimeSlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating time slots for school schedule (Monday-Friday, 8 periods + 1 break)...');

        // Clear existing time slots (delete instead of truncate due to foreign keys)
        // Note: timetables are already cleared in the migration
        TimeSlot::query()->delete();

        // Create the proper school schedule: 8 periods + 1 break
        $this->createSchoolSchedule();

        // Create additional time slots for after 1:30 PM (admin can use these)
        $this->createAdditionalTimeSlots();
    }

    /**
     * Create the main school schedule (8 periods + 1 break)
     * FIXED PRESET: 8:00 AM - 1:30 PM (Cannot be changed by admin)
     * Structure: 4 periods, 30min break, 4 periods
     */
    private function createSchoolSchedule(): void
    {
        $schedule = [
            // Period 1: 8:00 - 8:40
            ['start' => '08:00', 'end' => '08:40', 'type' => 'regular', 'period' => 1],
            // Period 2: 8:40 - 9:20
            ['start' => '08:40', 'end' => '09:20', 'type' => 'regular', 'period' => 2],
            // Period 3: 9:20 - 10:00
            ['start' => '09:20', 'end' => '10:00', 'type' => 'regular', 'period' => 3],
            // Period 4: 10:00 - 10:40
            ['start' => '10:00', 'end' => '10:40', 'type' => 'regular', 'period' => 4],
            // Break: 10:40 - 11:10 (30 minute break after 4 periods)
            ['start' => '10:40', 'end' => '11:10', 'type' => 'break', 'period' => null],
            // Period 5: 11:10 - 11:50
            ['start' => '11:10', 'end' => '11:50', 'type' => 'regular', 'period' => 5],
            // Period 6: 11:50 - 12:30
            ['start' => '11:50', 'end' => '12:30', 'type' => 'regular', 'period' => 6],
            // Period 7: 12:30 - 13:10
            ['start' => '12:30', 'end' => '13:10', 'type' => 'regular', 'period' => 7],
            // Period 8: 13:10 - 13:30 (ends at 1:30 PM - school day complete)
            ['start' => '13:10', 'end' => '13:30', 'type' => 'regular', 'period' => 8],
        ];

        $slotCounter = 1;

        foreach ($schedule as $slot) {
            $isBreak = $slot['type'] === 'break';

            if ($isBreak) {
                $slotName = 'Break';
                $slotCode = 'BREAK-1';
                $description = '30-minute break after 4 periods - Fixed preset time';
            } else {
                $slotName = "Period {$slot['period']}";
                $slotCode = "PERIOD-{$slot['period']}";
                $description = "Teaching period {$slot['period']} - Fixed preset time (Admin can assign Teacher & Subject only)";
            }

            TimeSlot::create([
                'slot_code' => $slotCode,
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'slot_name' => $slotName,
                'slot_type' => $slot['type'],
                'duration_minutes' => $this->calculateDuration($slot['start'], $slot['end']),
                'description' => $description,
                'status' => 'active'
            ]);

            $this->command->info("Created: {$slotName} ({$slot['start']} - {$slot['end']})");
            $slotCounter++;
        }
    }

    /**
     * Create additional time slots for after 1:30 PM
     * Admin can add these as needed for extended activities
     */
    private function createAdditionalTimeSlots(): void
    {
        $this->command->info('Creating sample additional time slots (after 1:30 PM - admin can add more)...');

        $additionalSlots = [
            ['start' => '13:30', 'end' => '14:15', 'name' => 'Extra Period 1', 'code' => 'EXTRA-1', 'description' => 'Additional period (admin can add after 1:30 PM)'],
            ['start' => '14:15', 'end' => '15:00', 'name' => 'Extra Period 2', 'code' => 'EXTRA-2', 'description' => 'Additional period (admin can add after 1:30 PM)'],
            ['start' => '15:00', 'end' => '15:45', 'name' => 'Study Hall', 'code' => 'STUDY-1', 'description' => 'Study time (admin can add after 1:30 PM)'],
            ['start' => '15:45', 'end' => '16:30', 'name' => 'Sports/Activities', 'code' => 'SPORTS-1', 'description' => 'Sports or activities (admin can add after 1:30 PM)'],
        ];

        foreach ($additionalSlots as $slot) {
            TimeSlot::create([
                'slot_code' => $slot['code'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'slot_name' => $slot['name'],
                'slot_type' => 'additional',
                'duration_minutes' => $this->calculateDuration($slot['start'], $slot['end']),
                'description' => $slot['description'],
                'status' => 'active'
            ]);

            $this->command->info("Created additional: {$slot['name']} ({$slot['start']} - {$slot['end']})");
        }

        $this->command->info('Fixed preset schedule created successfully!');
        $this->command->info('Schedule Summary:');
        $this->command->info('- FIXED PRESET: 8:00 AM - 1:30 PM (Cannot be changed by admin)');
        $this->command->info('- 4 Periods before break: 8:00 AM - 10:40 AM');
        $this->command->info('- 30-minute break: 10:40 AM - 11:10 AM');
        $this->command->info('- 4 Periods after break: 11:10 AM - 1:30 PM');
        $this->command->info('- Additional slots: Admin can add after 1:30 PM only');
        $this->command->info('- Admin can only assign: Teachers & Subjects (not change times)');
    }

    /**
     * Calculate duration in minutes between two times
     */
    private function calculateDuration(string $start, string $end): int
    {
        $startTime = Carbon::createFromFormat('H:i', $start);
        $endTime = Carbon::createFromFormat('H:i', $end);
        return $startTime->diffInMinutes($endTime);
    }
}
