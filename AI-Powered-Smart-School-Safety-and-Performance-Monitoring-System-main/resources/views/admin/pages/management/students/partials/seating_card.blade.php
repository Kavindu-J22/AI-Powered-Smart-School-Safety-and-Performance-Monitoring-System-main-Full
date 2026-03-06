<div class="card mb-4" id="seatingCard">
    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 d-flex align-items-center">
            <i class="material-symbols-rounded me-2 icon-size-sm">chair</i>
            Seating Arrangement
        </h6>
        <a href="{{ route('admin.management.seating.show', [$student->grade_level, $student->section]) }}"
            class="btn btn-outline-dark btn-sm">
            <i class="material-symbols-rounded me-1 icon-size-sm">open_in_new</i>Manage Class
        </a>
    </div>
    <div class="card-body px-4">

        <!-- Loading -->
        <div id="seatLoading" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <p class="text-secondary text-sm mt-2 mb-0">Loading seat information…</p>
        </div>

        <!-- Assigned Seat -->
        <div id="seatInfo" class="d-none">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-shape icon-md bg-gradient-primary shadow text-center me-3">
                            <i class="material-symbols-rounded text-white">chair</i>
                        </div>
                        <div>
                            <p class="text-xs text-secondary mb-0">Assigned Seat</p>
                            <h5 class="mb-0" id="seatLabel">—</h5>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <p class="text-xs text-secondary mb-0">Row</p>
                                <h6 class="mb-0" id="seatRow">—</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <p class="text-xs text-secondary mb-0">Column</p>
                                <h6 class="mb-0" id="seatCol">—</h6>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-secondary mb-0" id="seatGenTime"></p>
                </div>
                <div class="col-md-6">
                    <!-- Mini classroom map -->
                    <div class="text-center">
                        <div class="board-mini mx-auto mb-2">BOARD</div>
                        <div id="miniClassroomMap" style="font-size:11px;line-height:1.6"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Arrangement State -->
        <div id="seatEmpty" class="d-none text-center py-3">
            <i class="material-symbols-rounded text-warning" style="font-size:48px">warning</i>
            <p class="text-secondary text-sm mt-2 mb-2">No seating arrangement has been generated for Grade
                {{ $student->grade_level }}-{{ $student->section }} yet.</p>
            <a href="{{ route('admin.management.seating.show', [$student->grade_level, $student->section]) }}"
                class="btn btn-primary btn-sm">
                <i class="material-symbols-rounded me-1" style="font-size:16px">auto_awesome</i>
                Generate Now
            </a>
        </div>

        <!-- Error State -->
        <div id="seatError" class="d-none alert alert-warning text-sm py-2 mb-0">
            <i class="material-symbols-rounded align-middle me-1">info</i>
            Unable to load seating data.
        </div>

    </div>
</div>

<style>
    .board-mini {
        background: #343a40;
        color: #fff;
        border-radius: 4px;
        padding: 3px 12px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 6px;
    }

    .mini-row {
        display: flex;
        justify-content: center;
        gap: 4px;
        margin-bottom: 4px;
    }

    .mini-seat {
        width: 20px;
        height: 18px;
        border-radius: 3px;
        background: #f0f2f5;
        border: 1.5px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 7px;
        font-weight: 700;
    }

    .mini-seat.me {
        background: #3a416f;
        border-color: #3a416f;
        color: #fff;
    }

    .mini-seat.high {
        background: #d4edda;
        border-color: #28a745;
    }

    .mini-seat.mid {
        background: #fff3cd;
        border-color: #ffc107;
    }

    .mini-seat.low {
        background: #f8d7da;
        border-color: #dc3545;
    }
</style>

<script>
    (function() {
        const studentId = {{ $student->student_id }};
        const grade = {{ $student->grade_level }};
        const section = '{{ $student->section }}';
        const dataUrl = '{{ route('admin.management.seating.data', [$student->grade_level, $student->section]) }}';
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        fetch(dataUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('seatLoading').classList.add('d-none');

                if (!data.success || !data.data || !data.data.arrangement) {
                    document.getElementById('seatEmpty').classList.remove('d-none');
                    return;
                }

                const rows = data.data.arrangement.rows || [];
                let found = null;

                rows.forEach((row, ri) => {
                    row.forEach((seat, ci) => {
                        if (String(seat.student_id) === String(studentId)) {
                            found = {
                                row: ri + 1,
                                col: ci + 1,
                                seat,
                                ri,
                                ci
                            };
                        }
                    });
                });

                if (!found) {
                    document.getElementById('seatEmpty').classList.remove('d-none');
                    return;
                }

                document.getElementById('seatInfo').classList.remove('d-none');
                document.getElementById('seatLabel').textContent = found.seat.seat_label || ('R' + found.row +
                    'S' + found.col);
                document.getElementById('seatRow').textContent = found.row;
                document.getElementById('seatCol').textContent = found.col;

                if (data.data.generated_at) {
                    document.getElementById('seatGenTime').textContent =
                        'Generated: ' + new Date(data.data.generated_at).toLocaleString();
                }

                // Mini classroom map (max 8 rows, max 6 seats per row to keep it compact)
                const map = document.getElementById('miniClassroomMap');
                const MAX_ROWS = Math.min(rows.length, 8);
                const MAX_COLS = Math.min((rows[0] || []).length, 6);
                let html = '';
                for (let r = 0; r < MAX_ROWS; r++) {
                    html += '<div class="mini-row">';
                    for (let c = 0; c < MAX_COLS; c++) {
                        const s = (rows[r] || [])[c] || {};
                        let cls = '';
                        if (r === found.ri && c === found.ci) {
                            cls = 'me';
                        } else if (s.student_id) {
                            const m = s.average_marks;
                            cls = m >= 80 ? 'high' : (m >= 60 ? 'mid' : 'low');
                        }
                        html +=
                            `<div class="mini-seat ${cls}">${(r === found.ri && c === found.ci) ? '★' : ''}</div>`;
                    }
                    html += '</div>';
                }
                if (rows.length > MAX_ROWS) html +=
                    `<p class="text-xs text-muted text-center mb-0">+${rows.length - MAX_ROWS} more rows</p>`;
                map.innerHTML = html;
            })
            .catch(() => {
                document.getElementById('seatLoading').classList.add('d-none');
                document.getElementById('seatError').classList.remove('d-none');
            });
    })();
</script>
