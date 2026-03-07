@extends('admin.layouts.app')
@section('title', 'Student Performance – ' . ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))

@section('content')
@include('admin.layouts.sidebar')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">

        {{-- Back + header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="{{ route('admin.management.performance.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="material-symbols-outlined">arrow_back</i> Back
            </a>
            <h5 class="mb-0">
                <i class="material-symbols-outlined me-1">person</i>
                {{ $student->first_name }} {{ $student->last_name }}
                <small class="text-muted fs-6">&nbsp;({{ $student->student_code ?? 'N/A' }})</small>
            </h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-gradient-info fs-6">Grade {{ $student->schoolClass->grade_level ?? 'N/A' }} &bull; {{ $student->schoolClass->class_name ?? 'N/A' }}</span>
                <a href="{{ route('admin.management.performance.student-pdf', $student->student_id) }}"
                    class="btn btn-success btn-sm">
                    <span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;">download</span>
                    Generate Student Report (PDF)
                </a>
            </div>
        </div>

        {{-- Stats cards --}}
        <div class="row mb-4">
            @php
            $statCards = [
            ['label' => 'Assignments Attempted', 'value' => $stats['total_submissions'], 'icon' => 'assignment', 'color' => 'primary'],
            ['label' => 'Average Score', 'value' => number_format($stats['average_score'], 1) . '%', 'icon' => 'trending_up', 'color' => 'success'],
            ['label' => 'Highest Score', 'value' => number_format($stats['highest_score'], 1) . '%', 'icon' => 'emoji_events','color' => 'warning'],
            ['label' => 'On-Time Rate', 'value' => number_format($stats['on_time_rate'], 1) . '%', 'icon' => 'schedule', 'color' => 'info'],
            ];
            @endphp
            @foreach($statCards as $card)
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-{{ $card['color'] }} shadow-{{ $card['color'] }} text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">{{ $card['icon'] }}</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">{{ $card['label'] }}</p>
                            <h4 class="mb-0">{{ $card['value'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Subject performance chart + breakdown --}}
        <div class="row mb-4">
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-outlined me-1">bar_chart</i>Subject Averages</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="subjectChart" height="280"
                            data-labels="{{ e(implode(',', $subjectAverages->keys()->toArray())) }}"
                            data-values="{{ e(implode(',', $subjectAverages->values()->toArray())) }}">
                        </canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-outlined me-1">school</i>Subject Performance Breakdown</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Average</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subjectAverages as $subject => $avg)
                                    <tr>
                                        <td class="ps-4"><strong>{{ $subject }}</strong></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                {{ number_format($avg, 1) }}%
                                                <div class="progress" style="width:70px;height:5px;">
                                                    <div class="progress-bar bg-{{ $avg >= 75 ? 'success' : ($avg >= 50 ? 'warning' : 'danger') }}"
                                                        style="width:{{ min($avg, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-gradient-{{ $avg >= 75 ? 'success' : ($avg >= 60 ? 'info' : ($avg >= 40 ? 'warning' : 'danger')) }}">
                                                {{ \App\Models\HomeworkSubmission::calculateGrade($avg) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">No graded data yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- All attempted assignments table --}}
        <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6><i class="material-symbols-outlined me-1">assignment</i>Attempted Assignments &amp; Marks</h6>
                <span class="badge bg-gradient-secondary">{{ $submissions->count() }} attempt(s)</span>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Assignment</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Marks</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">%</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Submitted</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">On Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $i => $sub)
                            @php
                            $gradeColor = $sub->getGradeColor();
                            $pct = $sub->percentage ?? 0;
                            @endphp
                            <tr>
                                <td class="ps-4 text-secondary text-sm">{{ $i + 1 }}</td>
                                <td class="ps-2">
                                    <p class="text-sm font-weight-bold mb-0">{{ $sub->homework->title ?? 'N/A' }}</p>
                                    <p class="text-xs text-secondary mb-0">Due: {{ optional($sub->homework->due_date)->format('d M Y') ?? '—' }}</p>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $sub->homework->subject->subject_name ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($sub->status === 'graded')
                                    <strong>{{ number_format($sub->marks_obtained, 0) }}</strong>
                                    <span class="text-muted">/ {{ $sub->homework->total_marks ?? '?' }}</span>
                                    @else
                                    <span class="text-muted">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sub->status === 'graded')
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span class="text-sm font-weight-bold">{{ number_format($pct, 1) }}%</span>
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sub->status === 'graded')
                                    <span class="badge bg-gradient-{{ $gradeColor }}">
                                        {{ $sub->grade ?? \App\Models\HomeworkSubmission::calculateGrade($pct) }}
                                    </span>
                                    @else
                                    <span class="badge bg-gradient-secondary">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sub->status === 'graded')
                                    <span class="badge bg-gradient-success">Graded</span>
                                    @else
                                    <span class="badge bg-gradient-warning">Submitted</span>
                                    @endif
                                </td>
                                <td class="text-center text-sm">{{ optional($sub->submitted_at)->format('d M Y') ?? '—' }}</td>
                                <td class="text-center">
                                    @if($sub->is_late)
                                    <i class="material-symbols-outlined text-danger" title="Late">cancel</i>
                                    @else
                                    <i class="material-symbols-outlined text-success" title="On Time">check_circle</i>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No assignment attempts found for this student.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        var canvas = document.getElementById('subjectChart');
        var rawLabels = canvas.dataset.labels || '';
        var rawValues = canvas.dataset.values || '';
        var labels = rawLabels ? rawLabels.split(',') : [];
        var values = rawValues ? rawValues.split(',').map(Number) : [];

        if (labels.length > 0) {
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Score (%)',
                        data: values,
                        backgroundColor: [
                            'rgba(33,150,243,0.75)', 'rgba(76,175,80,0.75)',
                            'rgba(255,152,0,0.75)', 'rgba(156,39,176,0.75)',
                            'rgba(0,188,212,0.75)', 'rgba(233,30,99,0.75)'
                        ],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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
                            }
                        }
                    }
                }
            });
        } else {
            canvas.insertAdjacentHTML('afterend', '<p class="text-center text-muted mt-3">No graded data available for chart.</p>');
        }
    })();
</script>
@endpush
@endsection