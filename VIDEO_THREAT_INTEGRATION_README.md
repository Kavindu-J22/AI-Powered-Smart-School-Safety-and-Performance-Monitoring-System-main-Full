# Video Threat Detection Integration Guide

## Overview

This document describes the integration of the **Video-Based Left-Behind Object and Threat Detection** system with the **AI-Powered Smart School Safety and Performance Monitoring System**.

## Architecture

The integration follows a microservices architecture:

```
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Application                       │
│  (AI-Powered Smart School Safety System)                    │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  VideoThreatController                              │    │
│  │  - Dashboard view                                   │    │
│  │  - API proxy endpoints                              │    │
│  └────────────────┬───────────────────────────────────┘    │
│                   │ HTTP Requests                           │
└───────────────────┼─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│              Flask API (Port 5003)                           │
│  (Video Detection Service)                                   │
│                                                              │
│  Endpoints:                                                  │
│  - GET  /api/video/health                                   │
│  - GET  /api/video/status                                   │
│  - POST /api/video/detect-objects                           │
│  - POST /api/video/detect-threats                           │
│  - POST /api/video/process-frame                            │
│                                                              │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐ │
│  │ Object Detector│  │ Threat Detector│  │ Object Tracker│ │
│  │   (YOLOv8)     │  │  (CNN Model)   │  │   (SORT)      │ │
│  └────────────────┘  └────────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## Features

### 1. Real-Time Video Processing
- **PC Camera Support**: Direct access to computer webcam
- **ESP32-CAM Support**: Remote camera streaming via IP address
- **Frame Processing**: ~10 FPS for PC camera, ~5 FPS for ESP32-CAM

### 2. Object Detection
- Detects left-behind objects using YOLOv8
- Tracks objects over time using SORT algorithm
- Identifies stationary objects that exceed time threshold
- Filters objects by minimum size

### 3. Threat Detection
- Analyzes video frames for potential threats
- Provides threat classification and confidence scores
- Real-time alerts for detected threats

### 4. User Interface
- Live video feed with detection overlays
- Real-time statistics dashboard
- Detection history table
- Alert notifications

## Installation & Setup

### Prerequisites

1. **Python Environment** (for Flask API)
   - Python 3.8+
   - pip package manager

2. **Laravel Environment**
   - PHP 8.1+
   - Composer
   - Node.js & npm (for asset compilation)

### Step 1: Install Python Dependencies

```bash
cd Video_Based_Left_Behind_Object_and_Threat_Detection
pip install -r requirements.txt
```

### Step 2: Configure Environment Variables

Add to your Laravel `.env` file:

```env
VIDEO_THREAT_API_URL=http://127.0.0.1:5003
VIDEO_THREAT_TIMEOUT=30
```

### Step 3: Start the Flask API

```bash
cd Video_Based_Left_Behind_Object_and_Threat_Detection
python app.py
```

The API will start on `http://127.0.0.1:5003`

### Step 4: Compile Laravel Assets

```bash
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
npm install
npm run build
```

### Step 5: Access the Dashboard

Navigate to: `http://your-laravel-app/admin/management/video-threat`

## Usage Guide

### Using PC Camera

1. Click the "PC Camera" option
2. Click "Start Detection"
3. Allow browser camera access when prompted
4. View real-time detections on the video feed

### Using ESP32-CAM

1. Click the "ESP32-CAM" option
2. Enter your ESP32-CAM IP address (e.g., `192.168.1.100`)
3. Click "Connect"
4. Click "Start Detection"
5. View real-time detections from the remote camera

### Understanding Detection Results

**Left-Behind Objects:**
- Displayed with yellow/orange bounding boxes
- Shows object class, confidence, and time stationary
- Logged in detection history

**Threats:**
- Displayed with red overlay on video
- Shows threat type and confidence score
- Triggers immediate alert notification

## API Endpoints

### Health Check
```http
GET /api/video/health
```

### System Status
```http
GET /api/video/status
```

Response:
```json
{
  "status": "active",
  "object_detector_loaded": true,
  "threat_detector_loaded": true,
  "tracker_active": true
}
```

### Detect Objects
```http
POST /api/video/detect-objects
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

### Detect Threats
```http
POST /api/video/detect-threats
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

### Process Complete Frame
```http
POST /api/video/process-frame
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

Response:
```json
{
  "success": true,
  "objects": {
    "detections": [...],
    "left_behind_count": 2,
    "total_objects": 5
  },
  "threats": {
    "is_threat": false,
    "confidence": 0.0,
    "threat_type": null
  }
}
```

## Files Created/Modified

### New Files Created

1. **Flask API**
   - `Video_Based_Left_Behind_Object_and_Threat_Detection/app.py`

2. **Laravel Controller**
   - `app/Http/Controllers/Admin/Management/VideoThreatController.php`

3. **Blade Views**
   - `resources/views/admin/pages/management/video-threat/dashboard.blade.php`
   - `resources/views/admin/pages/management/video-threat/partials/detection-modal.blade.php`

4. **JavaScript**
   - `resources/js/admin/video-threat.js`

5. **CSS**
   - `resources/css/admin/video-threat.css`

### Modified Files

1. **Routes**
   - `routes/web.php` - Added video threat routes

2. **Configuration**
   - `config/sidebar.php` - Added menu item
   - `config/services.php` - Added API configuration

## Troubleshooting

### API Connection Issues

**Problem**: Cannot connect to Flask API

**Solution**:
1. Verify Flask API is running: `http://127.0.0.1:5003/api/video/health`
2. Check firewall settings
3. Verify `VIDEO_THREAT_API_URL` in `.env`

### Camera Access Denied

**Problem**: Browser denies camera access

**Solution**:
1. Use HTTPS (required for camera access in modern browsers)
2. Check browser permissions
3. Try a different browser

### ESP32-CAM Connection Failed

**Problem**: Cannot connect to ESP32-CAM

**Solution**:
1. Verify ESP32-CAM is on the same network
2. Check IP address is correct
3. Ensure ESP32-CAM firmware is running
4. Test stream URL directly: `http://ESP32_IP/stream`

### Low Frame Rate

**Problem**: Detection is slow

**Solution**:
1. Reduce video resolution
2. Increase processing interval in JavaScript
3. Use GPU acceleration if available
4. Close other applications

## Security Considerations

1. **API Authentication**: Consider adding API key authentication for production
2. **HTTPS**: Use HTTPS for camera access and secure communication
3. **Rate Limiting**: Implement rate limiting on API endpoints
4. **Input Validation**: All frame data is validated before processing
5. **CORS**: Configure CORS properly for production environments

## Performance Optimization

1. **Frame Rate**: Adjust processing interval based on hardware capabilities
2. **Resolution**: Lower resolution = faster processing
3. **Model Selection**: Use lighter models for real-time performance
4. **Caching**: Results are cached to reduce redundant processing
5. **Async Processing**: Consider using async processing for high-volume scenarios

## Future Enhancements

- [ ] Database logging of all detections
- [ ] Email/SMS alerts for threats
- [ ] Multi-camera support
- [ ] Recording and playback functionality
- [ ] Advanced analytics and reporting
- [ ] Integration with school security systems
- [ ] Mobile app support

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review Flask API logs
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify all dependencies are installed

## License

This integration follows the same license as the parent projects.

