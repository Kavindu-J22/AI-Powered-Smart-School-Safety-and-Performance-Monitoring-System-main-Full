@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/video-threat.css'])
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
                            <i class="material-symbols-rounded me-2">videocam</i>
                            Left Behind Object Detection System
                        </h4>
                        <p class="text-sm text-secondary mb-0">Real-time video monitoring for left-behind objects and threats</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="switchCameraBtn" class="btn btn-outline-info btn-sm">
                            <i class="material-symbols-rounded text-sm">switch_camera</i> Switch Source
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
                            <i class="material-symbols-rounded opacity-10">videocam</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Status</p>
                            <h4 class="mb-0" id="detectionStatus">Inactive</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="cameraStatus">
                            <span class="text-secondary text-sm">Camera not connected</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">shopping_bag</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Left-Behind Objects</p>
                            <h4 class="mb-0" id="objectCount">0</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="lastObjectTime">
                            <span class="text-secondary text-sm">No objects detected</span>
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
                            <p class="text-sm mb-0 text-capitalize">Frames Processed</p>
                            <h4 class="mb-0" id="framesProcessed">0</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="processingRate">
                            <span class="text-secondary text-sm">0 fps</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Feed & Detection Results -->
        <div class="row">
            <!-- Video Feed -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Video Feed</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="videoSource" id="pcCamera" value="pc" checked>
                            <label class="btn btn-outline-primary" for="pcCamera">PC Camera</label>

                            <input type="radio" class="btn-check" name="videoSource" id="esp32Camera" value="esp32">
                            <label class="btn btn-outline-primary" for="esp32Camera">ESP32-CAM</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- PC Camera Section -->
                        <div id="pcCameraSection">
                            <div class="video-container position-relative">
                                <video id="videoElement" autoplay playsinline class="w-100 rounded"></video>
                                <canvas id="detectionCanvas" class="detection-overlay"></canvas>
                                <div id="noVideoMsg" class="no-video-message">
                                    <i class="material-symbols-rounded" style="font-size: 64px;">videocam_off</i>
                                    <p class="mt-3">Click "Start Detection" to begin</p>
                                </div>
                            </div>
                        </div>

                        <!-- ESP32-CAM Section -->
                        <div id="esp32CameraSection" class="d-none">
                            <div class="mb-3">
                                <label for="esp32IpInput" class="form-label">ESP32-CAM IP Address</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="esp32IpInput"
                                        placeholder="192.168.1.100" value="">
                                    <button class="btn btn-primary" id="connectEsp32Btn">
                                        <i class="material-symbols-rounded text-sm">link</i> Connect
                                    </button>
                                </div>
                                <small class="text-muted">Enter the IP address of your ESP32-CAM device</small>
                            </div>
                            <div class="video-container position-relative">
                                <img id="esp32Stream" class="w-100 rounded" style="display: none;">
                                <canvas id="esp32DetectionCanvas" class="detection-overlay"></canvas>
                                <div id="noEsp32Msg" class="no-video-message">
                                    <i class="material-symbols-rounded" style="font-size: 64px;">camera</i>
                                    <p class="mt-3">Enter ESP32-CAM IP and click Connect</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detection Info Overlay -->
                        <div class="detection-info-overlay" id="detectionInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success" id="fpsCounter">0 FPS</span>
                                <span class="badge bg-info" id="latencyCounter">0ms</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detection Results -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Detection Results</h6>
                        <button class="btn btn-sm btn-outline-secondary" id="clearResultsBtn">
                            <i class="material-symbols-rounded text-sm">delete</i> Clear
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <div id="resultsContainer">
                            <div class="text-center text-secondary py-4" id="noResultsMsg">
                                <i class="material-symbols-rounded" style="font-size: 48px;">search</i>
                                <p class="mt-2">No detections yet. Start monitoring to see results.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detection History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Detection History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0" id="historyTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Details</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Confidence</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">
                                            No detection history available
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@include('admin.pages.management.video-threat.partials.detection-modal')
@endsection

@section('js')
@vite(['resources/js/admin/video-threat.js'])
<script>
    // Initialize Video Threat Detection
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof VideoThreatDetection !== 'undefined') {
            window.videoThreatDetection = new VideoThreatDetection({
                apiUrl: '{{ $apiUrl }}',
                csrfToken: '{{ csrf_token() }}',
                routes: {
                    status: '{{ route("admin.management.video-threat.status") }}',
                    detectObjects: '{{ route("admin.management.video-threat.detect-objects") }}',
                    detectThreats: '{{ route("admin.management.video-threat.detect-threats") }}',
                    processFrame: '{{ route("admin.management.video-threat.process-frame") }}'
                }
            });
        }
    });
</script>
@endsection