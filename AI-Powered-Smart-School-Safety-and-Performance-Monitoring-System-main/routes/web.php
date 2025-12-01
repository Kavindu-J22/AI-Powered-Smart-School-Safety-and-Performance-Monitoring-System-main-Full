<?php

use App\Enums\UserType;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Management\ParentController;
use App\Http\Controllers\Admin\Management\SchoolClassController;
use App\Http\Controllers\Admin\Management\SecurityStaffController;
use App\Http\Controllers\Admin\Management\StudentController;
use App\Http\Controllers\Admin\Management\SubjectController;
use App\Http\Controllers\Admin\Management\TeacherController;
use App\Http\Controllers\Admin\Management\TimetableController;
use App\Http\Controllers\Admin\Management\MarkController;
use App\Http\Controllers\Admin\TimetableViewerController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Setup\SettingsController as SetupSettingsController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\HomeworkController as StudentHomeworkController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => true, 'verify' => false]);

// Home route - redirect based on user type
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        // usertype is cast to UserType enum
        return match ($user->usertype) {
            UserType::STUDENT => Redirect::to('/student/dashboard'),
            UserType::TEACHER => Redirect::to('/teacher/dashboard'),
            UserType::PARENT => Redirect::to('/parent/dashboard'),
            UserType::SECURITY => Redirect::to('/security/dashboard'),
            default => Redirect::to('/admin/dashboard'),
        };
    }
    return Redirect::to('/login');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('dashboard')->name('dashboard.')->controller(DashboardController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });

        // Profile Management
        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/update', 'update')->name('update');
            Route::post('/change-password', 'changePassword')->name('change-password');
            Route::delete('/delete-image', 'deleteProfileImage')->name('delete-image');
            Route::post('/upload-image', 'uploadImage')->name('upload-image');
            Route::get('/stats', 'getProfileStats')->name('stats');
        });

        Route::prefix('management')->name('management.')->group(function () {
            // Students Management
            Route::prefix('students')->name('students.')->controller(StudentController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
                Route::get('/generate-code', 'generateCode')->name('generate-code');
                Route::get('/subjects-by-grade', 'getSubjectsByGrade')->name('subjects-by-grade');
                Route::get('/classes-by-grade', 'getClassesByGrade')->name('classes-by-grade');
                Route::post('/write-nfc', 'writeToNFC')->name('write-nfc');
                Route::get('/test-arduino', 'testArduino')->name('test-arduino');
            });

            // Teachers Management
            Route::prefix('teachers')->name('teachers.')->controller(TeacherController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
                Route::get('/generate-code', 'generateCode')->name('generate-code');
                Route::get('/subjects-by-level', 'getSubjectsByTeachingLevel')->name('subjects-by-level');
            });

            // Parents Management (View-only - Parents are created through Student management)
            Route::prefix('parents')->name('parents.')->controller(ParentController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/show/{id}', 'show')->name('show');
            });

            // Classes Management
            Route::prefix('classes')->name('classes.')->controller(SchoolClassController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
            });

            // Subjects Management
            Route::prefix('subjects')->name('subjects.')->controller(SubjectController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
            });

            // Security Staff Management
            Route::prefix('security')->name('security.')->controller(SecurityStaffController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
            });

            // Timetables Management
            Route::prefix('timetables')->name('timetables.')->controller(TimetableController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{timetable}', 'show')->name('show');
                Route::get('/{timetable}/edit', 'edit')->name('edit');
                Route::put('/{timetable}', 'update')->name('update');
                Route::delete('/{timetable}', 'destroy')->name('destroy');
                Route::get('/ajax/get-timetable', 'getTimetable')->name('get-timetable');
                Route::post('/create-time-slot', 'createTimeSlot')->name('create-time-slot');
                // Bulk operations for easier management
                Route::get('/bulk-assign', 'bulkAssignForm')->name('bulk-assign');
                Route::post('/bulk-assign', 'bulkAssignStore')->name('bulk-assign-store');
                Route::post('/quick-assign', 'quickAssign')->name('quick-assign');
                Route::delete('/bulk-delete', 'bulkDelete')->name('bulk-delete');
                Route::delete('/delete-time-slot/{timeSlot}', 'deleteTimeSlot')->name('delete-time-slot');
            });

            // Attendance Management
            Route::prefix('attendance')->name('attendance.')->controller(\App\Http\Controllers\Admin\Management\AttendanceController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/dashboard', 'dashboard')->name('dashboard');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{attendance}', 'show')->name('show');
                Route::get('/{attendance}/edit', 'edit')->name('edit');
                Route::put('/{attendance}', 'update')->name('update');
                Route::delete('/{attendance}', 'destroy')->name('destroy');
                Route::get('/report', 'report')->name('report');
                Route::get('/statistics', 'statistics')->name('statistics');
                Route::post('/search-student', 'searchStudent')->name('search-student');
                Route::post('/nfc-scan', 'nfcScan')->name('nfc-scan');
                Route::get('/student/{studentId}/percentage', 'studentPercentage')->name('student-percentage');

                // Device Management
                Route::get('/devices', 'devicesIndex')->name('devices.index');
                Route::get('/devices/list', 'devicesList')->name('devices.list');
                Route::post('/devices/register', 'devicesRegister')->name('devices.register');
                Route::post('/devices/sync', 'devicesSync')->name('devices.sync');
                Route::delete('/devices/remove', 'devicesRemove')->name('devices.remove');
            });

            // Marks Management
            Route::prefix('marks')->name('marks.')->controller(MarkController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{mark}', 'show')->name('show');
                Route::get('/{mark}/edit', 'edit')->name('edit');
                Route::put('/{mark}', 'update')->name('update');
                Route::delete('/{mark}', 'destroy')->name('destroy');
                Route::get('/student/details', 'getStudentDetails')->name('student.details');
            });

            // Lesson Management
            Route::prefix('lessons')->name('lessons.')->controller(\App\Http\Controllers\Admin\Management\LessonController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{lesson}', 'show')->name('show');
                Route::get('/{lesson}/edit', 'edit')->name('edit');
                Route::put('/{lesson}', 'update')->name('update');
                Route::delete('/{lesson}', 'destroy')->name('destroy');
                Route::get('/by-subject/{subject}', 'getBySubject')->name('by-subject');
            });

            // Homework Management
            Route::prefix('homework')->name('homework.')->controller(\App\Http\Controllers\Admin\Management\HomeworkController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/dashboard', 'dashboard')->name('dashboard');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{homework}', 'show')->name('show');
                Route::get('/{homework}/edit', 'edit')->name('edit');
                Route::put('/{homework}', 'update')->name('update');
                Route::post('/generate-questions', 'generateQuestions')->name('generate-questions');
                Route::post('/schedule-weekly', 'scheduleWeekly')->name('schedule-weekly');
                Route::post('/{homework}/assign', 'assignToStudents')->name('assign');

                // Submissions
                Route::get('/{homework}/submissions', [\App\Http\Controllers\Admin\Management\HomeworkSubmissionController::class, 'index'])->name('submissions.index');
                Route::get('/submissions/{submission}', [\App\Http\Controllers\Admin\Management\HomeworkSubmissionController::class, 'show'])->name('submissions.show');
                Route::post('/submissions/{submission}/submit', [\App\Http\Controllers\Admin\Management\HomeworkSubmissionController::class, 'submit'])->name('submissions.submit');
                Route::post('/{homework}/batch-grade', [\App\Http\Controllers\Admin\Management\HomeworkSubmissionController::class, 'batchGrade'])->name('batch-grade');
                Route::post('/evaluate-answer', [\App\Http\Controllers\Admin\Management\HomeworkSubmissionController::class, 'evaluateAnswer'])->name('evaluate-answer');
            });

            // Performance Tracking
            Route::prefix('performance')->name('performance.')->controller(\App\Http\Controllers\Admin\Management\PerformanceController::class)->group(function () {
                Route::get('/', 'dashboard')->name('dashboard');
                Route::get('/student/{student}', 'studentPerformance')->name('student');
                Route::get('/class/{class}', 'classPerformance')->name('class');
                Route::post('/trends', 'trends')->name('trends');
                Route::post('/heatmap', 'heatmap')->name('heatmap');
                Route::post('/weak-areas', 'weakAreas')->name('weak-areas');
            });

            // Monthly Reports
            Route::prefix('reports')->name('reports.')->controller(\App\Http\Controllers\Admin\Management\MonthlyReportController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{report}', 'show')->name('show');
                Route::post('/generate-class', 'generateForClass')->name('generate-class');
                Route::post('/generate-student', 'generateForStudent')->name('generate-student');
                Route::post('/send-to-parents', 'sendToParents')->name('send-to-parents');
                Route::post('/{report}/acknowledge', 'markAcknowledged')->name('acknowledge');
                Route::get('/{report}/download', 'downloadPdf')->name('download');
                Route::get('/statistics', 'statistics')->name('statistics');
            });

            // Audio Threat Detection
            Route::prefix('audio-threat')->name('audio-threat.')->controller(\App\Http\Controllers\Admin\Management\AudioThreatController::class)->group(function () {
                Route::get('/', 'dashboard')->name('dashboard');
                Route::get('/status', 'status')->name('status');
                Route::post('/analyze', 'analyze')->name('analyze');
                Route::post('/calibrate', 'calibrate')->name('calibrate');
                Route::post('/start-session', 'startSession')->name('start-session');
                Route::post('/stop-session', 'stopSession')->name('stop-session');
            });
        });

        // Placeholder routes for features in development
        Route::controller(PlaceholderController::class)->group(function () {
            // Academic Operations - Homework is now implemented above
            Route::get('grades', 'grades')->name('grades.index');
            // Route::get('attendance', 'attendance')->name('attendance.index'); // Removed - now implemented
            Route::get('timetable-viewer', [TimetableViewerController::class, 'index'])->name('timetable-viewer.index');

            // Security Additional
            Route::get('security/visitors', 'visitors')->name('security.visitors.index');
            Route::get('security/incidents', 'incidents')->name('security.incidents.index');

            // Reports
            Route::get('reports/students', 'studentReports')->name('reports.students.index');
            Route::get('reports/academic', 'academicReports')->name('reports.academic.index');
            Route::get('reports/attendance', 'attendanceReports')->name('reports.attendance.index');

            // Communication
            Route::get('communication/announcements', 'announcements')->name('communication.announcements.index');
            Route::get('communication/messages', 'messages')->name('communication.messages.index');

            // System Setup Additional
            Route::get('setup/school', 'schoolInfo')->name('setup.school.index');
            Route::get('setup/grade-levels', 'gradeLevels')->name('setup.grade-levels.index');
            Route::get('setup/academic-year', 'academicYear')->name('setup.academic-year.index');
            Route::get('setup/users', 'users')->name('setup.users.index');
        });

        Route::prefix('setup')->name('setup.')->group(function () {
            // Role Management
            Route::prefix('role')->name('role.')->controller(\App\Http\Controllers\Admin\Setup\RoleController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
            });

            // User Management
            Route::prefix('users')->name('users.')->controller(\App\Http\Controllers\Admin\Setup\UserController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form/{id?}', 'form')->name('form');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/delete/{id}', 'delete')->name('delete');
                Route::post('/enroll', 'enroll')->name('enroll');
            });

            Route::prefix('settings')->name('settings.')->controller(SetupSettingsController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/update', 'update')->name('update');

                // AJAX endpoints for settings page
                Route::post('/school-info', 'updateSchoolInfo')->name('school-info');
                Route::post('/theme', 'updateTheme')->name('theme');
                Route::post('/academic', 'updateAcademic')->name('academic');
                Route::post('/language', [SettingsController::class, 'updateLanguage'])->name('language');
            });
        });

        // Dashboard Settings Routes
        Route::prefix('settings')->name('settings.')->controller(SettingsController::class)->group(function () {
            Route::post('/update-school-info', 'updateSchoolInfo')->name('update-school-info');
            Route::post('/update-theme', 'updateTheme')->name('update-theme');
            Route::post('/update-academic', 'updateAcademic')->name('update-academic');
            Route::post('/update-social-media', 'updateSocialMedia')->name('update-social-media');
            Route::get('/theme-colors', 'getThemeColors')->name('theme-colors');
        });

        // Notification API routes
        Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/mark-as-read', 'markAsRead')->name('mark-as-read');
            Route::post('/mark-all-as-read', 'markAllAsRead')->name('mark-all-as-read');
            Route::get('/unread-count', 'getUnreadCount')->name('unread-count');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
    });

    // Student Routes
    Route::prefix('student')->name('student.')->middleware('usertype:' . UserType::STUDENT->value)->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard.index');

        // Homework routes
        Route::prefix('homework')->name('homework.')->group(function () {
            Route::get('/', [StudentHomeworkController::class, 'index'])->name('index');
            Route::get('/{submission}', [StudentHomeworkController::class, 'show'])->name('show');
            Route::get('/{submission}/results', [StudentHomeworkController::class, 'showResults'])->name('results');
            Route::post('/{submission}/submit', [StudentHomeworkController::class, 'submit'])->name('submit');
            Route::post('/{submission}/save-progress', [StudentHomeworkController::class, 'saveProgress'])->name('save-progress');
        });
    });

    // Teacher Routes (placeholder for future)
    Route::prefix('teacher')->name('teacher.')->middleware('usertype:' . UserType::TEACHER->value)->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('admin.dashboard.index');
        })->name('dashboard.index');
    });

    // Parent Routes (placeholder for future)
    Route::prefix('parent')->name('parent.')->middleware('usertype:' . UserType::PARENT->value)->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('admin.dashboard.index');
        })->name('dashboard.index');
    });

    // Security Routes (placeholder for future)
    Route::prefix('security')->name('security.')->middleware('usertype:' . UserType::SECURITY->value)->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('admin.dashboard.index');
        })->name('dashboard.index');
    });
});
