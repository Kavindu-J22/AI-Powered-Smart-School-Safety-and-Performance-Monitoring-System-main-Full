@extends('admin.layouts.app')

@section('title', isset($class) ? 'Edit Class' : 'Add New Class')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">{{ isset($class) ? 'edit' : 'add' }}</i>
                            {{ isset($class) ? 'Edit Class' : 'Add New Class' }}
                        </h3>
                        <a href="{{ route('admin.management.classes.index') }}" class="btn btn-secondary">
                            <i class="material-icons-outlined me-1">arrow_back</i>
                            Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.management.classes.enroll') }}">
                            @csrf
                            @if (isset($class))
                                <input type="hidden" name="id" value="{{ $class->id }}">
                            @endif

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">info</i>
                                                Basic Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Class Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                                    name="name" value="{{ old('name', $class->name ?? '') }}"
                                                    placeholder="e.g., Grade 1-A, Science Stream A" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="grade" class="form-label">Grade Level <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('grade') is-invalid @enderror"
                                                    id="grade" name="grade" required>
                                                    <option value="">Select Grade Level</option>
                                                    @for ($i = 1; $i <= 13; $i++)
                                                        <option value="{{ $i }}"
                                                            {{ old('grade', $class->grade ?? '') == $i ? 'selected' : '' }}>
                                                            Grade {{ $i }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                @error('grade')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="section" class="form-label">Section <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('section') is-invalid @enderror"
                                                    id="section" name="section" required>
                                                    <option value="">Select Section</option>
                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $section)
                                                        <option value="{{ $section }}"
                                                            {{ old('section', $class->section ?? '') == $section ? 'selected' : '' }}>
                                                            Section {{ $section }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="academic_year" class="form-label">Academic Year <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('academic_year') is-invalid @enderror"
                                                    id="academic_year" name="academic_year" required>
                                                    <option value="">Select Academic Year</option>
                                                    @php
                                                        $currentYear = date('Y');
                                                        $currentMonth = date('n');
                                                        $academicYear =
                                                            $currentMonth >= 1 && $currentMonth <= 3
                                                                ? $currentYear - 1 . '-' . $currentYear
                                                                : $currentYear . '-' . ($currentYear + 1);
                                                    @endphp
                                                    @for ($year = $currentYear - 2; $year <= $currentYear + 2; $year++)
                                                        @php $yearRange = $year . '-' . ($year + 1); @endphp
                                                        <option value="{{ $yearRange }}"
                                                            {{ old('academic_year', $class->academic_year ?? $academicYear) == $yearRange ? 'selected' : '' }}>
                                                            {{ $yearRange }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                @error('academic_year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="capacity" class="form-label">Class Capacity</label>
                                                <input type="number"
                                                    class="form-control @error('capacity') is-invalid @enderror"
                                                    id="capacity" name="capacity" min="1" max="50"
                                                    value="{{ old('capacity', $class->capacity ?? 30) }}"
                                                    placeholder="Maximum number of students">
                                                @error('capacity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment & Location -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">assignment_ind</i>
                                                Assignment & Location
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="class_teacher_id" class="form-label">Class Teacher</label>
                                                <select class="form-select @error('class_teacher_id') is-invalid @enderror"
                                                    id="class_teacher_id" name="class_teacher_id">
                                                    <option value="">Select Class Teacher</option>
                                                    @foreach ($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}"
                                                            {{ old('class_teacher_id', $class->class_teacher_id ?? '') == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->first_name }} {{ $teacher->last_name }} -
                                                            {{ $teacher->subject_specialization }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('class_teacher_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="room_number" class="form-label">Room Number</label>
                                                <input type="text"
                                                    class="form-control @error('room_number') is-invalid @enderror"
                                                    id="room_number" name="room_number"
                                                    value="{{ old('room_number', $class->room_number ?? '') }}"
                                                    placeholder="e.g., A101, Science Lab 1">
                                                @error('room_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="building" class="form-label">Building</label>
                                                <select class="form-select @error('building') is-invalid @enderror"
                                                    id="building" name="building">
                                                    <option value="">Select Building</option>
                                                    <option value="Main Building"
                                                        {{ old('building', $class->building ?? '') == 'Main Building' ? 'selected' : '' }}>
                                                        Main Building</option>
                                                    <option value="Science Block"
                                                        {{ old('building', $class->building ?? '') == 'Science Block' ? 'selected' : '' }}>
                                                        Science Block</option>
                                                    <option value="Arts Block"
                                                        {{ old('building', $class->building ?? '') == 'Arts Block' ? 'selected' : '' }}>
                                                        Arts Block</option>
                                                    <option value="Primary Block"
                                                        {{ old('building', $class->building ?? '') == 'Primary Block' ? 'selected' : '' }}>
                                                        Primary Block</option>
                                                    <option value="Secondary Block"
                                                        {{ old('building', $class->building ?? '') == 'Secondary Block' ? 'selected' : '' }}>
                                                        Secondary Block</option>
                                                </select>
                                                @error('building')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="floor" class="form-label">Floor</label>
                                                <select class="form-select @error('floor') is-invalid @enderror"
                                                    id="floor" name="floor">
                                                    <option value="">Select Floor</option>
                                                    <option value="Ground Floor"
                                                        {{ old('floor', $class->floor ?? '') == 'Ground Floor' ? 'selected' : '' }}>
                                                        Ground Floor</option>
                                                    <option value="First Floor"
                                                        {{ old('floor', $class->floor ?? '') == 'First Floor' ? 'selected' : '' }}>
                                                        First Floor</option>
                                                    <option value="Second Floor"
                                                        {{ old('floor', $class->floor ?? '') == 'Second Floor' ? 'selected' : '' }}>
                                                        Second Floor</option>
                                                    <option value="Third Floor"
                                                        {{ old('floor', $class->floor ?? '') == 'Third Floor' ? 'selected' : '' }}>
                                                        Third Floor</option>
                                                </select>
                                                @error('floor')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                    rows="3" placeholder="Additional information about the class">{{ old('description', $class->description ?? '') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Settings -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">settings</i>
                                                Status & Settings
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">Status</label>
                                                        <select class="form-select @error('status') is-invalid @enderror"
                                                            id="status" name="status">
                                                            <option value="active"
                                                                {{ old('status', $class->status ?? 'active') == 'active' ? 'selected' : '' }}>
                                                                Active</option>
                                                            <option value="inactive"
                                                                {{ old('status', $class->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                                                Inactive</option>
                                                            <option value="archived"
                                                                {{ old('status', $class->status ?? '') == 'archived' ? 'selected' : '' }}>
                                                                Archived</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="is_combined_class" name="is_combined_class"
                                                                value="1"
                                                                {{ old('is_combined_class', $class->is_combined_class ?? false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_combined_class">
                                                                Combined Class (Multiple Grades)
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-end">
                                            <a href="{{ route('admin.management.classes.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="material-icons-outlined me-1">cancel</i>
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="material-icons-outlined me-1">save</i>
                                                {{ isset($class) ? 'Update Class' : 'Create Class' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Auto-generate class name based on grade and section
                $('#grade, #section').on('change', function() {
                    const grade = $('#grade').val();
                    const section = $('#section').val();

                    if (grade && section) {
                        const className = `Grade ${grade}-${section}`;
                        $('#name').val(className);
                    }
                });

                // Form validation before submit
                $('form').on('submit', function(e) {
                    let isValid = true;

                    // Check required fields
                    $(this).find('[required]').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    // Check class capacity
                    const capacity = parseInt($('#capacity').val());
                    if (capacity && (capacity < 1 || capacity > 50)) {
                        $('#capacity').addClass('is-invalid');
                        notificationManager.error('Validation Error',
                            'Class capacity must be between 1 and 50 students');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        notificationManager.error('Validation Error',
                            'Please fill in all required fields correctly');
                    }
                });

                // Combined class toggle functionality
                $('#is_combined_class').on('change', function() {
                    if ($(this).is(':checked')) {
                        notificationManager.info('Combined Class',
                            'This class will accept students from multiple grade levels');
                    }
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .material-icons-outlined {
                font-size: 18px;
                vertical-align: middle;
            }

            .card-header .card-title {
                font-weight: 600;
            }

            .form-label {
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .form-check-input:checked {
                background-color: #0d6efd;
                border-color: #0d6efd;
            }

            .form-check-label {
                margin-left: 0.5rem;
            }
        </style>
    @endpush
@endsection
