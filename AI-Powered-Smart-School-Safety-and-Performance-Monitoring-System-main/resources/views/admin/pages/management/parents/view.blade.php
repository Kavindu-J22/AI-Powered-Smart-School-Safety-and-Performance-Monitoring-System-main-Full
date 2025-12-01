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
                                    <a class="btn btn-outline-dark mb-0 btn-back-auto"
                                        href="{{ route('admin.management.parents.index') }}">
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
                                                Parent Profile
                                            </h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-xl rounded-circle bg-gradient-primary mx-auto mb-3">
                                                <span
                                                    class="text-white text-lg">{{ strtoupper(substr($parent->first_name, 0, 1) . substr($parent->last_name, 0, 1)) }}</span>
                                            </div>
                                            <h5 class="mb-1">{{ $parent->full_name }}</h5>
                                            <p class="text-secondary mb-2">{{ $parent->parent_code }}</p>
                                            <div class="mb-2">
                                                <span
                                                    class="badge {{ $parent->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }} badge-sm">
                                                    {{ $parent->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                @if ($parent->is_emergency_contact)
                                                    <span class="badge bg-gradient-warning badge-sm ms-1">Emergency
                                                        Contact</span>
                                                @endif
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
                                                        <p class="text-dark font-weight-bold">{{ $parent->full_name }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Parent Code:</label>
                                                        <p class="text-dark font-weight-bold">{{ $parent->parent_code }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Gender:</label>
                                                        <p class="text-dark font-weight-bold">
                                                            @if ($parent->gender == 'M')
                                                                <span class="badge bg-gradient-info badge-sm">Male</span>
                                                            @elseif($parent->gender == 'F')
                                                                <span class="badge bg-gradient-pink badge-sm">Female</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-gradient-secondary badge-sm">{{ $parent->gender }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Relationship Type:</label>
                                                        <p class="text-dark font-weight-bold">
                                                            <span
                                                                class="badge bg-gradient-primary badge-sm">{{ $parent->relationship_type }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($parent->date_of_birth)
                                                    <div class="col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Date of Birth:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                {{ $parent->date_of_birth->format('M d, Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($parent->nationality)
                                                    <div class="col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nationality:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                {{ $parent->nationality }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6 class="mb-0 d-flex align-items-center">
                                                <i class="material-symbols-rounded me-2 icon-size-sm">contact_phone</i>
                                                Contact Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @if ($parent->mobile_phone)
                                                    <div class="col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Mobile Phone:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                <a href="tel:{{ $parent->mobile_phone }}"
                                                                    class="text-decoration-none">
                                                                    {{ $parent->mobile_phone }}
                                                                </a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($parent->work_phone)
                                                    <div class="col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Work Phone:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                <a href="tel:{{ $parent->work_phone }}"
                                                                    class="text-decoration-none">
                                                                    {{ $parent->work_phone }}
                                                                </a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($parent->email)
                                                    <div class="col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Email:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                <a href="mailto:{{ $parent->email }}"
                                                                    class="text-decoration-none">
                                                                    {{ $parent->email }}
                                                                </a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($parent->address_line1)
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Address:</label>
                                                            <p class="text-dark font-weight-bold">
                                                                {{ $parent->address_line1 }}
                                                                @if ($parent->address_line2)
                                                                    , {{ $parent->address_line2 }}
                                                                @endif
                                                                @if ($parent->city)
                                                                    <br>{{ $parent->city }}
                                                                @endif
                                                                @if ($parent->state)
                                                                    , {{ $parent->state }}
                                                                @endif
                                                                @if ($parent->postal_code)
                                                                    {{ $parent->postal_code }}
                                                                @endif
                                                                @if ($parent->country)
                                                                    <br>{{ $parent->country }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($parent->occupation || $parent->workplace)
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0 d-flex align-items-center">
                                                    <i class="material-symbols-rounded me-2 icon-size-sm">work</i>
                                                    Professional Information
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @if ($parent->occupation)
                                                        <div class="col-sm-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Occupation:</label>
                                                                <p class="text-dark font-weight-bold">
                                                                    {{ $parent->occupation }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($parent->workplace)
                                                        <div class="col-sm-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Workplace:</label>
                                                                <p class="text-dark font-weight-bold">
                                                                    {{ $parent->workplace }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($parent->students->count() > 0)
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h6 class="mb-0 d-flex align-items-center">
                                                    <i class="material-symbols-rounded me-2 icon-size-sm">school</i>
                                                    Associated Students
                                                    <span
                                                        class="badge bg-gradient-info badge-sm ms-2">{{ $parent->students->count() }}
                                                        Student{{ $parent->students->count() > 1 ? 's' : '' }}</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($parent->students as $student)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card border">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <div
                                                                            class="avatar avatar-sm rounded-circle bg-gradient-success me-2">
                                                                            <span
                                                                                class="text-white text-xs">{{ strtoupper(substr($student->first_name, 0, 1)) }}</span>
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="mb-0">{{ $student->full_name }}
                                                                            </h6>
                                                                            <small
                                                                                class="text-muted">{{ $student->student_code }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <p class="mb-1"><strong>Grade:</strong>
                                                                        {{ $student->grade_level }}</p>
                                                                    @if ($student->schoolClass)
                                                                        <p class="mb-1"><strong>Class:</strong>
                                                                            {{ $student->schoolClass->class_name }}</p>
                                                                    @endif
                                                                    <div class="text-end">
                                                                        <a href="{{ route('admin.management.students.show', $student->student_id) }}"
                                                                            class="btn btn-sm btn-outline-primary">
                                                                            <i
                                                                                class="material-symbols-rounded text-sm">visibility</i>
                                                                            View
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
