# Quick Reference Guide
## Video-Based Left Behind Object and Threat Detection System

---

## 🚀 Quick Start (5 Steps)

### Step 1: Install Dependencies
```bash
pip install -r requirements.txt
```

### Step 2: Verify Installation
```bash
python verify_packages.py
```

### Step 3: Check System
```bash
python check_system.py
```

### Step 4: Prepare Datasets (if training)
```bash
python scripts/prepare_datasets.py
```

### Step 5: Run Application
```bash
python main.py
```

---

## 📝 Common Commands

### System Verification
```bash
# Check all system components
python check_system.py

# Validate workflow
python validate_workflow.py

# Verify packages
python verify_packages.py
```

### Dataset Preparation
```bash
# Prepare all datasets
python scripts/prepare_datasets.py

# Prepare only object detection dataset
python scripts/prepare_datasets.py --only-object

# Prepare only threat detection dataset
python scripts/prepare_datasets.py --only-threat
```

### Model Training
```bash
# Train all models
python run_training.py

# Train only object detection
python run_training.py --object-only

# Train only threat detection
python run_training.py --threat-only

# Skip dataset preparation
python run_training.py --skip-prepare

# Custom epochs
python run_training.py --object-epochs 100 --threat-epochs 50
```

### Testing
```bash
# Run all system tests
python scripts/test_system.py

# Test specific model
python scripts/train_models.py --mode object --test-only
python scripts/train_models.py --mode threat --test-only
```

### Running the Application
```bash
# Run with default config
python main.py

# Run with custom config
python main.py --config path/to/config.yaml

# Run specific camera
python main.py --camera CAM_001 --source 0

# Run with video file
python main.py --camera CAM_001 --source path/to/video.mp4
```

---

## 🔧 Configuration

### Edit Configuration
```bash
# Main config file
config/config.yaml
```

### Key Configuration Sections
- **Cameras:** Camera IDs, IPs, locations
- **Object Detection:** Model path, confidence threshold, target classes
- **Threat Detection:** Model path, clip length, threat classes
- **Notifications:** Email, Telegram, SMS settings
- **Tracking:** IoU threshold, max age, left-behind threshold

---

## 📊 Model Training Metrics

### Object Detection (YOLOv8)
- mAP50
- mAP50-95
- Precision
- Recall
- F1 Score

### Threat Detection (3D CNN)
- Accuracy
- Precision
- Recall
- F1 Score
- Confusion Matrix

---

## 🔔 Notification Setup

### Email (SMTP)
Create `.env` file:
```
SMTP_SERVER=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
```

### Telegram
```
TELEGRAM_BOT_TOKEN=your-bot-token
```

### SMS (Twilio)
```
TWILIO_ACCOUNT_SID=your-account-sid
TWILIO_AUTH_TOKEN=your-auth-token
TWILIO_PHONE_NUMBER=+1234567890
```

---

## 📁 Directory Structure

```
Video_Based_Left_Behind_Object_and_Threat_Detection/
├── main.py                      # Main application
├── run_training.py              # Training pipeline
├── requirements.txt             # Dependencies
├── config/
│   └── config.yaml             # Configuration
├── src/
│   ├── models/
│   │   ├── object_detector.py  # Object detection
│   │   └── threat_detector.py  # Threat detection
│   ├── tracking/
│   │   └── object_tracker.py   # Object tracking
│   └── notifications/
│       └── alert_system.py     # Alerts
├── scripts/
│   ├── train_models.py         # Training scripts
│   ├── test_system.py          # Testing
│   └── prepare_datasets.py     # Dataset prep
├── datasets/                    # Training data
├── models/                      # Trained models
└── logs/                        # System logs
```

---

## 🐛 Troubleshooting

### Issue: Module not found
**Solution:** Install dependencies
```bash
pip install -r requirements.txt
```

### Issue: CUDA not available
**Solution:** Install PyTorch with CUDA support
```bash
pip install torch torchvision --index-url https://download.pytorch.org/whl/cu118
```

### Issue: Camera not opening
**Solution:** Check camera ID or stream URL in config.yaml

### Issue: Model not found
**Solution:** Train models first
```bash
python run_training.py
```

---

## 📈 Performance Tips

1. **Use GPU:** Set `use_gpu: true` in config.yaml
2. **Adjust frame_skip:** Higher values = faster but less accurate
3. **Batch processing:** Increase batch_size for better GPU utilization
4. **Model optimization:** Export to ONNX for faster inference

---

## 🎯 Target Classes

### Object Detection
- backpack
- handbag
- suitcase
- book
- bottle
- umbrella
- laptop

### Threat Detection
- fighting
- hitting
- pushing
- aggressive_behavior
- weapon_detection

---

## ⏱️ Time Thresholds

- **Left-behind detection:** 60 minutes (configurable)
- **Alert cooldown (objects):** 15 minutes
- **Alert cooldown (threats):** 5 minutes

---

## 📞 Quick Help

```bash
# Get help for any script
python main.py --help
python run_training.py --help
python scripts/prepare_datasets.py --help
```

---

**Last Updated:** December 11, 2025  
**Version:** 1.0.0

