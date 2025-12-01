@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/dashboard.css'])
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl me-3 bg-white rounded-circle">
                                @if($student->profile_photo)
                                <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="Profile" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                <i class="material-symbols-rounded text-primary" style="font-size: 40px;">person</i>
                                @endif
                            </div>
                            <div class="text-white">
                                <h4 class="mb-0 text-white">Welcome back, {{ $student->first_name }}!</h4>
                                <p class="mb-0 opacity-8">Grade {{ $student->grade_level }} - {{ $student->schoolClass->class_name ?? 'No Class' }}</p>
                                <small class="opacity-6">Student Code: {{ $student->student_code }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Attendance</p>
                                    <h5 class="font-weight-bolder">{{ $attendanceStats['attendance_percentage'] }}%</h5>
                                    <p class="mb-0 text-sm">
                                        <span class="text-success">{{ $attendanceStats['present_days'] }}</span> present days
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">event_available</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Average Marks</p>
                                    <h5 class="font-weight-bolder">{{ $overallPerformance['average'] }}%</h5>
                                    <p class="mb-0 text-sm">
                                        <span class="text-info">{{ $overallPerformance['total_exams'] }}</span> exams taken
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">school</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pending Homework</p>
                                    <h5 class="font-weight-bolder">{{ $pendingHomework->count() }}</h5>
                                    <p class="mb-0 text-sm">
                                        <span class="text-warning">Tasks</span> to complete
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">assignment</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Classes</p>
                                    <h5 class="font-weight-bolder">{{ $todayTimetable->count() }}</h5>
                                    <p class="mb-0 text-sm">
                                        <span class="text-primary">{{ now()->format('l') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">schedule</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Schedule -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-rounded me-2">today</i>Today's Schedule</h6>
                    </div>
                    <div class="card-body p-3">
                        @forelse($todayTimetable as $slot)
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md me-3">
                                <i class="material-symbols-rounded text-white opacity-10">class</i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 text-sm">{{ $slot->subject->subject_name ?? 'N/A' }}</h6>
                                <p class="text-xs text-secondary mb-0">
                                    @if($slot->timeSlot)
                                    {{ \Carbon\Carbon::parse($slot->timeSlot->start_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($slot->timeSlot->end_time)->format('h:i A') }}
                                    @endif
                                    @if($slot->teacher)
                                    | {{ $slot->teacher->first_name }} {{ $slot->teacher->last_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted py-4">No classes scheduled for today</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Marks -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-rounded me-2">grade</i>Recent Marks</h6>
                    </div>
                    <div class="card-body p-3">
                        @forelse($recentMarks as $mark)
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon icon-shape icon-sm {{ $mark->marks >= 50 ? 'bg-gradient-success' : 'bg-gradient-danger' }} shadow text-center border-radius-md me-3">
                                <i class="material-symbols-rounded text-white opacity-10">{{ $mark->marks >= 50 ? 'check_circle' : 'cancel' }}</i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 text-sm">{{ $mark->subject->subject_name ?? 'N/A' }}</h6>
                                <p class="text-xs text-secondary mb-0">{{ $mark->exam_type ?? 'Exam' }}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-gradient-{{ $mark->marks >= 75 ? 'success' : ($mark->marks >= 50 ? 'warning' : 'danger') }}">
                                    {{ $mark->marks }}%
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted py-4">No marks recorded yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Homework -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-rounded me-2">assignment</i>Pending Homework</h6>
                    </div>
                    <div class="card-body p-3">
                        @forelse($pendingHomework as $submission)
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-gray-100 border-radius-lg">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm bg-gradient-warning shadow text-center border-radius-md me-3">
                                    <i class="material-symbols-rounded text-white opacity-10">pending</i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-sm">{{ $submission->homework->title ?? 'Homework' }}</h6>
                                    <p class="text-xs text-secondary mb-0">
                                        {{ $submission->homework->subject->subject_name ?? 'N/A' }} |
                                        Due: {{ $submission->homework->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                            <span class="badge bg-gradient-{{ $submission->homework->due_date->isPast() ? 'danger' : 'warning' }}">
                                {{ $submission->homework->due_date->isPast() ? 'Overdue' : 'Pending' }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-muted py-4">No pending homework - Great job!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection