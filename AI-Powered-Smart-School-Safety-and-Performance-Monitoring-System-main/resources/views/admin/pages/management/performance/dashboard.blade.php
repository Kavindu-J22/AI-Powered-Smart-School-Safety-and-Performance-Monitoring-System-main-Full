@extends('admin.layouts.app')

@section('title', 'Performance & Reports')

@section('content')
@include('admin.layouts.sidebar')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">
                    <span class="material-symbols-outlined" style="vertical-align:middle;font-size:28px;">analytics</span>
                    Performance &amp; Reports
                </h4>
                <p class="text-sm text-secondary mb-0">Track student assignment performance and generate PDF reports</p>
            </div>
            @php
            $pdfUrl = route('admin.management.performance.download-all');
            $qs = http_build_query(array_filter(['grade_level' => $gradeLevel, 'subject_id' => $subjectId]));
            if ($qs) $pdfUrl .= '?' . $qs;
            @endphp
            <a href="{{ $pdfUrl }}" class="btn btn-success btn-sm">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">download</span>
                Generate Full Report (PDF)
            </a>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('admin.management.performance.dashboard') }}">
                    <div class="row align-items-end g-3">
                        <div class="col-md-4">
                            <label class="form-label text-xs text-uppercase text-secondary fw-bold mb-1">Grade Level</label>
                            <select name="grade_level" class="form-select form-select-sm">
                                <option value="">All Grades</option>
                                @foreach($grades as $g)
                                <option value="{{ $g }}" @selected($gradeLevel==$g)>Grade {{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-xs text-uppercase text-secondary fw-bold mb-1">Subject</label>
                            <select name="subject_id" class="form-select form-select-sm">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($subjectId==$subject->id)>{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">filter_list</span>
                                Apply Filters
                            </button>
                            <a href="{{ route('admin.management.performance.dashboard') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">groups</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Total Students</p>
                            <h4 class="mb-0">{{ $stats['total_students'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">trending_up</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Average Score</p>
                            <h4 class="mb-0">{{ number_format($stats['average_score'], 1) }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">check_circle</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Pass Rate</p>
                            <h4 class="mb-0">{{ number_format($stats['pass_rate'], 1) }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">assignment</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Total Submissions</p>
                            <h4 class="mb-0">{{ $stats['submissions_total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row mb-4">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>
                            <span class="material-symbols-outlined me-1" style="font-size:18px;vertical-align:middle;">bar_chart</span>
                            Subject Performance (Average Score)
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(count($subjectChartData) > 0)
                        <canvas id="subjectPerformanceChart" height="220"></canvas>
                        @else
                        <div class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined" style="font-size:40px;">bar_chart</span>
                            <p class="mt-2">No graded submissions yet</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>
                            <span class="material-symbols-outlined me-1" style="font-size:18px;vertical-align:middle;">pie_chart</span>
                            Grade Distribution
                        </h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        @php $totalGraded = array_sum($gradeDistribution); @endphp
                        @if($totalGraded > 0)
                        <canvas id="gradeDistributionChart" height="220"></canvas>
                        @else
                        <div class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined" style="font-size:40px;">pie_chart</span>
                            <p class="mt-2">No graded data available</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- All Students Table --}}
        <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>
                    <span class="material-symbols-outlined me-1" style="font-size:18px;vertical-align:middle;">groups</span>
                    All Students Performance
                    @if($gradeLevel || $subjectId)
                    <small class="text-muted">(filtered)</small>
                    @endif
                </h6>
                <span class="badge bg-gradient-secondary">{{ $studentPerformanceData->count() }} students</span>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade / Class</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Attempted</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Avg Score</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentPerformanceData as $i => $row)
                            @php
                            $avg = $row['average_score'];
                            $bc = $avg === null ? 'secondary' : ($avg >= 75 ? 'success' : ($avg >= 50 ? 'warning' : 'danger'));
                            @endphp
                            <tr>
                                <td class="ps-4 text-secondary text-sm">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex px-2 py-1 align-items-center">
                                        <div class="avatar avatar-sm me-3 bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="text-white text-xs fw-bold">{{ substr($row['student']->first_name ?? 'S', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-sm">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $row['student']->student_code ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm">Grade {{ $row['student']->grade_level ?? '—' }}</span>
                                    <p class="text-xs text-secondary mb-0">{{ $row['student']->schoolClass->class_name ?? '—' }}</p>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-weight-bold">{{ $row['total_attempted'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($avg !== null)
                                    <span class="badge bg-gradient-{{ $bc }}">{{ number_format($avg, 1) }}%</span>
                                    @else
                                    <span class="text-muted text-xs">No data</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-gradient-{{ $bc }}">{{ $row['grade'] }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.management.performance.student', $row['student']->student_id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">visibility</span> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No students found with the current filters.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function() {
        // ── Subject Performance Bar Chart ──────────────────────────────────
        var subjectCanvas = document.getElementById('subjectPerformanceChart');
        if (subjectCanvas) {
            var subjectLabels = @json(array_keys($subjectChartData));
            var subjectData = @json(array_values($subjectChartData));
            var barColors = ['#2196F3', '#FF9800', '#4CAF50', '#9C27B0', '#F44336', '#00BCD4', '#8BC34A', '#FF5722'];

            new Chart(subjectCanvas, {
                type: 'bar',
                data: {
                    labels: subjectLabels,
                    datasets: [{
                        label: 'Average Score (%)',
                        data: subjectData,
                        backgroundColor: subjectLabels.map(function(_, i) {
                            return barColors[i % barColors.length];
                        }),
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.parsed.y.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(v) {
                                    return v + '%';
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.06)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // ── Grade Distribution Pie Chart ───────────────────────────────────
        var pieCanvas = document.getElementById('gradeDistributionChart');
        if (pieCanvas) {
            var gradeLabels = @json(array_keys($gradeDistribution));
            var gradeData = @json(array_values($gradeDistribution));
            var pieColors = ['#4CAF50', '#8BC34A', '#FF9800', '#FF5722', '#F44336'];

            new Chart(pieCanvas, {
                type: 'pie',
                data: {
                    labels: gradeLabels.map(function(g) {
                        return 'Grade ' + g;
                    }),
                    datasets: [{
                        data: gradeData,
                        backgroundColor: pieColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var total = ctx.dataset.data.reduce(function(a, b) {
                                        return a + b;
                                    }, 0);
                                    var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    })();
</script>
@endpush