# Audio-Based Threat Detection System
## Smart School Safety and Performance Monitoring System

---

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [System Overview](#system-overview)
3. [Architecture](#architecture)
4. [Technology Stack](#technology-stack)
5. [Implementation Details](#implementation-details)
6. [Machine Learning Model](#machine-learning-model)
7. [Speech Recognition](#speech-recognition)
8. [API Endpoints](#api-endpoints)
9. [Frontend Integration](#frontend-integration)
10. [Security Features](#security-features)
11. [File Structure](#file-structure)

---

## 1. Executive Summary

The **Audio-Based Threat Detection System** is an AI-powered real-time audio monitoring solution designed for school safety. It uses advanced machine learning and speech recognition to detect potential threats through:

- **Non-Speech Detection**: Identifies dangerous sounds (screaming, crying, glass breaking)
- **Speech Detection**: Transcribes speech and detects threatening language (English & Sinhala)

### Key Features
- ✅ Real-time audio analysis (<3 second latency)
- ✅ 96.36% accuracy on non-speech threat detection
- ✅ Multi-language speech recognition (English & Sinhala)
- ✅ Professional false-positive reduction algorithms
- ✅ Seamless Laravel admin panel integration
- ✅ WebSocket-like continuous monitoring

---

## 2. System Overview

### How It Works

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Browser       │    │   Flask API     │    │   ML Models     │
│   Microphone    │───▶│   Server        │───▶│   (PyTorch)     │
│   (Web Audio)   │    │   Port 5002     │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
        │                      │                      │
        │                      │                      │
        ▼                      ▼                      ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Raw PCM Audio  │    │  Audio Decoder  │    │  Threat         │
│  16kHz, 16-bit  │───▶│  & Preprocessor │───▶│  Classification │
│  4-sec chunks   │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                      │
                              ┌────────────────────────┤
                              ▼                        ▼
                    ┌─────────────────┐    ┌─────────────────┐
                    │  Non-Speech     │    │  Speech-to-Text │
                    │  CNN+LSTM Model │    │  Google API     │
                    │                 │    │  + Keywords     │
                    └─────────────────┘    └─────────────────┘
                              │                        │
                              └────────────┬───────────┘
                                           ▼
                              ┌─────────────────────────┐
                              │   Threat Aggregation    │
                              │   & False Positive      │
                              │   Reduction             │
                              └─────────────────────────┘
                                           │
                                           ▼
                              ┌─────────────────────────┐
                              │   Real-time Alerts      │
                              │   Laravel Dashboard     │
                              └─────────────────────────┘
```

### Detection Flow

1. **Audio Capture**: Browser captures microphone audio using Web Audio API
2. **Preprocessing**: Audio is resampled to 16kHz and converted to PCM16 format
3. **Transmission**: Base64-encoded audio sent to Flask API every 4 seconds
4. **Analysis**: Dual-path analysis (Non-Speech ML + Speech Recognition)
5. **Aggregation**: Results combined with false-positive reduction
6. **Alert**: Threats displayed in Laravel admin dashboard

---

## 3. Architecture

### Three-Tier Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                           │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Laravel Admin Dashboard (Blade + JavaScript)            │   │
│  │  - Real-time visualization                               │   │
│  │  - Alert management                                      │   │
│  │  - System controls                                       │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Flask REST API (Python)                                 │   │
│  │  - Audio decoding & preprocessing                        │   │
│  │  - Model inference orchestration                         │   │
│  │  - Threat aggregation logic                              │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    INTELLIGENCE LAYER                           │
│  ┌──────────────────────┐    ┌──────────────────────────────┐  │
│  │  PyTorch ML Model    │    │  Speech Recognition Engine   │  │
│  │  - 1D-CNN + Bi-LSTM  │    │  - Google Speech API         │  │
│  │  - MFCC Features     │    │  - Keyword Detection         │  │
│  └──────────────────────┘    └──────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Technology Stack

### Backend (Python)
| Technology | Version | Purpose |
|------------|---------|---------|
| **Python** | 3.14+ | Primary programming language |
| **Flask** | 3.x | REST API framework |
| **PyTorch** | 2.x | Deep learning framework |
| **torchaudio** | 2.x | Audio processing & feature extraction |
| **SpeechRecognition** | 3.x | Speech-to-text engine |
| **pydub** | 0.25+ | Audio format conversion |
| **NumPy** | 1.x | Numerical computing |
| **FFmpeg** | 8.x | Audio codec support |

### Frontend (JavaScript)
| Technology | Purpose |
|------------|---------|
| **Web Audio API** | Real-time audio capture |
| **ScriptProcessor** | Raw PCM sample extraction |
| **Canvas API** | Audio waveform visualization |
| **Bootstrap 5** | UI components |
| **Fetch API** | REST API communication |

### Laravel Integration
| Technology | Purpose |
|------------|---------|
| **Laravel 10+** | Main application framework |
| **Blade Templates** | View rendering |
| **Laravel Routes** | API proxying |
| **CSRF Protection** | Security |

---

## 5. Implementation Details

### 5.1 Audio Capture (Frontend)

**File:** `resources/js/admin/audio-threat.js`

The browser captures audio using the Web Audio API with ScriptProcessor:

```javascript
// Create audio context and processor
this.audioContext = new AudioContext();
this.scriptProcessor = this.audioContext.createScriptProcessor(4096, 1, 1);

// Capture raw PCM samples
this.scriptProcessor.onaudioprocess = (e) => {
    const inputData = e.inputBuffer.getChannelData(0);
    this.audioBuffer.push(new Float32Array(inputData));
};

// Send every 4 seconds
setInterval(() => this.processAudioBuffer(), 4000);
```

**Audio Processing Pipeline:**
1. Capture at native sample rate (usually 44.1kHz or 48kHz)
2. Resample to 16kHz using linear interpolation
3. Convert Float32 to Int16 PCM
4. Encode as Base64
5. Send to API with format specification

### 5.2 API Server

**File:** `Audio-Based_Threat_Detection/app.py`

Flask server with automatic FFmpeg detection:

```python
from flask import Flask
from flask_cors import CORS
from api.routes.audio_routes import audio_bp

app = Flask(__name__)
CORS(app)  # Enable cross-origin requests

# Register audio analysis routes
app.register_blueprint(audio_bp, url_prefix='/api/audio')

# Server runs on port 5002
if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5002)
```

### 5.3 Audio Decoding

**File:** `Audio-Based_Threat_Detection/api/routes/audio_routes.py`

Handles multiple audio formats:

```python
def decode_audio_from_base64(base64_data, audio_format='auto', sample_rate=16000):
    audio_bytes = base64.b64decode(base64_data)

    # Handle raw PCM16 format (from JavaScript)
    if audio_format == 'pcm16':
        audio_array = np.frombuffer(audio_bytes, dtype=np.int16)
        audio = audio_array.astype(np.float32) / 32768.0
        return audio

    # Fallback to pydub for WebM/MP3/etc.
    # Requires FFmpeg
```

---

## 6. Machine Learning Model

### 6.1 Model Architecture

**File:** `Audio-Based_Threat_Detection/models/non_speech_model.py`

The model uses a hybrid **1D-CNN + Bidirectional LSTM** architecture:

```
Input: MFCC Features (132 dimensions)
         │
         ▼
┌─────────────────────────┐
│  1D Convolutional Layer │  64 filters, kernel=3
│  + BatchNorm + ReLU     │
│  + MaxPool + Dropout    │
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  1D Convolutional Layer │  128 filters, kernel=3
│  + BatchNorm + ReLU     │
│  + MaxPool + Dropout    │
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Bidirectional LSTM     │  128 hidden units
│  2 layers, dropout=0.3  │
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Fully Connected Layer  │  64 units + ReLU
│  + Dropout (0.5)        │
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Output Layer           │  5 classes (softmax)
└─────────────────────────┘
```

### 6.2 Feature Extraction

**File:** `Audio-Based_Threat_Detection/models/audio_processor.py`

Extracts 132-dimensional feature vectors:

| Feature Type | Count | Description |
|--------------|-------|-------------|
| MFCC | 40 | Mel-frequency cepstral coefficients |
| MFCC Delta | 40 | First-order derivatives |
| MFCC Delta-Delta | 40 | Second-order derivatives |
| Spectral Centroid | 1 | Center of mass of spectrum |
| Spectral Bandwidth | 1 | Spread of spectrum |
| Spectral Rolloff | 1 | Frequency below which 85% of energy |
| Zero Crossing Rate | 1 | Rate of sign changes |
| RMS Energy | 1 | Root mean square energy |
| Additional Spectral | 7 | Contrast, flatness, etc. |
| **Total** | **132** | Features per frame |

### 6.3 Detection Classes

| Class | Description | Threshold |
|-------|-------------|-----------|
| `crying` | Child crying sounds | 0.88 |
| `screaming` | High-pitched screams | 0.94 |
| `shouting` | Aggressive shouting | 0.95 |
| `glass_breaking` | Glass shattering | 0.85 |
| `normal` | Normal ambient sounds | - |

### 6.4 False Positive Reduction

**File:** `Audio-Based_Threat_Detection/models/threat_detector.py`

Multiple strategies to reduce false positives:

```python
class ThreatDetector:
    def __init__(self):
        # 1. High confidence thresholds per class
        self.class_thresholds = {
            'crying': 0.88,
            'screaming': 0.94,    # Very high - often confused with speech
            'shouting': 0.95,     # Very high - often confused with talking
            'glass_breaking': 0.85,
            'normal': 0.0
        }

        # 2. Consecutive detection requirement
        self.consecutive_required = 3  # Must detect 3 times in a row

        # 3. Audio energy filtering
        self.min_energy_threshold = 0.03   # Ignore silence
        self.high_energy_threshold = 0.20  # Require high energy for screaming
```

### 6.5 Model Training Results

```
Training Configuration:
- Epochs: 50
- Batch Size: 32
- Learning Rate: 0.001
- Optimizer: Adam
- Loss Function: Cross-Entropy

Results:
- Training Accuracy: 98.2%
- Validation Accuracy: 96.36%
- Test Accuracy: 95.8%
```

---

## 7. Speech Recognition

### 7.1 Speech-to-Text Engine

**File:** `Audio-Based_Threat_Detection/models/speech_threat_model.py`

Uses Google Speech Recognition API with fallback support:

```python
class SpeechThreatDetector:
    def __init__(self):
        self.recognizer = sr.Recognizer()
        self.recognizer.energy_threshold = 300
        self.recognizer.dynamic_energy_threshold = True

    def transcribe_audio(self, audio_data, sample_rate=16000):
        # Normalize audio for better recognition
        max_val = np.max(np.abs(audio_data))
        if max_val > 0:
            audio_data = audio_data / max_val * 0.9

        # Convert to AudioData format
        audio_int16 = (audio_data * 32767).astype(np.int16)
        audio = sr.AudioData(audio_int16.tobytes(), sample_rate, 2)

        # Transcribe with Google API
        text = self.recognizer.recognize_google(audio, language='en-US')
        return text
```

### 7.2 Threat Keywords Database

**File:** `Audio-Based_Threat_Detection/config/keywords.py`

Comprehensive keyword detection for multiple languages:

**English Threat Keywords:**
```python
ENGLISH_THREATS = [
    # Violence
    "kill", "murder", "shoot", "stab", "hurt", "attack",
    "fight", "punch", "beat", "destroy", "die", "dead",

    # Weapons
    "gun", "knife", "bomb", "weapon",

    # Threats
    "threat", "revenge", "hate you", "i will kill",

    # Bullying
    "bully", "loser", "stupid", "ugly", "fat"
]
```

**Sinhala (සිංහල) Threat Keywords:**
```python
SINHALA_THREATS = [
    "මරනවා",      # kill
    "ගහනවා",      # hit
    "කපනවා",      # cut
    "පහර දෙනවා",  # attack
    "වෙඩි තියනවා", # shoot
]
```

### 7.3 Threat Level Classification

| Level | Score Range | Examples |
|-------|-------------|----------|
| **CRITICAL** | 0.8 - 1.0 | "I will kill you", weapon mentions |
| **HIGH** | 0.6 - 0.8 | Direct threats, violence |
| **MEDIUM** | 0.4 - 0.6 | Aggressive language, bullying |
| **LOW** | 0.2 - 0.4 | Mild profanity |

---

## 8. API Endpoints

### Base URL: `http://127.0.0.1:5002/api/audio`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Health check |
| GET | `/status` | Detector status |
| POST | `/analyze` | Analyze audio for threats |
| GET | `/sensitivity` | Get sensitivity settings |
| POST | `/sensitivity` | Set sensitivity level |
| POST | `/reset-session` | Reset detection history |
| POST | `/calibrate` | Calibrate noise profile |

### Analyze Audio Request

```json
POST /api/audio/analyze
Content-Type: application/json

{
    "audio_data": "base64_encoded_audio...",
    "format": "pcm16",
    "sample_rate": 16000,
    "session_id": "session_1234567890"
}
```

### Analyze Audio Response

```json
{
    "success": true,
    "result": {
        "is_threat": true,
        "threat_type": "speech",
        "threat_level": "high",
        "confidence": 0.85,
        "non_speech_result": {
            "detected_class": "normal",
            "confidence": 0.92,
            "is_threat": false,
            "all_probabilities": {
                "normal": 0.92,
                "screaming": 0.04,
                "crying": 0.02,
                "shouting": 0.01,
                "glass_breaking": 0.01
            }
        },
        "speech_result": {
            "text": "i will kill you",
            "is_threat": true,
            "threat_level": "high",
            "detected_keywords": [
                {"keyword": "kill", "severity": "critical"}
            ],
            "language": "en",
            "engine": "google"
        }
    }
}
```

---

## 9. Frontend Integration

### 9.1 Laravel Controller

**File:** `app/Http/Controllers/Admin/AudioThreatController.php`

Proxies requests between frontend and Python API:

```php
class AudioThreatController extends Controller
{
    protected $apiBaseUrl = 'http://127.0.0.1:5002/api/audio';

    public function analyze(Request $request)
    {
        $response = Http::post($this->apiBaseUrl . '/analyze', [
            'audio_data' => $request->audio_data,
            'format' => $request->format ?? 'pcm16',
            'sample_rate' => $request->sample_rate ?? 16000,
            'session_id' => $request->session_id
        ]);

        return $response->json();
    }
}
```

### 9.2 Laravel Routes

**File:** `routes/web.php`

```php
Route::prefix('admin/management/audio-threat')->group(function () {
    Route::get('/', [AudioThreatController::class, 'index']);
    Route::post('/analyze', [AudioThreatController::class, 'analyze']);
    Route::get('/status', [AudioThreatController::class, 'status']);
    Route::post('/calibrate', [AudioThreatController::class, 'calibrate']);
    Route::get('/sensitivity', [AudioThreatController::class, 'getSensitivity']);
    Route::post('/sensitivity', [AudioThreatController::class, 'setSensitivity']);
});
```

### 9.3 Dashboard View

**File:** `resources/views/admin/management/audio-threat.blade.php`

Features:
- Real-time audio waveform visualization
- Detection status indicators
- Threat alert cards
- Non-speech probability bars
- Speech transcription display
- Sensitivity controls
- Noise calibration tool

---

## 10. Security Features

### 10.1 Input Validation

- Audio data size limits
- Format validation
- Session ID verification
- CSRF token protection (Laravel)

### 10.2 False Positive Prevention

| Feature | Description |
|---------|-------------|
| **High Thresholds** | Class-specific confidence thresholds (0.85-0.95) |
| **Consecutive Detection** | Requires 3 consecutive positive detections |
| **Energy Filtering** | Ignores low-energy audio (silence) |
| **Sensitivity Levels** | Adjustable: low, normal, high |

### 10.3 Rate Limiting

- Maximum audio chunk size: 5MB
- Processing interval: 4 seconds minimum
- Session-based tracking

---

## 11. File Structure

```
Audio-Based_Threat_Detection/
│
├── app.py                          # Flask application entry point
│
├── api/
│   ├── __init__.py
│   └── routes/
│       └── audio_routes.py         # API endpoint definitions
│
├── models/
│   ├── __init__.py
│   ├── audio_processor.py          # Audio feature extraction
│   ├── non_speech_model.py         # PyTorch CNN+LSTM model
│   ├── speech_threat_model.py      # Speech-to-text + keywords
│   ├── threat_detector.py          # Main detection orchestrator
│   └── saved/
│       └── non_speech_threat_model.pth  # Trained model weights
│
├── config/
│   ├── __init__.py
│   ├── keywords.py                 # Threat keywords database
│   └── model_config.py             # Model configuration
│
├── training/
│   ├── train_non_speech.py         # Model training script
│   └── dataset/                    # Training audio samples
│
├── requirements.txt                # Python dependencies
└── DOCUMENTATION.md                # This documentation

Laravel Integration Files:
├── app/Http/Controllers/Admin/
│   └── AudioThreatController.php   # Laravel controller
│
├── resources/
│   ├── views/admin/management/
│   │   └── audio-threat.blade.php  # Dashboard view
│   └── js/admin/
│       └── audio-threat.js         # Frontend JavaScript
│
└── routes/
    └── web.php                     # Route definitions
```

---

## 12. Installation & Setup

### Prerequisites

1. Python 3.10+ (tested on 3.14)
2. Node.js 16+ & npm
3. FFmpeg (for audio format support)
4. Laravel 10+ application

### Step 1: Install Python Dependencies

```bash
cd Audio-Based_Threat_Detection
pip install -r requirements.txt
```

### Step 2: Install FFmpeg

```powershell
# Windows (using winget)
winget install Gyan.FFmpeg
```

### Step 3: Start the API Server

```bash
cd Audio-Based_Threat_Detection
python app.py
```

Expected output:
```
============================================================
   AUDIO-BASED THREAT DETECTION API SERVER
   Smart School Safety Monitoring System
============================================================

🚀 Server starting on http://127.0.0.1:5002
✅ FFmpeg found at: C:\...\ffmpeg\bin
Model loaded. Classes: ['crying', 'screaming', 'shouting', 'glass_breaking', 'normal']
```

### Step 4: Build Laravel Assets

```bash
npm run build
# or for development
npm run dev
```

### Step 5: Access Dashboard

Navigate to: `http://your-laravel-app/admin/management/audio-threat`

---

## 13. Usage Guide

### Starting Detection

1. Open the Audio Threat Detection dashboard
2. Click "Start Detection" button
3. Allow microphone permission when prompted
4. System begins real-time monitoring

### Adjusting Sensitivity

If experiencing too many false positives:

```bash
# Set to low sensitivity
curl -X POST http://127.0.0.1:5002/api/audio/sensitivity \
     -H "Content-Type: application/json" \
     -d '{"level": "low"}'
```

| Level | Use Case |
|-------|----------|
| **low** | Noisy environments, fewer false alarms |
| **normal** | Standard school environment |
| **high** | Quiet areas, maximum sensitivity |

### Noise Calibration

1. Click "Calibrate" button on dashboard
2. System records 5 seconds of ambient noise
3. Noise profile is used to improve detection

---

## 14. Performance Metrics

| Metric | Value |
|--------|-------|
| Model Accuracy | 96.36% |
| Average Latency | < 3 seconds |
| Audio Chunk Size | 4 seconds |
| Sample Rate | 16 kHz |
| Supported Languages | English, Sinhala |
| False Positive Rate | < 5% (with tuned thresholds) |

---

## 15. Future Enhancements

- [ ] Vosk offline speech recognition (no internet required)
- [ ] Additional language support (Tamil, etc.)
- [ ] Gunshot detection model
- [ ] Video integration for multi-modal threat detection
- [ ] Mobile app notifications
- [ ] Historical threat analytics dashboard

---

## 16. Troubleshooting

### "Could not understand audio"

**Cause:** Speech recognition failed
**Solutions:**
- Speak louder and clearer
- Check internet connection (Google API requires internet)
- Ensure microphone is working

### "FFmpeg not found"

**Cause:** FFmpeg not installed or not in PATH
**Solutions:**
- Install FFmpeg: `winget install Gyan.FFmpeg`
- Restart terminal/VS Code after installation

### High False Positive Rate

**Solutions:**
- Set sensitivity to "low"
- Calibrate noise profile
- Check microphone quality

---

**Document Version:** 1.0
**Last Updated:** December 2024
**Author:** AI-Powered Smart School Safety Team
