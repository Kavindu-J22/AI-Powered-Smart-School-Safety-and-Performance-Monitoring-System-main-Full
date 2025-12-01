<?php

return [
    [
        'items' => [
            getSideBarElement(
                'home',
                'Dashboard',
                'admin.dashboard.index',
                [],
                [],
                additional_permissions: [
                    'admin.dashboard.view.school-statistics',
                ]
            ),
        ],
    ],

    [
        'name' => 'Management',
        'items' => [
            getSideBarElement(
                'school',
                'Students',
                'admin.management.students.index',
                [],
                [
                    'admin.management.students.form',
                    'admin.management.students.show',
                    'admin.management.students.delete',
                    'admin.management.students.enroll',
                ],
                additional_permissions: [
                    'admin.management.students.edit',
                    'admin.management.students.promote',
                    'admin.management.students.transfer',
                ]
            ),
            getSideBarElement(
                'person',
                'Teachers',
                'admin.management.teachers.index',
                [],
                [
                    'admin.management.teachers.form',
                    'admin.management.teachers.show',
                    'admin.management.teachers.delete',
                    'admin.management.teachers.enroll',
                ],
                additional_permissions: [
                    'admin.management.teachers.edit',
                    'admin.management.teachers.assign-class',
                ]
            ),
            getSideBarElement(
                'family_restroom',
                'Parents',
                'admin.management.parents.index',
                [],
                [
                    'admin.management.parents.form',
                    'admin.management.parents.show',
                    'admin.management.parents.delete',
                    'admin.management.parents.enroll',
                ],
                additional_permissions: [
                    'admin.management.parents.edit',
                    'admin.management.parents.link-student',
                ]
            ),
            getSideBarElement(
                'class',
                'Classes',
                'admin.management.classes.index',
                [],
                [
                    'admin.management.classes.form',
                    'admin.management.classes.show',
                    'admin.management.classes.delete',
                    'admin.management.classes.enroll',
                ],
                additional_permissions: [
                    'admin.management.classes.edit',
                    'admin.management.classes.assign-teacher',
                    'admin.management.classes.manage-students',
                ]
            ),
            getSideBarElement(
                'subject',
                'Subjects',
                'admin.management.subjects.index',
                [],
                [
                    'admin.management.subjects.form',
                    'admin.management.subjects.show',
                    'admin.management.subjects.delete',
                    'admin.management.subjects.enroll',
                ],
                additional_permissions: [
                    'admin.management.subjects.edit',
                ]
            ),
            getSideBarElement(
                'security',
                'Security Staff',
                'admin.management.security.index',
                [],
                [
                    'admin.management.security.form',
                    'admin.management.security.show',
                    'admin.management.security.delete',
                    'admin.management.security.enroll',
                ],
                additional_permissions: [
                    'admin.management.security.edit',
                ]
            ),
            getSideBarElement(
                'assessment',
                'Marks',
                'admin.management.marks.index',
                [],
                [
                    'admin.management.marks.form',
                    'admin.management.marks.show',
                    'admin.management.marks.delete',
                ],
                additional_permissions: [
                    'admin.management.marks.edit',
                ]
            ),
        ],
    ],

    [
        'name' => 'Academic Management',
        'items' => [
            getSideBarElement(
                'assignment',
                'Homework',
                'admin.management.homework.dashboard',
                [],
                [
                    'admin.management.homework.index',
                    'admin.management.homework.create',
                    'admin.management.homework.show',
                    'admin.management.homework.store',
                ],
                additional_permissions: [
                    'admin.management.homework.generate-questions',
                    'admin.management.homework.schedule-weekly',
                    'admin.management.homework.assign',
                ]
            ),
            getSideBarElement(
                'analytics',
                'Performance',
                'admin.management.performance.dashboard',
                [],
                [
                    'admin.management.performance.student',
                    'admin.management.performance.class',
                ],
                additional_permissions: [
                    'admin.management.performance.trends',
                    'admin.management.performance.heatmap',
                ]
            ),
            getSideBarElement(
                'summarize',
                'Monthly Reports',
                'admin.management.reports.index',
                [],
                [
                    'admin.management.reports.show',
                    'admin.management.reports.download',
                ],
                additional_permissions: [
                    'admin.management.reports.generate-class',
                    'admin.management.reports.generate-student',
                    'admin.management.reports.send-to-parents',
                ]
            ),
            getSideBarElement(
                'grade',
                'Grades',
                'admin.grades.index',
                [],
                [
                    'admin.grades.form',
                    'admin.grades.show',
                    'admin.grades.delete',
                    'admin.grades.enroll',
                ],
                additional_permissions: [
                    'admin.grades.edit',
                    'admin.grades.report-card',
                ]
            ),
            getSideBarElement(
                'calendar_today',
                'Attendance',
                'admin.attendance.index',
                [],
                [
                    'admin.attendance.form',
                    'admin.attendance.show',
                    'admin.attendance.mark',
                ],
                additional_permissions: [
                    'admin.attendance.edit',
                    'admin.attendance.reports',
                ]
            ),
            getSideBarElement(
                'schedule',
                'Timetable',
                'admin.timetable.index',
                [],
                [
                    'admin.timetable.form',
                    'admin.timetable.show',
                    'admin.timetable.delete',
                    'admin.timetable.enroll',
                ],
                additional_permissions: [
                    'admin.timetable.edit',
                ]
            ),
        ],
    ],

    [
        'name' => 'Reports',
        'items' => [
            getSideBarElement(
                'assessment',
                'Student Reports',
                'admin.reports.students.index',
                [],
                [
                    'admin.reports.students.generate',
                    'admin.reports.students.export',
                ]
            ),
            getSideBarElement(
                'trending_up',
                'Academic Reports',
                'admin.reports.academic.index',
                [],
                [
                    'admin.reports.academic.generate',
                    'admin.reports.academic.export',
                ]
            ),
            getSideBarElement(
                'event_note',
                'Attendance Reports',
                'admin.reports.attendance.index',
                [],
                [
                    'admin.reports.attendance.generate',
                    'admin.reports.attendance.export',
                ]
            ),
        ],
    ],

    [
        'name' => 'Security',
        'items' => [
            getSideBarElement(
                'person_add',
                'Visitors',
                'admin.security.visitors.index',
                [],
                [
                    'admin.security.visitors.form',
                    'admin.security.visitors.show',
                    'admin.security.visitors.delete',
                    'admin.security.visitors.enroll',
                ],
                additional_permissions: [
                    'admin.security.visitors.edit',
                    'admin.security.visitors.check-in',
                    'admin.security.visitors.check-out',
                ]
            ),
            getSideBarElement(
                'report_problem',
                'Security Incidents',
                'admin.security.incidents.index',
                [],
                [
                    'admin.security.incidents.form',
                    'admin.security.incidents.show',
                    'admin.security.incidents.delete',
                    'admin.security.incidents.enroll',
                ],
                additional_permissions: [
                    'admin.security.incidents.edit',
                ]
            ),
        ],
    ],

    [
        'name' => 'Communication',
        'items' => [
            getSideBarElement(
                'notifications',
                'Announcements',
                'admin.communication.announcements.index',
                [],
                [
                    'admin.communication.announcements.form',
                    'admin.communication.announcements.show',
                    'admin.communication.announcements.delete',
                    'admin.communication.announcements.enroll',
                ],
                additional_permissions: [
                    'admin.communication.announcements.edit',
                ]
            ),
            getSideBarElement(
                'mail',
                'Messages',
                'admin.communication.messages.index',
                [],
                [
                    'admin.communication.messages.form',
                    'admin.communication.messages.show',
                    'admin.communication.messages.delete',
                    'admin.communication.messages.send',
                ],
                additional_permissions: [
                    'admin.communication.messages.edit',
                ]
            ),
        ],
    ],

    [
        'name' => 'System Setup',
        'items' => [
            getSideBarElement(
                'school',
                'School Information',
                'admin.setup.school.index',
                [],
                [
                    'admin.setup.school.form',
                    'admin.setup.school.show',
                    'admin.setup.school.enroll',
                ],
                additional_permissions: [
                    'admin.setup.school.edit',
                ]
            ),
            getSideBarElement(
                'class',
                'Grade Levels',
                'admin.setup.grade-levels.index',
                [],
                [
                    'admin.setup.grade-levels.form',
                    'admin.setup.grade-levels.show',
                    'admin.setup.grade-levels.delete',
                    'admin.setup.grade-levels.enroll',
                ],
                additional_permissions: [
                    'admin.setup.grade-levels.edit',
                ]
            ),
            getSideBarElement(
                'event',
                'Academic Year',
                'admin.setup.academic-year.index',
                [],
                [
                    'admin.setup.academic-year.form',
                    'admin.setup.academic-year.show',
                    'admin.setup.academic-year.delete',
                    'admin.setup.academic-year.enroll',
                ],
                additional_permissions: [
                    'admin.setup.academic-year.edit',
                ]
            ),
            getSideBarElement(
                'admin_panel_settings',
                'Roles & Permissions',
                'admin.setup.role.index',
                [],
                [
                    'admin.setup.role.form',
                    'admin.setup.role.show',
                    'admin.setup.role.delete',
                    'admin.setup.role.enroll',
                ],
                additional_permissions: [
                    'admin.setup.role.edit',
                ]
            ),
            getSideBarElement(
                'account_circle',
                'Users',
                'admin.setup.users.index',
                [],
                [
                    'admin.setup.users.form',
                    'admin.setup.users.show',
                    'admin.setup.users.delete',
                    'admin.setup.users.enroll',
                ],
                additional_permissions: [
                    'admin.setup.users.edit',
                ]
            ),
            getSideBarElement(
                'settings',
                'Settings',
                'admin.setup.settings.index',
                [],
                [
                    'admin.setup.settings.update',
                ],
                additional_permissions: [
                    'admin.setup.settings.general',
                    'admin.setup.settings.academic',
                    'admin.setup.settings.security',
                ]
            ),
        ],
    ],
];
