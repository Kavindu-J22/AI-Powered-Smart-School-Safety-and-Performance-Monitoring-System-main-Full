<!-- Threat Alert Modal -->
<div class="modal fade" id="threatAlertModal" tabindex="-1" aria-labelledby="threatAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title text-white" id="threatAlertModalLabel">
                    <i class="material-symbols-rounded me-2">warning</i>
                    Threat Detected!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="threat-icon-container pulse-animation">
                        <i class="material-symbols-rounded" id="threatIcon" style="font-size: 64px; color: #EF4444;">emergency</i>
                    </div>
                </div>
                
                <div class="alert-details">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="text-sm text-secondary mb-1">Threat Type</p>
                            <h6 id="modalThreatType" class="text-capitalize">-</h6>
                        </div>
                        <div class="col-6">
                            <p class="text-sm text-secondary mb-1">Threat Level</p>
                            <h6 id="modalThreatLevel">
                                <span class="badge bg-danger">-</span>
                            </h6>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="text-sm text-secondary mb-1">Confidence</p>
                            <h6 id="modalConfidence">-</h6>
                        </div>
                        <div class="col-6">
                            <p class="text-sm text-secondary mb-1">Time Detected</p>
                            <h6 id="modalTime">-</h6>
                        </div>
                    </div>
                    
                    <div class="row" id="detectedClassRow">
                        <div class="col-12">
                            <p class="text-sm text-secondary mb-1">Detection Details</p>
                            <p id="modalDetails" class="mb-0">-</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3" id="speechTextRow" style="display: none;">
                        <div class="col-12">
                            <p class="text-sm text-secondary mb-1">Detected Speech</p>
                            <div class="p-3 bg-light rounded">
                                <p id="modalSpeechText" class="mb-0 font-italic">"-"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-danger" id="reportIncidentBtn">
                    <i class="material-symbols-rounded text-sm me-1">report</i> Report Incident
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Calibration Modal -->
<div class="modal fade" id="calibrationModal" tabindex="-1" aria-labelledby="calibrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title text-white" id="calibrationModalLabel">
                    <i class="material-symbols-rounded me-2">tune</i>
                    Noise Calibration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <div class="spinner-border text-info" role="status" id="calibrationSpinner" style="display: none;">
                        <span class="visually-hidden">Calibrating...</span>
                    </div>
                    <i class="material-symbols-rounded" id="calibrationIcon" style="font-size: 64px; color: #3B82F6;">settings_voice</i>
                </div>
                
                <h6 id="calibrationTitle">Calibrate Ambient Noise</h6>
                <p class="text-secondary" id="calibrationMessage">
                    Keep the environment quiet and normal. This will help the system distinguish threats from background noise.
                </p>
                
                <div class="progress mt-3" style="height: 10px; display: none;" id="calibrationProgress">
                    <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="startCalibrationBtn">
                    <i class="material-symbols-rounded text-sm me-1">play_arrow</i> Start Calibration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Microphone Permission Modal -->
<div class="modal fade" id="micPermissionModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="micPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="micPermissionModalLabel">
                    <i class="material-symbols-rounded me-2">mic</i>
                    Microphone Access Required
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="material-symbols-rounded" style="font-size: 64px; color: var(--primary-green);">mic_none</i>
                </div>
                
                <h6>Enable Microphone Access</h6>
                <p class="text-secondary">
                    To detect audio threats in real-time, the system needs access to your microphone. 
                    Your privacy is protected - audio is processed locally and discarded after analysis.
                </p>
                
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="material-symbols-rounded me-2">info</i>
                    <small>Audio is only analyzed for threat detection and is never stored or recorded.</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary btn-lg" id="grantMicPermissionBtn">
                    <i class="material-symbols-rounded text-sm me-1">check</i> Allow Microphone Access
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .threat-icon-container {
        display: inline-block;
        padding: 20px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.1);
    }
</style>

