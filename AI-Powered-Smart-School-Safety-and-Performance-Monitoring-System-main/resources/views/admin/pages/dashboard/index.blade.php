@extends('admin.layouts.app')

@section('css')
    @vite('resources/css/admin/dashboard.css')
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid py-2">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center p-3">
                        <div class="ms-0">
                            <h3 class="mb-0 h4 dashboard-title">{{ __('school.school_management_dashboard') }}</h3>

                            <!-- single-line icon + text; icon kept at text-size (no size classes) -->
                            <p class="mb-0 text-muted d-flex align-items-center">
                                <i class="material-symbols-rounded header-icon me-2" aria-hidden="true">school</i>
                                <span>{{ __('school.monitor_school_performance') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stat-card h-100" style="--index: 0;">
                        <div class="card-body p-3">
                            <div class="text-center">
                                <div class="stat-icon mx-auto floating-element">
                                    <i class="material-symbols-rounded text-lg">school</i>
                                </div>
                                <h4 class="mb-0 stat-number">{{ number_format($stats['total_students']) }}</h4>
                                <p class="text-sm mb-0 text-capitalize text-muted">{{ __('common.total_students') }}</p>
                                <small class="text-success">
                                    <i class="material-symbols-rounded text-xs">trending_up</i>
                                    {{ $stats['active_students'] }} {{ __('common.active') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stat-card h-100" style="--index: 1;">
                        <div class="card-body p-3">
                            <div class="text-center">
                                <div class="stat-icon mx-auto floating-element">
                                    <i class="material-symbols-rounded text-lg">person</i>
                                </div>
                                <h4 class="mb-0 stat-number">{{ number_format($stats['total_teachers']) }}</h4>
                                <p class="text-sm mb-0 text-capitalize text-muted">{{ __('common.total_teachers') }}</p>
                                <small class="text-success">
                                    <i class="material-symbols-rounded text-xs">check_circle</i>
                                    {{ $stats['active_teachers'] }} {{ __('common.active') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stat-card h-100" style="--index: 2;">
                        <div class="card-body p-3">
                            <div class="text-center">
                                <div class="stat-icon mx-auto floating-element">
                                    <i class="material-symbols-rounded text-lg">family_restroom</i>
                                </div>
                                <h4 class="mb-0 stat-number">{{ number_format($stats['total_parents']) }}</h4>
                                <p class="text-sm mb-0 text-capitalize text-muted">{{ __('common.total_parents') }}</p>
                                <small class="text-success">
                                    <i class="material-symbols-rounded text-xs">people</i>
                                    {{ __('common.registered') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card stat-card h-100" style="--index: 3;">
                        <div class="card-body p-3">
                            <div class="text-center">
                                <div class="stat-icon mx-auto floating-element">
                                    <i class="material-symbols-rounded text-lg">meeting_room</i>
                                </div>
                                <h4 class="mb-0 stat-number">{{ number_format($stats['total_classes']) }}</h4>
                                <p class="text-sm mb-0 text-capitalize text-muted">{{ __('common.total_classes') }}</p>
                                <small class="text-success">
                                    <i class="material-symbols-rounded text-xs">book</i>
                                    {{ $stats['total_subjects'] }} {{ __('common.subjects') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stat-card" style="--index: 4;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="width: 50px; height: 50px;">
                                    <i class="material-symbols-rounded">security</i>
                                </div>
                                <div>
                                    <p class="text-sm mb-0 text-capitalize text-muted">{{ __('common.security_staff') }}
                                    </p>
                                    <h4 class="mb-0 stat-number">{{ $stats['total_security_staff'] }}</h4>
                                    <small class="text-success">{{ $stats['active_security_staff'] }}
                                        {{ __('common.active') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stat-card" style="--index: 5;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="width: 50px; height: 50px;">
                                    <i class="material-symbols-rounded">person_add</i>
                                </div>
                                <div>
                                    <p class="text-sm mb-0 text-capitalize text-muted">{{ __('school.new_enrollments') }}
                                    </p>
                                    <h4 class="mb-0 stat-number">{{ $recent_enrollments }}</h4>
                                    <small class="text-success">{{ __('school.last_30_days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-sm-12">
                    <div class="card stat-card" style="--index: 6;">
                        <div class="card-body p-3">
                            <h6 class="quick-action">Quick Actions</h6>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="{{ route('admin.management.students.form') }}" class="quick-action-btn btn btn-sm"
                                    style="--index: 0; flex: 1; min-width: 120px;">
                                    <i class="material-symbols-rounded me-1">person_add</i>Add Student
                                </a>
                                <a href="{{ route('admin.management.teachers.form') }}" class="quick-action-btn btn btn-sm"
                                    style="--index: 1; flex: 1; min-width: 120px;">
                                    <i class="material-symbols-rounded me-1">school</i>Add Teacher
                                </a>
                                <a href="#" class="quick-action-btn btn btn-sm"
                                    style="--index: 1; flex: 1; min-width: 120px;">

                                    <i class="material-symbols-rounded me-1">class</i>Manage Classes
                                </a>
                                <a href="#" class="quick-action-btn btn btn-sm"
                                    style="--index: 1; flex: 1; min-width: 120px;">
                                    <i class="material-symbols-rounded me-1">book</i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="row">
                <!-- Grade Distribution Chart -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Grade Distribution</h6>
                            <p class="text-sm mb-0">Students by grade level</p>
                        </div>
                        <div class="card-body">
                            <div class="grade-chart">
                                @foreach ($grade_distribution as $grade)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-sm">Grade {{ $grade->grade_level }}</span>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 100px; height: 6px;">
                                                <div class="progress-bar bg-gradient-primary"
                                                    style="width: {{ $stats['total_students'] > 0 ? ($grade->count / $stats['total_students']) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                            <span class="text-sm fw-bold">{{ $grade->count }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classes Overview -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Classes Overview</h6>
                            <p class="text-sm mb-0">Student count per class</p>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @foreach ($classes_with_counts as $class)
                                <div class="recent-activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-sm">{{ $class->full_name }}</h6>
                                            <small class="text-muted">Grade {{ $class->grade_level }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-gradient-primary">{{ $class->students_count }}
                                                students</span>
                                            @if ($class->capacity)
                                                <small class="text-muted d-block">Capacity: {{ $class->capacity }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Recent Student Activities</h6>
                            <p class="text-sm mb-0">Latest enrolled students</p>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @foreach ($recent_students as $student)
                                <div class="recent-activity-item">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-gradient-primary rounded-circle me-3">
                                            <span class="text-white text-xs fw-bold">
                                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 text-sm">{{ $student->full_name }}</h6>
                                            <small class="text-muted">
                                                Grade {{ $student->grade_level }}
                                                @if ($student->schoolClass)
                                                    - {{ $student->schoolClass->class_name }}
                                                @endif
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="material-symbols-rounded text-xs">schedule</i>
                                                {{ $student->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span
                                                class="badge badge-sm bg-gradient-{{ $student->is_active ? 'success' : 'secondary' }}">
                                                {{ $student->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- School Settings & Theme Customization -->


            @include('admin.layouts.inner-footer')
        </div>

        <!-- Back to Top Button -->
        <button class="back-to-top" id="backToTop" aria-label="Back to top">
            <i class="material-symbols-rounded">arrow_upward</i>
        </button>
    </main>
@endsection

@section('js')
    @vite('resources/js/admin/dashboard.js')
    <script>
        // Back to Top Button
        const backToTopBtn = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
@endsection
