<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles for school management
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $parentRole = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $securityRole = Role::firstOrCreate(['name' => 'security', 'guard_name' => 'web']);

        // Create permissions from routes_and_permissions config
        foreach (config('routes_and_permissions') as $sidebarElement) {
            if (isset($sidebarElement['items'])) {
                foreach ($sidebarElement['items'] as $element) {

                    $mainPermission = formatPermissionString($element['route']);

                    // Skip 'enroll' permissions as mentioned in sample
                    if (isset(['enroll'][strtolower($mainPermission)])) {
                        continue;
                    }

                    $permission = Permission::firstOrCreate([
                        'name' => $mainPermission,
                        'guard_name' => 'web',
                    ]);

                    // Give admin role all permissions
                    if (! $adminRole->hasPermissionTo($permission)) {
                        $adminRole->givePermissionTo($permission);
                    }

                    // Handle other_selected_routes
                    if (isset($element['other_selected_routes'])) {
                        foreach ($element['other_selected_routes'] as $route) {
                            $otherPermission = formatPermissionString($route);
                            $otherPermissionModel = Permission::firstOrCreate([
                                'name' => $otherPermission,
                                'guard_name' => 'web',
                            ]);

                            if (! $adminRole->hasPermissionTo($otherPermissionModel)) {
                                $adminRole->givePermissionTo($otherPermissionModel);
                            }

                            // Assign to specific roles based on permission context
                            $this->assignPermissionToRoles($otherPermissionModel, $teacherRole, $studentRole, $parentRole, $securityRole);
                        }
                    }

                    // Handle additional_permissions
                    if (isset($element['additional_permissions'])) {
                        foreach ($element['additional_permissions'] as $route) {
                            $additionalPermission = formatPermissionString($route);
                            $additionalPermissionModel = Permission::firstOrCreate([
                                'name' => $additionalPermission,
                                'guard_name' => 'web',
                            ]);

                            if (! $adminRole->hasPermissionTo($additionalPermissionModel)) {
                                $adminRole->givePermissionTo($additionalPermissionModel);
                            }

                            // Assign to specific roles based on permission context
                            $this->assignPermissionToRoles($additionalPermissionModel, $teacherRole, $studentRole, $parentRole, $securityRole);
                        }
                    }

                    // Assign main permission to specific roles
                    $this->assignPermissionToRoles($permission, $teacherRole, $studentRole, $parentRole, $securityRole);
                }
            }
        }

        echo "Roles and permissions created successfully!\n";
        echo 'Admin role has '.$adminRole->permissions->count()." permissions\n";
        echo 'Teacher role has '.$teacherRole->permissions->count()." permissions\n";
        echo 'Student role has '.$studentRole->permissions->count()." permissions\n";
        echo 'Parent role has '.$parentRole->permissions->count()." permissions\n";
        echo 'Security role has '.$securityRole->permissions->count()." permissions\n";
    }

    /**
     * Assign permission to appropriate roles based on permission context
     */
    private function assignPermissionToRoles($permission, $teacherRole, $studentRole, $parentRole, $securityRole)
    {
        $permissionName = $permission->name;

        // Teacher permissions
        if ($this->isTeacherPermission($permissionName)) {
            if (! $teacherRole->hasPermissionTo($permission)) {
                $teacherRole->givePermissionTo($permission);
            }
        }

        // Student permissions
        if ($this->isStudentPermission($permissionName)) {
            if (! $studentRole->hasPermissionTo($permission)) {
                $studentRole->givePermissionTo($permission);
            }
        }

        // Parent permissions
        if ($this->isParentPermission($permissionName)) {
            if (! $parentRole->hasPermissionTo($permission)) {
                $parentRole->givePermissionTo($permission);
            }
        }

        // Security permissions
        if ($this->isSecurityPermission($permissionName)) {
            if (! $securityRole->hasPermissionTo($permission)) {
                $securityRole->givePermissionTo($permission);
            }
        }
    }

    /**
     * Check if permission should be granted to teacher role
     */
    private function isTeacherPermission($permission)
    {
        $teacherPermissions = [
            'admin.dashboard.index',
            'admin.students.index',
            'admin.students.show',
            'admin.classes.index',
            'admin.classes.show',
            'admin.classes.form',
            'admin.classes.enroll',
            'admin.grades.index',
            'admin.grades.form',
            'admin.grades.enroll',
            'admin.grades.show',
            'admin.attendance.index',
            'admin.attendance.form',
            'admin.attendance.mark',
            'admin.attendance.show',
            'admin.assignments.index',
            'admin.assignments.form',
            'admin.assignments.enroll',
            'admin.assignments.show',
            'admin.assignments.grade',
            'admin.subjects.index',
            'admin.subjects.show',
            'admin.timetable.index',
            'admin.timetable.show',
            'admin.reports.students.index',
            'admin.reports.academic.index',
            'admin.reports.attendance.index',
        ];

        return in_array($permission, $teacherPermissions) ||
            str_contains($permission, 'students') ||
            str_contains($permission, 'classes') ||
            str_contains($permission, 'grades') ||
            str_contains($permission, 'attendance') ||
            str_contains($permission, 'assignments') ||
            str_contains($permission, 'subjects') ||
            str_contains($permission, 'timetable');
    }

    /**
     * Check if permission should be granted to parent role
     */
    private function isParentPermission($permission)
    {
        $parentPermissions = [
            'admin.dashboard.index',
            'admin.students.show', // Only view their own child
            'admin.grades.index', // Only view their child's grades
            'admin.attendance.index', // Only view their child's attendance
            'admin.assignments.index', // Only view their child's assignments
            'admin.assignments.show',
            'admin.reports.students.index', // Only their child's reports
            'admin.communication.announcements.index',
            'admin.communication.messages.index',
        ];

        return in_array($permission, $parentPermissions);
    }

    /**
     * Check if permission should be granted to student role
     */
    private function isStudentPermission($permission)
    {
        $studentPermissions = [
            'admin.dashboard.index',
            'admin.grades.index', // Only view own grades
            'admin.attendance.index', // Only view own attendance
            'admin.assignments.index', // Only view own assignments
            'admin.assignments.show', // Only view own assignments
            'admin.timetable.index', // View own timetable
            'admin.timetable.show',
            'admin.communication.announcements.index',
            'admin.communication.messages.index',
        ];

        return in_array($permission, $studentPermissions);
    }

    /**
     * Check if permission should be granted to security role
     */
    private function isSecurityPermission($permission)
    {
        $securityPermissions = [
            'admin.dashboard.index',
            'admin.security.staff.index',
            'admin.security.visitors.index',
            'admin.security.visitors.form',
            'admin.security.visitors.enroll',
            'admin.security.visitors.show',
            'admin.security.visitors.check-in',
            'admin.security.visitors.check-out',
            'admin.security.incidents.index',
            'admin.security.incidents.form',
            'admin.security.incidents.enroll',
            'admin.security.incidents.show',
            'admin.attendance.index', // View attendance for security purposes
        ];

        return in_array($permission, $securityPermissions) ||
            str_contains($permission, 'security') ||
            str_contains($permission, 'visitors') ||
            str_contains($permission, 'incidents');
    }
}
