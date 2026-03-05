@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/audio-video-threat.css'])
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">

        <!-- ===================== HEADER ===================== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">
                            <i class="material-symbols-rounded me-2">sensors</i>
                            Audio &amp; Video Threat Detection
                        </h4>
                        <p class="text-sm text-secondary mb-0">Combined real-time audio and video monitoring for school safety</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button id="startAllBtn" class="btn btn-primary btn-sm">
                            <i class="material-symbols-rounded text-sm">play_arrow</i> Start Detection
                        </button>
                        <button id="stopAllBtn" class="btn btn-danger btn-sm d-none">
                            <i class="material-symbols-rounded text-sm">stop</i> Stop Detection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== ADMIN CONTACT NUMBER ===================== -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon icon-md icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl">
                            <i class="material-symbols-rounded opacity-10 text-white">phone_in_talk</i>
                        </div>
                        <div>
                            <p class="text-xs text-uppercase text-secondary mb-0 fw-bold">Critical SMS Alert Recipient</p>
                            <div id="contactDisplayRow" class="d-flex align-items-center gap-2 mt-1">
                                <span class="font-weight-bold text-dark" id="adminContactDisplay">+9470032488</span>
                                <button class="btn btn-outline-primary btn-xs py-1 px-2" id="editContactBtn" style="font-size:11px;">
                                    <i class="material-symbols-rounded" style="font-size:14px; vertical-align:middle;">edit</i> Edit
                                </button>
                            </div>
                            <div id="contactEditRow" class="d-none mt-1">
                                <div class="input-group input-group-sm" style="max-width:300px;">
                                    <span class="input-group-text"><i class="material-symbols-rounded" style="font-size:14px;">phone</i></span>
                                    <input type="tel" class="form-control" id="adminContactInput"
                                        placeholder="+9470032488" value="+9470032488"
                                        pattern="^\+[0-9]{7,15}$">
                                    <button class="btn btn-success btn-sm" id="saveContactBtn">Save</button>
                                    <button class="btn btn-secondary btn-sm" id="cancelContactBtn">Cancel</button>
                                </div>
                                <div class="text-xs text-secondary mt-1">Format: +[country code][number] e.g. +9470032488</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success" id="smsAlertStatus">SMS Alerts Active</span>
                        <p class="text-xs text-secondary mb-0 mt-1">Twilio SMS · Critical threats only</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== CRITICAL ALERT BANNER ===================== -->
        <div id="criticalAlertBanner" class="alert alert-critical d-none mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="material-symbols-rounded critical-icon">crisis_alert</i>
                <div>
                    <strong>⚠ CRITICAL COMBINED THREAT DETECTED</strong>
                    <p class="mb-0 text-sm" id="criticalAlertMsg">Simultaneous audio and video threats detected. SMS alert sent.</p>
                </div>
            </div>
        </div>

        <!-- ===================== STATUS CARDS ===================== -->
        <div class="row">
            <!-- Audio Status -->
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">mic</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Audio Status</p>
                            <h4 class="mb-0" id="audioStatus">Inactive</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="micStatus"><span class="text-secondary text-sm">Microphone not active</span></p>
                    </div>
                </div>
            </div>

            <!-- Video Status -->
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">videocam</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Video Status</p>
                            <h4 class="mb-0" id="videoStatus">Inactive</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="cameraStatus"><span class="text-secondary text-sm">Camera not active</span></p>
                    </div>
                </div>
            </div>

            <!-- Audio Threats -->
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">hearing</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Audio Threats</p>
                            <h4 class="mb-0" id="audioThreatCount">0</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="lastAudioThreat"><span class="text-secondary text-sm">No audio threats</span></p>
                    </div>
                </div>
            </div>

            <!-- Video Threats -->
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">visibility</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Video Threats</p>
                            <h4 class="mb-0" id="videoThreatCount">0</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0" id="lastVideoThreat"><span class="text-secondary text-sm">No video threats</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== MAIN DETECTION AREA ===================== -->
        <div class="row">

            <!-- ========== LEFT: AUDIO PANEL ========== -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6><i class="material-symbols-rounded text-sm me-1">mic</i> Audio Detection</h6>
                        <button id="calibrateAudioBtn" class="btn btn-outline-info btn-sm" disabled>
                            <i class="material-symbols-rounded text-sm">tune</i> Calibrate
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="audio-visualizer-container mb-3">
                            <canvas id="audioVisualizer" height="100" style="width:100%;"></canvas>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-sm">Input Level</span>
                                <span id="inputLevelValue" class="text-sm font-weight-bold">0%</span>
                            </div>
                            <div class="progress mt-1" style="height:8px;">
                                <div id="inputLevelBar" class="progress-bar bg-gradient-success" style="width:0%"></div>
                            </div>
                        </div>
                        <div id="noiseCalibrationStatus" class="mb-3">
                            <span class="badge bg-gradient-secondary">Noise Profile: Not Calibrated</span>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <h6 class="text-xs text-uppercase text-secondary mb-2">Non-Speech</h6>
                                <div id="nonSpeechResults">
                                    <p class="text-secondary text-sm">Waiting…</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-xs text-uppercase text-secondary mb-2">Speech</h6>
                                <div id="speechResults">
                                    <p class="text-secondary text-sm">Waiting…</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== RIGHT: VIDEO PANEL ========== -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6><i class="material-symbols-rounded text-sm me-1">videocam</i> Video Detection</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="videoSource" id="pcCamera" value="pc" checked>
                            <label class="btn btn-outline-primary" for="pcCamera">PC Camera</label>
                            <input type="radio" class="btn-check" name="videoSource" id="esp32Camera" value="esp32">
                            <label class="btn btn-outline-primary" for="esp32Camera">ESP32-CAM</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="pcCameraSection">
                            <div class="video-container position-relative">
                                <video id="videoElement" autoplay playsinline class="w-100 rounded"></video>
                                <canvas id="detectionCanvas" class="detection-overlay"></canvas>
                                <div id="noVideoMsg" class="no-video-message">
                                    <i class="material-symbols-rounded" style="font-size:48px;">videocam_off</i>
                                    <p class="mt-2">Click "Start Detection" to begin</p>
                                </div>
                            </div>
                        </div>
                        <div id="esp32CameraSection" class="d-none">
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="esp32IpInput" placeholder="192.168.1.100">
                                    <button class="btn btn-primary" id="connectEsp32Btn">
                                        <i class="material-symbols-rounded text-sm">link</i> Connect
                                    </button>
                                </div>
                                <small class="text-muted">Enter ESP32-CAM IP address</small>
                            </div>
                            <div class="video-container position-relative">
                                <img id="esp32Stream" class="w-100 rounded" style="display:none;">
                                <div id="noEsp32Msg" class="no-video-message">
                                    <i class="material-symbols-rounded" style="font-size:48px;">camera</i>
                                    <p class="mt-2">Enter IP and click Connect</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-success" id="fpsCounter">0 FPS</span>
                            <span class="badge bg-info ms-1" id="latencyCounter">0ms</span>
                            <span class="badge bg-secondary ms-1">
                                Objects: <span id="objectCount">0</span>
                            </span>
                        </div>

                        {{-- Object Detection Results --}}
                        <div class="mt-3">
                            <h6 class="text-xs text-uppercase text-secondary mb-2">Detected Objects</h6>
                            <div id="resultsContainer" style="max-height:180px; overflow-y:auto;">
                                <div class="text-center text-secondary py-3" id="noResultsMsg">
                                    <i class="material-symbols-rounded" style="font-size:36px;">search</i>
                                    <p class="mt-1 text-sm">No objects detected yet.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Video Threats Panel --}}
                        <div class="mt-3">
                            <h6 class="text-xs text-uppercase text-secondary mb-2 d-flex align-items-center gap-2">
                                <i class="material-symbols-rounded text-danger" style="font-size:16px;">dangerous</i>
                                Video Threats
                                <span class="badge bg-danger rounded-pill" id="videoThreatBadge" style="display:none;">0</span>
                            </h6>
                            <div id="videoThreatsContainer" style="max-height:180px; overflow-y:auto;">
                                <div class="text-center text-secondary py-2" id="noVideoThreatsMsg">
                                    <i class="material-symbols-rounded" style="font-size:28px;">verified_user</i>
                                    <p class="mt-1 text-sm">No video threats detected.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== ALERTS & HISTORY ===================== -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Real-time Alerts</h6>
                        <button class="btn btn-sm btn-outline-secondary" id="clearAlertsBtn">
                            <i class="material-symbols-rounded text-sm">delete</i> Clear
                        </button>
                    </div>
                    <div class="card-body" style="max-height:350px;overflow-y:auto;">
                        <div id="alertsContainer">
                            <div class="text-center text-secondary py-4" id="noAlertsMsg">
                                <i class="material-symbols-rounded" style="font-size:48px;">security</i>
                                <p class="mt-2">No alerts yet. Start detection to monitor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Detection History</h6>
                    </div>
                    <div class="card-body" style="max-height:350px;overflow-y:auto;">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Source</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Severity</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-3">No history yet</td>
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

@include('admin.pages.management.audio-video-threat.partials.critical-modal')
@endsection

@section('js')
<script>
    window.audioVideoConfig = {
        csrfToken: '{{ csrf_token() }}',
        routes: {
            audioStatus: '{{ route("admin.management.audio-video-threat.audio-status") }}',
            videoStatus: '{{ route("admin.management.audio-video-threat.video-status") }}',
            analyzeAudio: '{{ route("admin.management.audio-video-threat.analyze-audio") }}',
            calibrateAudio: '{{ route("admin.management.audio-video-threat.calibrate-audio") }}',
            startAudioSession: '{{ route("admin.management.audio-video-threat.start-audio-session") }}',
            stopAudioSession: '{{ route("admin.management.audio-video-threat.stop-audio-session") }}',
            processFrame: '{{ route("admin.management.audio-video-threat.process-frame") }}',
            sendCombinedAlert: '{{ route("admin.management.audio-video-threat.send-combined-alert") }}',
        }
    };
</script>
@vite(['resources/js/admin/audio-video-threat.js'])
@endsection