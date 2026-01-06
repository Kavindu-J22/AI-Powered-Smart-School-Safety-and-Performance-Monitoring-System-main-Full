# 🎯 Confidence Calculation - Quick Reference

## 📋 Quick Summary

| Model Type | Calculation Method | Range | Key File |
|------------|-------------------|-------|----------|
| **Non-Speech** | Softmax probabilities (max value) | 0.0 - 1.0 | `models/non_speech_model.py:210-230` |
| **Speech** | Keyword scoring (sum of weights) | 0.0 - 1.0 | `models/speech_threat_model.py:166-239` |
| **Overall** | Maximum of both models | 0.0 - 1.0 | `models/threat_detector.py:107-267` |

---

## 🔊 Non-Speech Confidence Formula

```
Step 1: Neural Network → Raw Outputs
Step 2: Apply Softmax → Probabilities for each class
Step 3: Confidence = max(P(crying), P(screaming), P(shouting), P(glass_breaking), P(normal))
Step 4: Detected Class = class with highest probability
```

**Example:**
```
Probabilities:
  crying: 0.1234 (12.3%)
  screaming: 0.9876 (98.8%) ← Maximum
  shouting: 0.0543 (5.4%)
  glass_breaking: 0.0123 (1.2%)
  normal: 0.0456 (4.6%)

Result:
  Detected Class: screaming
  Confidence: 0.9876 (98.8%)
```

---

## 🗣️ Speech Confidence Formula

```
threat_score = 0.0

For each detected keyword:
  - Severe threats (kill, murder, bomb, shoot, gun): +0.5
  - Regular threats (hurt, attack, weapon, stab): +0.3
  - Profanity: +0.15

threat_score = min(sum of all scores, 1.0)
```

**Example:**
```
Text: "I will kill you with a gun"

Detected Keywords:
  - "kill" → +0.5 (severe threat)
  - "gun" → +0.5 (severe threat)

threat_score = min(0.5 + 0.5, 1.0) = 1.0 (100%)
```

---

## 🎯 Overall Confidence Formula

```
overall_confidence = max(non_speech_confidence, speech_threat_score)
```

**Example:**
```
Non-Speech Confidence: 0.65 (screaming detected)
Speech Threat Score: 0.30 (mild threat detected)

Overall Confidence = max(0.65, 0.30) = 0.65 (65%)
```

---

## 📊 Threat Level Mapping

| Confidence Range | Threat Level | Color Code | Action |
|-----------------|--------------|------------|--------|
| **0.80 - 1.00** | Critical 🔴 | Red | Immediate alert |
| **0.60 - 0.79** | High 🟠 | Orange | High priority alert |
| **0.40 - 0.59** | Medium 🟡 | Yellow | Monitor closely |
| **0.00 - 0.39** | Low 🟢 | Green | Log for review |
| **0.00** | None ⚪ | White | No threat |

**Code Location:** `models/threat_detector.py` Lines 245-254

---

## 🎚️ Class-Specific Thresholds

| Class | Threshold | Reason |
|-------|-----------|--------|
| **crying** | 0.82 (82%) | Moderate sensitivity |
| **screaming** | 0.96 (96%) | Very high - avoid false positives |
| **shouting** | 0.97 (97%) | Very high - avoid false positives |
| **glass_breaking** | 0.78 (78%) | Lower - important safety event |
| **normal** | 0.00 (0%) | Always allowed |

**Code Location:** `models/threat_detector.py` Lines 49-55

**Note:** Confidence must exceed the threshold for the detected class to trigger an alert.

---

## 📺 How to Access Confidence in Code

### **Python (Direct)**
```python
from models.threat_detector import ThreatDetector

detector = ThreatDetector()
result = detector.analyze_audio(audio_data)

# Overall confidence
overall_confidence = result['confidence']
threat_level = result['threat_level']

# Non-speech details
if result.get('non_speech_result'):
    ns_confidence = result['non_speech_result']['confidence']
    detected_class = result['non_speech_result']['detected_class']
    all_probs = result['non_speech_result']['all_probabilities']

# Speech details
if result.get('speech_result'):
    speech_score = result['speech_result']['threat_score']
    detected_text = result['speech_result']['text']
```

### **API (HTTP)**
```bash
curl -X POST http://localhost:5000/api/detection/process-chunk \
  -H "Content-Type: application/json" \
  -d '{"audio_data": "base64_encoded_audio"}'
```

**Response:**
```json
{
  "confidence": 0.9876,
  "threat_level": "critical",
  "non_speech_result": {
    "confidence": 0.9876,
    "detected_class": "screaming",
    "all_probabilities": {
      "crying": 0.1234,
      "screaming": 0.9876,
      "shouting": 0.0543,
      "glass_breaking": 0.0123,
      "normal": 0.0456
    }
  }
}
```

---

## 🧪 Testing Confidence

### **Run Debug Script**
```bash
cd Audio-Based_Threat_Detection
python test_detection_debug.py
```

### **Run Example Display Script**
```bash
cd Audio-Based_Threat_Detection
python example_confidence_display.py
```

This will show:
- ✅ Simple display format
- 📊 Detailed display with all probabilities
- 📈 Visual display with progress bars
- 📋 JSON API response format

---

## 🔍 Key Differences

| Aspect | Non-Speech | Speech |
|--------|------------|--------|
| **Input** | Audio features (MFCC, spectral) | Transcribed text |
| **Method** | Neural network classification | Keyword matching |
| **Output** | Softmax probabilities | Weighted score sum |
| **Precision** | High (4 decimal places) | Medium (keyword-based) |
| **Speed** | Fast (~0.1s) | Slower (~0.5s, needs transcription) |
| **Reliability** | Very high with proper training | Depends on transcription quality |

---

## 📁 File Locations

| Component | File Path | Key Lines |
|-----------|-----------|-----------|
| Non-Speech Prediction | `models/non_speech_model.py` | 210-230 |
| Speech Threat Detection | `models/speech_threat_model.py` | 166-239 |
| Overall Analysis | `models/threat_detector.py` | 107-267 |
| Threat Level Mapping | `models/threat_detector.py` | 245-254 |
| Class Thresholds | `models/threat_detector.py` | 49-55 |
| API Endpoint | `api/routes/detection_routes.py` | 68-109 |
| Debug Script | `test_detection_debug.py` | 1-123 |
| Example Display | `example_confidence_display.py` | 1-180 |

---

## 💡 Tips

1. **For Production:** Use the API endpoint and parse JSON responses
2. **For Debugging:** Use `test_detection_debug.py` to see all probabilities
3. **For Integration:** Use `example_confidence_display.py` as a template
4. **For Visualization:** Display confidence as percentage with progress bars
5. **For Alerts:** Use threat_level to determine alert priority

---

**See Also:**
- `CONFIDENCE_CALCULATION_GUIDE.md` - Detailed explanation with code examples
- `THREAT_LEVEL_FILES_GUIDE.md` - Threat level logic and thresholds
- `DOCUMENTATION.md` - Complete system documentation

