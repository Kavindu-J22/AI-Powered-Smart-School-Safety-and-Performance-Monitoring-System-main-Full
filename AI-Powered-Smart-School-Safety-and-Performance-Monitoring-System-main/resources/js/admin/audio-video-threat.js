/**
 * Audio & Video Combined Threat Detection
 * Combines audio (port 5002) and video (port 5003) monitoring.
 * Triggers a CRITICAL alert + email when BOTH detect threats simultaneously.
 */

class AudioVideoThreatDetector {
    constructor() {
        this.cfg = window.audioVideoConfig || {};
        this.routes = this.cfg.routes || {};
        this.csrf = this.cfg.csrfToken || '';

        /* ---------- state ---------- */
        this.isRunning = false;
        this.sessionId = null;
        this.videoSource = 'pc'; // 'pc' | 'esp32'

        /* ---------- audio ---------- */
        this.audioContext = null;
        this.analyser = null;
        this.scriptProcessor = null;
        this.audioBuffer = [];
        this.sampleRate = 44100;
        this.audioInterval = null;
        this.animationId = null;
        this.mediaStream = null;
        this.audioStats = { threats: 0, chunks: 0 };

        /* ---------- video ---------- */
        this.cameraStream = null;
        this.frameInterval = null;
        this.esp32Interval = null;
        this.videoStats = { threats: 0, frames: 0, latencyTotal: 0 };

        /* ---------- combined threat state ---------- */
        this.lastAudioThreat = null;   // { data, time }
        this.lastVideoThreat = null;   // { data, time }
        this.combinedCooldown = false; // prevent spamming alerts
        this.COMBINED_WINDOW_MS = 8000; // threats within 8 s = combined

        /* ---------- history ---------- */
        this.history = [];

        this.init();
    }

    /* ============================================================
       INIT
    ============================================================ */
    init() {
        this._bindEls();
        this._bindEvents();
        this._checkApiStatuses();
    }

    _bindEls() {
        this.startBtn      = document.getElementById('startAllBtn');
        this.stopBtn       = document.getElementById('stopAllBtn');
        this.calibrateBtn  = document.getElementById('calibrateAudioBtn');
        this.clearAlertsBtn= document.getElementById('clearAlertsBtn');

        this.audioStatusEl    = document.getElementById('audioStatus');
        this.videoStatusEl    = document.getElementById('videoStatus');
        this.micStatusEl      = document.getElementById('micStatus');
        this.cameraStatusEl   = document.getElementById('cameraStatus');
        this.audioThreatCount = document.getElementById('audioThreatCount');
        this.videoThreatCount = document.getElementById('videoThreatCount');
        this.lastAudioEl      = document.getElementById('lastAudioThreat');
        this.lastVideoEl      = document.getElementById('lastVideoThreat');

        this.inputLevelBar   = document.getElementById('inputLevelBar');
        this.inputLevelValue = document.getElementById('inputLevelValue');
        this.nonSpeechDiv    = document.getElementById('nonSpeechResults');
        this.speechDiv       = document.getElementById('speechResults');

        this.videoEl     = document.getElementById('videoElement');
        this.canvas      = document.getElementById('detectionCanvas');
        this.esp32Img    = document.getElementById('esp32Stream');
        this.noVideoMsg  = document.getElementById('noVideoMsg');
        this.fpsCounter  = document.getElementById('fpsCounter');
        this.latencyEl   = document.getElementById('latencyCounter');

        this.alertsContainer      = document.getElementById('alertsContainer');
        this.noAlertsMsg          = document.getElementById('noAlertsMsg');
        this.historyBody          = document.getElementById('historyTableBody');
        this.videoThreatsContainer = document.getElementById('videoThreatsContainer');
        this.noVideoThreatsMsg    = document.getElementById('noVideoThreatsMsg');
        this.videoThreatBadge     = document.getElementById('videoThreatBadge');

        this.criticalBanner  = document.getElementById('criticalAlertBanner');
        this.criticalMsg     = document.getElementById('criticalAlertMsg');

        // Admin contact number UI
        this.adminContactDisplay = document.getElementById('adminContactDisplay');
        this.adminContactInput   = document.getElementById('adminContactInput');
        this.contactDisplayRow   = document.getElementById('contactDisplayRow');
        this.contactEditRow      = document.getElementById('contactEditRow');
        this.editContactBtn      = document.getElementById('editContactBtn');
        this.saveContactBtn      = document.getElementById('saveContactBtn');
        this.cancelContactBtn    = document.getElementById('cancelContactBtn');

        this.visualizer    = document.getElementById('audioVisualizer');
        this.visualizerCtx = this.visualizer?.getContext('2d');
    }

    _bindEvents() {
        this.startBtn?.addEventListener('click',     () => this.startAll());
        this.stopBtn?.addEventListener('click',      () => this.stopAll());
        this.calibrateBtn?.addEventListener('click', () => this._calibrateAudio());
        this.clearAlertsBtn?.addEventListener('click', () => this._clearAlerts());

        document.getElementById('acknowledgeAlertBtn')?.addEventListener('click', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('criticalThreatModal'));
            modal?.hide();
        });

        document.querySelectorAll('input[name="videoSource"]').forEach(r => {
            r.addEventListener('change', e => {
                if (!this.isRunning) this._switchVideoSource(e.target.value);
            });
        });

        document.getElementById('connectEsp32Btn')?.addEventListener('click', () => this._connectEsp32());

        // Admin contact number edit/save/cancel
        this.editContactBtn?.addEventListener('click', () => {
            this.adminContactInput.value = this.adminContactDisplay.textContent.trim();
            this.contactDisplayRow?.classList.add('d-none');
            this.contactEditRow?.classList.remove('d-none');
            this.adminContactInput?.focus();
        });

        this.saveContactBtn?.addEventListener('click', () => {
            const val = this.adminContactInput?.value?.trim();
            if (!val || !/^\+[0-9]{7,15}$/.test(val)) {
                this.adminContactInput?.classList.add('is-invalid');
                return;
            }
            this.adminContactInput?.classList.remove('is-invalid');
            if (this.adminContactDisplay) this.adminContactDisplay.textContent = val;
            this.contactEditRow?.classList.add('d-none');
            this.contactDisplayRow?.classList.remove('d-none');
        });

        this.cancelContactBtn?.addEventListener('click', () => {
            this.adminContactInput?.classList.remove('is-invalid');
            this.contactEditRow?.classList.add('d-none');
            this.contactDisplayRow?.classList.remove('d-none');
        });
    }

    /* ============================================================
       API STATUS CHECKS
    ============================================================ */
    async _checkApiStatuses() {
        await Promise.allSettled([this._checkAudioApi(), this._checkVideoApi()]);
    }

    async _checkAudioApi() {
        try {
            const r = await fetch(this.routes.audioStatus);
            if (r.ok) {
                this.calibrateBtn && (this.calibrateBtn.disabled = false);
                this._setStatus(this.audioStatusEl, 'Ready', 'text-success');
            }
        } catch { this._setStatus(this.audioStatusEl, 'Offline', 'text-danger'); }
    }

    async _checkVideoApi() {
        try {
            const r = await fetch(this.routes.videoStatus);
            if (r.ok) this._setStatus(this.videoStatusEl, 'Ready', 'text-success');
        } catch { this._setStatus(this.videoStatusEl, 'Offline', 'text-danger'); }
    }

    /* ============================================================
       START / STOP ALL
    ============================================================ */
    async startAll() {
        this.isRunning = true;
        this.startBtn?.classList.add('d-none');
        this.stopBtn?.classList.remove('d-none');
        this.sessionId = 'av_' + Date.now();
        await Promise.allSettled([this._startAudio(), this._startVideo()]);
    }

    async stopAll() {
        this.isRunning = false;
        this.startBtn?.classList.remove('d-none');
        this.stopBtn?.classList.add('d-none');
        this._stopAudio();
        this._stopVideo();
    }

    /* ============================================================
       AUDIO DETECTION
    ============================================================ */
    async _startAudio() {
        try {
            this.mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 2048;
            const source = this.audioContext.createMediaStreamSource(this.mediaStream);
            source.connect(this.analyser);

            // ScriptProcessor for raw PCM capture (same as standalone audio-threat.js)
            this.scriptProcessor = this.audioContext.createScriptProcessor(4096, 1, 1);
            this.audioBuffer = [];
            this.sampleRate = this.audioContext.sampleRate;
            source.connect(this.scriptProcessor);
            this.scriptProcessor.connect(this.audioContext.destination);

            this.scriptProcessor.onaudioprocess = (e) => {
                if (!this.isRunning) return;
                this.audioBuffer.push(new Float32Array(e.inputBuffer.getChannelData(0)));
            };

            // Process audio every 4 seconds
            this.audioInterval = setInterval(() => {
                if (this.audioBuffer.length > 0 && this.isRunning) {
                    this._processAudioBuffer();
                }
            }, 4000);

            this._setStatus(this.audioStatusEl, 'Active', 'text-success');
            this.micStatusEl && (this.micStatusEl.innerHTML = '<span class="text-success text-sm">🎙 Microphone active</span>');

            // Start session
            await fetch(this.routes.startAudioSession, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ session_id: this.sessionId })
            });

            this._drawAudioVisualizer();
        } catch (e) {
            console.error('Audio start error:', e);
            this._setStatus(this.audioStatusEl, 'Error', 'text-danger');
            this._addAlert('Microphone access denied or audio API unavailable.', 'warning', 'Audio');
        }
    }

    async _processAudioBuffer() {
        if (this.audioBuffer.length === 0) return;

        // Combine all captured chunks
        const totalLength = this.audioBuffer.reduce((acc, chunk) => acc + chunk.length, 0);
        const combined = new Float32Array(totalLength);
        let offset = 0;
        for (const chunk of this.audioBuffer) {
            combined.set(chunk, offset);
            offset += chunk.length;
        }
        this.audioBuffer = [];

        // Resample to 16 kHz if needed
        let audioData = combined;
        if (this.sampleRate !== 16000) {
            audioData = this._resampleAudio(combined, this.sampleRate, 16000);
        }

        // Convert Float32 → Int16 PCM
        const pcm = new Int16Array(audioData.length);
        for (let i = 0; i < audioData.length; i++) {
            const s = Math.max(-1, Math.min(1, audioData[i]));
            pcm[i] = s < 0 ? s * 0x8000 : s * 0x7FFF;
        }

        const base64 = this._arrayBufferToBase64(pcm.buffer);

        try {
            const r = await fetch(this.routes.analyzeAudio, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({
                    audio_data: base64,
                    format: 'pcm16',
                    sample_rate: 16000,
                    session_id: this.sessionId
                })
            });
            const data = await r.json();
            this.audioStats.chunks++;
            if (data.success && data.result?.is_threat) {
                this.audioStats.threats++;
                this._onAudioThreat(data.result);
            }
            this._renderAudioResults(data.result);
        } catch (e) { console.error('Audio analysis error:', e); }
    }

    _onAudioThreat(result) {
        this.lastAudioThreat = { data: result, time: Date.now() };
        this.audioThreatCount && (this.audioThreatCount.textContent = this.audioStats.threats);

        // Resolve human-readable label from the sub-result, not just threat_type
        const label = this._audioThreatLabel(result);

        this.lastAudioEl && (this.lastAudioEl.innerHTML =
            `<span class="text-warning text-sm">Last: ${label}</span>`);

        // Build detail line for the alert feed
        let detail = '';
        if (result.threat_type === 'non_speech' && result.non_speech_result?.detected_class) {
            detail = ` — Detected: ${result.non_speech_result.detected_class.replace(/_/g, ' ')}`;
        } else if (result.threat_type === 'speech' && result.speech_result?.text) {
            detail = ` — "${result.speech_result.text}"`;
        }

        this._addAlert(
            `Audio Threat: ${label} (${Math.round((result.confidence||0)*100)}%)${detail}`,
            'audio-threat', 'Audio'
        );
        this._addHistory('Audio', label, result.threat_level || 'High');
        this._checkCombinedThreat();
    }

    /**
     * Build a human-readable label for an audio threat result.
     * For non-speech: returns the detected class (e.g. "Screaming", "Glass Breaking").
     * For speech:     returns "Speech Threat" with keywords if present.
     * For combined:   returns both.
     */
    _audioThreatLabel(result) {
        const type = result.threat_type || '';
        if (type === 'non_speech') {
            const cls = result.non_speech_result?.detected_class || 'Non-Speech';
            return cls.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }
        if (type === 'speech') {
            const kw = result.speech_result?.detected_keywords?.map(k => k.keyword || k).join(', ');
            return kw ? `Speech (${kw})` : 'Speech Threat';
        }
        if (type === 'combined') {
            const ns  = result.non_speech_result?.detected_class || '';
            const kw  = result.speech_result?.detected_keywords?.map(k => k.keyword || k).join(', ') || '';
            const parts = [ns && ns.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()), kw && `Speech(${kw})`].filter(Boolean);
            return parts.length ? parts.join(' + ') : 'Combined Threat';
        }
        return type ? type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'Unknown';
    }

    _renderAudioResults(result) {
        if (!result) return;

        // Non-speech results — field names match standalone audio-threat.js
        const ns = result.non_speech_result;
        if (this.nonSpeechDiv && ns) {
            const probs = ns.all_probabilities || {};
            let html = `<div class="result-item">
                <div class="d-flex justify-content-between">
                    <span>Detected: <strong class="text-capitalize">${ns.detected_class || 'Clear'}</strong></span>
                    <span class="badge ${ns.is_threat ? 'bg-danger' : 'bg-success'}">${((ns.confidence||0)*100).toFixed(1)}%</span>
                </div>
            </div>`;
            for (const [cls, prob] of Object.entries(probs)) {
                if (cls === 'normal') continue;
                html += `<div class="result-item">
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-capitalize">${cls.replace('_',' ')}</span>
                        <span>${(prob*100).toFixed(1)}%</span>
                    </div>
                    <div class="probability-bar"><div class="fill ${cls}" style="width:${prob*100}%"></div></div>
                </div>`;
            }
            this.nonSpeechDiv.innerHTML = html;
        }

        // Speech results
        const sp = result.speech_result;
        if (this.speechDiv && sp) {
            let html = '';
            if (sp.text) {
                const kwHtml = (sp.detected_keywords?.length > 0)
                    ? `<div class="mt-2"><small class="text-danger"><strong>Keywords:</strong> ${sp.detected_keywords.map(k=>k.keyword||k).join(', ')}</small></div>`
                    : '';
                html = `<div class="result-item">
                    <p class="text-sm mb-2"><strong>Transcription:</strong></p>
                    <p class="font-italic">"${sp.text}"</p>
                    ${sp.is_threat ? `<span class="badge bg-danger">Threat - ${sp.threat_level}</span>` : '<span class="badge bg-success">Safe</span>'}
                    ${kwHtml}
                </div>`;
            } else {
                html = `<p class="text-secondary text-sm">${sp.transcription_error || 'Listening for speech...'}</p>`;
            }
            this.speechDiv.innerHTML = html;
        }
    }

    _stopAudio() {
        clearInterval(this.audioInterval);
        this.audioInterval = null;
        cancelAnimationFrame(this.animationId);

        if (this.scriptProcessor) {
            this.scriptProcessor.disconnect();
            this.scriptProcessor = null;
        }
        this.audioBuffer = [];
        this.mediaStream?.getTracks().forEach(t => t.stop());
        this.audioContext?.close();
        this.audioContext = null;

        this._setStatus(this.audioStatusEl, 'Stopped', 'text-secondary');
        this.micStatusEl && (this.micStatusEl.innerHTML = '<span class="text-secondary text-sm">Microphone inactive</span>');

        fetch(this.routes.stopAudioSession, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ session_id: this.sessionId })
        }).catch(() => {});
    }

    _drawAudioVisualizer() {
        if (!this.visualizerCtx || !this.analyser) return;
        const bufLen = this.analyser.frequencyBinCount;
        const dataArr = new Uint8Array(bufLen);
        const ctx = this.visualizerCtx;
        const W = this.visualizer.width;
        const H = this.visualizer.height;

        const draw = () => {
            if (!this.isRunning) return;
            this.animationId = requestAnimationFrame(draw);

            // Frequency bars — same as standalone audio-threat.js
            this.analyser.getByteFrequencyData(dataArr);
            ctx.fillStyle = '#1a1a2e';
            ctx.fillRect(0, 0, W, H);

            const barWidth = (W / bufLen) * 2.5;
            let x = 0;
            for (let i = 0; i < bufLen; i++) {
                const barHeight = (dataArr[i] / 255) * H;
                const hue = 120 - (dataArr[i] / 255) * 120;
                ctx.fillStyle = `hsl(${hue}, 80%, 50%)`;
                ctx.fillRect(x, H - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }

            // Level bar
            const avg = dataArr.reduce((a, b) => a + b, 0) / bufLen;
            const pct = Math.round((avg / 255) * 100);
            if (this.inputLevelBar) {
                this.inputLevelBar.style.width = pct + '%';
                this.inputLevelBar.classList.remove('low', 'medium', 'high');
                this.inputLevelBar.classList.add(pct < 30 ? 'low' : pct < 60 ? 'medium' : 'high');
            }
            if (this.inputLevelValue) this.inputLevelValue.textContent = pct + '%';
        };
        draw();
    }

    /* ============================================================
       VIDEO DETECTION
    ============================================================ */
    async _startVideo() {
        try {
            if (this.videoSource === 'pc') {
                this.cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 640, height: 480 }, audio: false
                });
                this.videoEl.srcObject = this.cameraStream;

                // Wait for metadata before starting frame capture (same as standalone video-threat.js)
                await new Promise((resolve, reject) => {
                    this.videoEl.onloadedmetadata = () => {
                        this.videoEl.play().then(resolve).catch(reject);
                    };
                    setTimeout(() => reject(new Error('Video loading timeout')), 5000);
                });

                this.noVideoMsg?.classList.add('d-none');
                this._setStatus(this.videoStatusEl, 'Active', 'text-success');
                this.cameraStatusEl && (this.cameraStatusEl.innerHTML = '<span class="text-success text-sm">📷 Camera active</span>');
                this._startFrameCapture();
            } else {
                // ESP32 mode — stream will be set when _connectEsp32 is called
                this._setStatus(this.videoStatusEl, 'Active', 'text-success');
                this._startEsp32Capture();
            }
        } catch (e) {
            console.error('Video start error:', e);
            this.isRunning = false;
            this._setStatus(this.videoStatusEl, 'Error', 'text-danger');
            this._addAlert('Camera access denied or video API unavailable.', 'warning', 'Video');
        }
    }

    _startFrameCapture() {
        const captureCanvas = document.createElement('canvas');
        const captureCtx = captureCanvas.getContext('2d');

        const processFrame = async () => {
            if (!this.isRunning) return;

            try {
                // Validate video is ready
                if (!this.videoEl.videoWidth || !this.videoEl.videoHeight) {
                    setTimeout(processFrame, 100);
                    return;
                }

                captureCanvas.width  = this.videoEl.videoWidth;
                captureCanvas.height = this.videoEl.videoHeight;
                captureCtx.drawImage(this.videoEl, 0, 0);
                const b64Frame = captureCanvas.toDataURL('image/jpeg', 0.8).split(',')[1];
                if (!b64Frame) { setTimeout(processFrame, 100); return; }

                const t0 = Date.now();
                const r = await fetch(this.routes.processFrame, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ frame: b64Frame })
                });
                const data = await r.json();
                const latency = Date.now() - t0;

                this.videoStats.frames++;
                this.videoStats.latencyTotal += latency;
                if (this.latencyEl) this.latencyEl.textContent = latency + 'ms';

                // FPS
                const fps = this.videoStats.frames / ((Date.now() - this._videoStartTime) / 1000);
                if (this.fpsCounter) this.fpsCounter.textContent = fps.toFixed(1) + ' FPS';

                if (data.success) {
                    this._drawDetections(data);
                    this._handleVideoDetections(data);
                }
            } catch (e) { console.error('Frame error:', e); }

            // Recursive scheduling — ~10 FPS, waits for response before next frame
            if (this.isRunning) setTimeout(processFrame, 100);
        };

        this._videoStartTime = Date.now();
        processFrame();
    }

    _startEsp32Capture() {
        this.esp32Interval = setInterval(async () => {
            if (!this.isRunning || !this.esp32Img?.src) return;
            try {
                const c = document.createElement('canvas');
                c.width = this.esp32Img.width || 640;
                c.height = this.esp32Img.height || 480;
                c.getContext('2d').drawImage(this.esp32Img, 0, 0);
                const b64 = c.toDataURL('image/jpeg', 0.8).split(',')[1];
                if (!b64) return;
                const r = await fetch(this.routes.processFrame, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ frame: b64 })
                });
                const data = await r.json();
                if (data.success) {
                    this._drawDetections(data);
                    this._handleVideoDetections(data);
                }
            } catch (e) { console.error('ESP32 frame error:', e); }
        }, 200);
    }

    _handleVideoDetections(data) {
        const objects = data.objects;
        const threats = data.threats;

        if (objects?.detections?.length > 0) {
            this._addDetectionResult('object', objects.detections);
            document.getElementById('objectCount') && (document.getElementById('objectCount').textContent = objects.total_objects || objects.detections.length);
        } else {
            this._clearDetectionResults();
        }

        if (threats?.is_threat) {
            this.videoStats.threats++;
            this._onVideoThreat(threats);
        } else if (objects?.left_behind_count > 0) {
            const leftBehindItems = objects.detections
                .filter(o => o.is_left_behind)
                .map(o => o.class_name).join(', ');
            this._addAlert(`Left-behind object: ${leftBehindItems}`, 'video-threat', 'Video');
            this._addHistory('Video', 'Left-Behind Object', 'Medium');
        }
    }

    _onVideoThreat(threats) {
        this.lastVideoThreat = { data: threats, time: Date.now() };
        this.videoThreatCount && (this.videoThreatCount.textContent = this.videoStats.threats);

        // Human-readable label (e.g. "Weapon Detected" → "Weapon Detected")
        const label = threats.threat_type
            ? threats.threat_type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
            : 'Unknown';

        this.lastVideoEl && (this.lastVideoEl.innerHTML =
            `<span class="text-danger text-sm">Last: ${label}</span>`);

        // Real-time alerts feed
        this._addAlert(
            `Video Threat: ${label} (${Math.round((threats.confidence||0)*100)}%)`,
            'video-threat', 'Video'
        );

        // Detection history
        this._addHistory('Video', label, threats.threat_level || 'High');

        // Dedicated video threats panel
        this._addVideoThreatItem(threats, label);

        this._checkCombinedThreat();
    }

    /**
     * Render a detected video threat card inside #videoThreatsContainer.
     */
    _addVideoThreatItem(threats, label) {
        if (!this.videoThreatsContainer) return;

        // Hide empty-state message
        if (this.noVideoThreatsMsg) this.noVideoThreatsMsg.style.display = 'none';

        // Update badge count
        if (this.videoThreatBadge) {
            this.videoThreatBadge.style.display = '';
            this.videoThreatBadge.textContent = this.videoStats.threats;
        }

        const time = new Date().toLocaleTimeString();
        const conf = Math.round((threats.confidence || 0) * 100);
        const level = (threats.threat_level || 'High');
        const levelCls = {
            Critical: 'danger',
            High:     'danger',
            Medium:   'warning',
            Low:      'secondary'
        }[level] || 'danger';

        const el = document.createElement('div');
        el.className = 'alert alert-danger mb-2 py-2';
        el.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="material-symbols-rounded text-danger" style="font-size:16px;">dangerous</i>
                        <strong>⚠ ${label}</strong>
                        <span class="badge bg-${levelCls}">${level}</span>
                    </div>
                    <div class="text-sm text-muted">Confidence: ${conf}%</div>
                </div>
                <small class="text-muted">${time}</small>
            </div>`;

        // Newest threats at top
        this.videoThreatsContainer.insertBefore(el, this.videoThreatsContainer.firstChild);
    }

    _drawDetections(data) {
        if (!this.canvas) return;
        const video = this.videoSource === 'pc' ? this.videoEl : this.esp32Img;
        const width  = video?.videoWidth  || video?.width  || 640;
        const height = video?.videoHeight || video?.height || 480;
        this.canvas.width  = width;
        this.canvas.height = height;
        const ctx = this.canvas.getContext('2d');
        ctx.clearRect(0, 0, width, height);

        // Draw bounding boxes — API returns [x1, y1, x2, y2] absolute coords
        if (data.objects?.detections?.length > 0) {
            data.objects.detections.forEach(obj => {
                const [x1, y1, x2, y2] = obj.bbox;
                if (x1 == null) return;

                // Color by type (matches standalone video-threat.js logic)
                let color, label;
                if (obj.class_name?.toLowerCase() === 'person') {
                    color = '#FCD34D'; label = `👤 ${obj.class_name}`;
                } else if (obj.is_left_behind) {
                    color = '#EF4444'; label = `${obj.class_name} [LEFT BEHIND]`;
                } else if (obj.is_unknown) {
                    color = '#A855F7'; label = `❓ unknown (${obj.original_class_name || 'unidentified'})`;
                } else {
                    color = '#10B981'; label = obj.class_name || 'object';
                }

                ctx.strokeStyle = color; ctx.lineWidth = 3;
                ctx.strokeRect(x1, y1, x2 - x1, y2 - y1);

                const lw = Math.max(200, ctx.measureText(label).width + 10);
                ctx.fillStyle = color;
                ctx.fillRect(x1, Math.max(0, y1 - 25), lw, 25);
                ctx.fillStyle = '#FFFFFF'; ctx.font = 'bold 14px Arial';
                ctx.fillText(label, x1 + 5, Math.max(15, y1 - 7));
                ctx.font = '12px Arial';
                ctx.fillText(`${((obj.confidence||0)*100).toFixed(1)}%`, x1 + 5, y2 - 5);
            });
        }

        // Threat overlay
        if (data.threats?.is_threat) {
            ctx.fillStyle = 'rgba(239,68,68,0.3)';
            ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#EF4444'; ctx.font = 'bold 24px Arial';
            ctx.strokeStyle = '#FFFFFF'; ctx.lineWidth = 3;
            ctx.strokeText(`⚠ THREAT: ${data.threats.threat_type}`, 10, 40);
            ctx.fillText(`⚠ THREAT: ${data.threats.threat_type}`, 10, 40);
            ctx.font = 'bold 18px Arial';
            ctx.strokeText(`Confidence: ${((data.threats.confidence||0)*100).toFixed(1)}%`, 10, 70);
            ctx.fillText(`Confidence: ${((data.threats.confidence||0)*100).toFixed(1)}%`, 10, 70);
        }
    }

    _stopVideo() {
        clearInterval(this.frameInterval);
        clearInterval(this.esp32Interval);
        this.cameraStream?.getTracks().forEach(t => t.stop());
        this.videoEl && (this.videoEl.srcObject = null);
        this.noVideoMsg?.classList.remove('d-none');
        this._setStatus(this.videoStatusEl, 'Stopped', 'text-secondary');
        this.cameraStatusEl && (this.cameraStatusEl.innerHTML = '<span class="text-secondary text-sm">Camera inactive</span>');
    }

    _switchVideoSource(source) {
        this.videoSource = source;
        document.getElementById('pcCameraSection')?.classList.toggle('d-none', source !== 'pc');
        document.getElementById('esp32CameraSection')?.classList.toggle('d-none', source !== 'esp32');
    }

    _connectEsp32() {
        const ip = document.getElementById('esp32IpInput')?.value?.trim();
        if (!ip) return;
        const imgEl = this.esp32Img;
        if (imgEl) {
            imgEl.src = `http://${ip}/stream`;
            imgEl.style.display = 'block';
            document.getElementById('noEsp32Msg')?.classList.add('d-none');
        }
    }

    /* ============================================================
       COMBINED CRITICAL THREAT LOGIC
    ============================================================ */
    _checkCombinedThreat() {
        if (this.combinedCooldown) return;
        if (!this.lastAudioThreat || !this.lastVideoThreat) return;

        const timeDiff = Math.abs(this.lastAudioThreat.time - this.lastVideoThreat.time);
        if (timeDiff <= this.COMBINED_WINDOW_MS) {
            this._triggerCriticalAlert();
        }
    }

    async _triggerCriticalAlert() {
        this.combinedCooldown = true;
        setTimeout(() => { this.combinedCooldown = false; }, 30000); // 30 s cooldown

        const audioData = this.lastAudioThreat.data;
        const videoData = this.lastVideoThreat.data;

        // Use human-readable labels (e.g. "Screaming" instead of "non_speech")
        const audioLabel = this._audioThreatLabel(audioData);
        const videoLabel = videoData.threat_type
            ? videoData.threat_type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
            : 'Unknown';

        // Read admin contact number from UI
        const alertNumber = this.adminContactDisplay?.textContent?.trim() || '+9470032488';

        // Show banner
        if (this.criticalBanner) {
            this.criticalBanner.classList.remove('d-none');
            this.criticalMsg && (this.criticalMsg.textContent =
                `Audio: ${audioLabel} + Video: ${videoLabel} — SMS alert sent to ${alertNumber}`);
        }

        // Populate modal fields
        const modalAudioType = document.getElementById('modalAudioType');
        const modalAudioConf = document.getElementById('modalAudioConf');
        const modalVideoType = document.getElementById('modalVideoType');
        const modalVideoConf = document.getElementById('modalVideoConf');
        if (modalAudioType) modalAudioType.textContent = audioLabel;
        if (modalAudioConf) modalAudioConf.textContent = `Confidence: ${Math.round((audioData.confidence||0)*100)}%`;
        if (modalVideoType) modalVideoType.textContent = videoLabel;
        if (modalVideoConf) modalVideoConf.textContent = `Confidence: ${Math.round((videoData.confidence||0)*100)}%`;

        // Show modal
        const modalEl = document.getElementById('criticalThreatModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // Add to alerts feed
        this._addAlert(
            `⚠ CRITICAL: Simultaneous Audio (${audioLabel}) + Video (${videoLabel}) threat!`,
            'combined-threat', 'CRITICAL'
        );
        this._addHistory('CRITICAL', `${audioLabel} + ${videoLabel}`, 'Critical');

        // Send SMS via Laravel → Twilio
        try {
            await fetch(this.routes.sendCombinedAlert, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({
                    audio_threat: audioData,
                    video_threat: videoData,
                    alert_number: alertNumber,
                })
            });
        } catch (e) { console.error('Failed to send combined alert SMS:', e); }
    }

    /* ============================================================
       AUDIO CALIBRATION
    ============================================================ */
    async _calibrateAudio() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const recorder = new MediaRecorder(stream);
            const chunks = [];
            recorder.ondataavailable = e => chunks.push(e.data);
            recorder.onstop = async () => {
                stream.getTracks().forEach(t => t.stop());
                const blob = new Blob(chunks, { type: 'audio/webm' });
                const b64 = await this._blobToBase64(blob);
                const r = await fetch(this.routes.calibrateAudio, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ audio_data: b64 })
                });
                const data = await r.json();
                const el = document.getElementById('noiseCalibrationStatus');
                if (el) el.innerHTML = data.success
                    ? '<span class="badge bg-gradient-success">Noise Profile: Calibrated ✓</span>'
                    : '<span class="badge bg-gradient-danger">Calibration Failed</span>';
            };
            recorder.start();
            setTimeout(() => recorder.stop(), 3000);
        } catch (e) { console.error('Calibration error:', e); }
    }

    /* ============================================================
       UI HELPERS
    ============================================================ */
    _addAlert(message, type, source) {
        this.noAlertsMsg?.classList.add('d-none');
        const div = document.createElement('div');
        div.className = `alert-item ${type}`;
        div.innerHTML = `<strong>[${source}]</strong> ${message} <small class="float-end text-muted">${new Date().toLocaleTimeString()}</small>`;
        this.alertsContainer?.insertBefore(div, this.alertsContainer.firstChild);
    }

    _addHistory(source, type, severity) {
        const tbody = this.historyBody;
        if (!tbody) return;
        const emptyRow = tbody.querySelector('td[colspan]');
        emptyRow?.parentElement?.remove();

        const row = document.createElement('tr');
        const sevCls = {
            Critical: 'severity-critical',
            High:     'severity-high',
            Medium:   'severity-medium',
            Low:      'severity-low'
        }[severity] || 'severity-medium';

        row.innerHTML = `
            <td class="text-xs">${new Date().toLocaleTimeString()}</td>
            <td class="text-xs">${source}</td>
            <td class="text-xs">${type}</td>
            <td class="text-xs ${sevCls}">${severity}</td>
        `;
        tbody.insertBefore(row, tbody.firstChild);
        this.history.push({ source, type, severity, time: Date.now() });
    }

    _clearAlerts() {
        if (this.alertsContainer) {
            this.alertsContainer.innerHTML =
                '<div class="text-center text-secondary py-4" id="noAlertsMsg">' +
                '<i class="material-symbols-rounded" style="font-size:48px;">security</i>' +
                '<p class="mt-2">No alerts yet. Start detection to monitor.</p></div>';
        }
        this.criticalBanner?.classList.add('d-none');
    }

    _setStatus(el, text, cls) {
        if (!el) return;
        el.textContent = text;
        el.className = `mb-0 ${cls}`;
    }

    _blobToBase64(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result.split(',')[1]);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

    /* ---- Audio DSP helpers (matches standalone audio-threat.js) ---- */
    _resampleAudio(audioData, fromRate, toRate) {
        if (fromRate === toRate) return audioData;
        const ratio = fromRate / toRate;
        const newLength = Math.round(audioData.length / ratio);
        const result = new Float32Array(newLength);
        for (let i = 0; i < newLength; i++) {
            const srcIdx = i * ratio;
            const floor = Math.floor(srcIdx);
            const ceil  = Math.min(floor + 1, audioData.length - 1);
            const t     = srcIdx - floor;
            result[i]   = audioData[floor] * (1 - t) + audioData[ceil] * t;
        }
        return result;
    }

    _arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    /* ---- Video detection results panel ---- */
    _addDetectionResult(type, detections) {
        const container = document.getElementById('resultsContainer');
        const noMsg     = document.getElementById('noResultsMsg');
        if (!container) return;

        this._clearDetectionResults();
        if (noMsg) noMsg.style.display = 'none';

        const time = new Date().toLocaleTimeString();

        if (type === 'object' && Array.isArray(detections)) {
            detections.forEach(obj => {
                let alertType, badge, icon;
                if (obj.class_name?.toLowerCase() === 'person') {
                    alertType = 'warning'; badge = '<span class="badge bg-warning text-dark">👤 PERSON</span>'; icon = '👤';
                } else if (obj.is_left_behind) {
                    alertType = 'danger';  badge = '<span class="badge bg-danger">⚠️ LEFT BEHIND</span>';  icon = '⚠️';
                } else if (obj.is_unknown) {
                    alertType = 'secondary'; badge = '<span class="badge bg-secondary">❓ UNKNOWN</span>'; icon = '❓';
                } else {
                    alertType = 'success'; badge = '<span class="badge bg-success">✓ TRACKED</span>'; icon = '✓';
                }
                const stationaryHtml = obj.time_stationary > 0
                    ? `<small class="text-muted ms-2">Stationary: ${obj.time_stationary.toFixed(1)}s</small>` : '';
                const displayName = obj.is_unknown && obj.original_class_name
                    ? `unknown (${obj.original_class_name})` : (obj.class_name || 'object');
                const el = document.createElement('div');
                el.className = `alert alert-${alertType} mb-2 py-2`;
                el.innerHTML = `<div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong>${icon} ${displayName}</strong> ${badge}
                            <span class="badge bg-secondary">ID:${obj.track_id ?? '?'}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-muted">Conf: ${((obj.confidence||0)*100).toFixed(1)}%</span>${stationaryHtml}
                        </div>
                    </div>
                    <small class="text-muted">${time}</small>
                </div>`;
                container.appendChild(el);
            });
        }
    }

    _clearDetectionResults() {
        const container = document.getElementById('resultsContainer');
        if (!container) return;
        container.querySelectorAll('.alert').forEach(a => a.remove());
        const noMsg = document.getElementById('noResultsMsg');
        if (noMsg) noMsg.style.display = 'block';
    }
}

/* ============================================================
   BOOTSTRAP
============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    window.audioVideoDetector = new AudioVideoThreatDetector();
});

