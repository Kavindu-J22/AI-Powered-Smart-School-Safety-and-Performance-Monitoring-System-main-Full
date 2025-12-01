@extends('admin.layouts.app')

@section('css')
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">
                                        {{ isset($timetable) ? 'Edit Timetable Entry' : 'Add Timetable Entry' }}
                                    </h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-secondary mb-0"
                                        href="{{ route('admin.management.timetables.index') }}">
                                        <i class="material-symbols-rounded text-sm me-1">arrow_back</i>Back
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST"
                                action="{{ isset($timetable) ? route('admin.management.timetables.update', $timetable) : route('admin.management.timetables.store') }}">
                                @csrf
                                @if (isset($timetable))
                                    @method('PUT')
                                @endif

                                <div class="row g-4">
                                    <!-- Class Selection -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Class *</label>
                                            <select name="school_class_id" class="form-control" required>
                                                <option value="">Select Class</option>
                                                @foreach ($classes as $class)
                                                    <option value="{{ $class->id }}"
                                                        {{ old('school_class_id', $preselected['class_id'] ?? ($timetable->school_class_id ?? '')) == $class->id ? 'selected' : '' }}>
                                                        {{ $class->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Day Selection -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Day *</label>
                                            <select name="day_of_week" class="form-control" required>
                                                @foreach ($days as $dayNum => $dayName)
                                                    <option value="{{ $dayNum }}"
                                                        {{ old('day_of_week', $preselected['day_of_week'] ?? ($timetable->day_of_week ?? 1)) == $dayNum ? 'selected' : '' }}>
                                                        {{ $dayName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Time Slot Selection -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Time Slot *</label>
                                            <select name="time_slot_id" class="form-control" required>
                                                <option value="">Select Time Slot</option>
                                                @foreach ($timeSlots as $timeSlot)
                                                    <option value="{{ $timeSlot->id }}"
                                                        {{ old('time_slot_id', $preselected['time_slot_id'] ?? ($timetable->time_slot_id ?? '')) == $timeSlot->id ? 'selected' : '' }}>
                                                        {{ $timeSlot->slot_name }} ({{ $timeSlot->time_range }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Subject Selection -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Subject *</label>
                                            <select name="subject_id" class="form-control" required>
                                                <option value="">Select Subject</option>
                                                @foreach ($subjects as $subject)
                                                    <option value="{{ $subject->id }}"
                                                        {{ old('subject_id', $timetable->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->subject_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Teacher Selection -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Teacher *</label>
                                            <select name="teacher_id" class="form-control" required>
                                                <option value="">Select Teacher</option>
                                                @foreach ($teachers as $teacher)
                                                    <option value="{{ $teacher->teacher_id }}"
                                                        {{ old('teacher_id', $timetable->teacher_id ?? '') == $teacher->teacher_id ? 'selected' : '' }}>
                                                        {{ $teacher->user->name }} (ID: {{ $teacher->teacher_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Effective Date -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Effective Date</label>
                                            <input type="date" name="effective_date" class="form-control"
                                                value="{{ old('effective_date', $timetable->effective_date ?? now()->format('Y-m-d')) }}">
                                        </div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">End Date (Optional)</label>
                                            <input type="date" name="end_date" class="form-control"
                                                value="{{ old('end_date', $timetable->end_date ?? '') }}">
                                        </div>
                                    </div>

                                    <!-- Alternative Teacher -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Alternative Teacher</label>
                                            <select name="alternative_teacher_id" class="form-control">
                                                <option value="">Select Alternative Teacher</option>
                                                @foreach ($teachers as $teacher)
                                                    <option value="{{ $teacher->teacher_id }}"
                                                        {{ old('alternative_teacher_id', $timetable->alternative_teacher_id ?? '') == $teacher->teacher_id ? 'selected' : '' }}>
                                                        {{ $teacher->user->name }} (ID: {{ $teacher->teacher_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">General Notes</label>
                                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $timetable->notes ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Teacher Notes -->
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Teacher Notes</label>
                                            <textarea name="teacher_notes" class="form-control" rows="3">{{ old('teacher_notes', $timetable->teacher_notes ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.management.timetables.index') }}"
                                                class="btn btn-outline-secondary">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn bg-gradient-primary">
                                                <i class="material-symbols-rounded text-sm me-1">save</i>
                                                {{ isset($timetable) ? 'Update Entry' : 'Create Entry' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enable form validation
            const form = document.querySelector('form');

            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let hasErrors = false;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        hasErrors = true;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });

            // Remove error styling when user starts typing
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
    </script>
@endsection
