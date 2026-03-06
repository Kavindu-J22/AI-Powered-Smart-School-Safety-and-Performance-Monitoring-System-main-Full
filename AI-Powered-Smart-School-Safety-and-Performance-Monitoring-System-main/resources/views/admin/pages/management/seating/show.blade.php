@extends('admin.layouts.app')

@section('title')
    Seating – Grade {{ $grade }}-{{ $section }}
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')

                    <!-- Header Card -->
                    <div class="card my-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="material-symbols-rounded me-2 icon-size-sm">chair</i>
                                    Seating Arrangement — Grade {{ $grade }}-{{ $section }}
                                </h6>
                                <p class="text-sm text-secondary mb-0 mt-1">
                                    {{ $students->count() }} students enrolled &bull; AI-optimised arrangement
                                </p>
                            </div>
                            <a href="{{ route('admin.management.seating.index') }}" class="btn btn-outline-dark btn-sm">
                                <i class="material-symbols-rounded me-1 icon-size-sm">arrow_back</i>Back
                            </a>
                        </div>

                        <div class="card-body px-4">

                            <!-- Generation Controls -->
                            <div class="card bg-gradient-dark mb-4">
                                <div class="card-body py-3 px-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h6 class="text-white mb-1">
                                                <i class="material-symbols-rounded me-1"
                                                    style="vertical-align:middle">auto_awesome</i>
                                                AI Seating Generator
                                            </h6>
                                            <p class="text-sm mb-0" style="color:#adb5bd">
                                                Optimally arranges students based on academic performance.
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label text-white text-xs mb-1">Seats per Row</label>
                                                    <input type="number" id="seatsPerRow"
                                                        class="form-control form-control-sm" value="5" min="2"
                                                        max="10">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label text-white text-xs mb-1">Total Rows</label>
                                                    <input type="number" id="totalRows"
                                                        class="form-control form-control-sm" value="6" min="2"
                                                        max="15">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button id="generateBtn" class="btn btn-primary btn-sm px-4"
                                                onclick="generateSeating()">
                                                <i class="material-symbols-rounded me-1" style="font-size:16px">refresh</i>
                                                {{ $saved ? 'Regenerate' : 'Generate Seating' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Generation Status -->
                            <div id="generationStatus" class="alert d-none mb-4" role="alert"></div>

                            <!-- Loading -->
                            <div id="loadingState" class="text-center py-5 d-none">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-secondary mt-3 mb-0">AI is computing optimal seating arrangement…</p>
                            </div>

                            <!-- Seating Map -->
                            <div id="seatingMapContainer" class="{{ $saved ? '' : 'd-none' }}">

                                <!-- Stats row -->
                                <div class="row mb-4" id="statsRow">
                                    <div class="col-md-3">
                                        <div class="card text-center p-3">
                                            <div class="text-primary mb-1">
                                                <i class="material-symbols-rounded" style="font-size:28px">groups</i>
                                            </div>
                                            <h4 class="mb-0" id="statStudents">{{ $students->count() }}</h4>
                                            <small class="text-secondary">Students</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center p-3">
                                            <div class="text-success mb-1">
                                                <i class="material-symbols-rounded" style="font-size:28px">event_seat</i>
                                            </div>
                                            <h4 class="mb-0" id="statSeats">—</h4>
                                            <small class="text-secondary">Total Seats</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center p-3">
                                            <div class="text-info mb-1">
                                                <i class="material-symbols-rounded" style="font-size:28px">view_week</i>
                                            </div>
                                            <h4 class="mb-0" id="statRows">—</h4>
                                            <small class="text-secondary">Rows</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center p-3">
                                            <div class="text-warning mb-1">
                                                <i class="material-symbols-rounded" style="font-size:28px">schedule</i>
                                            </div>
                                            <p class="mb-0 text-xs" id="statGenTime">—</p>
                                            <small class="text-secondary">Generated</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="d-flex gap-3 mb-3 align-items-center flex-wrap">
                                    <span class="fw-bold text-sm">Performance Legend:</span>
                                    <span class="d-flex align-items-center gap-1">
                                        <span class="seat-legend seat-high"></span>
                                        <small>High (≥80)</small>
                                    </span>
                                    <span class="d-flex align-items-center gap-1">
                                        <span class="seat-legend seat-mid"></span>
                                        <small>Mid (60–79)</small>
                                    </span>
                                    <span class="d-flex align-items-center gap-1">
                                        <span class="seat-legend seat-low"></span>
                                        <small>Low (&lt;60)</small>
                                    </span>
                                    <span class="d-flex align-items-center gap-1">
                                        <span class="seat-legend seat-empty"></span>
                                        <small>Empty Seat</small>
                                    </span>
                                </div>

                                <!-- Board indicator -->
                                <div class="text-center mb-2">
                                    <div class="board-indicator mx-auto">
                                        <i class="material-symbols-rounded me-1"
                                            style="font-size:14px">cast_for_education</i>
                                        BOARD / FRONT
                                    </div>
                                </div>

                                <!-- Classroom grid -->
                                <div class="classroom-container mb-4">
                                    <div id="classroomGrid" class="classroom-grid"></div>
                                </div>

                                <!-- Student list table -->
                                <div class="card">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0 text-sm">
                                            <i class="material-symbols-rounded me-1 icon-size-sm">table_rows</i>
                                            Student Seat Assignments
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" id="studentSeatTable">
                                                <thead>
                                                    <tr>
                                                        <th class="text-xs text-secondary fw-bold ps-3">Student</th>
                                                        <th class="text-xs text-secondary fw-bold">Row</th>
                                                        <th class="text-xs text-secondary fw-bold">Seat</th>
                                                        <th class="text-xs text-secondary fw-bold">Seat Label</th>
                                                        <th class="text-xs text-secondary fw-bold">Avg Marks</th>
                                                        <th class="text-xs text-secondary fw-bold">Performance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentSeatTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /seatingMapContainer -->

                            <!-- Empty State -->
                            <div id="emptyState" class="{{ $saved ? 'd-none' : 'text-center py-5' }}">
                                <i class="material-symbols-rounded text-secondary" style="font-size:72px">chair</i>
                                <h5 class="text-secondary mt-3">No seating arrangement yet</h5>
                                <p class="text-secondary">Click <strong>Generate Seating</strong> above to create one.</p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
@endsection

@push('styles')
    <style>
        .classroom-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            overflow-x: auto;
        }

        .classroom-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .classroom-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .row-label {
            width: 28px;
            text-align: right;
            font-size: 11px;
            color: #6c757d;
            font-weight: 600;
        }

        .seat-cell {
            width: 72px;
            height: 64px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            border: 2px solid transparent;
            text-align: center;
            padding: 4px;
            line-height: 1.2;
            position: relative;
        }

        .seat-cell:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .seat-cell.occupied-high {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .seat-cell.occupied-mid {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .seat-cell.occupied-low {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .seat-cell.empty {
            background: #f0f2f5;
            border-color: #dee2e6;
            color: #adb5bd;
        }

        .seat-cell .seat-label {
            font-size: 9px;
            opacity: 0.7;
        }

        .seat-cell .seat-marks {
            font-size: 9px;
            margin-top: 1px;
        }

        .board-indicator {
            background: #343a40;
            color: #fff;
            border-radius: 6px;
            padding: 6px 24px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            display: inline-block;
        }

        .seat-legend {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
            border: 2px solid transparent;
        }

        .seat-legend.seat-high {
            background: #d4edda;
            border-color: #28a745;
        }

        .seat-legend.seat-mid {
            background: #fff3cd;
            border-color: #ffc107;
        }

        .seat-legend.seat-low {
            background: #f8d7da;
            border-color: #dc3545;
        }

        .seat-legend.seat-empty {
            background: #f0f2f5;
            border-color: #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const GRADE = '{{ $grade }}';
        const SECTION = '{{ $section }}';
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        @if ($saved)
            // Load saved arrangement on page load
            document.addEventListener('DOMContentLoaded', function() {
                renderArrangement(@json($saved['arrangement']), '{{ $saved['generated_at'] }}');
            });
        @endif

        function generateSeating() {
            const btn = document.getElementById('generateBtn');
            const loading = document.getElementById('loadingState');
            const status = document.getElementById('generationStatus');
            const mapContainer = document.getElementById('seatingMapContainer');
            const emptyState = document.getElementById('emptyState');

            btn.disabled = true;
            loading.classList.remove('d-none');
            mapContainer.classList.add('d-none');
            emptyState.classList.add('d-none');
            status.className = 'alert d-none mb-4';

            const seatsPerRow = parseInt(document.getElementById('seatsPerRow').value) || 5;
            const totalRows = parseInt(document.getElementById('totalRows').value) || 6;

            fetch('{{ route('admin.management.seating.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        grade: GRADE,
                        section: SECTION,
                        seats_per_row: seatsPerRow,
                        total_rows: totalRows,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    loading.classList.add('d-none');
                    btn.disabled = false;
                    btn.textContent = '';
                    btn.innerHTML =
                        '<span class="material-symbols-rounded me-1" style="font-size:16px">refresh</span> Regenerate';

                    if (data.success) {
                        status.className = 'alert alert-success mb-4';
                        status.innerHTML = '<i class="material-symbols-rounded align-middle me-1">check_circle</i>' +
                            data.message;
                        mapContainer.classList.remove('d-none');
                        renderArrangement(data.arrangement, new Date().toLocaleString());
                    } else {
                        status.className = 'alert alert-danger mb-4';
                        status.innerHTML = '<i class="material-symbols-rounded align-middle me-1">error</i>' + (data
                            .message || 'Generation failed');
                        emptyState.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    loading.classList.add('d-none');
                    btn.disabled = false;
                    status.className = 'alert alert-danger mb-4';
                    status.innerHTML =
                        '<i class="material-symbols-rounded align-middle me-1">error</i>Failed to connect: ' + err
                        .message;
                    emptyState.classList.remove('d-none');
                });
        }

        function renderArrangement(arrangement, generatedAt) {
            const grid = document.getElementById('classroomGrid');
            const tbody = document.getElementById('studentSeatTableBody');
            const rows = arrangement.rows || [];

            grid.innerHTML = '';
            tbody.innerHTML = '';

            let totalSeats = 0;
            const tableRows = [];

            rows.forEach((row, ri) => {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'classroom-row';

                const rowLbl = document.createElement('div');
                rowLbl.className = 'row-label';
                rowLbl.textContent = 'R' + (ri + 1);
                rowDiv.appendChild(rowLbl);

                row.forEach((seat, si) => {
                    totalSeats++;
                    const cell = document.createElement('div');
                    const marks = seat.average_marks ?? null;
                    let cls = 'empty';
                    if (seat.student_id) {
                        if (marks >= 80) cls = 'occupied-high';
                        else if (marks >= 60) cls = 'occupied-mid';
                        else cls = 'occupied-low';
                    }

                    cell.className = 'seat-cell ' + cls;
                    cell.title = seat.student_id ?
                        seat.name + '\nMarks: ' + (marks ?? '—') + '\n' + seat.seat_label :
                        'Empty seat';

                    if (seat.student_id) {
                        const nameParts = (seat.name || '').split(' ');
                        const initials = nameParts.map(p => p[0]).join('').substring(0, 2).toUpperCase();
                        cell.innerHTML = `<span>${initials}</span>
                        <span class="seat-label">${seat.seat_label || ''}</span>
                        <span class="seat-marks">${marks !== null ? marks : ''}</span>`;

                        tableRows.push({
                            row: ri + 1,
                            seat: si + 1,
                            label: seat.seat_label,
                            name: seat.name,
                            id: seat.student_id,
                            marks
                        });
                    } else {
                        cell.innerHTML = `<span style="font-size:18px;opacity:0.3">○</span>
                        <span class="seat-label">${seat.seat_label || ''}</span>`;
                    }

                    rowDiv.appendChild(cell);
                });

                grid.appendChild(rowDiv);
            });

            // Stats
            document.getElementById('statSeats').textContent = totalSeats;
            document.getElementById('statRows').textContent = rows.length;
            document.getElementById('statStudents').textContent = arrangement.total_students || tableRows.length;
            document.getElementById('statGenTime').innerHTML = generatedAt ?
                `<strong>${typeof generatedAt === 'string' ? generatedAt : new Date(generatedAt).toLocaleString()}</strong>` :
                '—';

            // Table
            tableRows.sort((a, b) => a.row - b.row || a.seat - b.seat);
            tableRows.forEach(r => {
                let perf = '—',
                    badgeCls = 'bg-gradient-secondary';
                if (r.marks !== null) {
                    if (r.marks >= 80) {
                        perf = 'High';
                        badgeCls = 'bg-gradient-success';
                    } else if (r.marks >= 60) {
                        perf = 'Mid';
                        badgeCls = 'bg-gradient-warning';
                    } else {
                        perf = 'Low';
                        badgeCls = 'bg-gradient-danger';
                    }
                }
                tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="ps-3 py-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm rounded-circle bg-gradient-primary me-2 d-flex align-items-center justify-content-center">
                                <span class="text-white text-xxs">${(r.name||'').split(' ').map(p=>p[0]).join('').substring(0,2).toUpperCase()}</span>
                            </div>
                            <span class="text-sm font-weight-bold">${r.name}</span>
                        </div>
                    </td>
                    <td class="py-2 text-sm">${r.row}</td>
                    <td class="py-2 text-sm">${r.seat}</td>
                    <td class="py-2"><span class="badge bg-gradient-info badge-sm">${r.label}</span></td>
                    <td class="py-2 text-sm">${r.marks !== null ? r.marks : '—'}</td>
                    <td class="py-2"><span class="badge ${badgeCls} badge-sm">${perf}</span></td>
                </tr>
            `);
            });
        }
    </script>
@endpush
