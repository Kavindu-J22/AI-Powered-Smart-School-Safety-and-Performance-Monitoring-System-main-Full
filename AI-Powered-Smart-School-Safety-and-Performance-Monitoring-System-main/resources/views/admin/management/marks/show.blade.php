@extends('admin.layouts.app')

@section('title', 'View Mark Details')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">visibility</i>
                            Mark Details
                        </h3>
                        <div>
                            <a href="{{ route('admin.management.marks.edit', $mark->mark_id) }}" class="btn btn-warning">
                                <i class="material-icons-outlined me-1">edit</i>
                                Edit
                            </a>
                            <a href="{{ route('admin.management.marks.index') }}" class="btn btn-secondary">
                                <i class="material-icons-outlined me-1">arrow_back</i>
                                Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Student Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="material-icons-outlined me-2">person</i>
                                    Student Information
                                </h5>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Student ID</label>
                                <div class="fw-bold">{{ $mark->student->student_code }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Full Name</label>
                                <div class="fw-bold">{{ $mark->student->full_name }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Grade Level</label>
                                <div class="fw-bold">Grade {{ $mark->grade_level }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Class</label>
                                <div class="fw-bold">
                                    {{ $mark->student->schoolClass ? $mark->student->schoolClass->class_name : 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="material-icons-outlined me-2">school</i>
                                    Academic Information
                                </h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Subject</label>
                                <div class="fw-bold">
                                    {{ $mark->subject->subject_name }}
                                    <span class="badge bg-secondary">{{ $mark->subject->subject_code }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Academic Year</label>
                                <div class="fw-bold">{{ $mark->academic_year }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Term</label>
                                <div class="fw-bold">Term {{ $mark->term }}</div>
                            </div>
                        </div>

                        <!-- Marks Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="material-icons-outlined me-2">assessment</i>
                                    Marks & Performance
                                </h5>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Marks Obtained</label>
                                <div class="fw-bold fs-4 text-primary">{{ $mark->marks }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Total Marks</label>
                                <div class="fw-bold fs-4">{{ $mark->total_marks }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Percentage</label>
                                <div>
                                    <span
                                        class="badge fs-5
                                        @if ($mark->percentage >= 75) bg-success
                                        @elseif($mark->percentage >= 60) bg-primary
                                        @elseif($mark->percentage >= 50) bg-warning
                                        @else bg-danger @endif">
                                        {{ number_format($mark->percentage, 2) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Grade</label>
                                <div>
                                    <span
                                        class="badge fs-5
                                        @if (in_array($mark->grade, ['A+', 'A', 'A-'])) bg-success
                                        @elseif(in_array($mark->grade, ['B+', 'B', 'B-'])) bg-primary
                                        @elseif(in_array($mark->grade, ['C+', 'C', 'C-'])) bg-warning
                                        @else bg-danger @endif">
                                        {{ $mark->grade }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        @if ($mark->remarks)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="material-icons-outlined me-2">comment</i>
                                        Remarks
                                    </h5>
                                    <div class="alert alert-secondary">
                                        {{ $mark->remarks }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- System Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="material-icons-outlined me-2">info</i>
                                    System Information
                                </h5>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Entered By</label>
                                <div class="fw-bold">
                                    {{ $mark->enteredBy ? $mark->enteredBy->name : 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Created At</label>
                                <div class="fw-bold">
                                    {{ $mark->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Last Updated</label>
                                <div class="fw-bold">
                                    {{ $mark->updated_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small">Mark ID</label>
                                <div class="fw-bold">#{{ $mark->mark_id }}</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('admin.management.marks.edit', $mark->mark_id) }}"
                                            class="btn btn-warning">
                                            <i class="material-icons-outlined me-1">edit</i>
                                            Edit Marks
                                        </a>
                                        <a href="{{ route('admin.management.marks.index') }}" class="btn btn-secondary">
                                            <i class="material-icons-outlined me-1">arrow_back</i>
                                            Back to List
                                        </a>
                                    </div>
                                    <div>
                                        <form action="{{ route('admin.management.marks.destroy', $mark->mark_id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this mark entry? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="material-icons-outlined me-1">delete</i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .material-icons-outlined {
            vertical-align: middle;
        }

        .small {
            font-size: 0.875rem;
        }
    </style>
@endpush
