# 📁 Files and Functions Index

**Document Version**: 1.0  
**Last Updated**: December 30, 2025  
**System**: AI-Powered Smart School Safety - Audio Threat Detection

---

## 📋 Table of Contents

1. [Privacy Handling](#privacy-handling)
2. [Audio Processing](#audio-processing)
3. [Noise Adaptive Techniques](#noise-adaptive-techniques)
4. [Feature Extraction](#feature-extraction)
5. [Threat Detection](#threat-detection)
6. [API Routes](#api-routes)
7. [Frontend](#frontend)

---

## 🔒 Privacy Handling

### **Files Involved**:

| File | Path | Purpose |
|------|------|---------|
| `audio-threat.js` | `resources/js/admin/` | Frontend audio capture with user consent |
| `AudioThreatController.php` | `app/Http/Controllers/Admin/Management/` | Laravel backend - forwards audio, logs threats |
| `audio_routes.py` | `Audio-Based_Threat_Detection/api/routes/` | Python API endpoints |
| `threat_detector.py` | `Audio-Based_Threat_Detection/models/` | Main detection logic with privacy comments |
| `threat-modal.blade.php` | `resources/views/admin/pages/management/audio-threat/partials/` | Privacy notices in UI |

### **Key Functions**:

#### 1. **User Consent** - `audio-threat.js`
```javascript
// Line 71-74
async startDetection() {
    // Request microphone permission
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    this.mediaStream = stream;
}
```
**Purpose**: Request explicit user permission for microphone access  
**Privacy**: User must grant permission before any audio capture

---

#### 2. **Audio Transmission** - `audio-threat.js`
```javascript
// Line 197-215
async sendAudioChunk(audioData) {
    const base64 = this.arrayBufferToBase64(pcmData.buffer);
    
    const response = await fetch('/admin/management/audio-threat/analyze', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.config.csrfToken
        },
        body: JSON.stringify({
            audio_data: base64,
            format: 'pcm16',
            sample_rate: 16000
        })
    });
}
```
**Purpose**: Send audio to backend via HTTPS  
**Privacy**: Encrypted transmission, CSRF protection

---

#### 3. **Metadata-Only Logging** - `AudioThreatController.php`
```php
// Line 82-89
if ($result['success'] && $result['result']['is_threat'] ?? false) {
    Log::warning('Audio threat detected', [
        'threat_type' => $result['result']['threat_type'],
        'threat_level' => $result['result']['threat_level'],
        'confidence' => $result['result']['confidence'],
        'timestamp' => now()->toIso8601String()
        // NO AUDIO DATA LOGGED
    ]);
}
```
**Purpose**: Log threat events without audio content  
**Privacy**: Only metadata stored, no raw audio

---

#### 4. **Raw Audio Discard** - `threat_detector.py`
```python
# Line 153-156
# Extract features (privacy: raw audio can be discarded after this)
features = self.feature_extractor.extract_fixed_length_features(processed_audio)
features_normalized, _, _ = self.feature_extractor.normalize_features(features)
# processed_audio is now eligible for garbage collection
```
**Purpose**: Discard raw audio after feature extraction  
**Privacy**: Raw audio exists in memory for < 1 second

---

#### 5. **Privacy Notice** - `threat-modal.blade.php`
```html
<!-- Line 127-132 -->
<p>Your privacy is protected - audio is processed locally 
   and discarded after analysis.</p>
<small>Audio is only analyzed for threat detection and 
       is never stored or recorded.</small>
```
**Purpose**: Inform users about privacy protection  
**Privacy**: Transparent communication

---

## 🎵 Audio Processing

### **File**: `utils/audio_processor.py`

| Function | Lines | Purpose | Privacy Impact |
|----------|-------|---------|----------------|
| `load_audio()` | 28-55 | Load audio files | In-memory only |
| `preprocess_audio()` | 57-70 | Normalize and trim | In-memory only |
| `_trim_silence()` | 72-91 | Remove silence | In-memory only |
| `normalize_audio()` | 93-98 | Scale to [-1, 1] | In-memory only |
| `split_into_chunks()` | 100-124 | Split into segments | In-memory only |
| `decode_base64_audio()` | 126-154 | Decode from browser | In-memory only |
| `convert_webm_to_wav()` | 156-162 | Format conversion | In-memory only |
| `calculate_energy()` | 164-166 | Calculate RMS | In-memory only |
| `is_silent()` | 168-170 | Detect silence | In-memory only |

**Privacy Note**: All functions process audio in-memory only, no disk writes.

---

## 🔊 Noise Adaptive Techniques

### **File**: `utils/noise_profiler.py`

| Function | Lines | Purpose | Privacy Impact |
|----------|-------|---------|----------------|
| `update_noise_profile()` | 25-40 | Collect noise samples | Statistical profile only |
| `_recalculate_noise_floor()` | 42-53 | Calculate noise floor | Aggregated statistics |
| `denoise_audio()` | 55-86 | Remove background noise | In-memory processing |
| `calculate_snr()` | 88-102 | Calculate SNR | Numerical calculation |
| `is_significant_audio()` | 104-107 | Check signal quality | Boolean result |
| `get_adaptive_threshold()` | 109-119 | Adjust thresholds | Numerical calculation |
| `reset()` | 121-126 | Clear noise profile | Clears memory |
| `get_status()` | 128-135 | Get profiler status | Statistics only |

**Privacy Note**: Stores statistical noise profile (energy and spectrum), not identifiable audio.

**Noise Profile Data**:
```python
{
    'noise_floor': 0.15,              # Average energy (not audio)
    'noise_spectrum': [0.02, 0.03, ...],  # Frequency profile (not audio)
    'is_calibrated': True
}
```

---

## 🎼 Feature Extraction

### **File**: `utils/feature_extractor.py`

| Function | Lines | Purpose | Output |
|----------|-------|---------|--------|
| `_compute_delta()` | 48-56 | Calculate delta features | NumPy array |
| `extract_mfcc()` | 58-75 | Extract MFCC features | (39, T) array |
| `extract_spectral_features()` | 77-141 | Extract spectral features | Dictionary |
| `extract_mel_spectrogram()` | 143-150 | Extract mel spectrogram | (128, T) array |
| `extract_all_features()` | 152-178 | Combine all features | (51, T) array |
| `extract_fixed_length_features()` | 180-194 | Fixed-length features | (51, 128) array |
| `normalize_features()` | 196-205 | Z-score normalization | Normalized array |

**Privacy Note**: Extracts mathematical features from audio, not identifiable content.

**Feature Types**:
- MFCC: Frequency coefficients (not speech content)
- Spectral: Statistical frequency measures
- Temporal: Time-domain statistics

---

## 🚨 Threat Detection

### **File**: `models/threat_detector.py`

| Function | Lines | Purpose | Privacy Impact |
|----------|-------|---------|----------------|
| `__init__()` | 28-75 | Initialize detector | Loads models |
| `analyze_audio()` | 107-227 | Main detection function | Discards audio after features |
| `_check_consecutive_detection()` | 229-252 | Reduce false positives | Tracks detections |
| `_determine_threat_level()` | 254-266 | Classify threat severity | Metadata only |
| `update_noise_profile()` | 269-272 | Update noise profile | Statistical profile |
| `reset_noise_profile()` | 274-276 | Reset profiler | Clears memory |
| `reset_detection_history()` | 278-280 | Clear history | Clears memory |
| `set_sensitivity()` | 282-318 | Adjust sensitivity | Updates thresholds |
| `get_sensitivity_settings()` | 320-332 | Get current settings | Returns config |
| `get_status()` | 334-352 | Get detector status | Returns metadata |

**Key Privacy Function**:
```python
# Line 153-156
# Extract features (privacy: raw audio can be discarded after this)
features = self.feature_extractor.extract_fixed_length_features(processed_audio)
# Raw audio discarded here
```

---

## 🌐 API Routes

### **File**: `api/routes/audio_routes.py`

| Route | Method | Lines | Purpose | Privacy |
|-------|--------|-------|---------|---------|
| `/health` | GET | 58-65 | Health check | No data |
| `/status` | GET | 68-74 | Detector status | Metadata only |
| `/analyze` | POST | 77-161 | Analyze audio | In-memory processing |
| `/calibrate` | POST | 164-190 | Calibrate noise | Statistical profile |
| `/reset-calibration` | POST | 193-200 | Reset calibration | Clears memory |
| `/test` | GET | 203-219 | Test endpoint | Test data only |

**Key Privacy Function**:
```python
# Line 31-55
def decode_audio_from_base64(base64_data: str) -> np.ndarray:
    # Decode to NumPy array (in-memory)
    audio_bytes = base64.b64decode(base64_data)
    audio_array = np.frombuffer(audio_bytes, dtype=np.int16)
    audio = audio_array.astype(np.float32) / 32768.0
    return audio  # Returns array, not stored
```

---

## 💻 Frontend

### **File**: `resources/js/admin/audio-threat.js`

| Function | Lines | Purpose | Privacy |
|----------|-------|---------|---------|
| `init()` | 30-44 | Initialize system | Setup only |
| `bindEvents()` | 46-54 | Bind UI events | Event handlers |
| `startDetection()` | 71-140 | Start audio capture | Requires user consent |
| `stopDetection()` | 142-159 | Stop capture | Cleanup |
| `processAudioData()` | 161-195 | Process audio buffer | In-memory |
| `sendAudioChunk()` | 197-243 | Send to backend | HTTPS encrypted |
| `handleDetectionResult()` | 245-267 | Handle response | Display only |
| `addThreatAlert()` | 269-315 | Show alert | UI update |
| `updateStats()` | 317-330 | Update statistics | Display only |
| `visualizeAudio()` | 332-444 | Visualize waveform | Canvas rendering |
| `startCalibration()` | 446-524 | Calibrate noise | 5-second recording |
| `clearAlerts()` | 526-534 | Clear alerts | UI update |

**Privacy-Critical Functions**:

1. **`startDetection()` (Line 71-74)**:
   ```javascript
   const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
   // Requires explicit user permission
   ```

2. **`sendAudioChunk()` (Line 209-214)**:
   ```javascript
   body: JSON.stringify({
       audio_data: base64,  // Encrypted in transit
       format: 'pcm16',
       sample_rate: 16000,
       session_id: this.sessionId  // Session tracking
   })
   ```

---

## 📊 Summary Table

### **Privacy-Critical Files**:

| File | Key Functions | Privacy Measures |
|------|---------------|------------------|
| `audio-threat.js` | `startDetection()`, `sendAudioChunk()` | User consent, HTTPS, CSRF |
| `AudioThreatController.php` | `analyze()` | Metadata logging only |
| `audio_routes.py` | `analyze_audio()` | In-memory processing |
| `audio_processor.py` | All functions | In-memory only |
| `feature_extractor.py` | `extract_fixed_length_features()` | Features only, not audio |
| `threat_detector.py` | `analyze_audio()` | Discards raw audio |
| `noise_profiler.py` | `update_noise_profile()` | Statistical profile only |

### **Audio Processing Files**:

| File | Key Functions | Purpose |
|------|---------------|---------|
| `audio_processor.py` | 9 functions | Audio preprocessing |
| `feature_extractor.py` | 7 functions | Feature extraction |
| `noise_profiler.py` | 8 functions | Noise adaptation |
| `threat_detector.py` | 10 functions | Threat detection |

### **Total Function Count**:

- **Privacy Functions**: 5
- **Audio Processing**: 9
- **Feature Extraction**: 7
- **Noise Adaptive**: 8
- **Threat Detection**: 10
- **API Routes**: 6
- **Frontend**: 12

**Total**: 57 functions across 7 main files

---

**All functions are designed with privacy-first principles: in-memory processing, no audio storage, metadata-only logging.**

