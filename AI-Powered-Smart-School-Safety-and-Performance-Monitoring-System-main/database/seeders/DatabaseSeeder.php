<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core system setup
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(UsersSeed::class);
        $this->call(SettingSeed::class);

        // Basic data seeding (order matters due to relationships)
        $this->call(SubjectSeeder::class);
        $this->call(TimeSlotsSeeder::class); // Create time slots before classes
        $this->call(SchoolClassSeeder::class);
        $this->call(ParentSeeder::class);
        $this->call(TeacherSeeder::class);
        $this->call(SecurityStaffSeeder::class);
        $this->call(StudentSeeder::class); // Last because it depends on classes and parents

        // Final assignments (after all entities are created)
        $this->call(ClassTeacherAssignmentSeeder::class);
        $this->call(TimetableSeeder::class); // Create timetables last
    }
}
