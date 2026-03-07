{{--
    Face Capture Modal
    ──────────────────
    Usage: @include('admin.pages.management.students.partials.face_capture_modal', ['student' => $student])
    Requires window.faceCaptureStudentId and window.faceCaptureStudentName to be set before showing.
--}}

<div class="modal fade" id="faceCaptureModal" tabindex="-1" aria-labelledby="faceCaptureModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden shadow-lg">

            {{-- Header --}}
            <div class="modal-header face-modal-header px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="face-modal-icon-wrap">
                        <i class="material-symbols-rounded text-white" style="font-size:1.6rem">face</i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="faceCaptureModalLabel">
                            Face Registration
                        </h5>
                        <p class="text-white-50 small mb-0">Capture 40 images to train the recognition model</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="faceCapture.cancel()"
                    aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0">
                <div class="row g-0">

                    {{-- Camera column --}}
                    <div class="col-md-7 face-camera-col position-relative">
                        <video id="faceCamVideo" class="w-100 d-block" autoplay playsinline
                            style="object-fit:cover;height:360px;background:#111;"></video>
                        <canvas id="faceCamCanvas" class="d-none"></canvas>

                        {{-- Face overlay guide --}}
                        <div class="face-guide-overlay" id="faceGuideOverlay">
                            <div class="face-oval"></div>
                            <p class="face-guide-text">Position face inside the oval</p>
                        </div>

                        {{-- Capture flash effect --}}
                        <div class="face-flash" id="faceFlash"></div>

                        {{-- Status badges --}}
                        <div class="face-cam-badges position-absolute bottom-0 start-0 w-100 px-3 pb-2 d-flex gap-2">
                            <span class="badge bg-dark bg-opacity-75 small" id="faceCamStatus">
                                <i class="material-symbols-rounded align-middle me-1"
                                    style="font-size:.85rem">videocam</i>
                                Camera off
                            </span>
                            <span class="badge bg-dark bg-opacity-75 small d-none" id="faceDetectionBadge">
                                <i class="material-symbols-rounded align-middle me-1"
                                    style="font-size:.85rem">face_retouching_natural</i>
                                Face detected
                            </span>
                        </div>
                    </div>

                    {{-- Progress column --}}
                    <div class="col-md-5 face-progress-col p-4 d-flex flex-column">

                        {{-- Step: idle --}}
                        <div id="faceStepIdle">
                            <div class="text-center mb-3">
                                <div class="face-step-icon face-step-icon--idle mx-auto mb-3">
                                    <i class="material-symbols-rounded"
                                        style="font-size:2.5rem">face_retouching_natural</i>
                                </div>
                                <h6 class="fw-bold">Ready to capture</h6>
                                <p class="text-muted small mb-0">
                                    Click <strong>Start Capture</strong> to begin. The system will
                                    automatically take 40 photos with slight movements for best results.
                                </p>
                            </div>
                            <ul class="face-tips list-unstyled small text-muted mb-0">
                                <li><i class="material-symbols-rounded align-middle text-success"
                                        style="font-size:1rem">check_circle</i> Good lighting on your face</li>
                                <li><i class="material-symbols-rounded align-middle text-success"
                                        style="font-size:1rem">check_circle</i> Look directly at the camera</li>
                                <li><i class="material-symbols-rounded align-middle text-success"
                                        style="font-size:1rem">check_circle</i> Different angles will be captured</li>
                                <li><i class="material-symbols-rounded align-middle text-warning"
                                        style="font-size:1rem">warning</i> Remove glasses if possible</li>
                            </ul>
                        </div>

                        {{-- Step: capturing --}}
                        <div id="faceStepCapturing" class="d-none flex-column flex-grow-1">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small">Captured</span>
                                    <span class="fw-bold text-primary" id="faceCaptureCountLabel">0 / 40</span>
                                </div>
                                <div class="progress" style="height:10px;border-radius:8px;">
                                    <div id="faceCaptureProgress"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar" style="width:0%"></div>
                                </div>
                            </div>

                            {{-- Thumbnail grid --}}
                            <div id="faceThumbnailGrid" class="face-thumbnail-grid mb-3"></div>

                            <p class="text-muted small mt-auto mb-0" id="faceCaptureHint">Move your head slightly for
                                variety…</p>
                        </div>

                        {{-- Step: training --}}
                        <div id="faceStepTraining" class="d-none text-center">
                            <div class="face-step-icon face-step-icon--training mx-auto mb-3">
                                <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;">
                                </div>
                            </div>
                            <h6 class="fw-bold">Training model…</h6>
                            <p class="text-muted small">This may take a few seconds. Please wait.</p>
                        </div>

                        {{-- Step: done --}}
                        <div id="faceStepDone" class="d-none text-center">
                            <div class="face-step-icon face-step-icon--done mx-auto mb-3">
                                <i class="material-symbols-rounded text-success"
                                    style="font-size:2.5rem">check_circle</i>
                            </div>
                            <h6 class="fw-bold text-success">Face registered!</h6>
                            <p class="text-muted small" id="faceTrainSummary">Model trained successfully.</p>
                        </div>

                        {{-- Step: error --}}
                        <div id="faceStepError" class="d-none text-center">
                            <div class="face-step-icon face-step-icon--error mx-auto mb-3">
                                <i class="material-symbols-rounded text-danger" style="font-size:2.5rem">error</i>
                            </div>
                            <h6 class="fw-bold text-danger">Error</h6>
                            <p class="text-muted small" id="faceErrorMsg">Something went wrong.</p>
                        </div>

                    </div>{{-- /progress col --}}
                </div>{{-- /row --}}
            </div>{{-- /modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer px-4 py-3 justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="faceCapture.cancel()">
                    <i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">close</i> Close
                </button>
                <div class="d-flex gap-2" id="faceActionBtns">
                    <button type="button" class="btn btn-primary px-4" id="faceStartBtn"
                        onclick="faceCapture.start()">
                        <i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">play_arrow</i>
                        Start Capture
                    </button>
                    <button type="button" class="btn btn-success px-4 d-none" id="faceTrainBtn"
                        onclick="faceCapture.train()">
                        <i class="material-symbols-rounded me-1 align-middle"
                            style="font-size:1rem">model_training</i>
                        Train Model
                    </button>
                    <button type="button" class="btn btn-success px-4 d-none" id="faceDoneBtn"
                        onclick="faceCapture.close()">
                        <i class="material-symbols-rounded me-1 align-middle" style="font-size:1rem">check</i>
                        Done
                    </button>
                </div>
            </div>

        </div>{{-- /modal-content --}}
    </div>
</div>

{{-- ═══════════════════════ STYLES ═══════════════════════ --}}
<style>
    /* Header */
    .face-modal-header {
        background: linear-gradient(135deg, #1a3a6e 0%, #2563eb 100%);
    }

    .face-modal-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Camera column */
    .face-camera-col {
        background: #0f0f0f;
    }

    .face-guide-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .face-oval {
        width: 160px;
        height: 210px;
        border-radius: 50%;
        border: 2px dashed rgba(255, 255, 255, .5);
        animation: face-oval-pulse 2s ease-in-out infinite;
    }

    .face-oval.detected {
        border-color: #22c55e;
        animation: none;
    }

    @keyframes face-oval-pulse {

        0%,
        100% {
            border-color: rgba(255, 255, 255, .5);
        }

        50% {
            border-color: rgba(37, 99, 235, .9);
        }
    }

    .face-guide-text {
        color: rgba(255, 255, 255, .7);
        font-size: .78rem;
        margin-top: .75rem;
        background: rgba(0, 0, 0, .4);
        padding: .2rem .6rem;
        border-radius: 1rem;
    }

    .face-flash {
        position: absolute;
        inset: 0;
        background: #fff;
        opacity: 0;
        pointer-events: none;
        transition: opacity 50ms;
    }

    .face-flash.active {
        opacity: .4;
    }

    /* Progress column */
    .face-progress-col {
        background: #fff;
        min-height: 360px;
    }

    .face-step-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .face-step-icon--idle {
        background: #eff6ff;
        color: #2563eb;
    }

    .face-step-icon--training {
        background: #eff6ff;
    }

    .face-step-icon--done {
        background: #f0fdf4;
    }

    .face-step-icon--error {
        background: #fef2f2;
    }

    /* Tips list */
    .face-tips li {
        padding: .25rem 0;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    /* Thumbnail grid */
    .face-thumbnail-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 3px;
        max-height: 120px;
        overflow: hidden;
    }

    .face-thumb {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 3px;
        background: #e5e7eb;
        overflow: hidden;
        animation: face-thumb-pop .2s ease;
    }

    .face-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .face-thumb.ok::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(34, 197, 94, .25);
        border-radius: 3px;
    }

    .face-thumb {
        position: relative;
    }

    @keyframes face-thumb-pop {
        from {
            transform: scale(0.6);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>

{{-- ═══════════════════════ SCRIPT ═══════════════════════ --}}
<script>
    const faceCapture = (() => {
        const TARGET = 40;
        const INTERVAL = 600; // ms between auto-captures

        let _stream = null;
        let _timer = null;
        let _sessionId = null;
        let _captured = 0;
        let _studentId = null;
        let _studentName = null;

        const _el = id => document.getElementById(id);

        /* ─── Public: open modal ─── */
        function open(studentId, studentName) {
            _studentId = studentId;
            _studentName = studentName;
            _reset();
            const modal = bootstrap.Modal.getOrCreateInstance(_el('faceCaptureModal'));
            modal.show();
            _startCamera();
        }

        /* ─── Public: start capture sequence ─── */
        async function start() {
            if (!_stream) {
                _showError('Camera not ready — please allow camera access.');
                return;
            }

            _el('faceStartBtn').classList.add('d-none');

            // Start a registration session
            try {
                const resp = await fetch('{{ url('/api/face/registration/start') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf()
                    },
                    body: JSON.stringify({
                        student_id: _studentId,
                        student_name: _studentName,
                        capture_count: TARGET
                    }),
                });
                const data = await resp.json();
                if (!data.success && !data.session_id) {
                    _showError(data.message || 'Could not start registration session.');
                    return;
                }
                _sessionId = data.session_id;
            } catch (e) {
                _showError('Face API is unavailable. Make sure it is running on port 5004.');
                return;
            }

            _showStep('capturing');
            _el('faceGuideOverlay').style.display = 'none';
            _timer = setInterval(_captureOne, INTERVAL);
        }

        /* ─── Public: train after capture ─── */
        async function train() {
            _el('faceTrainBtn').classList.add('d-none');
            _showStep('training');
            try {
                const resp = await fetch(`{{ url('/api/face/training/train') }}/${_studentId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf()
                    },
                });
                let data;
                try {
                    data = await resp.json();
                } catch (_jsonErr) {
                    throw new Error(
                        `Server returned a non-JSON response (HTTP ${resp.status}). Check the face service logs.`
                        );
                }
                if (data.success === false) {
                    _showError(data.message || 'Training failed.');
                    return;
                }
                _el('faceTrainSummary').textContent =
                    `${TARGET} images trained successfully. ${_studentName} can now be recognised.`;
                _showStep('done');
                _el('faceDoneBtn').classList.remove('d-none');
                // Update the face registration badge on the student form
                _updateStudentFormBadge(true);
            } catch (e) {
                _showError('Training failed: ' + e.message);
            }
        }

        function close() {
            _cleanup();
            bootstrap.Modal.getOrCreateInstance(_el('faceCaptureModal')).hide();
        }

        function cancel() {
            _cleanup();
            bootstrap.Modal.getOrCreateInstance(_el('faceCaptureModal')).hide();
        }

        /* ─── Private: capture one frame ─── */
        async function _captureOne() {
            if (_captured >= TARGET) {
                clearInterval(_timer);
                _timer = null;
                _el('faceTrainBtn').classList.remove('d-none');
                _el('faceCaptureHint').textContent = 'All images captured! Click Train Model to continue.';
                return;
            }

            const video = _el('faceCamVideo');
            const canvas = _el('faceCamCanvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0);

            // Flash effect
            const flash = _el('faceFlash');
            flash.classList.add('active');
            setTimeout(() => flash.classList.remove('active'), 80);

            const imageB64 = canvas.toDataURL('image/jpeg', 0.85);

            // Add thumbnail
            _addThumb(imageB64);

            _captured++;
            _el('faceCaptureCountLabel').textContent = `${_captured} / ${TARGET}`;
            _el('faceCaptureProgress').style.width = `${(_captured / TARGET) * 100}%`;

            // Hints cycle
            const hints = [
                'Look straight at the camera…',
                'Tilt slightly to the left…',
                'Tilt slightly to the right…',
                'Chin slightly up…',
                'Chin slightly down…',
                'Almost there, keep going…',
            ];
            _el('faceCaptureHint').textContent = hints[Math.floor(_captured / (TARGET / hints.length)) %
                hints.length];

            // Send in background (don't await — keep timer smooth)
            fetch('{{ url('/api/face/registration/capture') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': _csrf()
                },
                body: JSON.stringify({
                    session_id: _sessionId,
                    image: imageB64
                }),
            }).catch(() => {});
        }

        /* ─── Private helpers ─── */
        async function _startCamera() {
            try {
                _stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    },
                    audio: false
                });
                const video = _el('faceCamVideo');
                video.srcObject = _stream;
                _el('faceCamStatus').innerHTML =
                    '<i class="material-symbols-rounded align-middle me-1" style="font-size:.85rem">videocam</i> Live';
                _el('faceCamStatus').className = 'badge bg-success bg-opacity-90 small';
            } catch (e) {
                _el('faceCamStatus').innerHTML =
                    '<i class="material-symbols-rounded align-middle me-1" style="font-size:.85rem">videocam_off</i> No camera';
                _el('faceCamStatus').className = 'badge bg-danger bg-opacity-90 small';
            }
        }

        function _stopCamera() {
            if (_stream) {
                _stream.getTracks().forEach(t => t.stop());
                _stream = null;
            }
        }

        function _cleanup() {
            clearInterval(_timer);
            _timer = null;
            _stopCamera();
        }

        function _reset() {
            _captured = 0;
            _sessionId = null;
            _el('faceCaptureCountLabel').textContent = '0 / 40';
            _el('faceCaptureProgress').style.width = '0%';
            _el('faceThumbnailGrid').innerHTML = '';
            _el('faceCaptureHint').textContent = 'Move your head slightly for variety…';
            _el('faceStartBtn').classList.remove('d-none');
            _el('faceTrainBtn').classList.add('d-none');
            _el('faceDoneBtn').classList.add('d-none');
            _el('faceGuideOverlay').style.display = '';
            _showStep('idle');
        }

        function _showStep(step) {
            ['idle', 'capturing', 'training', 'done', 'error'].forEach(s => {
                const el = _el('faceStep' + s.charAt(0).toUpperCase() + s.slice(1));
                if (el) el.classList.toggle('d-none', s !== step);
                if (el && s === 'capturing') el.classList.toggle('d-flex', s === step);
            });
        }

        function _showError(msg) {
            _el('faceErrorMsg').textContent = msg;
            _showStep('error');
            _el('faceStartBtn').classList.remove('d-none');
        }

        function _addThumb(dataUrl) {
            const grid = _el('faceThumbnailGrid');
            const wrap = document.createElement('div');
            wrap.className = 'face-thumb ok';
            const img = document.createElement('img');
            img.src = dataUrl;
            wrap.appendChild(img);
            grid.appendChild(wrap);
        }

        function _updateStudentFormBadge(trained) {
            const badge = document.getElementById('faceRegStatusBadge');
            if (!badge) return;
            badge.className = trained ? 'badge bg-success' : 'badge bg-secondary';
            badge.textContent = trained ? 'Registered' : 'Not Registered';
        }

        const _csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        return {
            open,
            start,
            train,
            close,
            cancel
        };
    })();
</script>
