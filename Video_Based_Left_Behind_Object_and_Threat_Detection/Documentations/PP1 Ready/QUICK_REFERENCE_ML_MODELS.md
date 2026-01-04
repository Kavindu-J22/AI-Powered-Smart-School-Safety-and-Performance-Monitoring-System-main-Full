# Quick Reference: ML Models Implementation

## 🎯 At a Glance

### **Two Main AI Systems**

| System | Model | Purpose | Alert Time |
|--------|-------|---------|------------|
| **Left Behind Detection** | YOLOv8 | Find forgotten items | 60 minutes |
| **Threat Detection** | SlowFast | Detect violence/weapons | Immediate |

---

## 🎒 Left Behind Object Detection

### **How It Works (Simple Explanation)**

1. **Camera sees object** (e.g., backpack)
2. **AI identifies it** using YOLOv8 neural network
3. **System tracks it** over time using DeepSORT
4. **Checks if moving** - calculates position changes
5. **Waits 60 minutes** if stationary
6. **Sends alert** to security staff

### **Technical Details**

- **Model**: YOLOv8n (nano version for speed)
- **Accuracy**: 77.49% mAP50
- **Speed**: 85 FPS on RTX 3060
- **Detects**: 40+ object classes (bags, books, bottles, laptops, etc.)
- **Never flags**: Persons (people are not "left behind objects")

### **Key Files**

```
src/models/object_detector.py       # YOLOv8 implementation
src/tracking/object_tracker.py      # DeepSORT tracking
config/config.yaml                  # Configuration
```

---

## 🚨 Threat Detection

### **How It Works (Simple Explanation)**

1. **Collects 32 frames** (about 1 second of video)
2. **Analyzes motion** using Fast Pathway (16 FPS)
3. **Analyzes appearance** using Slow Pathway (2 FPS)
4. **Combines features** to understand action
5. **Classifies threat** (fighting, hitting, weapon, etc.)
6. **Immediate alert** if threat detected

### **Technical Details**

- **Model**: SlowFast Networks (dual-pathway)
- **Accuracy**: 73.97%
- **Speed**: 15 clips/second
- **Detects**: Fighting, hitting, pushing, aggressive behavior, weapons
- **Buffer**: Needs 32 frames before detection

### **Key Files**

```
src/models/threat_detector.py       # SlowFast implementation
config/config.yaml                  # Configuration
```

---

## 🔄 Object Tracking (DeepSORT)

### **What It Does**

- Assigns unique ID to each detected object
- Tracks objects across frames (even if temporarily hidden)
- Calculates movement distance
- Determines if object is stationary
- Checks time threshold for "left behind" status

### **Key Algorithms**

1. **Hungarian Algorithm**: Matches detections to existing tracks
2. **Kalman Filter**: Predicts next position
3. **IoU Matching**: Measures bounding box overlap

---

## 🌐 Integration with Laravel

### **Architecture**

```
Laravel (PHP) ←→ Flask API (Python) ←→ ML Models (PyTorch)
```

### **API Endpoints**

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/video/health` | GET | Check system status |
| `/api/video/detect-objects` | POST | Detect left-behind items |
| `/api/video/detect-threats` | POST | Detect threats |
| `/api/video/process-frame` | POST | Combined detection |

### **Data Flow**

1. **Frontend** (JavaScript) captures video frame
2. **Converts** to base64 image
3. **Sends** to Laravel controller
4. **Laravel** forwards to Flask API (port 5002)
5. **Flask** runs ML models
6. **Returns** JSON results
7. **Frontend** displays detections

---

## 📊 Performance Metrics

### **Object Detection (YOLOv8)**

- **Accuracy**: 77.49% mAP50
- **Speed**: 85 FPS (640x480)
- **Latency**: 12ms per frame
- **GPU Memory**: 2GB

### **Threat Detection (SlowFast)**

- **Accuracy**: 73.97%
- **Speed**: 15 clips/second
- **Latency**: 67ms per clip
- **GPU Memory**: 3GB

### **System Requirements**

- **Minimum**: GTX 1050, 8GB RAM, i5 CPU
- **Recommended**: RTX 3060, 16GB RAM, i7 CPU

---

## 🎓 Training the Models

### **Object Detector**

```bash
python scripts/train_object_detector.py \
    --data datasets/left_behind_objects/dataset.yaml \
    --epochs 100 \
    --batch 16
```

**Training Time**: ~2 hours on RTX 3060

### **Threat Detector**

```bash
python run_training.py --threat-epochs 50
```

**Training Time**: ~6 hours on RTX 3060

---

## ⚙️ Configuration

### **Key Settings** (`config/config.yaml`)

```yaml
object_detection:
  confidence_threshold: 0.25      # Lower = more sensitive
  left_behind_threshold: 60       # Minutes
  min_object_size: 200            # Pixels

threat_detection:
  confidence_threshold: 0.7       # Higher = more accurate
  clip_length: 32                 # Frames
  immediate_alert: true

tracking:
  max_age: 5                      # Frames without detection
  min_hits: 2                     # Detections to confirm
```

---

## 🔧 Common Tasks

### **Start the System**

```bash
# Activate virtual environment
cd Video_Based_Left_Behind_Object_and_Threat_Detection
venv\Scripts\activate

# Run Flask API
python app.py
```

### **Test Detection**

```bash
# Test with webcam
python main.py --source 0

# Test with video file
python main.py --source test_video.mp4
```

### **Check System Health**

```bash
curl http://localhost:5002/api/video/health
```

---

## 📚 Learn More

- **Full Documentation**: `ML_MODEL_IMPLEMENTATION_DOCUMENTATION.md`
- **Setup Guide**: `Video_Based_Left_Behind_Object_and_Threat_Detection/README.md`
- **Troubleshooting**: `Video_Based_Left_Behind_Object_and_Threat_Detection/Documentations/TROUBLESHOOTING.md`

---

**Last Updated**: January 2024
