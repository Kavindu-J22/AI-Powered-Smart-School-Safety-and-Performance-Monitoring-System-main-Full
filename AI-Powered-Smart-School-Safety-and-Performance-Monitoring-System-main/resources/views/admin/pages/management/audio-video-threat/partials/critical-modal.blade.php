<!-- Critical Combined Threat Modal -->
<div class="modal fade" id="criticalThreatModal" tabindex="-1" role="dialog" aria-labelledby="criticalThreatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title" id="criticalThreatModalLabel">
                    <i class="material-symbols-rounded me-2">crisis_alert</i>
                    CRITICAL COMBINED THREAT
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="critical-pulse-icon">
                        <i class="material-symbols-rounded text-danger" style="font-size: 64px;">warning</i>
                    </div>
                    <h5 class="text-danger mt-2">Simultaneous Audio &amp; Video Threat Detected!</h5>
                    <p class="text-secondary text-sm">An email alert has been dispatched to security personnel.</p>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="card bg-light border-0">
                            <div class="card-body p-3 text-center">
                                <i class="material-symbols-rounded text-warning mb-1">mic</i>
                                <p class="text-xs text-uppercase text-secondary mb-1">Audio Threat</p>
                                <p class="font-weight-bold mb-0" id="modalAudioType">—</p>
                                <p class="text-xs text-secondary" id="modalAudioConf">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light border-0">
                            <div class="card-body p-3 text-center">
                                <i class="material-symbols-rounded text-danger mb-1">videocam</i>
                                <p class="text-xs text-uppercase text-secondary mb-1">Video Threat</p>
                                <p class="font-weight-bold mb-0" id="modalVideoType">—</p>
                                <p class="text-xs text-secondary" id="modalVideoConf">—</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0 py-2 text-sm">
                    <i class="material-symbols-rounded text-sm me-1">info</i>
                    Dispatch security personnel immediately and review live footage.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-danger btn-sm" id="acknowledgeAlertBtn">
                    <i class="material-symbols-rounded text-sm">check</i> Acknowledge
                </button>
            </div>
        </div>
    </div>
</div>

