@extends('admin.layouts.app')

@section('css')
    @vite(['resources/css/admin/timetables.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')

                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-md-6 d-flex align-items-center">
                                    <h6 class="mb-0">Bulk Timetable Assignment</h6>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a class="btn btn-outline-secondary mb-0 me-2"
                                        href="{{ route('admin.management.timetables.index') }}">
                                        <i class="material-symbols-rounded text-sm me-1">arrow_back</i>Back to Timetables
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.management.timetables.bulk-assign-store') }}"
                                id="bulkAssignForm">
                                @csrf

                                <!-- Class Selection -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">Select Class *</label>
                                            <select name="school_class_id" class="form-control" required
                                                onchange="updateAssignments()">
                                                <option value="">Choose a class...</option>
                                                @foreach ($classes as $class)
                                                    <option value="{{ $class->id }}"
                                                        {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="fillWithTemplate('math')">
                                                <i class="material-symbols-rounded text-sm me-1">functions</i>Math Template
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="clearAllAssignments()">
                                                <i class="material-symbols-rounded text-sm me-1">clear_all</i>Clear All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                onclick="copyToAllDays()">
                                                <i class="material-symbols-rounded text-sm me-1">content_copy</i>Copy Monday
                                                to All Days
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Day Tabs -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="btn-group" role="group">
                                            @foreach ($days as $dayNum => $dayName)
                                                <button type="button"
                                                    class="btn day-tab {{ $dayNum == 1 ? 'active' : 'btn-outline-primary' }}"
                                                    onclick="switchDay({{ $dayNum }})">
                                                    {{ $dayName }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Grid for each day -->
                                @foreach ($days as $dayNum => $dayName)
                                    <div class="day-assignments" id="day-{{ $dayNum }}"
                                        style="{{ $dayNum != 1 ? 'display: none;' : '' }}">
                                        <div class="row g-3 mb-4">
                                            @foreach ($timeSlots as $index => $timeSlot)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="assignment-card">
                                                        <div class="time-slot-header p-2 text-center">
                                                            <h6 class="mb-0">{{ $timeSlot->label }}</h6>
                                                            <small>{{ $timeSlot->time_range }}</small>
                                                        </div>
                                                        <div class="card-body p-3">
                                                            <!-- Subject Selection -->
                                                            <div class="mb-2">
                                                                <select
                                                                    name="assignments[{{ $dayNum }}_{{ $timeSlot->id }}][subject_id]"
                                                                    class="form-control form-control-sm"
                                                                    onchange="updateAssignment({{ $dayNum }}, {{ $timeSlot->id }})">
                                                                    <option value="">Select Subject</option>
                                                                    @foreach ($subjects as $subject)
                                                                        <option value="{{ $subject->id }}">
                                                                            {{ $subject->subject_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <!-- Teacher Selection -->
                                                            <div class="mb-2">
                                                                <select
                                                                    name="assignments[{{ $dayNum }}_{{ $timeSlot->id }}][teacher_id]"
                                                                    class="form-control form-control-sm">
                                                                    <option value="">Select Teacher</option>
                                                                    @foreach ($teachers as $teacher)
                                                                        <option value="{{ $teacher->teacher_id }}">
                                                                            {{ $teacher->user->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <!-- Hidden fields for day and time slot -->
                                                            <input type="hidden"
                                                                name="assignments[{{ $dayNum }}_{{ $timeSlot->id }}][day_of_week]"
                                                                value="{{ $dayNum }}">
                                                            <input type="hidden"
                                                                name="assignments[{{ $dayNum }}_{{ $timeSlot->id }}][time_slot_id]"
                                                                value="{{ $timeSlot->id }}">

                                                            <!-- Quick clear button -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger w-100"
                                                                onclick="clearTimeSlot({{ $dayNum }}, {{ $timeSlot->id }})">
                                                                <i
                                                                    class="material-symbols-rounded text-sm me-1">clear</i>Clear
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn bg-gradient-success">
                                            <i class="material-symbols-rounded text-sm me-1">save</i>
                                            Create All Timetable Entries
                                        </button>
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
        let currentDay = 1;

        function switchDay(dayNum) {
            // Hide all day assignments
            document.querySelectorAll('.day-assignments').forEach(el => {
                el.style.display = 'none';
            });

            // Show selected day
            document.getElementById('day-' + dayNum).style.display = 'block';

            // Update tab active state
            document.querySelectorAll('.day-tab').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('btn-outline-primary');
            });

            event.target.classList.add('active');
            event.target.classList.remove('btn-outline-primary');

            currentDay = dayNum;
        }

        function updateAssignment(dayNum, timeSlotId) {
            // You can add logic here to validate assignments
            console.log(`Updated assignment for day ${dayNum}, time slot ${timeSlotId}`);
        }

        function clearTimeSlot(dayNum, timeSlotId) {
            const subjectSelect = document.querySelector(
                `select[name="assignments[${dayNum}_${timeSlotId}][subject_id]"]`);
            const teacherSelect = document.querySelector(
                `select[name="assignments[${dayNum}_${timeSlotId}][teacher_id]"]`);

            if (subjectSelect) subjectSelect.value = '';
            if (teacherSelect) teacherSelect.value = '';
        }

        function clearAllAssignments() {
            if (confirm('Are you sure you want to clear all assignments?')) {
                document.querySelectorAll('select[name*="[subject_id]"]').forEach(select => {
                    select.value = '';
                });
                document.querySelectorAll('select[name*="[teacher_id]"]').forEach(select => {
                    select.value = '';
                });
            }
        }

        function copyToAllDays() {
            if (confirm('This will copy Monday\'s assignments to all other days. Continue?')) {
                const mondayAssignments = {};

                // Get Monday assignments
                @foreach ($timeSlots as $timeSlot)
                    const mondaySubject{{ $timeSlot->id }} = document.querySelector(
                        'select[name="assignments[1_{{ $timeSlot->id }}][subject_id]"]')?.value;
                    const mondayTeacher{{ $timeSlot->id }} = document.querySelector(
                        'select[name="assignments[1_{{ $timeSlot->id }}][teacher_id]"]')?.value;

                    // Apply to other days
                    for (let day = 2; day <= 5; day++) {
                        if (mondaySubject{{ $timeSlot->id }}) {
                            const subjectSelect = document.querySelector(
                                `select[name="assignments[${day}_{{ $timeSlot->id }}][subject_id]"]`);
                            if (subjectSelect) subjectSelect.value = mondaySubject{{ $timeSlot->id }};
                        }

                        if (mondayTeacher{{ $timeSlot->id }}) {
                            const teacherSelect = document.querySelector(
                                `select[name="assignments[${day}_{{ $timeSlot->id }}][teacher_id]"]`);
                            if (teacherSelect) teacherSelect.value = mondayTeacher{{ $timeSlot->id }};
                        }
                    }
                @endforeach
            }
        }

        function fillWithTemplate(template) {
            if (template === 'math') {
                // Sample math-heavy template
                alert('Math template feature coming soon!');
            }
        }

        // Form validation
        document.getElementById('bulkAssignForm').addEventListener('submit', function(e) {
            const classId = document.querySelector('select[name="school_class_id"]').value;
            if (!classId) {
                e.preventDefault();
                alert('Please select a class first.');
                return;
            }

            // Check if at least one assignment is made
            const hasAssignments = Array.from(document.querySelectorAll('select[name*="[subject_id]"]'))
                .some(select => select.value);

            if (!hasAssignments) {
                e.preventDefault();
                alert('Please make at least one subject assignment.');
                return;
            }

            // Remove empty assignments before submitting
            document.querySelectorAll('select[name*="[subject_id]"]').forEach(select => {
                if (!select.value) {
                    const container = select.closest('.assignment-card');
                    if (container) {
                        container.querySelectorAll('input, select').forEach(input => {
                            input.disabled = true;
                        });
                    }
                }
            });
        });
    </script>
@endsection
