@extends('admin.layouts.app')
@section('title', 'Monthly Report - ' . ($report->student->first_name ?? '') . ' ' . ($report->student->last_name ?? ''))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('admin.management.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="material-symbols-outlined">arrow_back</i> Back to Reports
                </a>
            </div>
            <div>
                <a href="{{ route('admin.management.reports.download', $report->report_id) }}" class="btn btn-primary btn-sm">
                    <i class="material-symbols-outlined">download</i> Download PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Report Header Card -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%); color: white;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1 text-white">Monthly Performance Report</h4>
                    <h5 class="mb-2 text-white opacity-75">{{ $report->getReportPeriod() }}</h5>
                    <p class="mb-0 opacity-75">
                        <i class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">person</i>
                        {{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}
                        &nbsp;&bull;&nbsp; Code: {{ $report->student->student_code ?? 'N/A' }}
                        &nbsp;&bull;&nbsp; Grade {{ $report->grade_level }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div style="font-size: 48px; font-weight: bold; line-height: 1;">
                        {{ $report->overall_grade ?? 'N/A' }}
                    </div>
                    <div class="opacity-75">{{ number_format($report->overall_average, 1) }}% Overall</div>
                    <div class="opacity-75 small">Rank: {{ $report->getRankText() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-symbols-outlined text-primary" style="font-size:36px;">assignment</i>
                    <h3 class="mt-2 mb-0">{{ $report->homework_completed ?? 0 }} / {{ $report->total_homework_assigned ?? 0 }}</h3>
                    <p class="text-muted small mb-0">Assignments Completed</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-symbols-outlined text-success" style="font-size:36px;">check_circle</i>
                    <h3 class="mt-2 mb-0">{{ number_format($report->completion_rate ?? 0, 1) }}%</h3>
                    <p class="text-muted small mb-0">Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-symbols-outlined text-info" style="font-size:36px;">schedule</i>
                    <h3 class="mt-2 mb-0">{{ $report->homework_on_time ?? 0 }}</h3>
                    <p class="text-muted small mb-0">Submitted On Time</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="material-symbols-outlined text-warning" style="font-size:36px;">trending_up</i>
                    <h3 class="mt-2 mb-0">{{ $report->academic_year ?? 'N/A' }}</h3>
                    <p class="text-muted small mb-0">Academic Year</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Performance Table -->
    @if($report->subject_performance && count($report->subject_performance) > 0)
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6><i class="material-symbols-outlined me-2">school</i>Subject Performance Breakdown</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Average Score</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Assigned</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Completed</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->subject_performance as $subject => $data)
                        <tr>
                            <td class="ps-4"><strong>{{ $subject }}</strong></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-2">{{ number_format($data['average'] ?? 0, 1) }}%</span>
                                    <div class="progress" style="width:80px;height:6px;">
                                        <div class="progress-bar bg-{{ $data['average'] >= 75 ? 'success' : ($data['average'] >= 50 ? 'warning' : 'danger') }}"
                                             style="width:{{ min($data['average'] ?? 0, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-gradient-{{ $data['average'] >= 75 ? 'success' : ($data['average'] >= 60 ? 'info' : ($data['average'] >= 40 ? 'warning' : 'danger')) }}">
                                    {{ $data['grade'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $data['assigned'] ?? 0 }}</td>
                            <td class="text-center">{{ $data['completed'] ?? 0 }}</td>
                            <td class="text-center">
                                @if(($data['trend'] ?? '') === 'improving')
                                    <i class="material-symbols-outlined text-success" title="Improving">trending_up</i>
                                @elseif(($data['trend'] ?? '') === 'declining')
                                    <i class="material-symbols-outlined text-danger" title="Declining">trending_down</i>
                                @else
                                    <i class="material-symbols-outlined text-secondary" title="Stable">trending_flat</i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Strengths & Areas for Improvement -->
    <div class="row">
        @if($report->strengths && count($report->strengths) > 0)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-start border-success border-3">
                <div class="card-header pb-0">
                    <h6 class="text-success"><i class="material-symbols-outlined me-1">thumb_up</i>Strengths</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($report->strengths as $strength)
                        <li class="py-1"><i class="material-symbols-outlined text-success me-1" style="font-size:16px;vertical-align:middle;">check</i>{{ $strength }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
        @if($report->areas_for_improvement && count($report->areas_for_improvement) > 0)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-start border-warning border-3">
                <div class="card-header pb-0">
                    <h6 class="text-warning"><i class="material-symbols-outlined me-1">lightbulb</i>Areas for Improvement</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($report->areas_for_improvement as $area)
                        <li class="py-1"><i class="material-symbols-outlined text-warning me-1" style="font-size:16px;vertical-align:middle;">arrow_forward</i>{{ $area }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
        @if($report->recommendations && count($report->recommendations) > 0)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-start border-info border-3">
                <div class="card-header pb-0">
                    <h6 class="text-info"><i class="material-symbols-outlined me-1">recommend</i>Recommendations</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($report->recommendations as $rec)
                        <li class="py-1"><i class="material-symbols-outlined text-info me-1" style="font-size:16px;vertical-align:middle;">star</i>{{ $rec }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

