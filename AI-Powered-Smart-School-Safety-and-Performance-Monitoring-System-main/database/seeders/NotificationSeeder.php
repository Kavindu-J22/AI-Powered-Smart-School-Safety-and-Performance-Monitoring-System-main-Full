<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->info('No users found. Please run UserSeeder first.');

            return;
        }

        $notifications = [
            [
                'type' => 'created',
                'title' => 'Student Created',
                'message' => $user->name.' created a new Student: John Doe',
                'entity_type' => 'Student',
                'entity_id' => 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_read' => false,
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'type' => 'updated',
                'title' => 'Teacher Updated',
                'message' => $user->name.' updated Teacher: Jane Smith',
                'entity_type' => 'Teacher',
                'entity_id' => 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_read' => false,
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'type' => 'created',
                'title' => 'Subject Created',
                'message' => $user->name.' created a new Subject: Mathematics',
                'entity_type' => 'Subject',
                'entity_id' => 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_read' => true,
                'created_at' => Carbon::now()->subHours(1),
            ],
            [
                'type' => 'deleted',
                'title' => 'SecurityStaff Deleted',
                'message' => $user->name.' deleted SecurityStaff: Mike Johnson',
                'entity_type' => 'SecurityStaff',
                'entity_id' => 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'type' => 'updated',
                'title' => 'Student Updated',
                'message' => $user->name.' updated Student: Sarah Wilson',
                'entity_type' => 'Student',
                'entity_id' => 2,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_read' => true,
                'created_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }

        $this->command->info('Notification seeder completed! Created '.count($notifications).' test notifications.');
    }
}
