@extends('admin.layouts.app')

@section('title', __('Manual Attendance Entry'))

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>{{ __('Manual Attendance Entry') }}</h6>
                                    <p class="text-sm mb-0">{{ __('Record attendance manually by entering student code') }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.management.attendance.dashboard') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="material-symbols-rounded text-sm">arrow_back</i> {{ __('Back to Dashboard') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- Mode switcher --}}
                            <ul class="nav nav-pills mb-4" id="attendanceModeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-manual" data-bs-toggle="pill"
                                        data-bs-target="#panel-manual" type="button" role="tab">
                                        <i class="material-symbols-rounded me-1 align-middle"
                                            style="font-size:1.1rem">edit_note</i>
                                        Manual Entry
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $attendanceMode === 'rfid' ? '' : 'opacity-50' }}"
                                        id="tab-rfid" data-bs-toggle="pill" data-bs-target="#panel-rfid" type="button"
                                        role="tab" onclick="rfidScanTab.start()">
                                        <i class="material-symbols-rounded me-1 align-middle"
                                            style="font-size:1.1rem">contactless</i>
                                        RFID Scan Mode
                                        @if ($attendanceMode === 'rfid')
                                            <span class="badge bg-success ms-1" style="font-size:.65rem">Active</span>
                                        @else
                                            <span class="badge bg-secondary ms-1" style="font-size:.65rem">Inactive</span>
                                        @endif
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link {{ $attendanceMode === 'face_recognition' ? '' : 'opacity-50' }}"
                                        id="tab-face" data-bs-toggle="pill" data-bs-target="#panel-face" type="button"
                                        role="tab" onclick="faceScanTab.start()">
                                        <i class="material-symbols-rounded me-1 align-middle"
                                            style="font-size:1.1rem">face_retouching_natural</i>
                                        Face Recognition
                                        @if ($attendanceMode === 'face_recognition')
                                            <span class="badge bg-success ms-1" style="font-size:.65rem">Active</span>
                                        @else
                                            <span class="badge bg-secondary ms-1" style="font-size:.65rem">Inactive</span>
                                        @endif
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="attendanceModeContent">

                                {{-- ── RFID Scan Panel ──────────────────────────── --}}
                                <div class="tab-pane fade" id="panel-rfid" role="tabpanel" aria-labelledby="tab-rfid">

                                    {{-- Status bar --}}
                                    <div class="rfid-status-bar d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3"
                                        id="rfidStatusBar">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rfid-status-dot" id="rfidStatusDot"></span>
                                            <span class="fw-semibold text-white small" id="rfidStatusLabel">RFID SCAN MODE —
                                                ACTIVE</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light py-1 px-3"
                                            onclick="rfidScanTab.toggle()">
                                            <i class="material-symbols-rounded me-1 align-middle" style="font-size:.9rem"
                                                id="rfidToggleIcon">stop_circle</i>
                                            <span id="rfidToggleLabel">Stop</span>
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        {{-- Main display --}}
                                        <div class="col-lg-7">
                                            <div class="rfid-main-card rounded-4 p-4 text-center" id="rfidMainCard">

                                                {{-- Waiting state --}}
                                                <div id="rfidStateWaiting">
                                                    <div class="rfid-wave-container mb-3">
                                                        <div class="rfid-wave rfid-wave-1"></div>
                                                        <div class="rfid-wave rfid-wave-2"></div>
                                                        <div class="rfid-wave rfid-wave-3"></div>
                                                        <i
                                                            class="material-symbols-rounded rfid-center-icon text-primary">contactless</i>
                                                    </div>
                                                    <h5 class="fw-bold mb-1 text-dark">Waiting for wristband…</h5>
                                                    <p class="text-muted small mb-0">Ask the student to tap their wristband
                                                        on the reader</p>
                                                </div>

                                                {{-- Scan result state --}}
                                                <div id="rfidStateResult" class="d-none">
                                                    <div class="rfid-avatar mx-auto mb-3" id="rfidAvatar">
                                                        <span id="rfidAvatarInitials" class="rfid-avatar-text">?</span>
                                                    </div>
                                                    <h4 class="fw-bold mb-1" id="rfidResultName">—</h4>
                                                    <p class="text-muted small mb-3">
                                                        <span class="badge bg-light text-dark border me-1"
                                                            id="rfidResultCode">—</span>
                                                        <span id="rfidResultMeta">—</span>
                                                    </p>
                                                    <div class="mb-3">
                                                        <span class="rfid-action-badge" id="rfidResultAction">—</span>
                                                    </div>
                                                    <div class="rfid-detail-row rounded-3 px-4 py-2 d-inline-flex gap-4">
                                                        <div class="text-center">
                                                            <div class="rfid-detail-label">Time</div>
                                                            <div class="rfid-detail-value" id="rfidResultTime">—</div>
                                                        </div>
                                                        <div class="text-center" id="rfidDetailExtraWrap">
                                                            <div class="rfid-detail-label" id="rfidDetailExtraLabel">—
                                                            </div>
                                                            <div class="rfid-detail-value" id="rfidDetailExtraValue">—
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Error state --}}
                                                <div id="rfidStateError" class="d-none">
                                                    <div class="mb-3">
                                                        <i class="material-symbols-rounded text-danger"
                                                            style="font-size:4rem">error</i>
                                                    </div>
                                                    <h5 class="fw-bold text-danger mb-1" id="rfidErrorTitle">Scan Failed
                                                    </h5>
                                                    <p class="text-muted small mb-0" id="rfidErrorMsg">—</p>
                                                </div>

                                            </div>
                                        </div>

                                        {{-- Scan history log --}}
                                        <div class="col-lg-5">
                                            <div class="card border h-100">
                                                <div
                                                    class="card-header py-2 d-flex align-items-center justify-content-between">
                                                    <span class="fw-semibold small">
                                                        <i class="material-symbols-rounded align-middle me-1"
                                                            style="font-size:1rem">history</i>
                                                        Today's Scans
                                                    </span>
                                                    <span class="badge bg-primary rounded-pill"
                                                        id="rfidScanCount">0</span>
                                                </div>
                                                <div class="card-body p-0" style="overflow-y:auto;max-height:360px;">
                                                    <div id="rfidScanLog">
                                                        <div class="text-center text-muted py-5 small" id="rfidLogEmpty">
                                                            <i class="material-symbols-rounded d-block mb-1"
                                                                style="font-size:2rem;opacity:.4">receipt_long</i>
                                                            No scans yet
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted small mt-3 mb-0">
                                        <i class="material-symbols-rounded align-middle me-1"
                                            style="font-size:.9rem">info</i>
                                        Make sure <code>rfid_bridge.py</code> is running and the Arduino is connected via
                                        USB.
                                    </p>
                                </div>

                                {{-- ── Face Recognition Panel ────────────────────── --}}
                                <div class="tab-pane fade" id="panel-face" role="tabpanel" aria-labelledby="tab-face">

                                    @if ($attendanceMode !== 'face_recognition')
                                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                                            <i class="material-symbols-rounded">warning</i>
                                            <div>
                                                Facial Recognition mode is currently <strong>inactive</strong>.
                                                Go to <a href="{{ route('admin.setup.settings.index') }}">Settings →
                                                    Attendance
                                                    System</a> to enable it.
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Status bar --}}
                                    <div class="rfid-status-bar d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3"
                                        id="faceStatusBar">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rfid-status-dot" id="faceStatusDot"></span>
                                            <span class="fw-semibold text-white small" id="faceStatusLabel">
                                                FACE RECOGNITION — STOPPED
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light py-1 px-3"
                                            onclick="faceScanTab.toggle()">
                                            <i class="material-symbols-rounded me-1 align-middle" style="font-size:.9rem"
                                                id="faceToggleIcon">play_circle</i>
                                            <span id="faceToggleLabel">Start</span>
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        {{-- Main camera display --}}
                                        <div class="col-lg-8">
                                            <div class="rfid-main-card rounded-4 overflow-hidden p-0 position-relative"
                                                id="faceMainCard" style="min-height:480px;background:#111;">

                                                {{-- Live camera feed (shown when active) --}}
                                                <video id="faceAttendVideo" class="w-100 d-none" autoplay playsinline
                                                    style="display:block;object-fit:cover;height:480px;"></video>
                                                {{-- Hidden capture canvas (never shown) --}}
                                                <canvas id="faceAttendCanvas" class="d-none"></canvas>
                                                {{-- Bounding-box overlay canvas (always on top of video) --}}
                                                <canvas id="faceBboxCanvas"
                                                    style="position:absolute;top:0;left:0;width:100%;height:480px;pointer-events:none;"></canvas>

                                                {{-- Waiting state --}}
                                                <div id="faceStateWaiting"
                                                    class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                                                    <div class="rfid-wave-container mb-3">
                                                        <div class="rfid-wave rfid-wave-1"></div>
                                                        <div class="rfid-wave rfid-wave-2"></div>
                                                        <div class="rfid-wave rfid-wave-3"></div>
                                                        <i
                                                            class="material-symbols-rounded rfid-center-icon text-primary">face_retouching_natural</i>
                                                    </div>
                                                    <h5 class="fw-bold mb-1 text-white" id="faceWaitTitle">Camera stopped
                                                    </h5>
                                                    <p class="text-white-50 small mb-0">Click Start to activate face
                                                        recognition</p>
                                                </div>

                                                {{-- Result overlay — shown on top of live video --}}
                                                <div id="faceStateResult"
                                                    class="d-none position-absolute bottom-0 start-0 w-100"
                                                    style="background:linear-gradient(transparent 0%,rgba(0,0,0,.92) 35%);padding:24px 20px 20px;">
                                                    {{-- Already-marked banner --}}
                                                    <div id="faceAlreadyBanner" class="d-none mb-2">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="badge bg-warning text-dark fw-bold px-3 py-2"
                                                                style="font-size:.85rem;border-radius:1rem;">
                                                                <i class="material-symbols-rounded align-middle me-1"
                                                                    style="font-size:1rem">task_alt</i>
                                                                ALREADY MARKED TODAY
                                                            </span>
                                                            <span class="text-white-50 small">
                                                                <i class="material-symbols-rounded align-middle"
                                                                    style="font-size:.85rem">login</i>
                                                                <span id="faceAlreadyCheckIn">—</span>
                                                                &nbsp;&nbsp;
                                                                <i class="material-symbols-rounded align-middle"
                                                                    style="font-size:.85rem">logout</i>
                                                                <span id="faceAlreadyCheckOut">—</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rfid-avatar rfid-avatar--in flex-shrink-0"
                                                            id="faceAvatar" style="width:64px;height:64px;">
                                                            <span id="faceAvatarInitials" class="rfid-avatar-text"
                                                                style="font-size:1.4rem;">?</span>
                                                        </div>
                                                        <div class="flex-grow-1 text-white">
                                                            <h4 class="fw-bold mb-0 lh-1" id="faceResultName"
                                                                style="font-size:1.35rem;">—</h4>
                                                            <p class="mb-1 mt-1" style="font-size:.85rem;">
                                                                <span
                                                                    class="badge bg-light text-dark border fw-semibold me-1"
                                                                    id="faceResultCode">—</span>
                                                                <span class="text-white-50" id="faceResultMeta">—</span>
                                                            </p>
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <span class="rfid-action-badge"
                                                                    id="faceResultAction">—</span>
                                                                <span class="badge bg-info text-white" id="faceResultConf"
                                                                    style="font-size:.72rem;"></span>
                                                            </div>
                                                        </div>
                                                        <div class="text-center text-white flex-shrink-0">
                                                            <div
                                                                style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.5)">
                                                                Time</div>
                                                            <div class="fw-bold" style="font-size:1.1rem;"
                                                                id="faceResultTime">—</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Error state --}}
                                                <div id="faceStateError"
                                                    class="d-none position-absolute bottom-0 start-0 w-100 p-3"
                                                    style="background:linear-gradient(transparent,rgba(0,0,0,.85));">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="material-symbols-rounded text-danger"
                                                            style="font-size:2rem;">face_retouching_off</i>
                                                        <div>
                                                            <div class="fw-bold text-white small" id="faceErrorTitle">Not
                                                                Recognised</div>
                                                            <div class="text-white-50" style="font-size:.78rem;"
                                                                id="faceErrorMsg">—</div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        {{-- Scan history log --}}
                                        <div class="col-lg-4">
                                            <div class="card border h-100">
                                                <div
                                                    class="card-header py-2 d-flex align-items-center justify-content-between">
                                                    <span class="fw-semibold small">
                                                        <i class="material-symbols-rounded align-middle me-1"
                                                            style="font-size:1rem">history</i>
                                                        Today's Recognitions
                                                    </span>
                                                    <span class="badge bg-primary rounded-pill"
                                                        id="faceScanCount">0</span>
                                                </div>
                                                <div class="card-body p-0" style="overflow-y:auto;max-height:360px;">
                                                    <div id="faceScanLog">
                                                        <div class="text-center text-muted py-5 small" id="faceLogEmpty">
                                                            <i class="material-symbols-rounded d-block mb-1"
                                                                style="font-size:2rem;opacity:.4">face</i>
                                                            No recognitions yet
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted small mt-3 mb-0">
                                        <i class="material-symbols-rounded align-middle me-1"
                                            style="font-size:.9rem">info</i>
                                        Make sure the <code>Facial Recognition Attendance Systems</code> service is running
                                        on port 5004.
                                    </p>
                                </div>

                                {{-- ── Manual Entry Panel ───────────────────────── --}}
                                <div class="tab-pane fade show active" id="panel-manual" role="tabpanel"
                                    aria-labelledby="tab-manual">
                                    <div class="row">
                                        <!-- Student Search -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-gradient-primary">
                                                    <h6 class="text-white mb-0">{{ __('Step 1: Find Student') }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="input-group input-group-outline mb-3">
                                                        <label class="form-label">{{ __('Enter Student Code') }}</label>
                                                        <input type="text" class="form-control" id="studentCodeInput"
                                                            autofocus>
                                                    </div>
                                                    <button type="button" class="btn btn-primary w-100"
                                                        onclick="searchStudent()">
                                                        <i class="material-symbols-rounded text-sm">search</i>
                                                        {{ __('Search Student') }}
                                                    </button>

                                                    <!-- Student Info Display -->
                                                    <div id="studentInfo" class="mt-4" style="display: none;">
                                                        <div class="alert alert-success">
                                                            <h6 class="mb-2">{{ __('Student Found') }}</h6>
                                                            <div class="row">
                                                                <div class="col-6"><strong>{{ __('Name') }}:</strong>
                                                                </div>
                                                                <div class="col-6" id="studentName">-</div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6"><strong>{{ __('Code') }}:</strong>
                                                                </div>
                                                                <div class="col-6" id="studentCode">-</div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6"><strong>{{ __('Class') }}:</strong>
                                                                </div>
                                                                <div class="col-6" id="studentClass">-</div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6"><strong>{{ __('Grade') }}:</strong>
                                                                </div>
                                                                <div class="col-6" id="studentGrade">-</div>
                                                            </div>
                                                        </div>

                                                        <!-- Today's Attendance Status -->
                                                        <div id="todayStatus" class="alert alert-info mt-3">
                                                            <h6 class="mb-2">{{ __("Today's Status") }}</h6>
                                                            <div id="statusContent">
                                                                <p class="mb-0">
                                                                    {{ __('No attendance recorded yet today') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="studentError" class="alert alert-danger mt-3"
                                                        style="display: none;">
                                                        <p class="mb-0" id="errorMessage">{{ __('Student not found') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attendance Form -->
                                        <div class="col-md-6">
                                            <div class="card border">
                                                <div class="card-header bg-gradient-success">
                                                    <h6 class="text-white mb-0">{{ __('Step 2: Record Attendance') }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <form id="attendanceForm">
                                                        @csrf
                                                        <input type="hidden" id="selectedStudentCode"
                                                            name="student_code">

                                                        <div class="input-group input-group-outline mb-3">
                                                            <label class="form-label">{{ __('Attendance Type') }}</label>
                                                            <select class="form-control" id="attendanceType"
                                                                name="attendance_type" required>
                                                                <option value="">{{ __('Select Type') }}</option>
                                                                <option value="check_in">{{ __('Check In') }}</option>
                                                                <option value="check_out">{{ __('Check Out') }}</option>
                                                                <option value="absent">{{ __('Mark Absent') }}</option>
                                                            </select>
                                                        </div>

                                                        <div class="input-group input-group-outline mb-3">
                                                            <label class="form-label">{{ __('Date') }}</label>
                                                            <input type="date" class="form-control" name="date"
                                                                id="attendanceDate" value="{{ date('Y-m-d') }}">
                                                        </div>

                                                        <div id="checkInTimeGroup"
                                                            class="input-group input-group-outline mb-3"
                                                            style="display: none;">
                                                            <label class="form-label">{{ __('Check In Time') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="check_in_time" id="checkInTime">
                                                        </div>

                                                        <div id="checkOutTimeGroup"
                                                            class="input-group input-group-outline mb-3"
                                                            style="display: none;">
                                                            <label class="form-label">{{ __('Check Out Time') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="check_out_time" id="checkOutTime">
                                                        </div>

                                                        <div class="input-group input-group-outline mb-3">
                                                            <label class="form-label">{{ __('Notes (Optional)') }}</label>
                                                            <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                                                        </div>

                                                        <button type="submit" class="btn btn-success w-100"
                                                            id="submitBtn" disabled>
                                                            <i class="material-symbols-rounded text-sm">save</i>
                                                            {{ __('Record Attendance') }}
                                                        </button>
                                                    </form>

                                                    <div id="successMessage" class="alert alert-success mt-3"
                                                        style="display: none;">
                                                        <p class="mb-0" id="successText">
                                                            {{ __('Attendance recorded successfully!') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>{{-- /panel-manual --}}
                            </div>{{-- /tab-content --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentStudent = null;

            // Search student by code
            async function searchStudent() {
                const code = document.getElementById('studentCodeInput').value.trim();

                if (!code) {
                    alert('{{ __('Please enter a student code') }}');
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.management.attendance.search-student') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            code: code
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        currentStudent = result.data.student;
                        displayStudentInfo(result.data);
                        document.getElementById('selectedStudentCode').value = currentStudent.student_code;
                        document.getElementById('submitBtn').disabled = false;
                    } else {
                        showError(result.message);
                    }
                } catch (error) {
                    showError('{{ __('Error searching for student') }}');
                    console.error(error);
                }
            }

            // Display student information
            function displayStudentInfo(data) {
                document.getElementById('studentName').textContent = data.student.full_name;
                document.getElementById('studentCode').textContent = data.student.student_code;
                document.getElementById('studentClass').textContent = data.student.class_name;
                document.getElementById('studentGrade').textContent = data.student.grade_level;

                if (data.today_attendance) {
                    const status = data.today_attendance;
                    document.getElementById('statusContent').innerHTML = `
            <div class="row">
                <div class="col-6"><strong>{{ __('Status') }}:</strong></div>
                <div class="col-6">${status.status}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>{{ __('Check In') }}:</strong></div>
                <div class="col-6">${status.check_in_time || '-'}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>{{ __('Check Out') }}:</strong></div>
                <div class="col-6">${status.check_out_time || '-'}</div>
            </div>
            ${status.is_late ? '<p class="text-warning mb-0 mt-2"><i class="material-symbols-rounded text-sm">schedule</i> {{ __('Late arrival') }}</p>' : ''}
        `;
                    document.getElementById('todayStatus').classList.remove('alert-info');
                    document.getElementById('todayStatus').classList.add('alert-warning');
                }

                document.getElementById('studentInfo').style.display = 'block';
                document.getElementById('studentError').style.display = 'none';
            }

            // Show error message
            function showError(message) {
                document.getElementById('errorMessage').textContent = message;
                document.getElementById('studentError').style.display = 'block';
                document.getElementById('studentInfo').style.display = 'none';
                document.getElementById('submitBtn').disabled = true;
            }

            // Handle attendance type change
            document.getElementById('attendanceType').addEventListener('change', function() {
                const type = this.value;

                document.getElementById('checkInTimeGroup').style.display = type === 'check_in' ? 'block' : 'none';
                document.getElementById('checkOutTimeGroup').style.display = type === 'check_out' ? 'block' : 'none';

                // Set default times
                if (type === 'check_in') {
                    document.getElementById('checkInTime').value = new Date().toTimeString().slice(0, 5);
                }
                if (type === 'check_out') {
                    document.getElementById('checkOutTime').value = new Date().toTimeString().slice(0, 5);
                }
            });

            // Handle form submission
            document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                try {
                    const response = await fetch('{{ route('admin.management.attendance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('successText').textContent = result.message;
                        document.getElementById('successMessage').style.display = 'block';

                        // Reset form after 2 seconds
                        setTimeout(() => {
                            this.reset();
                            document.getElementById('studentCodeInput').value = '';
                            document.getElementById('studentInfo').style.display = 'none';
                            document.getElementById('successMessage').style.display = 'none';
                            document.getElementById('submitBtn').disabled = true;
                            currentStudent = null;
                        }, 2000);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('{{ __('Error recording attendance') }}');
                    console.error(error);
                }
            });

            // Allow Enter key to search
            document.getElementById('studentCodeInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchStudent();
                }
            });

            // ── RFID Scan Mode ────────────────────────────────────────────────
            const rfidScanTab = (() => {
                let _pollTimer = null;
                let _lastScannedAt = null;
                let _resetTimer = null;
                let _scanCount = 0;
                let _active = false;
                const POLL_MS = 1500;
                const RESET_MS = 6000;

                /* ─── start / stop / toggle ─── */
                function start() {
                    if (_pollTimer) return;
                    _active = true;
                    _pollTimer = setInterval(_poll, POLL_MS);
                    _setStatusBar(true);
                }

                function stop() {
                    clearInterval(_pollTimer);
                    _pollTimer = null;
                    _active = false;
                    _setStatusBar(false);
                }

                function toggle() {
                    _active ? stop() : start();
                }

                /* ─── status bar ─── */
                function _setStatusBar(active) {
                    const bar = document.getElementById('rfidStatusBar');
                    const dot = document.getElementById('rfidStatusDot');
                    const label = document.getElementById('rfidStatusLabel');
                    const icon = document.getElementById('rfidToggleIcon');
                    const lbl = document.getElementById('rfidToggleLabel');
                    if (active) {
                        bar.className =
                            'rfid-status-bar rfid-status-bar--active d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3';
                        dot.className = 'rfid-status-dot rfid-status-dot--active';
                        label.textContent = 'RFID SCAN MODE — ACTIVE';
                        icon.textContent = 'stop_circle';
                        lbl.textContent = 'Stop';
                    } else {
                        bar.className =
                            'rfid-status-bar rfid-status-bar--stopped d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3';
                        dot.className = 'rfid-status-dot';
                        label.textContent = 'RFID SCAN MODE — STOPPED';
                        icon.textContent = 'play_circle';
                        lbl.textContent = 'Start';
                        _showState('waiting');
                    }
                }

                /* ─── polling ─── */
                async function _poll() {
                    try {
                        const resp = await fetch('{{ url('/api/attendance/rfid-last-scan') }}');
                        const data = await resp.json();
                        if (data.found && data.data && data.data.scanned_at !== _lastScannedAt) {
                            _lastScannedAt = data.data.scanned_at;
                            _renderResult(data.data);
                        }
                    } catch (_) {
                        /* network blip */
                    }
                }

                /* ─── render result ─── */
                function _renderResult(d) {
                    clearTimeout(_resetTimer);

                    const name = d.student_name || 'Unknown';
                    const code = d.student_code || '—';
                    const grade = d.grade || '—';
                    const cls = d.class || '—';
                    const initials = _getInitials(name);

                    document.getElementById('rfidResultName').textContent = name;
                    document.getElementById('rfidResultCode').textContent = code;
                    document.getElementById('rfidResultMeta').textContent = `Grade ${grade} · ${cls}`;
                    document.getElementById('rfidAvatarInitials').textContent = initials;

                    const avatar = document.getElementById('rfidAvatar');
                    const actionBadge = document.getElementById('rfidResultAction');
                    const timEl = document.getElementById('rfidResultTime');
                    const extraWrap = document.getElementById('rfidDetailExtraWrap');
                    const extraLabel = document.getElementById('rfidDetailExtraLabel');
                    const extraValue = document.getElementById('rfidDetailExtraValue');

                    if (d.action === 'check_in') {
                        avatar.className = 'rfid-avatar rfid-avatar--in mx-auto mb-3';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--in';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">login</i>CHECKED IN`;
                        timEl.textContent = d.time || '—';
                        if (d.is_late) {
                            extraWrap.classList.remove('d-none');
                            extraLabel.textContent = 'Status';
                            extraValue.innerHTML = `<span class="text-warning fw-bold">Late</span>`;
                        } else {
                            extraWrap.classList.add('d-none');
                        }
                        _addToLog(d, 'in');
                    } else if (d.action === 'check_out') {
                        avatar.className = 'rfid-avatar rfid-avatar--out mx-auto mb-3';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--out';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">logout</i>CHECKED OUT`;
                        timEl.textContent = d.time || '—';
                        if (d.duration) {
                            extraWrap.classList.remove('d-none');
                            extraLabel.textContent = 'Duration';
                            extraValue.textContent = d.duration;
                        } else {
                            extraWrap.classList.add('d-none');
                        }
                        _addToLog(d, 'out');
                    } else if (d.action === 'already_complete') {
                        avatar.className = 'rfid-avatar rfid-avatar--done mx-auto mb-3';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--done';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">task_alt</i>ALREADY RECORDED`;
                        timEl.textContent = d.check_in || '—';
                        extraWrap.classList.remove('d-none');
                        extraLabel.textContent = 'Check-Out';
                        extraValue.textContent = d.check_out || '—';
                        _addToLog(d, 'done');
                    } else {
                        _showError('Unknown action', 'Unrecognised scan action: ' + d.action);
                        return;
                    }

                    _showState('result');
                    _resetTimer = setTimeout(() => _showState('waiting'), RESET_MS);
                }

                /* ─── helpers ─── */
                function _showState(state) {
                    ['waiting', 'result', 'error'].forEach(s => {
                        const el = document.getElementById('rfidState' + s.charAt(0).toUpperCase() + s.slice(
                            1));
                        if (el) el.classList.toggle('d-none', s !== state);
                    });
                }

                function _showError(title, msg) {
                    document.getElementById('rfidErrorTitle').textContent = title;
                    document.getElementById('rfidErrorMsg').textContent = msg;
                    _showState('error');
                    _resetTimer = setTimeout(() => _showState('waiting'), RESET_MS);
                }

                function _getInitials(name) {
                    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('');
                }

                function _addToLog(d, type) {
                    const log = document.getElementById('rfidScanLog');
                    const empty = document.getElementById('rfidLogEmpty');
                    if (empty) empty.remove();

                    _scanCount++;
                    document.getElementById('rfidScanCount').textContent = _scanCount;

                    const colours = {
                        in: 'success',
                        out: 'primary',
                        done: 'warning'
                    };
                    const icons = {
                        in: 'login',
                        out: 'logout',
                        done: 'task_alt'
                    };
                    const labels = {
                        in: 'Check In',
                        out: 'Check Out',
                        done: 'Already In'
                    };
                    const c = colours[type] || 'secondary';
                    const ico = icons[type] || 'radio_button_checked';
                    const lbl = labels[type] || type;
                    const time = d.time || d.scanned_at || '';

                    const row = document.createElement('div');
                    row.className = 'rfid-log-row d-flex align-items-center gap-2 px-3 py-2 border-bottom';
                    row.innerHTML = `
                        <i class="material-symbols-rounded text-${c}" style="font-size:1.2rem">${ico}</i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate small">${d.student_name || '—'}</div>
                            <div class="text-muted" style="font-size:.75rem">${d.student_code || ''}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${c}-subtle text-${c} border border-${c}-subtle">${lbl}</span>
                            <div class="text-muted" style="font-size:.72rem">${time}</div>
                        </div>`;
                    log.insertBefore(row, log.firstChild);
                }

                // Auto-start when RFID tab is shown; stop when leaving
                document.getElementById('tab-rfid').addEventListener('shown.bs.tab', start);
                document.getElementById('tab-manual').addEventListener('shown.bs.tab', stop);

                return {
                    start,
                    stop,
                    toggle
                };
            })();

            // ── Face Recognition Mode ────────────────────────────────────────
            const faceScanTab = (() => {
                let _pollTimer = null;
                let _captureTimer = null;
                let _stream = null;
                let _lastScanAt = null;
                let _resetTimer = null;
                let _scanCount = 0;
                let _active = false;
                const POLL_MS = 1000;
                const CAPTURE_MS = 1000;
                const RESET_MS = 5000;

                /* ── start / stop / toggle ── */
                async function start() {
                    if (_active) return;
                    _active = true;
                    _setStatusBar(true);
                    await _startCamera();
                    _pollTimer = setInterval(_poll, POLL_MS);
                    _captureTimer = setInterval(_sendFrame, CAPTURE_MS);
                    _showState('waiting');
                    document.getElementById('faceWaitTitle').textContent = 'Looking for faces…';
                    document.getElementById('faceAttendVideo').classList.remove('d-none');
                    document.getElementById('faceStateWaiting').classList.add('face-overlay-waiting');
                }

                function stop() {
                    clearInterval(_pollTimer);
                    clearInterval(_captureTimer);
                    _pollTimer = _captureTimer = null;
                    _active = false;
                    _stopCamera();
                    _setStatusBar(false);
                    document.getElementById('faceAttendVideo').classList.add('d-none');
                    document.getElementById('faceStateWaiting').classList.remove('face-overlay-waiting');
                    document.getElementById('faceWaitTitle').textContent = 'Camera stopped';
                    _showState('waiting');
                    _drawFaceBoxes([], 640, 480);
                }

                function toggle() {
                    _active ? stop() : start();
                }

                /* ── camera ── */
                async function _startCamera() {
                    try {
                        _stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                width: 640,
                                height: 480,
                                facingMode: 'user'
                            },
                            audio: false
                        });
                        document.getElementById('faceAttendVideo').srcObject = _stream;
                    } catch (_) {
                        /* camera unavailable */
                    }
                }

                function _stopCamera() {
                    if (_stream) {
                        _stream.getTracks().forEach(t => t.stop());
                        _stream = null;
                    }
                    const v = document.getElementById('faceAttendVideo');
                    if (v) v.srcObject = null;
                }

                /* ── send camera frame for recognition ── */
                async function _sendFrame() {
                    const video = document.getElementById('faceAttendVideo');
                    const canvas = document.getElementById('faceAttendCanvas');
                    if (!video || video.readyState < 2) return;
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    const imageB64 = canvas.toDataURL('image/jpeg', 0.82);
                    try {
                        const resp = await fetch('{{ url('/api/face/attendance/recognize') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content ?? '',
                            },
                            body: JSON.stringify({
                                image: imageB64
                            }),
                        });
                        const data = await resp.json();
                        _drawFaceBoxes(data.faces || [], video.videoWidth || 640, video.videoHeight || 480);
                        // Render result card immediately — don't wait for the poll cycle
                        const scanData = data.data;
                        if (scanData && scanData.scanned_at && scanData.scanned_at !== _lastScanAt) {
                            _lastScanAt = scanData.scanned_at;
                            _renderResult(scanData);
                        }
                    } catch (_) {
                        _drawFaceBoxes([], 640, 480);
                    }
                }

                /* ── draw bounding boxes + confidence on overlay canvas ── */
                function _drawFaceBoxes(faces, vw, vh) {
                    const overlay = document.getElementById('faceBboxCanvas');
                    if (!overlay) return;
                    overlay.width = overlay.offsetWidth || vw;
                    overlay.height = overlay.offsetHeight || vh;
                    const ctx = overlay.getContext('2d');
                    ctx.clearRect(0, 0, overlay.width, overlay.height);
                    if (!faces || !faces.length) return;
                    const scaleX = overlay.width / vw;
                    const scaleY = overlay.height / vh;
                    for (const face of faces) {
                        if (!face.bbox) continue;
                        const [x1, y1, x2, y2] = face.bbox;
                        const x = x1 * scaleX,
                            y = y1 * scaleY;
                        const w = (x2 - x1) * scaleX,
                            h = (y2 - y1) * scaleY;
                        const color = face.is_recognized ? '#00ff88' : '#ffc107';
                        /* semi-transparent fill */
                        ctx.fillStyle = face.is_recognized ?
                            'rgba(0,255,136,0.07)' :
                            'rgba(255,193,7,0.07)';
                        ctx.fillRect(x, y, w, h);
                        /* main box */
                        ctx.strokeStyle = color;
                        ctx.lineWidth = 2;
                        ctx.strokeRect(x, y, w, h);
                        /* corner accents */
                        const cLen = Math.min(w, h) * 0.22;
                        ctx.strokeStyle = color;
                        ctx.lineWidth = 4;
                        ctx.beginPath();
                        ctx.moveTo(x, y + cLen);
                        ctx.lineTo(x, y);
                        ctx.lineTo(x + cLen, y);
                        ctx.moveTo(x + w - cLen, y);
                        ctx.lineTo(x + w, y);
                        ctx.lineTo(x + w, y + cLen);
                        ctx.moveTo(x, y + h - cLen);
                        ctx.lineTo(x, y + h);
                        ctx.lineTo(x + cLen, y + h);
                        ctx.moveTo(x + w - cLen, y + h);
                        ctx.lineTo(x + w, y + h);
                        ctx.lineTo(x + w, y + h - cLen);
                        ctx.stroke();
                        /* label pill */
                        const label = face.is_recognized ?
                            `${face.student_name || ''}  ${face.confidence}%` :
                            `${face.confidence}%`;
                        ctx.font = 'bold 13px monospace';
                        const tw = ctx.measureText(label).width;
                        const pad = 6,
                            lh = 20;
                        const lx = x,
                            ly = y > lh + 4 ? y - lh - 4 : y + h + 4;
                        ctx.fillStyle = face.is_recognized ? 'rgba(0,255,136,0.88)' : 'rgba(255,193,7,0.88)';
                        ctx.beginPath();
                        ctx.roundRect(lx, ly, tw + pad * 2, lh, 4);
                        ctx.fill();
                        ctx.fillStyle = '#000';
                        ctx.fillText(label, lx + pad, ly + lh - 4);
                    }
                }

                /* ── status bar ── */
                function _setStatusBar(active) {
                    const bar = document.getElementById('faceStatusBar');
                    const dot = document.getElementById('faceStatusDot');
                    const label = document.getElementById('faceStatusLabel');
                    const icon = document.getElementById('faceToggleIcon');
                    const lbl = document.getElementById('faceToggleLabel');
                    if (active) {
                        bar.className =
                            'rfid-status-bar rfid-status-bar--active d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3';
                        dot.className = 'rfid-status-dot rfid-status-dot--active';
                        label.textContent = 'FACE RECOGNITION — ACTIVE';
                        icon.textContent = 'stop_circle';
                        lbl.textContent = 'Stop';
                    } else {
                        bar.className =
                            'rfid-status-bar rfid-status-bar--stopped d-flex align-items-center justify-content-between px-3 py-2 rounded-3 mb-3';
                        dot.className = 'rfid-status-dot';
                        label.textContent = 'FACE RECOGNITION — STOPPED';
                        icon.textContent = 'play_circle';
                        lbl.textContent = 'Start';
                    }
                }

                /* ── polling ── */
                async function _poll() {
                    try {
                        const resp = await fetch('{{ url('/api/face/attendance/last-scan') }}');
                        const data = await resp.json();
                        if (data.found && data.data && data.data.scanned_at !== _lastScanAt) {
                            _lastScanAt = data.data.scanned_at;
                            _renderResult(data.data);
                        }
                    } catch (_) {
                        /* network blip */
                    }
                }

                /* ── render result ── */
                function _renderResult(d) {
                    clearTimeout(_resetTimer);
                    const name = d.student_name || 'Unknown';
                    const code = d.student_code || '—';
                    const grade = d.grade || '—';
                    const cls = d.class || '—';
                    const initials = name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('');

                    document.getElementById('faceResultName').textContent = name;
                    document.getElementById('faceResultCode').textContent = code;
                    document.getElementById('faceResultMeta').textContent = `Grade ${grade} · ${cls}`;
                    document.getElementById('faceAvatarInitials').textContent = initials;
                    document.getElementById('faceResultTime').textContent = d.time || '—';

                    // Confidence badge
                    const confEl = document.getElementById('faceResultConf');
                    if (d.confidence) {
                        confEl.textContent = `${d.confidence} match`;
                        confEl.classList.remove('d-none');
                    } else {
                        confEl.classList.add('d-none');
                    }

                    const avatar = document.getElementById('faceAvatar');
                    const actionBadge = document.getElementById('faceResultAction');
                    const alreadyBanner = document.getElementById('faceAlreadyBanner');

                    // Reset already banner
                    alreadyBanner.classList.add('d-none');

                    if (d.action === 'check_in') {
                        avatar.className = 'rfid-avatar rfid-avatar--in flex-shrink-0';
                        avatar.style.cssText = 'width:64px;height:64px;';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--in';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">login</i>CHECKED IN`;
                        _addToLog(d, 'in');
                    } else if (d.action === 'check_out') {
                        avatar.className = 'rfid-avatar rfid-avatar--out flex-shrink-0';
                        avatar.style.cssText = 'width:64px;height:64px;';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--out';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">logout</i>CHECKED OUT`;
                        _addToLog(d, 'out');
                    } else if (d.action === 'already_complete') {
                        avatar.className = 'rfid-avatar rfid-avatar--done flex-shrink-0';
                        avatar.style.cssText = 'width:64px;height:64px;';
                        actionBadge.className = 'rfid-action-badge rfid-action-badge--done';
                        actionBadge.innerHTML =
                            `<i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">task_alt</i>FULLY RECORDED`;
                        // Show check-in / check-out times in banner
                        document.getElementById('faceAlreadyCheckIn').textContent = d.check_in || '—';
                        document.getElementById('faceAlreadyCheckOut').textContent = d.check_out || '—';
                        // Override the Time field to show check-in time
                        document.getElementById('faceResultTime').textContent = d.check_in || d.time || '—';
                        alreadyBanner.classList.remove('d-none');
                        _addToLog(d, 'done');
                    } else {
                        document.getElementById('faceErrorTitle').textContent = 'Not Recognised';
                        document.getElementById('faceErrorMsg').textContent = d.message ||
                            'Face not found in database.';
                        _showState('error');
                        _resetTimer = setTimeout(() => _showState('waiting'), RESET_MS);
                        return;
                    }

                    _showState('result');
                    _resetTimer = setTimeout(() => _showState('waiting'), RESET_MS);
                }

                /* ── helpers ── */
                function _showState(state) {
                    document.getElementById('faceStateWaiting').classList.toggle('d-none', state !== 'waiting');
                    document.getElementById('faceStateResult').classList.toggle('d-none', state !== 'result');
                    document.getElementById('faceStateError').classList.toggle('d-none', state !== 'error');
                    // Keep video visible if active
                    if (_active) {
                        document.getElementById('faceAttendVideo').classList.remove('d-none');
                    }
                }

                function _addToLog(d, type) {
                    const log = document.getElementById('faceScanLog');
                    const empty = document.getElementById('faceLogEmpty');
                    if (empty) empty.remove();
                    _scanCount++;
                    document.getElementById('faceScanCount').textContent = _scanCount;
                    const colours = {
                        in: 'success',
                        out: 'primary',
                        done: 'warning'
                    };
                    const icons = {
                        in: 'login',
                        out: 'logout',
                        done: 'task_alt'
                    };
                    const labels = {
                        in: 'Check In',
                        out: 'Check Out',
                        done: 'Already In'
                    };
                    const c = colours[type] || 'secondary';
                    const ico = icons[type] || 'face';
                    const lbl = labels[type] || type;
                    const row = document.createElement('div');
                    row.className = 'rfid-log-row d-flex align-items-center gap-2 px-3 py-2 border-bottom';
                    row.innerHTML = `
                        <i class="material-symbols-rounded text-${c}" style="font-size:1.2rem">${ico}</i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate small">${d.student_name || '—'}</div>
                            <div class="text-muted" style="font-size:.75rem">${d.student_code || ''}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${c}-subtle text-${c} border border-${c}-subtle">${lbl}</span>
                            <div class="text-muted" style="font-size:.72rem">${d.time || ''}</div>
                        </div>`;
                    log.insertBefore(row, log.firstChild);
                }

                document.getElementById('tab-face').addEventListener('shown.bs.tab', start);
                document.getElementById('tab-manual').addEventListener('shown.bs.tab', () => {
                    stop();
                    rfidScanTab.stop();
                });

                return {
                    start,
                    stop,
                    toggle
                };
            })();
        </script>

        <style>
            /* ── RFID Status Bar ─────────────────────────────── */
            .rfid-status-bar {
                transition: background .3s;
            }

            .rfid-status-bar--active {
                background: linear-gradient(135deg, #1a7a4a 0%, #198754 100%);
            }

            .rfid-status-bar--stopped {
                background: #6c757d;
            }

            .rfid-status-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #adb5bd;
                display: inline-block;
            }

            .rfid-status-dot--active {
                background: #a8ffcd;
                box-shadow: 0 0 0 0 rgba(168, 255, 205, .8);
                animation: rfid-dot-ping 1.4s ease-in-out infinite;
            }

            @keyframes rfid-dot-ping {
                0% {
                    box-shadow: 0 0 0 0 rgba(168, 255, 205, .8);
                }

                70% {
                    box-shadow: 0 0 0 8px rgba(168, 255, 205, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(168, 255, 205, 0);
                }
            }

            /* ── Main display card ───────────────────────────── */
            .rfid-main-card {
                background: #f8f9ff;
                border: 2px solid #e9ecef;
                min-height: 320px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .rfid-main-card>div {
                width: 100%;
            }

            /* ── Wave animation (waiting state) ─────────────── */
            .rfid-wave-container {
                position: relative;
                width: 120px;
                height: 120px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .rfid-wave {
                position: absolute;
                border-radius: 50%;
                border: 2px solid rgba(13, 110, 253, .35);
                animation: rfid-wave-expand 2.4s ease-out infinite;
            }

            .rfid-wave-1 {
                width: 60px;
                height: 60px;
                animation-delay: 0s;
            }

            .rfid-wave-2 {
                width: 90px;
                height: 90px;
                animation-delay: .6s;
            }

            .rfid-wave-3 {
                width: 120px;
                height: 120px;
                animation-delay: 1.2s;
            }

            @keyframes rfid-wave-expand {
                0% {
                    transform: scale(.6);
                    opacity: .8;
                }

                100% {
                    transform: scale(1.2);
                    opacity: 0;
                }
            }

            .rfid-center-icon {
                font-size: 3rem;
                z-index: 1;
                position: relative;
            }

            /* ── Avatar ──────────────────────────────────────── */
            .rfid-avatar {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background .3s;
            }

            .rfid-avatar--in {
                background: #d1fae5;
                border: 3px solid #10b981;
            }

            .rfid-avatar--out {
                background: #dbeafe;
                border: 3px solid #3b82f6;
            }

            .rfid-avatar--done {
                background: #fef3c7;
                border: 3px solid #f59e0b;
            }

            .rfid-avatar-text {
                font-size: 1.6rem;
                font-weight: 700;
                letter-spacing: .5px;
            }

            .rfid-avatar--in .rfid-avatar-text {
                color: #065f46;
            }

            .rfid-avatar--out .rfid-avatar-text {
                color: #1e40af;
            }

            .rfid-avatar--done .rfid-avatar-text {
                color: #92400e;
            }

            /* ── Action badge ────────────────────────────────── */
            .rfid-action-badge {
                display: inline-flex;
                align-items: center;
                padding: .45rem 1.2rem;
                border-radius: 2rem;
                font-weight: 700;
                font-size: .8rem;
                letter-spacing: .06em;
            }

            .rfid-action-badge--in {
                background: #d1fae5;
                color: #065f46;
                border: 1.5px solid #10b981;
            }

            .rfid-action-badge--out {
                background: #dbeafe;
                color: #1e40af;
                border: 1.5px solid #3b82f6;
            }

            .rfid-action-badge--done {
                background: #fef3c7;
                color: #92400e;
                border: 1.5px solid #f59e0b;
            }

            /* ── Detail row ──────────────────────────────────── */
            .rfid-detail-row {
                background: #f1f3f9;
            }

            .rfid-detail-label {
                font-size: .7rem;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: #6c757d;
            }

            .rfid-detail-value {
                font-size: .95rem;
                font-weight: 600;
                color: #212529;
            }

            /* ── Log rows ────────────────────────────────────── */
            .rfid-log-row:last-child {
                border-bottom: 0 !important;
            }

            /* ── Face panel overlays ─────────────────────────── */
            .face-overlay-waiting {
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, .45);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .face-overlay-waiting h5,
            .face-overlay-waiting p {
                color: #fff !important;
            }

            #panel-face .rfid-main-card {
                background: #111;
                border-color: #333;
            }
        </style>
    </main>
@endsection
