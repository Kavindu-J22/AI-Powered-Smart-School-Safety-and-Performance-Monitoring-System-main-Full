<?php

function getSideBarElement($icon, $text, $route, $route_params = [], $other_selected_routes = [], $additional_permissions = [])
{
    return [
        'icon' => $icon,
        'text' => $text,
        'route' => $route,
        'route_params' => $route_params,
        'other_selected_routes' => $other_selected_routes,
        'additional_permissions' => $additional_permissions,
    ];
}

function formatPermissionString($route)
{
    // Convert route name to permission string
    // Example: admin.students.index -> admin students index
    return str_replace('.', ' ', $route);
}

function translateSidebarText($text)
{
    // Map English text to translation keys
    $translationMap = [
        // Section names
        'Management' => __('common.management'),
        'Academic Operations' => __('common.academic_operations'),
        'Security' => __('common.security'),
        'Reports' => __('common.reports'),
        'Communication' => __('common.communication'),
        'System Setup' => __('common.system_setup'),

        // Menu items
        'Dashboard' => __('common.dashboard'),
        'Students' => __('common.students'),
        'Teachers' => __('common.teachers'),
        'Parents' => __('common.parents'),
        'Classes' => __('common.classes'),
        'Subjects' => __('common.subjects'),
        'Security Staff' => __('common.security_staff'),
        'Assignments' => __('common.assignments'),
        'Grades' => __('common.grades'),
        'Attendance' => __('common.attendance'),
        'Timetable' => __('common.timetable'),
        'Timetable Viewer' => __('common.timetable_viewer'),
        'Visitors' => __('common.visitors'),
        'Incidents' => __('common.incidents'),
        'Student Reports' => __('common.student_reports'),
        'Academic Reports' => __('common.academic_reports'),
        'Attendance Reports' => __('common.attendance_reports'),
        'Announcements' => __('common.announcements'),
        'Messages' => __('common.messages'),
        'School Information' => __('school.school_information'),
        'Grade Levels' => __('common.grade_levels'),
        'Academic Year' => __('school.academic_year'),
        'Roles & Permissions' => __('common.roles_permissions'),
        'Users' => __('common.users'),
        'Settings' => __('common.settings'),
    ];

    return $translationMap[$text] ?? $text;
}
