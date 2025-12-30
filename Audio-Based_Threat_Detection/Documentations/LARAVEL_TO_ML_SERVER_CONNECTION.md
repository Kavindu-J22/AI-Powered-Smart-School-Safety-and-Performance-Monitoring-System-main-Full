# 🔗 Laravel to ML Server Connection Guide

**Document Version**: 1.0  
**Last Updated**: December 30, 2025  
**For**: Non-PHP Developers  
**System**: AI-Powered Smart School Safety

---

## 📋 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Step-by-Step Connection Setup](#step-by-step-connection-setup)
3. [File Locations](#file-locations)
4. [Configuration](#configuration)
5. [Testing the Connection](#testing-the-connection)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Architecture Overview

### **How It Works**:

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│   Web Browser   │         │  Laravel Server  │         │   ML Server     │
│  (JavaScript)   │         │      (PHP)       │         │    (Python)     │
└────────┬────────┘         └────────┬─────────┘         └────────┬────────┘
         │                           │                            │
         │  1. Capture Audio         │                            │
         │     (Microphone)          │                            │
         │                           │                            │
         │  2. Send Audio (HTTPS)    │                            │
         ├──────────────────────────>│                            │
         │                           │                            │
         │                           │  3. Forward Audio (HTTP)   │
         │                           ├───────────────────────────>│
         │                           │                            │
         │                           │                            │  4. Analyze
         │                           │                            │     Audio
         │                           │                            │
         │                           │  5. Return Result (JSON)   │
         │                           │<───────────────────────────┤
         │                           │                            │
         │  6. Display Alert         │                            │
         │<──────────────────────────┤                            │
         │                           │                            │
```

### **Components**:

1. **Frontend (JavaScript)**: Captures audio from microphone
2. **Laravel Backend (PHP)**: Receives audio, forwards to ML server
3. **ML Server (Python Flask)**: Analyzes audio, returns threat detection result

---

## 🚀 Step-by-Step Connection Setup

### **STEP 1: Start the ML Server (Python)**

**Location**: `Audio-Based_Threat_Detection/`

**What to do**:

1. Open a terminal/command prompt

2. Navigate to the ML server directory:
```bash
cd Audio-Based_Threat_Detection
```

3. Install Python dependencies (first time only):
```bash
pip install -r requirements.txt
```

4. Start the ML server:
```bash
python app.py
```

**Expected Output**:
```
============================================================
🎤 Audio-Based Threat Detection API
============================================================
✓ Non-Speech Model Loaded: non_speech_threat_model.h5
✓ Speech Detector Initialized
✓ Noise Profiler Ready

Starting server on 127.0.0.1:5002

Available Endpoints:
   - GET  /api/audio/health          Health Check
   - GET  /api/audio/status          Detector Status
   - POST /api/audio/analyze         Analyze Audio
   - POST /api/audio/calibrate       Calibrate Noise
============================================================

 * Running on http://127.0.0.1:5002
```

**Important**: Keep this terminal window open! The ML server must be running.

**Default Settings**:
- **Host**: `127.0.0.1` (localhost)
- **Port**: `5002`
- **URL**: `http://127.0.0.1:5002`

---

### **STEP 2: Configure Laravel to Connect to ML Server**

**Location**: `AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/config/services.php`

**What to do**:

1. Open the file: `config/services.php`

2. Find the `audio_threat` section (around line 59):

```php
'audio_threat' => [
    'url' => env('AUDIO_THREAT_API_URL', 'http://127.0.0.1:5002'),
    'timeout' => env('AUDIO_THREAT_TIMEOUT', 30),
],
```

**Explanation**:
- `url`: The address of your ML server
- `timeout`: How long to wait for a response (30 seconds)
- `env('AUDIO_THREAT_API_URL', ...)`: Reads from `.env` file, or uses default

**Default is correct**: `http://127.0.0.1:5002`

---

### **STEP 3: Set Environment Variables (Optional)**

**Location**: `AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/.env`

**What to do**:

1. Open the file: `.env` (in the Laravel root directory)

2. Add these lines (if not already present):

```bash
# Audio Threat Detection ML Server
AUDIO_THREAT_API_URL=http://127.0.0.1:5002
AUDIO_THREAT_TIMEOUT=30
```

**When to change**:
- If ML server is on a different computer: `AUDIO_THREAT_API_URL=http://192.168.1.100:5002`
- If ML server uses a different port: `AUDIO_THREAT_API_URL=http://127.0.0.1:5003`

**Note**: If you change `.env`, restart Laravel:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### **STEP 4: Understand the Laravel Controller (No Changes Needed)**

**Location**: `AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/app/Http/Controllers/Admin/Management/AudioThreatController.php`

**What it does**:

This PHP file acts as a **bridge** between the frontend and ML server.

**Key Parts**:

#### **1. Constructor** (Lines 18-21):
```php
public function __construct()
{
    // Read ML server URL from config
    $this->apiBaseUrl = config('services.audio_threat.url', 'http://127.0.0.1:5002');
}
```
**Explanation**: Gets the ML server URL from `config/services.php`

---

#### **2. Analyze Audio Method** (Lines 66-92):
```php
public function analyze(Request $request): JsonResponse
{
    // Validate that audio data is present
    $request->validate([
        'audio_data' => 'required|string'
    ]);

    try {
        // Send audio to ML server
        $response = Http::timeout($this->timeout)
            ->post("{$this->apiBaseUrl}/api/audio/analyze", [
                'audio_data' => $request->audio_data
            ]);
        
        if ($response->successful()) {
            $result = $response->json();
            
            // Log threats (metadata only, no audio)
            if ($result['success'] && $result['result']['is_threat'] ?? false) {
                Log::warning('Audio threat detected', [
                    'threat_type' => $result['result']['threat_type'] ?? 'unknown',
                    'threat_level' => $result['result']['threat_level'] ?? 'unknown',
                    'confidence' => $result['result']['confidence'] ?? 0,
                    'timestamp' => now()->toIso8601String()
                ]);
            }
            
            return response()->json($result);
        }
        
        // Handle errors...
    } catch (\Exception $e) {
        // Handle connection errors...
    }
}
```

**Explanation**:
1. **Validate**: Check that audio data exists
2. **Send**: Forward audio to ML server at `http://127.0.0.1:5002/api/audio/analyze`
3. **Log**: If threat detected, log metadata (not audio)
4. **Return**: Send result back to frontend

**You don't need to change this file!**

---

### **STEP 5: Understand the ML Server Endpoint (No Changes Needed)**

**Location**: `Audio-Based_Threat_Detection/api/routes/audio_routes.py`

**What it does**:

This Python file receives audio from Laravel and analyzes it.

**Key Part** (Lines 77-161):

```python
@audio_bp.route('/analyze', methods=['POST'])
def analyze_audio():
    """Analyze uploaded audio for threats"""
    try:
        # Get JSON data from Laravel
        data = request.get_json()
        audio_base64 = data.get('audio_data')
        
        # Decode audio from base64
        audio_data = decode_audio_from_base64(audio_base64)
        
        # Analyze for threats
        result = threat_detector.analyze_audio(audio_data)
        
        # Return result to Laravel
        return jsonify({
            'success': True,
            'result': result
        })
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500
```

**Explanation**:
1. **Receive**: Get audio data from Laravel (base64 encoded)
2. **Decode**: Convert base64 to NumPy array
3. **Analyze**: Run threat detection
4. **Return**: Send result back to Laravel as JSON

**You don't need to change this file!**

---

## 📁 File Locations

### **Complete File Structure**:

```
Project Root/
│
├── AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/
│   │
│   ├── config/
│   │   └── services.php                    ← STEP 2: ML server URL config
│   │
│   ├── .env                                 ← STEP 3: Environment variables
│   │
│   ├── app/Http/Controllers/Admin/Management/
│   │   └── AudioThreatController.php       ← STEP 4: Laravel controller (no changes)
│   │
│   └── resources/js/admin/
│       └── audio-threat.js                  ← Frontend (sends audio)
│
└── Audio-Based_Threat_Detection/
    │
    ├── app.py                               ← STEP 1: Start this file!
    │
    ├── api/routes/
    │   └── audio_routes.py                  ← STEP 5: ML endpoint (no changes)
    │
    ├── models/
    │   └── threat_detector.py               ← Threat detection logic
    │
    └── requirements.txt                     ← Python dependencies
```

---

## ⚙️ Configuration

### **Summary of All Settings**:

| Setting | File | Line | Default Value | When to Change |
|---------|------|------|---------------|----------------|
| **ML Server URL** | `config/services.php` | 60 | `http://127.0.0.1:5002` | ML server on different computer |
| **Timeout** | `config/services.php` | 61 | `30` seconds | Slow network |
| **ML Server Host** | `Audio-Based_Threat_Detection/config/__init__.py` | 310 | `127.0.0.1` | Allow external connections |
| **ML Server Port** | `Audio-Based_Threat_Detection/config/__init__.py` | 311 | `5002` | Port conflict |

---

### **Example: ML Server on Different Computer**

**Scenario**: ML server runs on computer with IP `192.168.1.100`

**Changes**:

1. **On ML Server Computer** - Edit `Audio-Based_Threat_Detection/config/__init__.py` (line 310):
```python
HOST = os.environ.get('FLASK_HOST', '0.0.0.0')  # Allow external connections
```

2. **On Laravel Computer** - Edit `.env`:
```bash
AUDIO_THREAT_API_URL=http://192.168.1.100:5002
```

3. **Restart both servers**

---

## 🧪 Testing the Connection

### **Test 1: Check ML Server is Running**

**Method 1 - Browser**:
1. Open browser
2. Go to: `http://127.0.0.1:5002/api/audio/health`
3. Expected response:
```json
{
  "status": "healthy",
  "service": "Audio Threat Detection API",
  "version": "1.0.0"
}
```

**Method 2 - Command Line**:
```bash
curl http://127.0.0.1:5002/api/audio/health
```

---

### **Test 2: Check Laravel Can Connect**

**Method 1 - Laravel Tinker**:
```bash
php artisan tinker
```

Then run:
```php
$response = Http::get('http://127.0.0.1:5002/api/audio/health');
echo $response->body();
```

Expected output:
```json
{"status":"healthy","service":"Audio Threat Detection API","version":"1.0.0"}
```

**Method 2 - Browser**:
1. Start Laravel: `php artisan serve`
2. Login to admin panel
3. Go to: Audio Threat Detection page
4. Click "Start Detection"
5. If connection works, you'll see "Listening..." status

---

### **Test 3: End-to-End Test**

1. **Start ML Server**:
```bash
cd Audio-Based_Threat_Detection
python app.py
```

2. **Start Laravel**:
```bash
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve
```

3. **Open Browser**:
   - Go to: `http://localhost:8000`
   - Login as admin
   - Navigate to: Audio Threat Detection

4. **Test Detection**:
   - Click "Start Detection"
   - Allow microphone access
   - Make a loud noise (clap, shout)
   - Check if detection works

**Expected**: You should see audio visualization and detection results

---

## 🔧 Troubleshooting

### **Problem 1: "API service unavailable"**

**Symptoms**:
- Frontend shows "API service unavailable"
- Laravel logs: "Connection refused"

**Cause**: ML server is not running

**Solution**:
```bash
cd Audio-Based_Threat_Detection
python app.py
```

---

### **Problem 2: "Connection timeout"**

**Symptoms**:
- Request takes 30 seconds then fails
- Laravel logs: "cURL error 28: Timeout"

**Cause**: ML server is slow or not responding

**Solution**:
1. Check ML server is running
2. Increase timeout in `config/services.php`:
```php
'timeout' => env('AUDIO_THREAT_TIMEOUT', 60),  // 60 seconds
```
3. Restart Laravel:
```bash
php artisan config:clear
```

---

### **Problem 3: "Connection refused" (Different Computer)**

**Symptoms**:
- ML server on `192.168.1.100`
- Laravel can't connect

**Cause**: ML server only listening on `127.0.0.1`

**Solution**:
1. Edit `Audio-Based_Threat_Detection/config/__init__.py`:
```python
HOST = '0.0.0.0'  # Listen on all interfaces
```
2. Restart ML server
3. Check firewall allows port 5002

---

### **Problem 4: "Port already in use"**

**Symptoms**:
```
OSError: [Errno 48] Address already in use
```

**Cause**: Another program using port 5002

**Solution**:

**Option 1 - Kill existing process**:
```bash
# Windows
netstat -ano | findstr :5002
taskkill /PID <PID> /F

# Linux/Mac
lsof -i :5002
kill -9 <PID>
```

**Option 2 - Use different port**:
1. Edit `Audio-Based_Threat_Detection/config/__init__.py`:
```python
PORT = 5003  # Use port 5003 instead
```
2. Edit Laravel `.env`:
```bash
AUDIO_THREAT_API_URL=http://127.0.0.1:5003
```
3. Restart both servers

---

### **Problem 5: "CORS error" (Cross-Origin)**

**Symptoms**:
- Browser console: "CORS policy blocked"

**Cause**: Laravel and ML server on different domains

**Solution**:
1. Edit `Audio-Based_Threat_Detection/config/__init__.py` (line 313):
```python
CORS_ORIGINS = ['http://localhost:8000', 'http://127.0.0.1:8000', 'http://your-domain.com']
```
2. Restart ML server

---

## 📊 Connection Flow Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                         CONNECTION FLOW                          │
└──────────────────────────────────────────────────────────────────┘

1. FRONTEND (JavaScript)
   File: resources/js/admin/audio-threat.js
   ↓
   Captures audio from microphone
   Encodes to base64
   ↓
   POST /admin/management/audio-threat/analyze
   ↓

2. LARAVEL ROUTE
   File: routes/web.php
   ↓
   Route::post('/analyze', [AudioThreatController::class, 'analyze'])
   ↓

3. LARAVEL CONTROLLER
   File: app/Http/Controllers/Admin/Management/AudioThreatController.php
   ↓
   Reads ML server URL from config/services.php
   ↓
   Http::post('http://127.0.0.1:5002/api/audio/analyze', [...])
   ↓

4. ML SERVER (Python Flask)
   File: Audio-Based_Threat_Detection/api/routes/audio_routes.py
   ↓
   @audio_bp.route('/analyze', methods=['POST'])
   ↓
   Decodes audio
   Analyzes for threats
   ↓
   Returns JSON result
   ↓

5. LARAVEL CONTROLLER
   ↓
   Receives result
   Logs if threat detected
   ↓
   Returns JSON to frontend
   ↓

6. FRONTEND (JavaScript)
   ↓
   Displays alert if threat detected
```

---

## ✅ Quick Reference

### **Start Servers**:

```bash
# Terminal 1 - ML Server
cd Audio-Based_Threat_Detection
python app.py

# Terminal 2 - Laravel
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve
```

### **Check Connection**:

```bash
# Test ML server
curl http://127.0.0.1:5002/api/audio/health

# Test Laravel
curl http://localhost:8000
```

### **Files to Edit** (if needed):

| What to Change | File | Line |
|----------------|------|------|
| ML server URL | `config/services.php` | 60 |
| ML server URL (env) | `.env` | Add `AUDIO_THREAT_API_URL=...` |
| ML server port | `Audio-Based_Threat_Detection/config/__init__.py` | 311 |
| ML server host | `Audio-Based_Threat_Detection/config/__init__.py` | 310 |

---

**That's it! The connection is now set up and working.** 🎉

