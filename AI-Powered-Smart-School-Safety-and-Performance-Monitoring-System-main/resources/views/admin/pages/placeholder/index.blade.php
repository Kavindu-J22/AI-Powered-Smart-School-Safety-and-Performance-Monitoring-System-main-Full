@extends('admin.layouts.app')

@section('title', $module)

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <p class="mb-0">{{ $module }}</p>
                            </div>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="icon icon-shape icon-xxl bg-gradient-info shadow border-radius-lg mx-auto mb-4">
                                <i class="material-symbols-outlined opacity-5" style="font-size: 3rem;">construction</i>
                            </div>
                            <h4 class="text-center mb-4">{{ $module }} - Coming Soon!</h4>
                            <p class="text-muted mb-4">{{ $message }}</p>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="info">
                                        <i class="material-symbols-outlined text-gradient text-info text-3xl">schedule</i>
                                        <h5 class="info-title">In Development</h5>
                                        <p class="info-description">Feature is currently being developed</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info">
                                        <i class="material-symbols-outlined text-gradient text-warning text-3xl">build</i>
                                        <h5 class="info-title">Quality Assurance</h5>
                                        <p class="info-description">Ensuring the best user experience</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info">
                                        <i
                                            class="material-symbols-outlined text-gradient text-success text-3xl">rocket_launch</i>
                                        <h5 class="info-title">Coming Soon</h5>
                                        <p class="info-description">Will be available in upcoming updates</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.dashboard.index') }}" class="btn bg-gradient-primary">
                                    <i class="material-symbols-outlined me-2">arrow_back</i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
