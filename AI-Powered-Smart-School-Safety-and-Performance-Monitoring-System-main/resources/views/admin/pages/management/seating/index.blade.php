@extends('admin.layouts.app')

@section('title', 'Seating Arrangement Management')

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="material-symbols-rounded me-2 icon-size-sm">chair</i>
                                    Seating Arrangement Management (AI Powered)
                                </h6>
                                <p class="text-sm text-secondary mb-0 mt-1">
                                    Generate and manage AI-optimised seating arrangements for each class.
                                </p>
                            </div>
                        </div>

                        <div class="card-body px-4">

                            <!-- Stats Row -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card bg-gradient-primary text-white text-center p-3">
                                        <h3 class="mb-0">{{ count($classes) }}</h3>
                                        <p class="mb-0 text-sm">Total Classes</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-gradient-success text-white text-center p-3">
                                        <h3 class="mb-0">{{ collect($classes)->where('has_arrangement', true)->count() }}
                                        </h3>
                                        <p class="mb-0 text-sm">Arranged</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-gradient-warning text-white text-center p-3">
                                        <h3 class="mb-0">{{ collect($classes)->where('has_arrangement', false)->count() }}
                                        </h3>
                                        <p class="mb-0 text-sm">Pending</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Class Grid -->
                            <div class="row">
                                @forelse($classes as $class)
                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                        <div
                                            class="card h-100 border {{ $class['has_arrangement'] ? 'border-success' : 'border-secondary' }} border-opacity-25">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h5 class="mb-0 font-weight-bold">
                                                            Grade {{ $class['grade_level'] }}-{{ $class['section'] }}
                                                        </h5>
                                                        <small class="text-secondary">
                                                            {{ $class['student_count'] }}
                                                            student{{ $class['student_count'] != 1 ? 's' : '' }}
                                                        </small>
                                                    </div>
                                                    @if ($class['has_arrangement'])
                                                        <span class="badge bg-gradient-success badge-sm">
                                                            <i class="material-symbols-rounded"
                                                                style="font-size:12px">check_circle</i>
                                                            Arranged
                                                        </span>
                                                    @else
                                                        <span class="badge bg-gradient-secondary badge-sm">
                                                            <i class="material-symbols-rounded"
                                                                style="font-size:12px">pending</i>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="text-center mb-3">
                                                    <div class="seating-mini-preview d-inline-block">
                                                        @for ($r = 0; $r < 3; $r++)
                                                            <div class="d-flex gap-1 mb-1 justify-content-center">
                                                                @for ($s = 0; $s < 4; $s++)
                                                                    <div
                                                                        class="mini-seat {{ $class['has_arrangement'] ? 'mini-seat-filled' : 'mini-seat-empty' }}">
                                                                    </div>
                                                                @endfor
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>

                                                <a href="{{ route('admin.management.seating.show', [$class['grade_level'], $class['section']]) }}"
                                                    class="btn btn-sm w-100 {{ $class['has_arrangement'] ? 'btn-outline-primary' : 'btn-primary' }}">
                                                    <i class="material-symbols-rounded me-1" style="font-size:14px">
                                                        {{ $class['has_arrangement'] ? 'visibility' : 'auto_awesome' }}
                                                    </i>
                                                    {{ $class['has_arrangement'] ? 'View Arrangement' : 'Generate' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="material-symbols-rounded text-secondary"
                                            style="font-size: 64px">school</i>
                                        <h5 class="text-secondary mt-3">No classes with students found</h5>
                                        <p class="text-secondary">Enrol students to see classes here.</p>
                                    </div>
                                @endforelse
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
        .mini-seat {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            border: 1px solid #ccc;
        }

        .mini-seat-filled {
            background: #3a86ff;
            border-color: #3a86ff;
        }

        .mini-seat-empty {
            background: #f0f0f0;
            border-color: #ccc;
        }

        .seating-mini-preview {
            line-height: 1;
        }
    </style>
@endpush
