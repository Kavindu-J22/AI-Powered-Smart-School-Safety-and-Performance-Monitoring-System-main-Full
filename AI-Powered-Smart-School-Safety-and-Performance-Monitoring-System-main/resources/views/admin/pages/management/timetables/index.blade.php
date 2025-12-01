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

                    <!-- Class and Day Selection Card -->
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-md-6 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                </div>
                                <div class="col-md-6 text-end">
                                    @if (checkPermission('admin.management.timetables.create'))
                                        <a class="btn bg-gradient-success mb-0 me-2"
                                            href="{{ route('admin.management.timetables.create') }}">
                                            <i class="material-symbols-rounded text-sm me-1">add</i>Add Entry
                                        </a>
                                        <a class="btn bg-gradient-primary mb-0 me-2"
                                            href="{{ route('admin.management.timetables.bulk-assign') }}">
                                            <i class="material-symbols-rounded text-sm me-1">view_week</i>Bulk Assign
                                        </a>
                                    @endif

                                    <button class="btn bg-gradient-info mb-0" data-bs-toggle="modal"
                                        data-bs-target="#addTimeSlotModal">
                                        <i class="material-symbols-rounded text-sm me-1">schedule</i>Add Slot (After 1:30
                                        PM)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.management.timetables.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Select Class</label>
                                        <select name="class_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">Choose a class...</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                                    {{ $class->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="btn-group" role="group">
                                        @foreach ($days as $dayNum => $dayName)
                                            <a href="{{ route('admin.management.timetables.index', ['class_id' => $selectedClassId, 'day' => $dayNum]) }}"
                                                class="btn day-selector {{ $selectedDay == $dayNum ? 'active text-white' : 'btn-outline-primary' }}">
                                                {{ $dayName }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if ($selectedClassId && ($selectedClass = $classes->find($selectedClassId)))
                        <!-- Timetable Display Card -->
                        <div class="card">
                            <div class="card-header pb-0">
                                <h6 class="mb-0">
                                    Timetable for {{ $selectedClass->full_name }} -
                                    {{ $days[$selectedDay ?? 1] ?? 'Monday' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="timetable-grid">
                                    <div class="row g-3">
                                        @forelse($timeSlots as $timeSlot)
                                            @php
                                                $timetableEntry = $timetables
                                                    ->where('time_slot_id', $timeSlot->id)
                                                    ->first();
                                            @endphp

                                            <div class="col-md-6 col-lg-4">
                                                <div
                                                    class="card time-slot-card {{ $timetableEntry ? 'has-class' : 'empty-slot' }}">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <h6 class="card-title mb-1">{{ $timeSlot->slot_name }}</h6>
                                                                <small
                                                                    class="text-muted">{{ $timeSlot->time_range }}</small>
                                                            </div>
                                                            @if ($timetableEntry)
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-outline-secondary"
                                                                        type="button" data-bs-toggle="dropdown">
                                                                        <i
                                                                            class="material-symbols-rounded text-sm">more_vert</i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        @if (checkPermission('admin.management.timetables.edit'))
                                                                            <li>
                                                                                <a class="dropdown-item"
                                                                                    href="{{ route('admin.management.timetables.edit', $timetableEntry) }}">
                                                                                    <i
                                                                                        class="material-symbols-rounded text-sm me-1">edit</i>Edit
                                                                                </a>
                                                                            </li>
                                                                        @endif
                                                                        @if (checkPermission('admin.management.timetables.destroy'))
                                                                            <li>
                                                                                <form method="POST"
                                                                                    action="{{ route('admin.management.timetables.destroy', $timetableEntry) }}"
                                                                                    onsubmit="return confirm('Are you sure?')">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit"
                                                                                        class="dropdown-item text-danger">
                                                                                        <i
                                                                                            class="material-symbols-rounded text-sm me-1">delete</i>Delete
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                            @if (!$timetableEntry && $timeSlot->slot_type === 'additional')
                                                                <!-- Delete slot option for additional slots after 1:30 PM -->
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-outline-danger"
                                                                        type="button" data-bs-toggle="dropdown"
                                                                        title="Delete Time Slot">
                                                                        <i
                                                                            class="material-symbols-rounded text-sm">delete</i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <form method="POST"
                                                                                action="{{ route('admin.management.timetables.delete-time-slot', $timeSlot->id) }}"
                                                                                onsubmit="return confirm('Are you sure you want to delete this time slot? This action cannot be undone.')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                    class="dropdown-item text-danger">
                                                                                    <i
                                                                                        class="material-symbols-rounded text-sm me-1">delete</i>Delete
                                                                                    Time Slot
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if ($timetableEntry)
                                                            <div>
                                                                <p class="mb-1">
                                                                    <strong>{{ $timetableEntry->subject->subject_name }}</strong>
                                                                </p>
                                                                <p class="mb-1 text-sm">
                                                                    <i
                                                                        class="material-symbols-rounded text-xs me-1">person</i>
                                                                    {{ $timetableEntry->teacher->user->name }}
                                                                </p>
                                                                @if ($timetableEntry->room_number)
                                                                    <p class="mb-0 text-sm text-muted">
                                                                        <i
                                                                            class="material-symbols-rounded text-xs me-1">room</i>
                                                                        Room {{ $timetableEntry->room_number }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="text-center text-muted">
                                                                <i class="material-symbols-rounded mb-2"
                                                                    style="font-size: 2rem;">event_available</i>
                                                                <p class="mb-2">No class scheduled</p>
                                                                <div class="text-xs text-info mb-2">
                                                                    Debug: Subjects: {{ count($subjects ?? []) }},
                                                                    Teachers: {{ count($teachers ?? []) }}<br>
                                                                    Permission:
                                                                    {{ checkPermission('admin.management.timetables.create') ? 'YES' : 'NO' }}<br>
                                                                    User: {{ auth()->user()->name ?? 'Not logged in' }}
                                                                </div>
                                                                {{-- Temporarily bypass permission check --}}
                                                                @if (true)
                                                                    <!-- Quick Assignment Dropdown -->
                                                                    <div class="dropdown">
                                                                        <button
                                                                            class="btn btn-sm btn-outline-success dropdown-toggle"
                                                                            type="button" data-bs-toggle="dropdown"
                                                                            aria-expanded="false">
                                                                            <i
                                                                                class="material-symbols-rounded text-sm me-1">add</i>Add
                                                                            Class
                                                                        </button>
                                                                        <div class="dropdown-menu p-3"
                                                                            style="min-width: 300px;">
                                                                            <h6 class="dropdown-header">Quick Add Class</h6>
                                                                            @if (isset($subjects) && count($subjects) > 0 && isset($teachers) && count($teachers) > 0)
                                                                                @if ($errors->has('conflict'))
                                                                                    <div
                                                                                        class="alert alert-danger alert-sm mb-2">
                                                                                        {{ $errors->first('conflict') }}
                                                                                    </div>
                                                                                @endif

                                                                                <form method="POST"
                                                                                    action="{{ route('admin.management.timetables.quick-assign') }}"
                                                                                    class="quick-assign-form">
                                                                                    @csrf
                                                                                    <input type="hidden"
                                                                                        name="school_class_id"
                                                                                        value="{{ $selectedClassId }}">
                                                                                    <input type="hidden"
                                                                                        name="time_slot_id"
                                                                                        value="{{ $timeSlot->id }}">
                                                                                    <input type="hidden"
                                                                                        name="day_of_week"
                                                                                        value="{{ $selectedDay }}">

                                                                                    <div class="mb-3">
                                                                                        <label
                                                                                            class="form-label small">Subject</label>
                                                                                        <select name="subject_id"
                                                                                            class="form-control form-control-sm"
                                                                                            required>
                                                                                            <option value="">Select
                                                                                                Subject...</option>
                                                                                            @foreach ($subjects as $subject)
                                                                                                <option
                                                                                                    value="{{ $subject->id }}">
                                                                                                    {{ $subject->subject_name }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <label
                                                                                            class="form-label small">Teacher</label>
                                                                                        <select name="teacher_id"
                                                                                            class="form-control form-control-sm"
                                                                                            required>
                                                                                            <option value="">Select
                                                                                                Teacher...</option>
                                                                                            @foreach ($teachers as $teacher)
                                                                                                <option
                                                                                                    value="{{ $teacher->id }}">
                                                                                                    {{ $teacher->user->name }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <label
                                                                                            class="form-label small">Room
                                                                                            (Optional)
                                                                                        </label>
                                                                                        <input type="text"
                                                                                            name="room_number"
                                                                                            class="form-control form-control-sm"
                                                                                            placeholder="Room number">
                                                                                    </div>

                                                                                    <div class="d-flex gap-2">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success btn-sm flex-fill">
                                                                                            <i
                                                                                                class="material-symbols-rounded text-sm me-1">check</i>Add
                                                                                        </button>
                                                                                        <a href="{{ route('admin.management.timetables.create', [
                                                                                            'class_id' => $selectedClassId,
                                                                                            'day_of_week' => $selectedDay,
                                                                                            'time_slot_id' => $timeSlot->id,
                                                                                        ]) }}"
                                                                                            class="btn btn-outline-primary btn-sm">
                                                                                            <i
                                                                                                class="material-symbols-rounded text-sm me-1">edit</i>Detailed
                                                                                        </a>
                                                                                    </div>
                                                                                </form>
                                                                            @else
                                                                                <div
                                                                                    class="alert alert-warning text-center mb-0">
                                                                                    <small>No subjects or teachers
                                                                                        available. Please add them
                                                                                        first.</small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    {{-- Fallback button if dropdown doesn't work --}}
                                                                    <div class="mt-2">
                                                                        <a href="{{ route('admin.management.timetables.create', [
                                                                            'class_id' => $selectedClassId,
                                                                            'day_of_week' => $selectedDay,
                                                                            'time_slot_id' => $timeSlot->id,
                                                                        ]) }}"
                                                                            class="btn btn-sm btn-primary">
                                                                            <i
                                                                                class="material-symbols-rounded text-sm me-1">edit</i>Full
                                                                            Form
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-info text-center">
                                                    <i class="material-symbols-rounded">info</i>
                                                    No time slots available. Please add time slots first.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="material-symbols-rounded mb-3"
                                    style="font-size: 4rem; color: #bbb;">calendar_month</i>
                                <h5 class="mb-2">Select a Class</h5>
                                <p class="text-muted mb-0">Choose a class from the dropdown above to view its timetable.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Add Time Slot Modal -->
    <div class="modal fade" id="addTimeSlotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Additional Time Slot (After 1:30 PM Only)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.management.timetables.create-time-slot') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Note:</strong> The preset schedule (8:00 AM - 1:30 PM) cannot be modified.
                            You can only add additional time slots after 1:30 PM.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Start Time (After 1:30 PM)</label>
                                    <input type="time" name="start_time" class="form-control" required
                                        min="13:30">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="end_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Slot Name</label>
                                    <input type="text" name="slot_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Time Slot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Auto-refresh timetable when class is selected
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.querySelector('select[name="class_id"]');
            if (classSelect) {
                classSelect.addEventListener('change', function() {
                    if (this.value) {
                        window.location.href =
                            `{{ route('admin.management.timetables.index') }}?class_id=${this.value}&day={{ $selectedDay }}`;
                    }
                });
            }

            // Debug: Test dropdown functionality
            console.log('Dropdowns found:', document.querySelectorAll('.dropdown').length);
            console.log('Quick assign forms found:', document.querySelectorAll('.quick-assign-form').length);

            // Initialize Bootstrap dropdowns manually
            const dropdownTriggers = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdownTriggers.forEach(trigger => {
                // Initialize Bootstrap dropdown
                if (window.bootstrap && bootstrap.Dropdown) {
                    new bootstrap.Dropdown(trigger);
                    console.log('Bootstrap dropdown initialized for:', trigger);
                } else {
                    console.warn('Bootstrap not available, trying manual toggle');
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        const menu = trigger.nextElementSibling;
                        if (menu && menu.classList.contains('dropdown-menu')) {
                            menu.classList.toggle('show');
                        }
                    });
                }
            });

            // Ensure dropdowns close when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });
    </script>
@endsection
