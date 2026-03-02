# 🎯 Prediction Confidence Calculation Guide

## Overview
This guide explains how prediction confidence is calculated and displayed in the Audio-Based Threat Detection System for both **Speech** and **Non-Speech** models.

---

## 📊 Table of Contents
1. [Non-Speech Model Confidence](#non-speech-model-confidence)
2. [Speech Model Confidence](#speech-model-confidence)
3. [Overall Confidence Calculation](#overall-confidence-calculation)
4. [How to Display Confidence](#how-to-display-confidence)
5. [Key Files Reference](#key-files-reference)

---

## 🔊 Non-Speech Model Confidence

### **File**: `models/non_speech_model.py` (Lines 210-230)

### Calculation Process:

```python
def predict(self, features: np.ndarray) -> tuple:
    """Predict threat class and confidence"""
    # 1. Forward pass through neural network
    with torch.no_grad():
        x = torch.FloatTensor(features).to(self.device)
        outputs = self.model(x)
        
        # 2. Apply Softmax to get probabilities
        probabilities = torch.softmax(outputs, dim=1)[0].cpu().numpy()
    
    # 3. Get class with highest probability
    class_idx = np.argmax(probabilities)
    
    # 4. Confidence = Maximum probability value
    confidence = float(probabilities[class_idx])
    
    # 5. Get class name
    class_name = self.classes[class_idx]
    
    return class_name, confidence, probabilities.tolist()
```

### Formula:
```
Confidence = max(P(crying), P(screaming), P(shouting), P(glass_breaking), P(normal))
```

Where each P(class) is the softmax probability for that class.

### Example Output:
```
All Probabilities:
  crying              : 0.1234 (12.3%)
  screaming           : 0.9876 (98.8%)  ← Highest
  shouting            : 0.0543 (5.4%)
  glass_breaking      : 0.0123 (1.2%)
  normal              : 0.0456 (4.6%)

Detected Class: screaming
Confidence: 0.9876 (98.8%)
```

---

## 🗣️ Speech Model Confidence

### **File**: `models/speech_threat_model.py` (Lines 166-239)

### Calculation Process:

```python
def detect_threats(self, text: str, language: str = 'english') -> Dict:
    """Detect threatening content in transcribed text"""
    
    detected_keywords = []
    threat_score = 0.0
    
    # 1. Check for severe threats
    for threat in dangerous_words:  # kill, murder, shoot, gun, bomb
        if word_found:
            threat_score += 0.5  # High severity
    
    # 2. Check for regular threats
    for threat in english_threats:  # hurt, attack, etc.
        if word_found:
            threat_score += 0.3  # Medium severity
    
    # 3. Check for profanity
    for profanity in english_profanity:
        if word_found:
            threat_score += 0.15  # Low severity
    
    # 4. Cap at 1.0
    threat_score = min(threat_score, 1.0)
    
    return {
        'threat_score': threat_score,  # This is the confidence
        'is_threat': threat_score > 0,
        'detected_keywords': detected_keywords
    }
```

### Formula:
```
threat_score = min(
    Σ(severe_threats × 0.5) + 
    Σ(regular_threats × 0.3) + 
    Σ(profanity × 0.15),
    1.0
)
```

### Threat Level Mapping (Lines 231-239):
```python
if threat_score >= 0.5:
    threat_level = 'high'      # 50%+ confidence
elif threat_score >= 0.3:
    threat_level = 'medium'    # 30-49% confidence
elif threat_score > 0:
    threat_level = 'low'       # 1-29% confidence
else:
    threat_level = 'none'      # 0% confidence
```

### Example Output:
```
Transcribed Text: "I will kill you"
Detected Keywords: [
    {'keyword': 'kill', 'type': 'threat', 'language': 'english'}
]
Threat Score (Confidence): 0.5 (50%)
Threat Level: high
```

---

## 🎯 Overall Confidence Calculation

### **File**: `models/threat_detector.py` (Lines 107-267)

### Combined Confidence Logic:

```python
def analyze_audio(self, audio_data: np.ndarray) -> Dict:
    result = {
        'is_threat': False,
        'confidence': 0.0,
        'threat_level': 'none'
    }
    
    # 1. Non-Speech Detection
    if enable_non_speech:
        class_name, confidence, all_probs = self.non_speech_model.predict(features)
        
        if confirmed_threat:
            result['confidence'] = confidence  # Use non-speech confidence
    
    # 2. Speech Detection
    if enable_speech:
        speech_result = self.speech_detector.analyze_audio(audio)
        
        if speech_result['is_threat']:
            speech_score = speech_result['threat_score']
            # Take maximum of both confidences
            result['confidence'] = max(result['confidence'], speech_score)
    
    # 3. Map to Overall Threat Level (Lines 245-254)
    if result['is_threat']:
        if result['confidence'] >= 0.8:
            result['threat_level'] = 'critical'  # 80%+
        elif result['confidence'] >= 0.6:
            result['threat_level'] = 'high'      # 60-79%
        elif result['confidence'] >= 0.4:
            result['threat_level'] = 'medium'    # 40-59%
        else:
            result['threat_level'] = 'low'       # <40%
    
    return result
```

### Formula:
```
Overall Confidence = max(non_speech_confidence, speech_threat_score)
```

---

## 📺 How to Display Confidence

### **Method 1: API Response (JSON)**

**File**: `api/routes/detection_routes.py` (Lines 68-109)

When you call the `/api/detection/process-chunk` endpoint, you get:

```json
{
  "success": true,
  "is_threat": true,
  "threat_type": "non_speech",
  "threat_level": "high",
  "confidence": 0.9876,

  "non_speech_result": {
    "detected_class": "screaming",
    "confidence": 0.9876,
    "is_threat": true,
    "all_probabilities": {
      "crying": 0.1234,
      "screaming": 0.9876,
      "shouting": 0.0543,
      "glass_breaking": 0.0123,
      "normal": 0.0456
    },
    "threshold_used": 0.96,
    "class_threshold": 0.96
  },

  "speech_result": {
    "text": "help me",
    "is_threat": false,
    "threat_level": "none",
    "threat_score": 0.0,
    "detected_keywords": []
  },

  "processing_time": 0.234,
  "latency_ok": true
}
```

### **Method 2: Console Output (Debug)**

**File**: `test_detection_debug.py` (Lines 34-46)

```python
if result.get('non_speech_result'):
    ns = result['non_speech_result']
    print(f"\nDetected Class: {ns['detected_class']}")
    print(f"Confidence: {ns['confidence']:.4f}")
    print(f"Class Threshold: {ns.get('class_threshold', 'N/A'):.4f}")
    print(f"Adaptive Threshold: {ns.get('threshold_used', 'N/A'):.4f}")
    print(f"Initial Detection: {ns.get('initial_detection', False)}")
    print(f"Confirmed Threat: {ns['is_threat']}")

    print(f"\nAll Probabilities:")
    for cls, prob in ns['all_probabilities'].items():
        print(f"  {cls:20s}: {prob:.4f} ({prob*100:.1f}%)")

print(f"\nFinal Result: {'THREAT' if result['is_threat'] else 'SAFE'}")
print(f"Threat Type: {result.get('threat_type', 'None')}")
print(f"Overall Confidence: {result['confidence']:.2%}")
print(f"Threat Level: {result['threat_level']}")
```

**Output Example:**
```
Detected Class: screaming
Confidence: 0.9876
Class Threshold: 0.9600
Adaptive Threshold: 0.9600
Initial Detection: True
Confirmed Threat: True

All Probabilities:
  crying              : 0.1234 (12.3%)
  screaming           : 0.9876 (98.8%)
  shouting            : 0.0543 (5.4%)
  glass_breaking      : 0.0123 (1.2%)
  normal              : 0.0456 (4.6%)

Final Result: THREAT
Threat Type: non_speech
Overall Confidence: 98.76%
Threat Level: critical
```

---

## 🔑 Key Files Reference

### **1. Non-Speech Model Prediction**
- **File**: `models/non_speech_model.py`
- **Function**: `predict()` (Lines 210-230)
- **Returns**: `(class_name, confidence, all_probabilities)`

### **2. Speech Threat Detection**
- **File**: `models/speech_threat_model.py`
- **Function**: `detect_threats()` (Lines 166-239)
- **Returns**: `{'threat_score': float, 'is_threat': bool, 'threat_level': str}`

### **3. Overall Analysis**
- **File**: `models/threat_detector.py`
- **Function**: `analyze_audio()` (Lines 107-267)
- **Returns**: Complete result dictionary with combined confidence

### **4. API Endpoint**
- **File**: `api/routes/detection_routes.py`
- **Endpoint**: `POST /api/detection/process-chunk` (Lines 68-109)
- **Returns**: JSON response with all confidence data

### **5. Debug/Testing**
- **File**: `test_detection_debug.py`
- **Functions**: `test_normal_audio()`, `test_high_energy_audio()`
- **Shows**: Detailed console output with all probabilities

---

## 📈 Confidence Thresholds

### **Non-Speech Class Thresholds** (`threat_detector.py` Lines 49-55)
```python
self.class_thresholds = {
    'crying': 0.82,           # Requires 82% confidence
    'screaming': 0.96,        # Requires 96% confidence
    'shouting': 0.97,         # Requires 97% confidence
    'glass_breaking': 0.78,   # Requires 78% confidence
    'normal': 0.0             # Always allowed
}
```

### **Overall Threat Level Mapping** (`threat_detector.py` Lines 245-254)
```python
if confidence >= 0.8:
    threat_level = 'critical'  # 80-100%
elif confidence >= 0.6:
    threat_level = 'high'      # 60-79%
elif confidence >= 0.4:
    threat_level = 'medium'    # 40-59%
else:
    threat_level = 'low'       # 0-39%
```

---

## 🧪 Testing Confidence Display

Run the debug script to see confidence in action:

```bash
cd Audio-Based_Threat_Detection
python test_detection_debug.py
```

This will show:
- Current detector settings
- All class probabilities
- Confidence scores
- Threshold comparisons
- Final threat determination

---

## 💡 Summary

### **Non-Speech Confidence:**
- Calculated using **Softmax probabilities** from neural network
- Confidence = **Maximum probability** among all classes
- Must exceed **class-specific thresholds** to trigger alert

### **Speech Confidence:**
- Calculated by **keyword matching** and scoring
- Severe threats: +0.5, Regular threats: +0.3, Profanity: +0.15
- Capped at **1.0 maximum**

### **Overall Confidence:**
- Takes the **maximum** of both model confidences
- Maps to threat levels: **Critical (80%+), High (60%+), Medium (40%+), Low (<40%)**
- Displayed in API responses, console output, and web dashboards

---

**For more information, see:**
- `THREAT_LEVEL_FILES_GUIDE.md` - Detailed threat level logic
- `NOISE_ADAPTIVE_TECHNIQUES.md` - Adaptive threshold adjustments
- `DOCUMENTATION.md` - Complete system documentation
