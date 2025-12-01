@extends('admin.layouts.app')

@section('title', pageTitle())

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-dark mb-0 d-flex align-items-center justify-content-center btn-back-auto"
                                        href="{{ route('admin.management.teachers.index') }}">
                                        <i class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>Back
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0 d-flex align-items-center">
                                                <i class="material-symbols-rounded me-2 icon-size-sm">person</i>
                                                Teacher Profile
                                            </h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-xl rounded-circle bg-gradient-info mx-auto mb-3">
                                                <span
                                                    class="text-white text-lg">{{ strtoupper(substr($teacher->first_name, 0, 1) . substr($teacher->last_name, 0, 1)) }}</span>
                                            </div>
                                            <h5 class="mb-1">{{ $teacher->full_name }}</h5>
                                            <p class="text-secondary mb-2">{{ $teacher->teacher_code }}</p>
                                            <div class="mb-2">
                                                @php
                                                    $teachingLevelBadges = [
                                                        'Primary' => 'bg-gradient-success',
                                                        'Secondary' => 'bg-gradient-info',
                                                        'Arts' => 'bg-gradient-warning',
                                                        'Commerce' => 'bg-gradient-primary',
                                                        'Science' => 'bg-gradient-danger',
                                                        'Technology' => 'bg-gradient-secondary',
                                                    ];
                                                    $badgeClass =
                                                        $teachingLevelBadges[$teacher->teaching_level] ??
                                                        'bg-gradient-info';
                                                @endphp
                                                <span
                                                    class="badge {{ $badgeClass }} badge-sm me-1">{{ $teacher->teaching_level }}</span>
                                                <span
                                                    class="badge {{ $teacher->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }} badge-sm">
                                                    {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6 class="mb-0 d-flex align-items-center">
                                                <i class="material-symbols-rounded me-2 icon-size-sm">info</i>
                                                Personal Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Full Name:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->full_name }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Teacher Code:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->teacher_code }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Date of Birth:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->date_of_birth ? $teacher->date_of_birth->format('M d, Y') : 'Not provided' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Gender:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->gender ?? 'Not specified' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Phone:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->phone ?? 'Not provided' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Email:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->user->email ?? 'Not provided' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">NIC Number:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->nic_number ?? 'Not provided' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Hire Date:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->hire_date ? $teacher->hire_date->format('M d, Y') : 'Not provided' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Address:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->address ?? 'Not provided' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6 class="mb-0 d-flex align-items-center">
                                                <i class="material-symbols-rounded me-2 icon-size-sm">school</i>
                                                Professional Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Specialization:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->specialization ?? 'Not specified' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Experience:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            @if ($teacher->experience_years)
                                                                <span
                                                                    class="badge bg-gradient-warning badge-sm">{{ floor($teacher->experience_years) }}
                                                                    year{{ floor($teacher->experience_years) != 1 ? 's' : '' }}</span>
                                                            @else
                                                                Not specified
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Qualifications:</label>
                                                        <p class="text-dark font-weight-bold" style="padding-left: 8px;">
                                                            {{ $teacher->qualifications ?? 'Not provided' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($teacher->subjects->count() > 0)
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0 d-flex align-items-center">
                                                    <i class="material-symbols-rounded me-2 icon-size-sm">subject</i>
                                                    Teaching Subjects
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($teacher->subjects as $subject)
                                                        <div class="col-md-4 mb-3">
                                                            <div class="border rounded p-3 text-center d-flex flex-column"
                                                                style="height: 180px; min-height: 180px;">
                                                                <h6 class="mb-2 text-truncate"
                                                                    title="{{ $subject->subject_name }}">
                                                                    {{ $subject->subject_name }}</h6>
                                                                <p class="mb-2"><strong>Code:</strong>
                                                                    {{ $subject->subject_code }}</p>
                                                                <div class="mt-auto">
                                                                    <span
                                                                        class="badge bg-gradient-success badge-sm mb-2">{{ $subject->category }}</span>
                                                                    @if ($subject->credits)
                                                                        <br><small
                                                                            class="text-secondary">{{ $subject->credits }}
                                                                            Credits</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Show assigned class if teacher has one --}}
                                    @if (isset($teacher->assignedClass) && $teacher->assignedClass)
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0 d-flex align-items-center">
                                                    <i class="material-symbols-rounded me-2 icon-size-sm">class</i>
                                                    Assigned Class
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="border rounded p-3">
                                                    <h5 class="mb-2">{{ $teacher->assignedClass->class_name }}</h5>
                                                    <p class="mb-1"><strong>Grade Level:</strong>
                                                        {{ $teacher->assignedClass->grade_level }}</p>
                                                    <p class="mb-1"><strong>Room:</strong>
                                                        {{ $teacher->assignedClass->room_number ?? 'Not assigned' }}</p>
                                                    <p class="mb-1"><strong>Students:</strong>
                                                        {{ $teacher->assignedClass->students->count() }}</p>
                                                    <p class="mb-0"><strong>Capacity:</strong>
                                                        {{ $teacher->assignedClass->capacity ?? 'Not set' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if (checkPermission('admin.management.teachers.edit'))
                                <div class="text-end mt-4">
                                    <a href="{{ route('admin.management.teachers.form', ['id' => $teacher->teacher_id]) }}"
                                        class="btn btn-primary">
                                        <i class="material-symbols-rounded me-1">edit</i>Edit Teacher
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
