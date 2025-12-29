# Project Overview
## Video-Based Left Behind Object and Threat Detection System

**Status**: ✅ **Implementation Complete - Ready for Deployment**

---

## 🎯 Project Goals

This system addresses two critical school safety challenges:

1. **Left-Behind Object Management**
   - Automatically detect items left in classrooms
   - Alert security staff after 1 hour
   - Reduce manual monitoring effort
   - Improve object retrieval process

2. **Threat Detection and Prevention**
   - Real-time detection of aggressive behavior
   - Immediate alerts to administrators
   - Enable rapid intervention
   - Enhance student safety

---

## ✅ Implementation Status

### All Core Components Completed

| Component | Status | Files |
|-----------|--------|-------|
| Object Detection | ✅ Complete | `src/models/object_detector.py` |
| Threat Detection | ✅ Complete | `src/models/threat_detector.py` |
| Object Tracking | ✅ Complete | `src/tracking/object_tracker.py` |
| Alert System | ✅ Complete | `src/notifications/alert_system.py` |
| ESP32-CAM Firmware | ✅ Complete | `firmware/esp32_cam/esp32_cam_stream.ino` |
| Main Integration | ✅ Complete | `main.py` |
| Configuration | ✅ Complete | `config/config.yaml` |
| Documentation | ✅ Complete | Multiple `.md` files |

---

## 📁 Project Structure

```
Video_Based_Left_Behind_Object_and_Threat_Detection/
│
├── 📄 README.md                          # Main documentation
├── 📄 QUICK_START.md                     # 30-minute setup guide
├── 📄 DATASET_PREPARATION_GUIDE.md       # Dataset collection guide
├── 📄 ESP32_CAM_SETUP_GUIDE.md          # Hardware setup guide
├── 📄 IMPLEMENTATION_SUMMARY.md          # Implementation details
├── 📄 PROJECT_OVERVIEW.md                # This file
│
├── 📂 config/
│   └── config.yaml                       # System configuration
│
├── 📂 src/
│   ├── 📂 models/
│   │   ├── object_detector.py           # YOLOv8 object detection
│   │   └── threat_detector.py           # SlowFast threat detection
│   ├── 📂 tracking/
│   │   └── object_tracker.py            # Multi-object tracking
│   └── 📂 notifications/
│       └── alert_system.py              # Multi-channel alerts
│
├── 📂 firmware/
│   └── 📂 esp32_cam/
│       └── esp32_cam_stream.ino         # ESP32-CAM firmware
│
├── 📂 scripts/
│   ├── download_models.py               # Model downloader
│   ├── test_camera.py                   # Camera tester
│   └── train_object_detector.py         # Training script
│
├── 📂 data/
│   ├── 📂 left_behind_objects/
│   │   └── dataset.yaml                 # Dataset config
│   └── 📂 snapshots/                    # Alert images
│
├── 📂 models/                            # Trained models
├── 📂 logs/                              # System logs
│
├── main.py                               # Main application
├── requirements.txt                      # Dependencies
├── .env.example                          # Environment template
└── .gitignore                            # Git ignore rules
```

---

## 🚀 Quick Start

### 1. Installation (5 minutes)

```bash
# Clone/download project
cd Video_Based_Left_Behind_Object_and_Threat_Detection

# Create virtual environment
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt
```

### 2. Download Models (5 minutes)

```bash
python scripts/download_models.py --model yolov8n
```

### 3. Run with Webcam (2 minutes)

```bash
python main.py --camera TEST --source 0
```

**That's it!** The system is now running.

For detailed instructions, see [QUICK_START.md](QUICK_START.md)

---

## 🔧 Key Features

### Object Detection
- ✅ YOLOv8-based detection
- ✅ 7+ object classes (backpack, book, bottle, etc.)
- ✅ Real-time processing
- ✅ Configurable thresholds
- ✅ Custom training support

### Threat Detection
- ✅ Video action recognition
- ✅ SlowFast/X3D models
- ✅ Fighting, pushing, aggression detection
- ✅ Temporal analysis
- ✅ Confidence scoring

### Object Tracking
- ✅ Multi-object tracking
- ✅ Stationary detection
- ✅ 1-hour threshold for left-behind
- ✅ Movement analysis
- ✅ Track lifecycle management

### Alert System
- ✅ Email notifications (SMTP)
- ✅ Telegram messages
- ✅ SMS alerts (Twilio)
- ✅ Image attachments
- ✅ Alert cooldown

### ESP32-CAM Support
- ✅ Low-cost IoT cameras ($8-12 each)
- ✅ WiFi streaming
- ✅ MQTT integration
- ✅ Complete firmware
- ✅ Setup guide included

---

## 📊 System Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                    ESP32-CAM Cameras                        │
│              (Classrooms, Hallways, etc.)                   │
└────────────────────┬────────────────────────────────────────┘
                     │ Video Stream (WiFi/MQTT)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                 Main Processing Server                      │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  1. Object Detection (YOLOv8)                        │  │
│  │     - Detect backpacks, books, bottles, etc.        │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                  │
│                          ▼                                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  2. Object Tracking (DeepSORT)                       │  │
│  │     - Track objects over time                        │  │
│  │     - Detect stationary objects                      │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                  │
│                          ▼                                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  3. Left-Behind Detection                            │  │
│  │     - Check if stationary > 60 minutes               │  │
│  │     - After last class ends                          │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  4. Threat Detection (SlowFast)                      │  │
│  │     - Analyze video for aggressive behavior          │  │
│  │     - Fighting, pushing, etc.                        │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                  │
│                          ▼                                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  5. Alert System                                     │  │
│  │     - Send notifications                             │  │
│  │     - Save snapshots                                 │  │
│  └──────────────────────────────────────────────────────┘  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    Notifications                            │
│  📧 Email → Security Staff                                  │
│  📱 Telegram → Principal/Teachers                           │
│  💬 SMS → Emergency Contacts                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 📚 Documentation Guide

### For Quick Testing
1. **Start here**: [QUICK_START.md](QUICK_START.md)
   - 30-minute setup
   - Test with webcam
   - Verify system works

### For Dataset Preparation
2. **Read**: [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md)
   - How to collect data
   - Annotation tools
   - Pre-trained models
   - Transfer learning

### For Hardware Setup
3. **Follow**: [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md)
   - Hardware assembly
   - Firmware installation
   - Network configuration
   - Troubleshooting

### For Complete Understanding
4. **Review**: [README.md](README.md)
   - Full system overview
   - All features
   - Configuration options
   - Deployment scenarios

### For Implementation Details
5. **Check**: [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)
   - What's implemented
   - Technical details
   - Code structure
   - Next steps

---

## 🎓 Dataset Preparation Summary

### Option 1: Use Pre-trained Models (Fastest)
- YOLOv8 already trained on COCO dataset
- Includes common objects (backpack, book, bottle)
- **Ready to use immediately**
- Good for initial testing

### Option 2: Fine-tune on Your Data (Recommended)
- Collect 500-1000 images from your classrooms
- Annotate using LabelImg or Roboflow
- Fine-tune for 50-100 epochs
- **Better accuracy for your environment**

### Option 3: Train from Scratch (Advanced)
- Collect 5000+ images
- Comprehensive annotation
- Train for 200+ epochs
- **Best accuracy, most time-consuming**

**Detailed Guide**: [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md)

---

## 🔌 ESP32-CAM Hardware Summary

### Why ESP32-CAM?
- 💰 **Low Cost**: $8-12 per camera
- 📶 **WiFi Built-in**: Easy integration
- 🔋 **Low Power**: Continuous operation
- 📷 **Adequate Quality**: 640x480 @ 15fps
- 🛠️ **Easy Setup**: Arduino IDE programming

### What You Need (Per Camera)
- ESP32-CAM module
- ESP32-CAM-MB programmer (or FTDI)
- 5V 2A power supply
- Micro USB cable
- **Total: ~$18-28 per camera**

### Setup Time
- First camera: ~30 minutes
- Additional cameras: ~10 minutes each

**Complete Guide**: [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md)

---

## ⚙️ Configuration Highlights

### School Schedule
```yaml
schedule:
  periods:
    - name: "Period 1"
      start: "08:00"
      end: "08:45"
  school_days: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
```

### Detection Settings
```yaml
object_detection:
  left_behind_threshold: 60  # minutes
  target_classes: ["backpack", "book", "bottle"]
  
threat_detection:
  confidence_threshold: 0.7
  immediate_alert: true
```

### Notifications
```yaml
notifications:
  left_behind_objects:
    channels: ["email", "telegram"]
    cooldown_minutes: 15
  threats:
    channels: ["email", "telegram", "sms"]
    cooldown_minutes: 5
```

---

## 🎯 Use Cases

### Use Case 1: Classroom Monitoring
- **Scenario**: Students leave items after class
- **Detection**: System identifies left-behind backpack
- **Action**: After 60 minutes, alerts security staff
- **Result**: Item collected and stored for retrieval

### Use Case 2: Threat Prevention
- **Scenario**: Physical altercation in hallway
- **Detection**: System identifies fighting behavior
- **Action**: Immediately alerts principal and security
- **Result**: Rapid intervention prevents escalation

### Use Case 3: Multi-Camera Deployment
- **Scenario**: 20 classrooms across school
- **Setup**: 20 ESP32-CAM modules + 1 server
- **Cost**: ~$500 total hardware
- **Result**: Comprehensive monitoring coverage

---

## 📈 Performance Expectations

### Object Detection
- **Speed**: 30-85 FPS (depending on GPU)
- **Accuracy**: 85-95% (with fine-tuning)
- **Latency**: 10-40ms per frame

### Threat Detection
- **Speed**: 8-30 FPS
- **Accuracy**: 75-90% (dataset dependent)
- **Latency**: 30-125ms per clip

### System Requirements
- **Minimum**: i5 CPU, 8GB RAM, GTX 1050
- **Recommended**: i7 CPU, 16GB RAM, RTX 3060
- **Optimal**: Server-grade with multiple GPUs

---

## 🛠️ Customization Options

### Easy Customizations
- Add/remove object classes
- Adjust time thresholds
- Configure notification recipients
- Set detection zones
- Modify alert messages

### Advanced Customizations
- Train custom models
- Implement new detection algorithms
- Add database integration
- Create web dashboard
- Develop mobile app

---

## 🔒 Privacy and Security

### Built-in Privacy Features
- ✅ Local processing (no cloud required)
- ✅ Configurable data retention
- ✅ Face blurring option
- ✅ Encrypted storage
- ✅ Access control

### Compliance Considerations
- GDPR compliance options
- COPPA compliance for schools
- Local data protection laws
- Student consent management
- Video surveillance regulations

---

## 📞 Getting Help

### Documentation
- [README.md](README.md) - Complete overview
- [QUICK_START.md](QUICK_START.md) - Quick setup
- [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md) - Dataset guide
- [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md) - Hardware guide

### Troubleshooting
- Check logs: `logs/system.log`
- Review configuration: `config/config.yaml`
- Test cameras: `python scripts/test_camera.py`
- Verify models: `python scripts/download_models.py`

---

## 🎉 Ready to Deploy!

The system is **fully implemented** and ready for:

1. ✅ **Testing** - Use webcam or video files
2. ✅ **Development** - Train custom models
3. ✅ **Deployment** - Install in schools
4. ✅ **Scaling** - Add multiple cameras

### Next Steps

1. **Quick Test**: Follow [QUICK_START.md](QUICK_START.md)
2. **Prepare Data**: Read [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md)
3. **Setup Hardware**: Follow [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md)
4. **Deploy**: Use [README.md](README.md) deployment guide

---

**Made with ❤️ for School Safety**

**Project Status**: ✅ **Complete and Production-Ready**

