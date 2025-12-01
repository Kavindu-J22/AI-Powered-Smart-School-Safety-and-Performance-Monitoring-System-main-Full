@extends('admin.layouts.app')

@section('css')
    @vite(['resources/css/admin/timetable-viewer.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')

                    <!-- Header Section -->
                    <div class="timetable-header text-center">
                        <h3 class="mb-2">📚 School Timetable Viewer</h3>
                        <p class="mb-0 opacity-8">View class schedules in a beautiful, easy-to-read format</p>
                    </div>

                    <!-- Class Selection -->
                    <div class="class-selector mb-4">
                        <form method="GET" action="{{ route('admin.timetable-viewer.index') }}" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Select Class</label>
                                <select name="class_id" class="form-control" onchange="this.form.submit()" required>
                                    <option value="">Choose a class to view timetable...</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                            {{ $class->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($selectedClassId)
                                <div class="col-md-4">
                                    <div class="export-buttons">
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                            onclick="printTimetable()">
                                            <i class="material-symbols-rounded text-sm me-1">print</i>Print
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm"
                                            onclick="exportTimetable()">
                                            <i class="material-symbols-rounded text-sm me-1">download</i>Export
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="refreshTimetable()">
                                            <i class="material-symbols-rounded text-sm me-1">refresh</i>Refresh
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>

                    @if ($selectedClassId && !empty($timetable))
                        <!-- Legend -->
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-box"
                                    style="background: linear-gradient(145deg, #ffffff 0%, #f8fdff 100%); border: 1px solid #e8f4f8;">
                                </div>
                                <span>Regular Period</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box"
                                    style="background: linear-gradient(45deg, #fbbf24 0%, #f59e0b 100%);"></div>
                                <span>Break Time</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box"
                                    style="background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%); border: 1px dashed #cbd5e1;">
                                </div>
                                <span>No Class</span>
                            </div>
                        </div>

                        <!-- Timetable Display -->
                        <div class="card">
                            <div class="card-header pb-0">
                                <h6 class="mb-2">
                                    📅 Weekly Timetable:
                                    {{ $classes->find($selectedClassId)->full_name ?? 'Selected Class' }}
                                </h6>
                                <p class="text-sm mb-0 text-muted">School Hours: 8:00 AM - 1:30 PM | Monday to Friday</p>
                            </div>
                            <div class="card-body">
                                <div class="timetable-grid">
                                    <table class="table timetable-table" id="timetableTable">
                                        <thead>
                                            <tr>
                                                <th class="time-column">Time</th>
                                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                                    <th class="day-header">{{ $day }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $maxPeriods = 0;
                                                foreach ($timetable as $day) {
                                                    $maxPeriods = max($maxPeriods, count($day['periods']));
                                                }
                                            @endphp

                                            @for ($i = 0; $i < $maxPeriods; $i++)
                                                <tr>
                                                    @php
                                                        $firstDayPeriod = $timetable[1]['periods'][$i] ?? null;
                                                        $isBreak = $firstDayPeriod && $firstDayPeriod['is_break'];
                                                    @endphp

                                                    <td class="time-column text-center">
                                                        @if ($firstDayPeriod)
                                                            <div class="py-2">
                                                                @if ($isBreak)
                                                                    <strong>Break</strong><br>
                                                                @else
                                                                    <strong>Period
                                                                        {{ $firstDayPeriod['time_slot']->period_number }}</strong><br>
                                                                @endif
                                                                <small>{{ $firstDayPeriod['time_slot']->start_time->format('H:i') }}
                                                                    -
                                                                    {{ $firstDayPeriod['time_slot']->end_time->format('H:i') }}</small>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    @foreach ([1, 2, 3, 4, 5] as $dayNum)
                                                        @php
                                                            $period = $timetable[$dayNum]['periods'][$i] ?? null;
                                                        @endphp

                                                        @if ($period && $period['is_break'])
                                                            <td class="break-cell">
                                                                <div class="py-3">
                                                                    ☕ Break Time<br>
                                                                    <small>30 minutes</small>
                                                                </div>
                                                            </td>
                                                        @elseif($period && $period['entry'])
                                                            <td class="period-cell">
                                                                <div class="period-number">
                                                                    {{ $period['time_slot']->period_number }}</div>
                                                                <div class="period-content">
                                                                    <div class="subject-name">
                                                                        {{ $period['entry']->subject->subject_name }}</div>
                                                                    <div class="teacher-name">
                                                                        <i
                                                                            class="material-symbols-rounded text-sm me-1">person</i>
                                                                        {{ $period['entry']->teacher->user->name }}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        @elseif($period && !$period['is_break'])
                                                            <td class="period-cell empty-period">
                                                                <div class="period-number">
                                                                    {{ $period['time_slot']->period_number }}</div>
                                                                <div class="period-content">
                                                                    <div class="py-2">No Class</div>
                                                                </div>
                                                            </td>
                                                        @else
                                                            <td class="period-cell empty-period">
                                                                <div class="period-content">
                                                                    <div class="py-2">-</div>
                                                                </div>
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @elseif($selectedClassId)
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="material-symbols-rounded mb-3" style="font-size: 4rem; color: #bbb;">schedule</i>
                                <h5 class="mb-2">No Timetable Found</h5>
                                <p class="text-muted mb-0">No timetable has been created for this class yet.</p>
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="material-symbols-rounded mb-3"
                                    style="font-size: 4rem; color: #bbb;">calendar_month</i>
                                <h5 class="mb-2">Select a Class</h5>
                                <p class="text-muted mb-0">Choose a class from the dropdown above to view its timetable.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script>
        function printTimetable() {
            window.print();
        }

        function exportTimetable() {
            // You can implement CSV export or other formats here
            alert('Export feature will be implemented soon!');
        }

        function refreshTimetable() {
            location.reload();
        }

        // Auto-submit form when class is selected
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.querySelector('select[name="class_id"]');
            if (classSelect) {
                classSelect.addEventListener('change', function() {
                    if (this.value) {
                        this.form.submit();
                    }
                });
            }
        });
    </script>
@endsection
