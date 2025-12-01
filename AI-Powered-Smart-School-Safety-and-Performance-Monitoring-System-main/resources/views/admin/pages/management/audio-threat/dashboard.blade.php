@extends('admin.layouts.app')

@section('css')
    @vite(['resources/css/admin/audio-threat.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid py-4">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="material-symbols-rounded me-2">mic</i>
                                Audio Threat Detection
                            </h4>
                            <p class="text-sm text-secondary mb-0">Real-time audio monitoring for school safety</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button id="calibrateBtn" class="btn btn-outline-info btn-sm" disabled>
                                <i class="material-symbols-rounded text-sm">tune</i> Calibrate
                            </button>
                            <button id="startDetectionBtn" class="btn btn-primary btn-sm">
                                <i class="material-symbols-rounded text-sm">play_arrow</i> Start Detection
                            </button>
                            <button id="stopDetectionBtn" class="btn btn-danger btn-sm d-none">
                                <i class="material-symbols-rounded text-sm">stop</i> Stop Detection
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">mic</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Status</p>
                                <h4 class="mb-0" id="detectionStatus">Inactive</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0" id="micStatus">
                                <span class="text-secondary text-sm">Microphone not connected</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">warning</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Threats Detected</p>
                                <h4 class="mb-0" id="threatCount">0</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0" id="lastThreatTime">
                                <span class="text-secondary text-sm">No threats detected</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">check_circle</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Chunks Processed</p>
                                <h4 class="mb-0" id="chunksProcessed">0</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0" id="processingRate">
                                <span class="text-secondary text-sm">0 chunks/min</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">speed</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Avg Latency</p>
                                <h4 class="mb-0" id="avgLatency">0 ms</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <p class="mb-0">
                                <span class="text-success text-sm font-weight-bolder" id="latencyStatus">Target: &lt;3s</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Visualization & Alerts -->
            <div class="row">
                <!-- Audio Visualizer -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Audio Input</h6>
                        </div>
                        <div class="card-body">
                            <div class="audio-visualizer-container">
                                <canvas id="audioVisualizer" width="100%" height="150"></canvas>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-sm">Input Level</span>
                                    <span id="inputLevelValue" class="text-sm font-weight-bold">0%</span>
                                </div>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div id="inputLevelBar" class="progress-bar bg-gradient-success" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="mt-3" id="noiseCalibrationStatus">
                                <span class="badge bg-gradient-secondary">Noise Profile: Not Calibrated</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Alerts -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h6>Real-time Alerts</h6>
                            <button class="btn btn-sm btn-outline-secondary" id="clearAlertsBtn">
                                <i class="material-symbols-rounded text-sm">delete</i> Clear
                            </button>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <div id="alertsContainer">
                                <div class="text-center text-secondary py-4" id="noAlertsMsg">
                                    <i class="material-symbols-rounded" style="font-size: 48px;">security</i>
                                    <p class="mt-2">No alerts yet. Start detection to monitor audio.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detection Results Panel -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h6>Current Detection Analysis</h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="detectionResultsPanel">
                                <!-- Non-Speech Detection -->
                                <div class="col-md-6">
                                    <h6 class="text-sm text-uppercase text-secondary mb-3">Non-Speech Detection</h6>
                                    <div id="nonSpeechResults">
                                        <p class="text-secondary text-sm">Waiting for audio...</p>
                                    </div>
                                </div>
                                <!-- Speech Detection -->
                                <div class="col-md-6">
                                    <h6 class="text-sm text-uppercase text-secondary mb-3">Speech Detection</h6>
                                    <div id="speechResults">
                                        <p class="text-secondary text-sm">Waiting for audio...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Threat Alert Modal -->
    @include('admin.pages.management.audio-threat.partials.threat-modal')
@endsection

@section('js')
    <script>
        window.audioThreatConfig = {
            apiUrl: '{{ $apiUrl }}',
            csrfToken: '{{ csrf_token() }}',
            routes: {
                analyze: '{{ route("admin.management.audio-threat.analyze") }}',
                calibrate: '{{ route("admin.management.audio-threat.calibrate") }}',
                status: '{{ route("admin.management.audio-threat.status") }}'
            }
        };
    </script>
    @vite(['resources/js/admin/audio-threat.js'])
@endsection

