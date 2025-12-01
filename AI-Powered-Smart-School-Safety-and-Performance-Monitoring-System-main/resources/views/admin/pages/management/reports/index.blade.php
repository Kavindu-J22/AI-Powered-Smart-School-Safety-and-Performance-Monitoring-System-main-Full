@extends('admin.layouts.app')

@section('title', 'Monthly Reports')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Monthly Performance Reports</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateReportsModal">
                        <i class="material-symbols-outlined">auto_fix_high</i> Generate Reports
                    </button>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Period</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Average</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports ?? [] as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">
                                                    {{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}
                                                </h6>
                                                <p class="text-xs text-secondary mb-0">Grade {{ $report->grade_level }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $report->getReportPeriod() }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-sm font-weight-bold">{{ number_format($report->overall_average, 1) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-gradient-{{ $report->getGradeColor() }}">{{ $report->overall_grade }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($report->status === 'generated')
                                        <span class="badge bg-warning">Pending</span>
                                        @elseif($report->status === 'sent_to_parents')
                                        <span class="badge bg-info">Sent</span>
                                        @elseif($report->status === 'acknowledged')
                                        <span class="badge bg-success">Acknowledged</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.management.reports.show', $report->report_id) }}"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="material-symbols-outlined">visibility</i>
                                        </a>
                                        <a href="{{ route('admin.management.reports.download', $report->report_id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="material-symbols-outlined">download</i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-0">No reports generated yet</p>
                                        <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#generateReportsModal">
                                            Generate First Report
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($reports) && $reports->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $reports->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Report Status Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="reportStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Bulk Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-outline-primary w-100" id="sendAllPending">
                                <i class="material-symbols-outlined me-2">send</i>
                                Send All Pending Reports to Parents
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#generateReportsModal">
                                <i class="material-symbols-outlined me-2">auto_fix_high</i>
                                Generate Reports for Class
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Reports Modal -->
<div class="modal fade" id="generateReportsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Monthly Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateReportsForm">
                    <div class="mb-3">
                        <label class="form-label">Select Class</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Month</label>
                                <select name="month" class="form-control" required>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Year</label>
                                <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="generateBtn">Generate Reports</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('reportStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Generated', 'Sent', 'Acknowledged'],
            datasets: [{
                data: [10, 25, 45],
                backgroundColor: ['#FFC107', '#2196F3', '#4CAF50']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endpush
@endsection