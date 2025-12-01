@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/dashboard.css'])
<style>
    .homework-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .homework-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .due-date-warning {
        color: #dc3545;
        font-weight: 600;
    }

    .due-date-normal {
        color: #6c757d;
    }

    .tab-content {
        min-height: 300px;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0 text-white">
                                    <i class="material-symbols-rounded me-2">assignment</i>My Homework
                                </h4>
                                <p class="text-white opacity-8 mb-0">View and complete your assignments</p>
                            </div>
                            <div class="text-white text-end">
                                <h3 class="mb-0">{{ $stats['average_score'] ? number_format($stats['average_score'], 1) . '%' : 'N/A' }}</h3>
                                <small class="opacity-8">Average Score</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Pending</p>
                                <h5 class="font-weight-bolder text-warning">{{ $stats['pending'] }}</h5>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">pending</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Submitted</p>
                                <h5 class="font-weight-bolder text-info">{{ $stats['submitted'] }}</h5>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">upload</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Graded</p>
                                <h5 class="font-weight-bolder text-success">{{ $stats['graded'] }}</h5>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">check_circle</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">On Time</p>
                                <h5 class="font-weight-bolder text-primary">{{ $stats['on_time'] }}/{{ $stats['graded'] }}</h5>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="material-symbols-rounded opacity-10">schedule</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#pending" role="tab">
                            <i class="material-symbols-rounded me-1">pending</i>Pending
                            @if($pending->count() > 0)
                            <span class="badge bg-warning ms-1">{{ $pending->count() }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#submitted" role="tab">
                            <i class="material-symbols-rounded me-1">upload</i>Submitted
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#graded" role="tab">
                            <i class="material-symbols-rounded me-1">grading</i>Graded
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Pending Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        @forelse($pending as $submission)
                        @include('student.pages.homework.partials.homework-card', [
                        'submission' => $submission,
                        'type' => 'pending'
                        ])
                        @empty
                        <div class="empty-state">
                            <i class="material-symbols-rounded">task_alt</i>
                            <h5>No Pending Homework</h5>
                            <p>Great job! You're all caught up.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Submitted Tab -->
                    <div class="tab-pane fade" id="submitted" role="tabpanel">
                        @forelse($submitted as $submission)
                        @include('student.pages.homework.partials.homework-card', [
                        'submission' => $submission,
                        'type' => 'submitted'
                        ])
                        @empty
                        <div class="empty-state">
                            <i class="material-symbols-rounded">hourglass_empty</i>
                            <h5>No Submissions Awaiting Grading</h5>
                            <p>Your submitted homework is being processed.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Graded Tab -->
                    <div class="tab-pane fade" id="graded" role="tabpanel">
                        @forelse($graded as $submission)
                        @include('student.pages.homework.partials.homework-card', [
                        'submission' => $submission,
                        'type' => 'graded'
                        ])
                        @empty
                        <div class="empty-state">
                            <i class="material-symbols-rounded">school</i>
                            <h5>No Graded Homework Yet</h5>
                            <p>Complete and submit homework to see your grades here.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection