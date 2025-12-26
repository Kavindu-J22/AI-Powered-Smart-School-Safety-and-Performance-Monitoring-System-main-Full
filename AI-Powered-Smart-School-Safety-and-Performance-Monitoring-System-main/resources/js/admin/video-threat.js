/**
 * Video Threat Detection Module
 * Handles PC camera and ESP32-CAM integration with real-time detection
 */

class VideoThreatDetection {
    constructor(config) {
        this.config = config;
        this.isRunning = false;
        this.videoSource = 'pc'; // 'pc' or 'esp32'
        this.stream = null;
        this.esp32Interval = null;
        
        // Elements
        this.videoElement = document.getElementById('videoElement');
        this.detectionCanvas = document.getElementById('detectionCanvas');
        this.esp32Stream = document.getElementById('esp32Stream');
        this.esp32Canvas = document.getElementById('esp32DetectionCanvas');
        
        // Stats
        this.stats = {
            framesProcessed: 0,
            objectsDetected: 0,
            threatsDetected: 0,
            totalLatency: 0,
            startTime: null
        };
        
        // Detection history
        this.detectionHistory = [];
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkApiStatus();
    }

    setupEventListeners() {
        // Start/Stop buttons
        document.getElementById('startDetectionBtn')?.addEventListener('click', () => this.startDetection());
        document.getElementById('stopDetectionBtn')?.addEventListener('click', () => this.stopDetection());
        
        // Video source toggle
        document.querySelectorAll('input[name="videoSource"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.switchVideoSource(e.target.value));
        });
        
        // ESP32 connection
        document.getElementById('connectEsp32Btn')?.addEventListener('click', () => this.connectEsp32());
        
        // Clear buttons
        document.getElementById('clearResultsBtn')?.addEventListener('click', () => this.clearResults());
        document.getElementById('clearAlertsBtn')?.addEventListener('click', () => this.clearHistory());
    }

    async checkApiStatus() {
        try {
            const response = await fetch(this.config.routes.status);
            const data = await response.json();
            
            if (data.status === 'active') {
                this.updateStatusIndicator('ready', 'Models loaded and ready');
            } else {
                this.updateStatusIndicator('error', 'API service unavailable');
            }
        } catch (error) {
            console.error('API status check failed:', error);
            this.updateStatusIndicator('error', 'Cannot connect to detection service');
        }
    }

    async startDetection() {
        if (this.isRunning) return;
        
        try {
            if (this.videoSource === 'pc') {
                await this.startPcCamera();
            } else {
                await this.startEsp32Stream();
            }
            
            this.isRunning = true;
            this.stats.startTime = Date.now();
            
            // Update UI
            document.getElementById('startDetectionBtn').classList.add('d-none');
            document.getElementById('stopDetectionBtn').classList.remove('d-none');
            document.getElementById('detectionStatus').textContent = 'Active';
            document.getElementById('cameraStatus').innerHTML = '<span class="text-success text-sm">Camera connected</span>';
            
            this.showNotification('Detection started successfully', 'success');
        } catch (error) {
            console.error('Failed to start detection:', error);
            this.showNotification('Failed to start detection: ' + error.message, 'error');
        }
    }

    async startPcCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480 }
            });
            
            this.videoElement.srcObject = this.stream;
            document.getElementById('noVideoMsg').style.display = 'none';
            
            // Start processing frames
            this.processVideoFrames();
        } catch (error) {
            throw new Error('Camera access denied or not available');
        }
    }

    async startEsp32Stream() {
        const ip = document.getElementById('esp32IpInput').value.trim();
        if (!ip) {
            throw new Error('Please enter ESP32-CAM IP address');
        }
        
        const streamUrl = `http://${ip}/stream`;
        this.esp32Stream.src = streamUrl;
        this.esp32Stream.style.display = 'block';
        document.getElementById('noEsp32Msg').style.display = 'none';
        
        // Start processing ESP32 frames
        this.processEsp32Frames();
    }

    processVideoFrames() {
        if (!this.isRunning) return;
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const processFrame = async () => {
            if (!this.isRunning) return;
            
            // Capture frame
            canvas.width = this.videoElement.videoWidth;
            canvas.height = this.videoElement.videoHeight;
            ctx.drawImage(this.videoElement, 0, 0);
            
            // Convert to base64
            const frameData = canvas.toDataURL('image/jpeg', 0.8).split(',')[1];
            
            // Send for processing
            await this.processFrame(frameData);
            
            // Continue processing
            setTimeout(processFrame, 100); // Process at ~10 FPS
        };
        
        processFrame();
    }

    processEsp32Frames() {
        this.esp32Interval = setInterval(async () => {
            if (!this.isRunning) return;
            
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                canvas.width = this.esp32Stream.width;
                canvas.height = this.esp32Stream.height;
                ctx.drawImage(this.esp32Stream, 0, 0);

                const frameData = canvas.toDataURL('image/jpeg', 0.8).split(',')[1];
                await this.processFrame(frameData);
            } catch (error) {
                console.error('ESP32 frame processing error:', error);
            }
        }, 200); // Process at ~5 FPS for ESP32
    }

    async processFrame(frameData) {
        const startTime = Date.now();

        try {
            const response = await fetch(this.config.routes.processFrame, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({ frame: frameData })
            });

            const data = await response.json();
            const latency = Date.now() - startTime;

            if (data.success) {
                this.stats.framesProcessed++;
                this.updateStats(latency);

                // Handle detections
                this.handleDetections(data);

                // Draw on canvas
                this.drawDetections(data);
            }
        } catch (error) {
            console.error('Frame processing error:', error);
        }
    }

    handleDetections(data) {
        const { objects, threats } = data;

        // Handle object detections
        if (objects && objects.detections) {
            const leftBehind = objects.detections.filter(obj => obj.is_left_behind);

            if (leftBehind.length > 0) {
                this.stats.objectsDetected += leftBehind.length;
                this.addDetectionResult('object', leftBehind);
                this.updateObjectCount(objects.left_behind_count);
            }
        }

        // Handle threat detections
        if (threats && threats.is_threat) {
            this.stats.threatsDetected++;
            this.addDetectionResult('threat', threats);
            this.updateThreatCount(this.stats.threatsDetected);
            this.showThreatAlert(threats);
        }
    }

    drawDetections(data) {
        const canvas = this.videoSource === 'pc' ? this.detectionCanvas : this.esp32Canvas;
        const video = this.videoSource === 'pc' ? this.videoElement : this.esp32Stream;

        canvas.width = video.videoWidth || video.width;
        canvas.height = video.videoHeight || video.height;

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw object detections
        if (data.objects && data.objects.detections) {
            data.objects.detections.forEach(obj => {
                const [x1, y1, x2, y2] = obj.bbox;
                const color = obj.is_left_behind ? '#EF4444' : '#10B981';

                ctx.strokeStyle = color;
                ctx.lineWidth = 3;
                ctx.strokeRect(x1, y1, x2 - x1, y2 - y1);

                // Label
                ctx.fillStyle = color;
                ctx.fillRect(x1, y1 - 25, 200, 25);
                ctx.fillStyle = '#FFFFFF';
                ctx.font = '14px Arial';
                ctx.fillText(`${obj.class_name} ${obj.is_left_behind ? '[LEFT BEHIND]' : ''}`, x1 + 5, y1 - 7);
            });
        }

        // Draw threat indicator
        if (data.threats && data.threats.is_threat) {
            ctx.fillStyle = 'rgba(239, 68, 68, 0.3)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#EF4444';
            ctx.font = 'bold 24px Arial';
            ctx.fillText(`THREAT: ${data.threats.threat_type}`, 10, 40);
        }
    }

    addDetectionResult(type, data) {
        const container = document.getElementById('resultsContainer');
        const noResultsMsg = document.getElementById('noResultsMsg');

        if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }

        const resultDiv = document.createElement('div');
        resultDiv.className = `alert alert-${type === 'threat' ? 'danger' : 'warning'} mb-2`;

        const time = new Date().toLocaleTimeString();

        if (type === 'object') {
            resultDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Left-Behind Objects</strong>
                        <p class="mb-0 text-sm">${data.length} object(s) detected</p>
                    </div>
                    <small>${time}</small>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Threat Detected</strong>
                        <p class="mb-0 text-sm">${data.threat_type} (${(data.confidence * 100).toFixed(1)}%)</p>
                    </div>
                    <small>${time}</small>
                </div>
            `;
        }

        container.insertBefore(resultDiv, container.firstChild);

        // Add to history
        this.addToHistory(type, data);
    }

    addToHistory(type, data) {
        const tbody = document.getElementById('historyTableBody');
        const emptyRow = tbody.querySelector('td[colspan="5"]');

        if (emptyRow) {
            tbody.innerHTML = '';
        }

        const row = tbody.insertRow(0);
        const time = new Date().toLocaleTimeString();

        row.innerHTML = `
            <td class="text-sm">${time}</td>
            <td><span class="badge bg-${type === 'threat' ? 'danger' : 'warning'}">${type.toUpperCase()}</span></td>
            <td class="text-sm">${type === 'object' ? `${data.length} objects` : data.threat_type}</td>
            <td class="text-sm">${type === 'threat' ? (data.confidence * 100).toFixed(1) + '%' : 'N/A'}</td>
            <td><span class="badge bg-info">New</span></td>
        `;

        this.detectionHistory.unshift({ type, data, time });
    }

    showThreatAlert(threat) {
        // Show modal or notification
        this.showNotification(`THREAT DETECTED: ${threat.threat_type}`, 'error');
    }

    stopDetection() {
        this.isRunning = false;

        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        if (this.esp32Interval) {
            clearInterval(this.esp32Interval);
            this.esp32Interval = null;
        }

        // Update UI
        document.getElementById('startDetectionBtn').classList.remove('d-none');
        document.getElementById('stopDetectionBtn').classList.add('d-none');
        document.getElementById('detectionStatus').textContent = 'Inactive';
        document.getElementById('cameraStatus').innerHTML = '<span class="text-secondary text-sm">Camera disconnected</span>';

        this.showNotification('Detection stopped', 'info');
    }

    switchVideoSource(source) {
        if (this.isRunning) {
            this.stopDetection();
        }

        this.videoSource = source;

        if (source === 'pc') {
            document.getElementById('pcCameraSection').classList.remove('d-none');
            document.getElementById('esp32CameraSection').classList.add('d-none');
        } else {
            document.getElementById('pcCameraSection').classList.add('d-none');
            document.getElementById('esp32CameraSection').classList.remove('d-none');
        }
    }

    connectEsp32() {
        const ip = document.getElementById('esp32IpInput').value.trim();
        if (!ip) {
            this.showNotification('Please enter ESP32-CAM IP address', 'warning');
            return;
        }

        this.showNotification('Connecting to ESP32-CAM...', 'info');
        // Connection will be established when detection starts
    }

    updateStats(latency) {
        this.stats.totalLatency += latency;

        // Update UI
        document.getElementById('framesProcessed').textContent = this.stats.framesProcessed;

        const fps = this.stats.framesProcessed / ((Date.now() - this.stats.startTime) / 1000);
        document.getElementById('processingRate').innerHTML = `<span class="text-success text-sm">${fps.toFixed(1)} fps</span>`;

        document.getElementById('fpsCounter').textContent = `${fps.toFixed(1)} FPS`;
        document.getElementById('latencyCounter').textContent = `${latency}ms`;
    }

    updateObjectCount(count) {
        document.getElementById('objectCount').textContent = count;
        document.getElementById('lastObjectTime').innerHTML = `<span class="text-warning text-sm">Just now</span>`;
    }

    updateThreatCount(count) {
        document.getElementById('threatCount').textContent = count;
        document.getElementById('lastThreatTime').innerHTML = `<span class="text-danger text-sm">Just now</span>`;
    }

    updateStatusIndicator(status, message) {
        // Update status indicator
        console.log(`Status: ${status} - ${message}`);
    }

    clearResults() {
        document.getElementById('resultsContainer').innerHTML = `
            <div class="text-center text-secondary py-4" id="noResultsMsg">
                <i class="material-symbols-rounded" style="font-size: 48px;">search</i>
                <p class="mt-2">No detections yet. Start monitoring to see results.</p>
            </div>
        `;
    }

    clearHistory() {
        document.getElementById('historyTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-secondary py-4">
                    No detection history available
                </td>
            </tr>
        `;
        this.detectionHistory = [];
    }

    showNotification(message, type) {
        // Simple notification - can be enhanced with a toast library
        console.log(`[${type.toUpperCase()}] ${message}`);

        // You can integrate with your existing notification system here
        if (window.showToast) {
            window.showToast(message, type);
        }
    }
}

// Export for use in blade template
window.VideoThreatDetection = VideoThreatDetection;


