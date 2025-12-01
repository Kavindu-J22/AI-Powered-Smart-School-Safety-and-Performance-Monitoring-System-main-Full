<?php

return [
    [
        'items' => [
            getSideBarElement('home', 'Dashboard', 'admin.dashboard.index'),
        ],
    ],
    [
        'name' => 'Management',
        'items' => [
            getSideBarElement('school', 'Students', 'admin.management.students.index'),
            getSideBarElement('person', 'Teachers', 'admin.management.teachers.index'),
            getSideBarElement('family_restroom', 'Parents', 'admin.management.parents.index'),
            getSideBarElement('class', 'Classes', 'admin.management.classes.index'),
            getSideBarElement('subject', 'Subjects', 'admin.management.subjects.index'),
            getSideBarElement('schedule', 'Timetables', 'admin.management.timetables.index'),
            getSideBarElement('security', 'Security Staff', 'admin.management.security.index'),
            getSideBarElement('fact_check', 'Attendance', 'admin.management.attendance.dashboard'),
            getSideBarElement('assessment', 'Marks', 'admin.management.marks.index'),
        ],
    ],
    [
        'name' => 'Academic Operations',
        'items' => [
            getSideBarElement('menu_book', 'Lessons', 'admin.management.lessons.index'),
            getSideBarElement('assignment', 'Homework', 'admin.management.homework.dashboard'),
            getSideBarElement('analytics', 'Performance', 'admin.management.performance.dashboard'),
            getSideBarElement('summarize', 'Monthly Reports', 'admin.management.reports.index'),
            getSideBarElement('grade', 'Grades', 'admin.grades.index'),
            getSideBarElement('schedule', 'Timetable Viewer', 'admin.timetable-viewer.index'),
        ],
    ],
    [
        'name' => 'Security',
        'items' => [
            getSideBarElement('mic', 'Audio Threat Detection', 'admin.management.audio-threat.dashboard'),
            getSideBarElement('person_add', 'Visitors', 'admin.security.visitors.index'),
            getSideBarElement('report_problem', 'Incidents', 'admin.security.incidents.index'),
        ],
    ],
    [
        'name' => 'Reports',
        'items' => [
            getSideBarElement('assessment', 'Student Reports', 'admin.reports.students.index'),
            getSideBarElement('trending_up', 'Academic Reports', 'admin.reports.academic.index'),
            getSideBarElement('event_note', 'Attendance Reports', 'admin.reports.attendance.index'),
        ],
    ],
    [
        'name' => 'Communication',
        'items' => [
            getSideBarElement('notifications', 'Announcements', 'admin.communication.announcements.index'),
            getSideBarElement('mail', 'Messages', 'admin.communication.messages.index'),
        ],
    ],
    [
        'name' => 'System Setup',
        'items' => [
            getSideBarElement('school', 'School Information', 'admin.setup.school.index'),
            getSideBarElement('class', 'Grade Levels', 'admin.setup.grade-levels.index'),
            getSideBarElement('event', 'Academic Year', 'admin.setup.academic-year.index'),
            getSideBarElement('admin_panel_settings', 'Roles & Permissions', 'admin.setup.role.index'),
            getSideBarElement('account_circle', 'Users', 'admin.setup.users.index'),
            getSideBarElement('settings', 'Settings', 'admin.setup.settings.index'),
        ],
    ],
];
