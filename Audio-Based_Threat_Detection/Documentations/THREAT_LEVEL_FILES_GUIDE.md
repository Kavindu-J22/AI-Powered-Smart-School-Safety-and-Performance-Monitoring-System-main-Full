# 🎯 Files That Identify Threat Levels in the System

This document lists all files involved in threat level identification and their specific roles.

---

## 📁 Core Files for Threat Level Identification

### **1. `models/threat_detector.py`** ⭐ MAIN FILE
**Role:** Main orchestrator - combines all detection methods and assigns final threat levels

**Key Functions:**
- `analyze_audio()` - Lines 107-267
  - Combines non-speech and speech detection
  - **Determines overall threat level** (Lines 245-254)
  
**Threat Level Logic (Lines 245-254):**
```python
if result['is_threat']:
    if result['confidence'] >= 0.8:
        result['threat_level'] = 'critical'  # 80%+ confidence
    elif result['confidence'] >= 0.6:
        result['threat_level'] = 'high'      # 60-79% confidence
    elif result['confidence'] >= 0.4:
        result['threat_level'] = 'medium'    # 40-59% confidence
    else:
        result['threat_level'] = 'low'       # <40% confidence
```

**Class-Specific Thresholds (Lines 49-55):**
```python
self.class_thresholds = {
    'crying': 0.82,           # 82% confidence needed
    'screaming': 0.96,        # 96% confidence needed
    'shouting': 0.97,         # 97% confidence needed
    'glass_breaking': 0.78,   # 78% confidence needed
    'normal': 0.0             # Always allow
}
```

---

### **2. `models/speech_threat_model.py`** 🗣️ SPEECH ANALYSIS
**Role:** Analyzes speech content and assigns threat levels based on keywords

**Key Functions:**
- `detect_threats()` - Lines 166-249
  - Detects threatening keywords in transcribed text
  - **Calculates threat score and assigns level**

**Threat Level Logic (Lines 231-239):**
```python
if threat_score >= 0.5:
    threat_level = 'high'      # Critical keywords detected
elif threat_score >= 0.3:
    threat_level = 'medium'    # Serious threats detected
elif threat_score > 0:
    threat_level = 'low'       # Mild profanity/insults
else:
    threat_level = 'none'      # No threats
```

**Keyword Scoring (Lines 167-196):**
- Critical keywords (kill, gun, bomb): **+0.5 points**
- High keywords (hurt, assault, threat): **+0.3 points**
- Profanity: **+0.15 points**
- Sinhala threats: **+0.45 points**

---

### **3. `models/non_speech_model.py`** 🔊 NON-SPEECH ANALYSIS
**Role:** Detects non-verbal threats (screaming, crying, glass breaking)

**Key Functions:**
- `predict()` - Lines 210-230
  - Predicts threat class and confidence
  - Returns: class_name, confidence, probabilities

**Output:**
- Class: crying, screaming, shouting, glass_breaking, normal
- Confidence: 0.0 to 1.0
- Used by `threat_detector.py` to determine final threat level

---

### **4. `config/__init__.py`** ⚙️ CONFIGURATION
**Role:** Defines threat keywords and detection thresholds

**Key Classes:**
- `ModelConfig` (Lines 23-42)
  - `NON_SPEECH_THRESHOLD = 0.7`
  - `SPEECH_THREAT_THRESHOLD = 0.6`
  - `MAX_LATENCY = 3.0`

- `ThreatKeywords` (Lines 45-304)
  - `ENGLISH_THREATS` - 200+ keywords
  - `SINHALA_THREATS` - 200+ keywords
  - `PROFANITY_ENGLISH` - 100+ words
  - `PROFANITY_SINHALA` - 100+ words

---

## 🔄 Threat Level Flow

```
Audio Input
    ↓
[threat_detector.py] - Main Orchestrator
    ↓
    ├─→ [non_speech_model.py] - Detects crying, screaming, etc.
    │   └─→ Returns: class + confidence
    │
    ├─→ [speech_threat_model.py] - Analyzes speech content
    │   ├─→ Transcribes audio to text
    │   ├─→ Matches keywords from [config/__init__.py]
    │   ├─→ Calculates threat_score
    │   └─→ Returns: threat_level (high/medium/low/none)
    │
    └─→ [threat_detector.py] - Combines results
        └─→ Final threat_level: critical/high/medium/low/none
```

---

## 📊 Supporting Files

### **5. `utils/noise_profiler.py`**
**Role:** Adjusts thresholds based on ambient noise
- `get_adaptive_threshold()` - Increases thresholds in noisy environments

### **6. `utils/feature_extractor.py`**
**Role:** Extracts audio features (MFCC, spectral) for model input

### **7. `utils/audio_processor.py`**
**Role:** Preprocesses audio (normalization, resampling)

### **8. `api/routes/audio_routes.py`**
**Role:** API endpoint that receives audio and returns threat analysis
- `/analyze` endpoint - Calls `threat_detector.analyze_audio()`

---

## 🎯 Summary Table

| File | Role | Threat Levels Assigned |
|------|------|----------------------|
| **threat_detector.py** | Main orchestrator | ✅ **critical, high, medium, low, none** |
| **speech_threat_model.py** | Speech analysis | ✅ **high, medium, low, none** |
| **non_speech_model.py** | Non-speech detection | ❌ (Returns confidence only) |
| **config/__init__.py** | Configuration | ❌ (Defines thresholds & keywords) |
| **noise_profiler.py** | Adaptive thresholds | ❌ (Adjusts thresholds) |
| **audio_routes.py** | API endpoint | ❌ (Passes through results) |

---

## 🔍 How to Find Threat Level Code

### Search for these patterns in files:

1. **Threat level assignment:**
   ```python
   threat_level = 'critical'
   threat_level = 'high'
   threat_level = 'medium'
   threat_level = 'low'
   ```

2. **Threat score calculation:**
   ```python
   threat_score += 0.5  # Critical
   threat_score += 0.3  # High
   threat_score += 0.15 # Medium/Low
   ```

3. **Confidence thresholds:**
   ```python
   if confidence >= 0.8:  # Critical
   if confidence >= 0.6:  # High
   if confidence >= 0.4:  # Medium
   ```

---

## 📝 Quick Reference

**To modify threat levels, edit these files:**

1. **Overall threat levels** → `models/threat_detector.py` (Lines 245-254)
2. **Speech threat levels** → `models/speech_threat_model.py` (Lines 231-239)
3. **Keyword scores** → `models/speech_threat_model.py` (Lines 167-196)
4. **Class thresholds** → `models/threat_detector.py` (Lines 49-55)
5. **Threat keywords** → `config/__init__.py` (Lines 45-304)

---

**Last Updated:** 2026-01-05

