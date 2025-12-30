# 🎤 Speech-to-Text Integration Guide

**Document Version**: 1.0  
**Last Updated**: December 30, 2025  
**System**: AI-Powered Smart School Safety - Audio Threat Detection

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Available Speech-to-Text Engines](#available-speech-to-text-engines)
3. [Current Implementation](#current-implementation)
4. [How to Add New STT Engines](#how-to-add-new-stt-engines)
5. [Configuration](#configuration)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The Audio Threat Detection System includes **Speech-to-Text (STT)** capabilities for detecting threatening speech content in both **English** and **Sinhala** languages.

### **Current Features**:
- ✅ **Google Speech Recognition** (online, free)
- ✅ **Vosk** (offline, optional)
- ✅ **Bilingual support** (English + Sinhala)
- ✅ **Threat keyword detection** (300+ keywords)
- ✅ **Profanity detection**
- ✅ **Automatic language detection**

### **File Location**:
```
Audio-Based_Threat_Detection/
├── models/
│   └── speech_threat_model.py    ← Main STT implementation
├── config/
│   └── __init__.py                ← Threat keywords configuration
└── api/routes/
    └── audio_routes.py            ← API endpoint
```

---

## 🔧 Available Speech-to-Text Engines

### **1. Google Speech Recognition** (Default - ACTIVE)

**Status**: ✅ **Currently Implemented**

**Pros**:
- Free to use (with limitations)
- High accuracy for English and Sinhala
- No setup required
- Cloud-based (always up-to-date)

**Cons**:
- Requires internet connection
- Rate limits (50 requests/day for free tier)
- Privacy concerns (audio sent to Google)

**Installation**:
```bash
pip install SpeechRecognition
```

**Code Location**: `models/speech_threat_model.py:76-123`

**Implementation**:
```python
import speech_recognition as sr

recognizer = sr.Recognizer()
audio = sr.AudioData(audio_bytes, sample_rate, 2)

# English
text = recognizer.recognize_google(audio, language='en-US')

# Sinhala
text = recognizer.recognize_google(audio, language='si-LK')
```

---

### **2. Vosk** (Offline - OPTIONAL)

**Status**: ⚠️ **Partially Implemented** (fallback only)

**Pros**:
- Completely offline
- No API limits
- Privacy-friendly (local processing)
- Fast inference

**Cons**:
- Lower accuracy than Google
- Requires model download (~50MB per language)
- English only (Sinhala models limited)

**Installation**:
```bash
pip install vosk
```

**Download Models**:
```bash
# English model
wget https://alphacephei.com/vosk/models/vosk-model-small-en-us-0.15.zip
unzip vosk-model-small-en-us-0.15.zip -d Audio-Based_Threat_Detection/models/vosk/
```

**Code Location**: `models/speech_threat_model.py:127-143`

**Implementation**:
```python
from vosk import Model as VoskModel, KaldiRecognizer
import json

# Load model
vosk_model = VoskModel("models/vosk/vosk-model-small-en-us-0.15")
rec = KaldiRecognizer(vosk_model, sample_rate)

# Recognize
rec.AcceptWaveform(audio_bytes)
result = json.loads(rec.FinalResult())
text = result.get('text')
```

---

### **3. Whisper (OpenAI)** - RECOMMENDED TO ADD

**Status**: ❌ **Not Implemented** (Recommended)

**Pros**:
- State-of-the-art accuracy
- Multilingual (99 languages including Sinhala)
- Offline capable
- Open source

**Cons**:
- Requires GPU for real-time processing
- Large model size (1.5GB for medium model)
- Higher latency (~2-3 seconds)

**Installation**:
```bash
pip install openai-whisper
```

**How to Add**:

1. **Install Whisper**:
```bash
cd Audio-Based_Threat_Detection
pip install openai-whisper
```

2. **Edit `models/speech_threat_model.py`**:

Add at the top (line 27):
```python
try:
    import whisper
    WHISPER_AVAILABLE = True
except ImportError:
    WHISPER_AVAILABLE = False
```

3. **Add to `__init__` method** (line 41):
```python
self.whisper_model = None
if WHISPER_AVAILABLE:
    # Load small model (faster, less accurate) or medium (slower, more accurate)
    self.whisper_model = whisper.load_model("small")  # or "medium"
```

4. **Add Whisper transcription method** (after line 144):
```python
def transcribe_with_whisper(self, audio_data: np.ndarray, sample_rate: int = 16000) -> Dict:
    """Transcribe using Whisper"""
    if not WHISPER_AVAILABLE or self.whisper_model is None:
        return {'text': '', 'error': 'Whisper not available'}
    
    try:
        # Whisper expects float32 audio
        result = self.whisper_model.transcribe(
            audio_data,
            language='en',  # or None for auto-detect
            task='transcribe'
        )
        
        return {
            'text': result['text'],
            'language': result.get('language', 'unknown'),
            'confidence': 0.9,  # Whisper doesn't provide confidence
            'engine': 'whisper',
            'error': None
        }
    except Exception as e:
        return {'text': '', 'error': f'Whisper error: {str(e)}'}
```

5. **Update `transcribe_audio` method** (line 48) to try Whisper first:
```python
def transcribe_audio(self, audio_data: np.ndarray, sample_rate: int = 16000) -> Dict:
    # ... existing validation code ...
    
    # Try Whisper first (best accuracy)
    if WHISPER_AVAILABLE and self.whisper_model:
        whisper_result = self.transcribe_with_whisper(audio_data, sample_rate)
        if whisper_result['text']:
            return whisper_result
    
    # Fallback to Google (existing code)
    if SPEECH_RECOGNITION_AVAILABLE:
        # ... existing Google code ...
```

---

### **4. Azure Speech Services** - ENTERPRISE OPTION

**Status**: ❌ **Not Implemented**

**Pros**:
- Very high accuracy
- Real-time streaming
- Sinhala support
- Enterprise-grade reliability

**Cons**:
- Paid service ($1 per hour of audio)
- Requires Azure account
- Internet required

**Installation**:
```bash
pip install azure-cognitiveservices-speech
```

**How to Add**:

1. **Get Azure API Key**:
   - Sign up at https://azure.microsoft.com/en-us/services/cognitive-services/speech-services/
   - Get API key and region

2. **Add to `.env`**:
```bash
AZURE_SPEECH_KEY=your_api_key_here
AZURE_SPEECH_REGION=eastus
```

3. **Add to `models/speech_threat_model.py`**:
```python
import os
try:
    import azure.cognitiveservices.speech as speechsdk
    AZURE_AVAILABLE = True
except ImportError:
    AZURE_AVAILABLE = False

class SpeechThreatDetector:
    def __init__(self):
        # ... existing code ...
        
        # Azure setup
        if AZURE_AVAILABLE:
            speech_key = os.getenv('AZURE_SPEECH_KEY')
            speech_region = os.getenv('AZURE_SPEECH_REGION')
            if speech_key and speech_region:
                self.azure_config = speechsdk.SpeechConfig(
                    subscription=speech_key,
                    region=speech_region
                )
    
    def transcribe_with_azure(self, audio_data: np.ndarray, sample_rate: int = 16000) -> Dict:
        """Transcribe using Azure"""
        if not AZURE_AVAILABLE or not hasattr(self, 'azure_config'):
            return {'text': '', 'error': 'Azure not configured'}
        
        try:
            # Convert to WAV format
            audio_bytes = (audio_data * 32767).astype(np.int16).tobytes()
            stream = speechsdk.audio.PushAudioInputStream()
            stream.write(audio_bytes)
            stream.close()
            
            audio_config = speechsdk.audio.AudioConfig(stream=stream)
            recognizer = speechsdk.SpeechRecognizer(
                speech_config=self.azure_config,
                audio_config=audio_config
            )
            
            result = recognizer.recognize_once()
            
            if result.reason == speechsdk.ResultReason.RecognizedSpeech:
                return {
                    'text': result.text,
                    'language': 'english',
                    'confidence': 0.95,
                    'engine': 'azure',
                    'error': None
                }
            else:
                return {'text': '', 'error': 'No speech recognized'}
        except Exception as e:
            return {'text': '', 'error': f'Azure error: {str(e)}'}
```

---

### **5. DeepSpeech (Mozilla)** - DEPRECATED

**Status**: ❌ **Not Recommended** (Project discontinued)

Mozilla DeepSpeech is no longer maintained. Use Whisper instead.

---

## 📝 Current Implementation

### **File**: `models/speech_threat_model.py`

**Class**: `SpeechThreatDetector`

**Main Methods**:

| Method | Purpose | Lines |
|--------|---------|-------|
| `__init__()` | Initialize STT engines | 32-46 |
| `transcribe_audio()` | Convert audio to text | 48-144 |
| `detect_threats()` | Find threat keywords | 146-218 |
| `analyze_audio()` | Full pipeline | 221-239 |

---

### **How It Works**:

```
Audio Input (NumPy array)
    ↓
Validation (length, energy check)
    ↓
Normalization (scale to [-1, 1])
    ↓
Try Google Speech Recognition (English)
    ↓ (if failed)
Try Google Speech Recognition (Sinhala)
    ↓ (if failed)
Try Vosk (offline fallback)
    ↓
Return transcription result
    ↓
Detect threat keywords
    ↓
Calculate threat score
    ↓
Return threat analysis
```

---

### **Example Usage**:

```python
from models.speech_threat_model import SpeechThreatDetector
import numpy as np

# Initialize
detector = SpeechThreatDetector()

# Analyze audio
audio_data = np.random.randn(16000)  # 1 second of audio
result = detector.analyze_audio(audio_data, sample_rate=16000)

print(result)
# Output:
# {
#     'transcription': {
#         'text': 'I will hurt you',
#         'language': 'english',
#         'confidence': 0.85,
#         'engine': 'google',
#         'error': None
#     },
#     'threat_analysis': {
#         'is_threat': True,
#         'threat_level': 'high',
#         'detected_keywords': [
#             {'keyword': 'hurt', 'type': 'threat', 'language': 'english'}
#         ],
#         'threat_score': 0.4
#     },
#     'is_threat': True,
#     'threat_level': 'high'
# }
```

---

## ⚙️ Configuration

### **File**: `config/__init__.py`

**Threat Keywords** (Lines 44-304):

```python
class ThreatKeywords:
    # English threats (100+ keywords)
    ENGLISH_THREATS = [
        "I will kill you", "I'm going to hurt you",
        "I'll shoot you", "bomb threat", ...
    ]
    
    # English profanity (50+ words)
    PROFANITY_ENGLISH = [
        "fuck", "shit", "bitch", ...
    ]
    
    # Sinhala threats (100+ keywords)
    SINHALA_THREATS = [
        "මරනවා", "ගහනවා", "තල්ලු කරනවා", ...
    ]
    
    # Sinhala profanity (50+ words)
    PROFANITY_SINHALA = [
        "හුත්තා", "පකෝ", "බල්ලා", ...
    ]
```

**Model Configuration** (Lines 23-42):

```python
class ModelConfig:
    # Speech detection threshold
    SPEECH_THREAT_THRESHOLD = 0.6  # 60% confidence
    
    # Latency target
    MAX_LATENCY = 3.0  # 3 seconds
```

---

### **How to Add Custom Keywords**:

1. **Edit** `config/__init__.py`

2. **Add to English threats** (line 47):
```python
ENGLISH_THREATS = [
    # ... existing keywords ...
    "your new threat phrase",
    "another dangerous keyword",
]
```

3. **Add to Sinhala threats** (line 150):
```python
SINHALA_THREATS = [
    # ... existing keywords ...
    "ඔබේ නව තර්ජනය",
]
```

4. **Restart the API**:
```bash
cd Audio-Based_Threat_Detection
python app.py
```

---

## 🧪 Testing

### **Test Script**: `test_speech_detection.py`

Create this file in `Audio-Based_Threat_Detection/`:

```python
"""Test speech-to-text detection"""
import numpy as np
from models.speech_threat_model import SpeechThreatDetector

def test_speech_detection():
    detector = SpeechThreatDetector()
    
    # Test 1: Keyword detection (no audio needed)
    print("Test 1: Keyword Detection")
    result = detector.detect_threats("I will kill you", "english")
    print(f"  Is Threat: {result['is_threat']}")
    print(f"  Threat Level: {result['threat_level']}")
    print(f"  Keywords: {result['detected_keywords']}")
    print()
    
    # Test 2: Sinhala keyword detection
    print("Test 2: Sinhala Keyword Detection")
    result = detector.detect_threats("මරනවා", "sinhala")
    print(f"  Is Threat: {result['is_threat']}")
    print(f"  Threat Level: {result['threat_level']}")
    print()
    
    # Test 3: Normal speech
    print("Test 3: Normal Speech")
    result = detector.detect_threats("Hello, how are you?", "english")
    print(f"  Is Threat: {result['is_threat']}")
    print()

if __name__ == '__main__':
    test_speech_detection()
```

**Run**:
```bash
cd Audio-Based_Threat_Detection
python test_speech_detection.py
```

**Expected Output**:
```
Test 1: Keyword Detection
  Is Threat: True
  Threat Level: high
  Keywords: [{'keyword': 'kill', 'type': 'threat', 'language': 'english'}]

Test 2: Sinhala Keyword Detection
  Is Threat: True
  Threat Level: medium

Test 3: Normal Speech
  Is Threat: False
```

---

## 🔍 Troubleshooting

### **Problem 1: "Speech recognition not available"**

**Cause**: `SpeechRecognition` library not installed

**Solution**:
```bash
pip install SpeechRecognition
```

---

### **Problem 2: "Google API error: [Errno 11001] getaddrinfo failed"**

**Cause**: No internet connection

**Solution**:
- Check internet connection
- Or install Vosk for offline recognition

---

### **Problem 3: "Could not understand audio"**

**Cause**: Audio too short, too quiet, or no speech

**Solution**:
- Ensure audio is at least 1.5 seconds
- Check audio energy (should be > 0.005)
- Speak clearly and loudly

---

### **Problem 4: Low accuracy for Sinhala**

**Cause**: Google's Sinhala model has lower accuracy

**Solution**:
- Use Whisper (better multilingual support)
- Increase audio quality
- Speak more clearly

---

### **Problem 5: High latency (> 3 seconds)**

**Cause**: Google API network delay

**Solution**:
- Use Vosk (offline, faster)
- Use Whisper with GPU
- Reduce audio chunk size

---

## 📊 Comparison Table

| Engine | Accuracy | Speed | Offline | Sinhala | Cost | Recommendation |
|--------|----------|-------|---------|---------|------|----------------|
| **Google** | ⭐⭐⭐⭐ | ⭐⭐⭐ | ❌ | ✅ | Free* | ✅ Default |
| **Whisper** | ⭐⭐⭐⭐⭐ | ⭐⭐ | ✅ | ✅ | Free | ✅ **Best** |
| **Vosk** | ⭐⭐⭐ | ⭐⭐⭐⭐ | ✅ | ⚠️ | Free | ⚠️ Fallback |
| **Azure** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ❌ | ✅ | Paid | 💼 Enterprise |

**Recommendation**: Add **Whisper** for best accuracy and offline support.

---

## ✅ Summary

**Current Status**:
- ✅ Google Speech Recognition (active)
- ⚠️ Vosk (fallback, optional)
- ✅ 300+ threat keywords (English + Sinhala)
- ✅ Automatic language detection

**Recommended Additions**:
1. **Whisper** (best accuracy, offline)
2. **Azure** (enterprise option)

**Next Steps**:
1. Install Whisper: `pip install openai-whisper`
2. Follow "How to Add Whisper" section above
3. Test with `test_speech_detection.py`
4. Update documentation

**Files to Edit**:
- `models/speech_threat_model.py` (add new engines)
- `config/__init__.py` (add keywords)
- `requirements.txt` (add dependencies)

