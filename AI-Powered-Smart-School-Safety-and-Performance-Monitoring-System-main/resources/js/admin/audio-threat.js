/**
 * Audio Threat Detection Dashboard
 * Real-time audio analysis for school safety monitoring
 */

class AudioThreatDetector {
    constructor() {
        this.config = window.audioThreatConfig || {};
        this.mediaRecorder = null;
        this.audioContext = null;
        this.analyser = null;
        this.isRecording = false;
        this.sessionId = null;
        this.stats = {
            threatCount: 0,
            chunksProcessed: 0,
            totalLatency: 0,
            startTime: null
        };
        
        this.init();
    }
    
    init() {
        this.bindElements();
        this.bindEvents();
        this.checkApiStatus();
    }
    
    bindElements() {
        this.startBtn = document.getElementById('startDetectionBtn');
        this.stopBtn = document.getElementById('stopDetectionBtn');
        this.calibrateBtn = document.getElementById('calibrateBtn');
        this.visualizer = document.getElementById('audioVisualizer');
        this.visualizerCtx = this.visualizer?.getContext('2d');
        this.alertsContainer = document.getElementById('alertsContainer');
        this.noAlertsMsg = document.getElementById('noAlertsMsg');
        
        // Status elements
        this.detectionStatus = document.getElementById('detectionStatus');
        this.micStatus = document.getElementById('micStatus');
        this.threatCount = document.getElementById('threatCount');
        this.chunksProcessed = document.getElementById('chunksProcessed');
        this.avgLatency = document.getElementById('avgLatency');
    }
    
    bindEvents() {
        this.startBtn?.addEventListener('click', () => this.startDetection());
        this.stopBtn?.addEventListener('click', () => this.stopDetection());
        this.calibrateBtn?.addEventListener('click', () => this.showCalibrationModal());
        document.getElementById('clearAlertsBtn')?.addEventListener('click', () => this.clearAlerts());
        document.getElementById('grantMicPermissionBtn')?.addEventListener('click', () => this.requestMicPermission());
        document.getElementById('startCalibrationBtn')?.addEventListener('click', () => this.startCalibration());
    }
    
    async checkApiStatus() {
        try {
            const response = await fetch(this.config.routes?.status || '/admin/management/audio-threat/status');
            const data = await response.json();
            
            if (data.status === 'ok') {
                console.log('API connected:', data);
                this.calibrateBtn.disabled = false;
            }
        } catch (error) {
            console.error('API not available:', error);
            this.showNotification('API service not available. Please start the Python server.', 'warning');
        }
    }
    
    async startDetection() {
        try {
            // Request microphone permission
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.mediaStream = stream;  // Store reference for cleanup

            // Setup audio context
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 2048;

            const source = this.audioContext.createMediaStreamSource(stream);
            source.connect(this.analyser);

            // Setup audio capture using ScriptProcessor for raw PCM
            this.scriptProcessor = this.audioContext.createScriptProcessor(4096, 1, 1);
            this.audioBuffer = [];
            this.sampleRate = this.audioContext.sampleRate;

            source.connect(this.scriptProcessor);
            this.scriptProcessor.connect(this.audioContext.destination);

            // Collect raw PCM samples
            this.scriptProcessor.onaudioprocess = (e) => {
                if (!this.isRecording) return;
                const inputData = e.inputBuffer.getChannelData(0);
                // Store a copy of the samples
                this.audioBuffer.push(new Float32Array(inputData));
            };

            // Send audio every 4 seconds
            this.audioInterval = setInterval(() => {
                if (this.audioBuffer.length > 0) {
                    this.processAudioBuffer();
                }
            }, 4000);
            this.isRecording = true;
            this.stats.startTime = Date.now();
            this.sessionId = `session_${Date.now()}`;
            
            // Update UI
            this.startBtn.classList.add('d-none');
            this.stopBtn.classList.remove('d-none');
            this.detectionStatus.textContent = 'Active';
            this.detectionStatus.className = 'status-active';
            this.micStatus.innerHTML = '<span class="recording-indicator"><span class="dot"></span>Recording</span>';
            
            // Start visualization
            this.startVisualization();
            
            this.showNotification('Audio threat detection started', 'success');
            
        } catch (error) {
            console.error('Failed to start detection:', error);
            if (error.name === 'NotAllowedError') {
                this.showMicPermissionModal();
            } else {
                this.showNotification('Failed to access microphone: ' + error.message, 'error');
            }
        }
    }
    
    stopDetection() {
        // Stop audio interval
        if (this.audioInterval) {
            clearInterval(this.audioInterval);
            this.audioInterval = null;
        }

        // Stop script processor
        if (this.scriptProcessor) {
            this.scriptProcessor.disconnect();
            this.scriptProcessor = null;
        }

        // Stop media stream
        if (this.mediaStream) {
            this.mediaStream.getTracks().forEach(track => track.stop());
        }

        if (this.audioContext) {
            this.audioContext.close();
        }

        this.isRecording = false;
        this.audioBuffer = [];

        // Update UI
        this.startBtn.classList.remove('d-none');
        this.stopBtn.classList.add('d-none');
        this.detectionStatus.textContent = 'Inactive';
        this.detectionStatus.className = 'status-inactive';
        this.micStatus.innerHTML = '<span class="text-secondary text-sm">Microphone stopped</span>';

        this.showNotification('Audio threat detection stopped', 'info');
    }

    async processAudioBuffer() {
        if (this.audioBuffer.length === 0) return;

        const startTime = Date.now();

        // Combine all audio chunks into one buffer
        const totalLength = this.audioBuffer.reduce((acc, chunk) => acc + chunk.length, 0);
        const combinedBuffer = new Float32Array(totalLength);
        let offset = 0;
        for (const chunk of this.audioBuffer) {
            combinedBuffer.set(chunk, offset);
            offset += chunk.length;
        }

        // Clear buffer for next iteration
        this.audioBuffer = [];

        // Resample to 16kHz if needed
        let audioData = combinedBuffer;
        if (this.sampleRate !== 16000) {
            audioData = this.resampleAudio(combinedBuffer, this.sampleRate, 16000);
        }

        // Convert Float32 to Int16 PCM
        const pcmData = new Int16Array(audioData.length);
        for (let i = 0; i < audioData.length; i++) {
            const s = Math.max(-1, Math.min(1, audioData[i]));
            pcmData[i] = s < 0 ? s * 0x8000 : s * 0x7FFF;
        }

        // Convert to base64
        const base64 = this.arrayBufferToBase64(pcmData.buffer);

        try {
            // Send to API
            const response = await fetch(this.config.routes?.analyze || '/admin/management/audio-threat/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    audio_data: base64,
                    format: 'pcm16',
                    sample_rate: 16000,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            const latency = Date.now() - startTime;

            // Update stats
            this.stats.chunksProcessed++;
            this.stats.totalLatency += latency;
            this.updateStats(latency);
            
            // Handle result
            if (data.success && data.result) {
                this.handleDetectionResult(data.result);
            }
            
        } catch (error) {
            console.error('Failed to process audio:', error);
        }
    }
    
    handleDetectionResult(result) {
        // Update detection panels
        this.updateNonSpeechResults(result.non_speech_result);
        this.updateSpeechResults(result.speech_result);
        
        // Handle threat - show in Real-time Alerts section only (no popup)
        if (result.is_threat) {
            this.stats.threatCount++;
            this.threatCount.textContent = this.stats.threatCount;
            this.addAlert(result);
            // Popup disabled - alerts shown in Real-time Alerts section
            // this.showThreatModal(result);
            // this.playAlertSound();
        }
    }
    
    updateNonSpeechResults(result) {
        if (!result) return;
        
        const container = document.getElementById('nonSpeechResults');
        const probs = result.all_probabilities || {};
        
        let html = `<div class="result-item">
            <div class="d-flex justify-content-between">
                <span>Detected: <strong class="text-capitalize">${result.detected_class}</strong></span>
                <span class="badge ${result.is_threat ? 'bg-danger' : 'bg-success'}">${(result.confidence * 100).toFixed(1)}%</span>
            </div>
        </div>`;
        
        // Add probability bars
        for (const [cls, prob] of Object.entries(probs)) {
            html += `<div class="result-item">
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-capitalize">${cls.replace('_', ' ')}</span>
                    <span>${(prob * 100).toFixed(1)}%</span>
                </div>
                <div class="probability-bar">
                    <div class="fill ${cls}" style="width: ${prob * 100}%"></div>
                </div>
            </div>`;
        }
        
        container.innerHTML = html;
    }
    
    updateSpeechResults(result) {
        if (!result) return;

        const container = document.getElementById('speechResults');

        let html = '';
        if (result.text) {
            // Show transcription and threat status
            let keywordsHtml = '';
            if (result.detected_keywords && result.detected_keywords.length > 0) {
                keywordsHtml = `<div class="mt-2">
                    <small class="text-danger"><strong>Keywords found:</strong>
                    ${result.detected_keywords.map(k => k.keyword).join(', ')}</small>
                </div>`;
            }

            html = `<div class="result-item">
                <p class="text-sm mb-2"><strong>Transcription:</strong> <small class="text-muted">(${result.engine || 'unknown'})</small></p>
                <p class="font-italic">"${result.text}"</p>
                ${result.is_threat ? `<span class="badge bg-danger">Threat Detected - ${result.threat_level}</span>` : '<span class="badge bg-success">Safe</span>'}
                ${keywordsHtml}
            </div>`;
        } else {
            // Show why speech wasn't detected
            let reason = 'Listening for speech...';
            if (result.transcription_error) {
                reason = result.transcription_error;
            }
            html = `<p class="text-secondary text-sm">${reason}</p>`;
        }

        container.innerHTML = html;
    }
    
    addAlert(result) {
        if (this.noAlertsMsg) {
            this.noAlertsMsg.style.display = 'none';
        }

        const time = new Date().toLocaleTimeString();

        // Build detail info based on threat type
        let detailInfo = '';
        if (result.threat_type === 'speech' && result.speech_result) {
            detailInfo = `<div class="alert-extra mt-1 text-sm">
                <em>"${result.speech_result.text}"</em>
            </div>`;
        } else if (result.threat_type === 'non_speech' && result.non_speech_result) {
            detailInfo = `<div class="alert-extra mt-1 text-sm">
                Detected: ${result.non_speech_result.detected_class}
            </div>`;
        }

        const alertHtml = `
            <div class="alert-card ${result.threat_level}">
                <div class="alert-header">
                    <span class="alert-title">
                        <i class="material-symbols-rounded text-sm me-1">warning</i>
                        ${result.threat_type === 'speech' ? 'Speech Threat' : result.threat_type || 'Threat'} Detected
                    </span>
                    <span class="alert-time">${time}</span>
                </div>
                <div class="alert-details">
                    <span class="level-badge ${result.threat_level}">${result.threat_level}</span>
                    <span class="ms-2">Confidence: ${(result.confidence * 100).toFixed(1)}%</span>
                </div>
                ${detailInfo}
            </div>
        `;

        this.alertsContainer.insertAdjacentHTML('afterbegin', alertHtml);
        document.getElementById('lastThreatTime').innerHTML = `<span class="text-danger text-sm">Last: ${time}</span>`;
    }
    
    showThreatModal(result) {
        const modal = new bootstrap.Modal(document.getElementById('threatAlertModal'));
        
        document.getElementById('modalThreatType').textContent = result.threat_type || 'Unknown';
        document.getElementById('modalThreatLevel').innerHTML = `<span class="level-badge ${result.threat_level}">${result.threat_level}</span>`;
        document.getElementById('modalConfidence').textContent = `${(result.confidence * 100).toFixed(1)}%`;
        document.getElementById('modalTime').textContent = new Date().toLocaleTimeString();
        
        if (result.non_speech_result) {
            document.getElementById('modalDetails').textContent = `Detected: ${result.non_speech_result.detected_class}`;
        }
        
        if (result.speech_result?.text) {
            document.getElementById('speechTextRow').style.display = 'block';
            document.getElementById('modalSpeechText').textContent = result.speech_result.text;
        } else {
            document.getElementById('speechTextRow').style.display = 'none';
        }
        
        modal.show();
    }
    
    updateStats(latency) {
        this.chunksProcessed.textContent = this.stats.chunksProcessed;
        const avgLat = this.stats.totalLatency / this.stats.chunksProcessed;
        this.avgLatency.textContent = `${avgLat.toFixed(0)} ms`;

        // Update latency status
        const latencyStatus = document.getElementById('latencyStatus');
        if (avgLat < 3000) {
            latencyStatus.className = 'text-success text-sm font-weight-bolder';
            latencyStatus.textContent = 'Within target (<3s)';
        } else {
            latencyStatus.className = 'text-warning text-sm font-weight-bolder';
            latencyStatus.textContent = 'Above target (>3s)';
        }

        // Update processing rate
        const elapsed = (Date.now() - this.stats.startTime) / 60000; // minutes
        const rate = elapsed > 0 ? (this.stats.chunksProcessed / elapsed).toFixed(1) : 0;
        document.getElementById('processingRate').innerHTML = `<span class="text-secondary text-sm">${rate} chunks/min</span>`;
    }

    startVisualization() {
        if (!this.visualizerCtx || !this.analyser) return;

        const bufferLength = this.analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        const draw = () => {
            if (!this.isRecording) return;
            requestAnimationFrame(draw);

            this.analyser.getByteFrequencyData(dataArray);

            const width = this.visualizer.width;
            const height = this.visualizer.height;

            this.visualizerCtx.fillStyle = '#1a1a2e';
            this.visualizerCtx.fillRect(0, 0, width, height);

            const barWidth = (width / bufferLength) * 2.5;
            let x = 0;

            for (let i = 0; i < bufferLength; i++) {
                const barHeight = (dataArray[i] / 255) * height;

                // Gradient based on amplitude
                const hue = 120 - (dataArray[i] / 255) * 120;
                this.visualizerCtx.fillStyle = `hsl(${hue}, 80%, 50%)`;

                this.visualizerCtx.fillRect(x, height - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }

            // Update input level
            const avgLevel = dataArray.reduce((a, b) => a + b, 0) / bufferLength;
            const levelPercent = (avgLevel / 255) * 100;
            document.getElementById('inputLevelValue').textContent = `${levelPercent.toFixed(0)}%`;
            const levelBar = document.getElementById('inputLevelBar');
            levelBar.style.width = `${levelPercent}%`;

            // Color coding
            levelBar.classList.remove('low', 'medium', 'high');
            if (levelPercent < 30) levelBar.classList.add('low');
            else if (levelPercent < 60) levelBar.classList.add('medium');
            else levelBar.classList.add('high');
        };

        draw();
    }

    async startCalibration() {
        const spinner = document.getElementById('calibrationSpinner');
        const icon = document.getElementById('calibrationIcon');
        const title = document.getElementById('calibrationTitle');
        const message = document.getElementById('calibrationMessage');
        const progress = document.getElementById('calibrationProgress');
        const btn = document.getElementById('startCalibrationBtn');

        try {
            spinner.style.display = 'block';
            icon.style.display = 'none';
            title.textContent = 'Calibrating...';
            message.textContent = 'Recording ambient noise. Please keep quiet.';
            progress.style.display = 'block';
            btn.disabled = true;

            // Record 5 seconds of ambient audio
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mediaRecorder = new MediaRecorder(stream);
            const chunks = [];

            mediaRecorder.ondataavailable = (e) => chunks.push(e.data);
            mediaRecorder.start();

            // Progress animation
            let prog = 0;
            const progressBar = progress.querySelector('.progress-bar');
            const interval = setInterval(() => {
                prog += 20;
                progressBar.style.width = `${prog}%`;
            }, 1000);

            await new Promise(resolve => setTimeout(resolve, 5000));

            clearInterval(interval);
            mediaRecorder.stop();
            stream.getTracks().forEach(track => track.stop());

            // Convert and send
            await new Promise(resolve => {
                mediaRecorder.onstop = async () => {
                    const blob = new Blob(chunks, { type: 'audio/webm' });
                    const base64 = await this.blobToBase64(blob);

                    const response = await fetch(this.config.routes?.calibrate || '/admin/management/audio-threat/calibrate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.config.csrfToken
                        },
                        body: JSON.stringify({ audio_data: base64 })
                    });

                    const data = await response.json();

                    if (data.success) {
                        title.textContent = 'Calibration Complete';
                        message.textContent = 'Noise profile updated successfully.';
                        icon.style.display = 'block';
                        icon.textContent = 'check_circle';
                        icon.style.color = '#10B981';

                        document.getElementById('noiseCalibrationStatus').innerHTML =
                            '<span class="badge bg-gradient-success">Noise Profile: Calibrated</span>';
                    }

                    resolve();
                };
            });

        } catch (error) {
            console.error('Calibration failed:', error);
            title.textContent = 'Calibration Failed';
            message.textContent = error.message;
        } finally {
            spinner.style.display = 'none';
            btn.disabled = false;
        }
    }

    clearAlerts() {
        this.alertsContainer.innerHTML = `
            <div class="text-center text-secondary py-4" id="noAlertsMsg">
                <i class="material-symbols-rounded" style="font-size: 48px;">security</i>
                <p class="mt-2">Alerts cleared.</p>
            </div>
        `;
        this.noAlertsMsg = document.getElementById('noAlertsMsg');
    }

    showMicPermissionModal() {
        new bootstrap.Modal(document.getElementById('micPermissionModal')).show();
    }

    showCalibrationModal() {
        new bootstrap.Modal(document.getElementById('calibrationModal')).show();
    }

    async requestMicPermission() {
        try {
            await navigator.mediaDevices.getUserMedia({ audio: true });
            bootstrap.Modal.getInstance(document.getElementById('micPermissionModal'))?.hide();
            this.startDetection();
        } catch (error) {
            this.showNotification('Microphone permission denied', 'error');
        }
    }

    blobToBase64(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    resampleAudio(audioData, fromRate, toRate) {
        if (fromRate === toRate) return audioData;

        const ratio = fromRate / toRate;
        const newLength = Math.round(audioData.length / ratio);
        const result = new Float32Array(newLength);

        for (let i = 0; i < newLength; i++) {
            const srcIndex = i * ratio;
            const srcIndexFloor = Math.floor(srcIndex);
            const srcIndexCeil = Math.min(srcIndexFloor + 1, audioData.length - 1);
            const t = srcIndex - srcIndexFloor;
            result[i] = audioData[srcIndexFloor] * (1 - t) + audioData[srcIndexCeil] * t;
        }

        return result;
    }

    playAlertSound() {
        try {
            const audio = new Audio('/assets/audio/alert.mp3');
            audio.volume = 0.5;
            audio.play().catch(() => {});
        } catch (e) {}
    }

    showNotification(message, type = 'info') {
        const icons = { success: 'check_circle', error: 'error', warning: 'warning', info: 'info' };
        const colors = { success: '#10B981', error: '#EF4444', warning: '#F59E0B', info: '#3B82F6' };

        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header" style="background: ${colors[type]}; color: white;">
                    <i class="material-symbols-rounded me-2">${icons[type]}</i>
                    <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.audioThreatDetector = new AudioThreatDetector();
});

