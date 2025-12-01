@extends('admin.layouts.app')

@section('css')
    @vite(['resources/css/admin/attendance-dashboard.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0">Attendance Dashboard</h4>
                            <p class="text-sm text-secondary mb-0">Real-time attendance monitoring -
                                {{ now()->format('l, F j, Y') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.management.attendance.create') }}" class="btn btn-primary btn-sm">
                                <i class="material-symbols-rounded text-sm">add</i> Manual Entry
                            </a>
                            <a href="{{ route('admin.management.attendance.index') }}"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="material-symbols-rounded text-sm">list</i> View All
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div
                                class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">check_circle</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Present</p>
                                <h4 class="mb-0">{{ $stats['present'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0"><span
                                    class="text-success text-sm font-weight-bolder">{{ number_format((($stats['present'] ?? 0) / max($stats['total'] ?? 1, 1)) * 100, 1) }}%
                                </span>of total students</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div
                                class="icon icon-lg icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">cancel</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Absent</p>
                                <h4 class="mb-0">{{ $stats['absent'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0"><span
                                    class="text-danger text-sm font-weight-bolder">{{ number_format((($stats['absent'] ?? 0) / max($stats['total'] ?? 1, 1)) * 100, 1) }}%
                                </span>of total students</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div
                                class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">schedule</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Late</p>
                                <h4 class="mb-0">{{ $stats['late'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0"><span
                                    class="text-warning text-sm font-weight-bolder">{{ number_format((($stats['late'] ?? 0) / max($stats['present'] ?? 1, 1)) * 100, 1) }}%
                                </span>of present students</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div
                                class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">people</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Total Students</p>
                                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0"><span class="text-info text-sm font-weight-bolder">Active</span> in system</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Check-Ins -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h6>Recent Check-Ins</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshCheckIns()">
                                    <i class="material-symbols-rounded text-sm">refresh</i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Student</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Class</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Status</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Check In</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Check Out</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Method</th>
                                        </tr>
                                    </thead>
                                    <tbody id="checkInsTableBody">
                                        @forelse($recentCheckIns as $attendance)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-3 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $attendance->student->first_name }}
                                                                {{ $attendance->student->last_name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ $attendance->student->student_code }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $attendance->student->schoolClass->class_name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-secondary mb-0">Grade
                                                        {{ $attendance->student->grade_level }}</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    @if ($attendance->status === 'present')
                                                        <span class="badge badge-sm bg-gradient-success">Present</span>
                                                    @elseif($attendance->status === 'absent')
                                                        <span class="badge badge-sm bg-gradient-danger">Absent</span>
                                                    @elseif($attendance->status === 'late')
                                                        <span class="badge badge-sm bg-gradient-warning">Late</span>
                                                    @else
                                                        <span
                                                            class="badge badge-sm bg-gradient-info">{{ ucfirst($attendance->status) }}</span>
                                                    @endif
                                                    @if ($attendance->is_late)
                                                        <i class="material-symbols-rounded text-warning text-sm"
                                                            title="Late arrival">schedule</i>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '-' }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '-' }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if ($attendance->device_id === 'nfc')
                                                        <span class="badge badge-sm bg-gradient-primary">NFC</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">Manual</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <p class="text-secondary mb-0">No check-ins yet today</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>
@endsection

<script>
    function refreshCheckIns() {
        window.location.reload();
    }

    // Auto-refresh every 30 seconds
    setInterval(refreshCheckIns, 30000);
</script>

@vite(['resources/css/admin/dashboard.css'])
