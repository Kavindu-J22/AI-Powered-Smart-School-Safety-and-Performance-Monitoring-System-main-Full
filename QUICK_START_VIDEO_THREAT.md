# Quick Start Guide - Video Threat Detection

## 🚀 Get Started in 5 Minutes

### Step 1: Start the Flask API (Terminal 1)

```bash
cd Video_Based_Left_Behind_Object_and_Threat_Detection
python app.py
```

You should see:
```
============================================================
Video-Based Threat Detection API
============================================================
Starting server on 0.0.0.0:5003

Available Endpoints:
   - GET  /api/video/health          Health Check
   - GET  /api/video/status          System Status
   - POST /api/video/detect-objects  Detect Objects
   - POST /api/video/detect-threats  Detect Threats
   - POST /api/video/process-frame   Process Complete Frame
============================================================
```

### Step 2: Verify API is Running

Open your browser and visit:
```
http://127.0.0.1:5003/api/video/health
```

You should see:
```json
{
  "status": "healthy",
  "message": "Video threat detection API is running"
}
```

### Step 3: Start Laravel Application (Terminal 2)

```bash
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve
```

### Step 4: Access the Dashboard

Open your browser and navigate to:
```
http://127.0.0.1:8000/admin/management/video-threat
```

### Step 5: Start Detection

1. **For PC Camera:**
   - Ensure "PC Camera" is selected
   - Click "Start Detection"
   - Allow camera access when prompted
   - Watch real-time detections!

2. **For ESP32-CAM:**
   - Select "ESP32-CAM"
   - Enter your ESP32-CAM IP address
   - Click "Connect"
   - Click "Start Detection"

## 📊 What You'll See

### Dashboard Features

1. **Status Cards** (Top Row)
   - Detection Status (Active/Inactive)
   - Left-Behind Objects Count
   - Threats Detected Count
   - Frames Processed & FPS

2. **Video Feed** (Left Panel)
   - Live camera stream
   - Detection overlays (bounding boxes)
   - FPS and latency indicators

3. **Detection Results** (Right Panel)
   - Real-time detection alerts
   - Object and threat information
   - Timestamps

4. **Detection History** (Bottom)
   - Complete log of all detections
   - Filterable and sortable table

## 🎯 Testing the System

### Test 1: Object Detection

1. Start detection with PC camera
2. Place an object (bag, book, etc.) in view
3. Keep it stationary for a few seconds
4. Watch it get detected as "left-behind"

### Test 2: Multiple Objects

1. Place multiple objects in view
2. Move some, keep others stationary
3. System tracks each object independently
4. Only stationary objects trigger alerts

### Test 3: ESP32-CAM (if available)

1. Power on your ESP32-CAM
2. Note its IP address (check your router or serial monitor)
3. Enter IP in the dashboard
4. Connect and start detection

## ⚙️ Configuration

### Adjust Detection Sensitivity

Edit `Video_Based_Left_Behind_Object_and_Threat_Detection/config.yaml`:

```yaml
object_detection:
  model_path: 'models/yolov8n.pt'
  confidence_threshold: 0.5  # Lower = more detections
  min_object_size: 1000      # Minimum pixel area

tracking:
  max_age: 30                # Frames before removing track
  min_hits: 3                # Frames before confirming track
  iou_threshold: 0.3         # Overlap threshold

left_behind:
  stationary_threshold: 5.0  # Seconds before "left-behind"
```

### Change API Port

Edit `Video_Based_Left_Behind_Object_and_Threat_Detection/app.py`:

```python
class FlaskConfig:
    HOST = '0.0.0.0'
    PORT = 5003  # Change this
    DEBUG = False
```

Then update Laravel `.env`:
```env
VIDEO_THREAT_API_URL=http://127.0.0.1:YOUR_NEW_PORT
```

## 🔧 Common Issues & Solutions

### Issue: "API service unavailable"

**Solution:**
```bash
# Check if Flask API is running
curl http://127.0.0.1:5003/api/video/health

# If not, start it:
cd Video_Based_Left_Behind_Object_and_Threat_Detection
python app.py
```

### Issue: "Camera access denied"

**Solution:**
- Use HTTPS (required by modern browsers)
- Check browser permissions
- Try: `chrome://settings/content/camera`

### Issue: "Models not found"

**Solution:**
```bash
cd Video_Based_Left_Behind_Object_and_Threat_Detection

# Download YOLOv8 model
python -c "from ultralytics import YOLO; YOLO('yolov8n.pt')"

# Verify models directory
ls models/
```

### Issue: Low FPS / Slow Detection

**Solution:**
1. Close other applications
2. Use smaller YOLOv8 model (yolov8n instead of yolov8x)
3. Reduce video resolution
4. Increase processing interval in JavaScript

## 📱 ESP32-CAM Setup

### Hardware Requirements
- ESP32-CAM module
- USB-to-Serial adapter (for programming)
- 5V power supply

### Firmware Setup

1. **Install Arduino IDE**
2. **Add ESP32 Board Support**
3. **Upload Camera Web Server Example**
   - File → Examples → ESP32 → Camera → CameraWebServer
   - Select your camera model (AI-THINKER)
   - Configure WiFi credentials
   - Upload

4. **Get IP Address**
   - Open Serial Monitor (115200 baud)
   - Note the IP address printed
   - Test: `http://ESP32_IP/stream`

### Connect to Dashboard

1. Enter ESP32 IP in dashboard
2. Click "Connect"
3. Start detection

## 🎨 Customization

### Change Detection Colors

Edit `resources/js/admin/video-threat.js`:

```javascript
// Line ~280
const color = obj.is_left_behind ? '#EF4444' : '#10B981';
// Change to your preferred colors
```

### Modify Alert Behavior

Edit `resources/js/admin/video-threat.js`:

```javascript
showThreatAlert(threat) {
    // Add custom alert logic here
    // e.g., play sound, send notification, etc.
}
```

### Add Custom Threat Types

Edit threat detection model or add custom logic in:
`Video_Based_Left_Behind_Object_and_Threat_Detection/threat_detector.py`

## 📈 Performance Tips

1. **Use GPU if available**
   ```bash
   pip install torch torchvision --index-url https://download.pytorch.org/whl/cu118
   ```

2. **Optimize frame rate**
   - PC Camera: ~10 FPS (100ms interval)
   - ESP32-CAM: ~5 FPS (200ms interval)

3. **Reduce resolution**
   ```javascript
   // In video-threat.js
   video: { width: 640, height: 480 }  // Lower for better performance
   ```

## 🎓 Next Steps

1. ✅ Test basic object detection
2. ✅ Test threat detection
3. ✅ Configure ESP32-CAM (optional)
4. ✅ Customize detection parameters
5. ✅ Integrate with school security workflow
6. ✅ Set up alerts and notifications
7. ✅ Review detection logs and analytics

## 📚 Additional Resources

- Full Documentation: `VIDEO_THREAT_INTEGRATION_README.md`
- YOLOv8 Docs: https://docs.ultralytics.com/
- ESP32-CAM Guide: https://randomnerdtutorials.com/esp32-cam-video-streaming-web-server-camera-home-assistant/

## 🆘 Need Help?

1. Check logs:
   - Flask: Terminal output
   - Laravel: `storage/logs/laravel.log`
   - Browser: Developer Console (F12)

2. Verify configuration:
   - `.env` file
   - `config.yaml`
   - API endpoints

3. Test components individually:
   - API health check
   - Camera access
   - Model loading

---

**Happy Detecting! 🎥🔍**

