# Quick Start Guide
## Get Your School Security System Running in 30 Minutes

This guide will help you get the system up and running quickly for testing and evaluation.

---

## Prerequisites

- Windows 10/11, Ubuntu 20.04+, or macOS 10.14+
- Python 3.8 or higher
- Webcam or IP camera
- 8GB RAM minimum
- Internet connection (for initial setup)

---

## Step 1: Installation (5 minutes)

### 1.1 Clone or Download

```bash
# If you have git
git clone https://github.com/yourusername/school-security-system.git
cd school-security-system

# Or download ZIP and extract
```

### 1.2 Create Virtual Environment

```bash
# Windows
python -m venv venv
venv\Scripts\activate

# Linux/Mac
python3 -m venv venv
source venv/bin/activate
```

### 1.3 Install Dependencies

```bash
pip install -r requirements.txt
```

This will take 5-10 minutes depending on your internet speed.

---

## Step 2: Download Models (5 minutes)

```bash
# Download YOLOv8 nano model (smallest, fastest)
python scripts/download_models.py --model yolov8n
```

The model will be downloaded automatically (~6 MB).

---

## Step 3: Basic Configuration (5 minutes)

### 3.1 Copy Environment File

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

### 3.2 Edit Configuration (Optional for Testing)

For quick testing, you can skip this step. The default configuration will work with a webcam.

If you want to enable notifications, edit `.env`:

```bash
# Email notifications (optional)
SMTP_SERVER=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
ALERT_EMAIL_RECIPIENTS=your_email@gmail.com
```

---

## Step 4: Test with Webcam (5 minutes)

### 4.1 Run the System

```bash
python main.py --camera TEST --source 0
```

- `--camera TEST`: Camera identifier
- `--source 0`: Use default webcam (use 1, 2, etc. for other cameras)

### 4.2 What You Should See

A window will open showing:
- Live video feed from your webcam
- Detected objects with bounding boxes
- Object tracking IDs
- Threat detection status

### 4.3 Test Object Detection

1. Place a backpack, book, or bottle in view
2. The system should detect and track it
3. Keep it stationary for the configured time (default: 60 minutes, but you can change this for testing)

### 4.4 Quick Test with Shorter Threshold

Edit `config/config.yaml` to test faster:

```yaml
object_detection:
  left_behind_threshold: 1  # Change to 1 minute for testing
```

Then run again:

```bash
python main.py --camera TEST --source 0
```

Now objects will be marked as "left behind" after just 1 minute!

---

## Step 5: Test with Video File (Optional)

If you don't have a camera or want to test with recorded video:

```bash
# Download a test video or use your own
python main.py --camera TEST --source path/to/your/video.mp4
```

---

## Step 6: Test ESP32-CAM (If You Have One)

### 6.1 Setup ESP32-CAM

Follow the detailed guide: [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md)

Quick steps:
1. Flash firmware from `firmware/esp32_cam/`
2. Configure WiFi credentials
3. Note the IP address from Serial Monitor

### 6.2 Add to Configuration

Edit `config/config.yaml`:

```yaml
cameras:
  - id: "CAM_001"
    name: "Test Camera"
    location: "Test Location"
    type: "ESP32-CAM"
    ip: "192.168.1.101"  # Your ESP32-CAM IP
    enabled: true
```

### 6.3 Test Connection

```bash
python scripts/test_camera.py --camera CAM_001
```

### 6.4 Run with ESP32-CAM

```bash
python main.py
```

---

## Common Issues and Solutions

### Issue 1: "No module named 'torch'"

**Solution:**
```bash
pip install torch torchvision
```

### Issue 2: "Camera not found"

**Solution:**
- Check if webcam is connected
- Try different source numbers: `--source 1`, `--source 2`
- On Linux, check permissions: `sudo usermod -a -G video $USER`

### Issue 3: "CUDA not available"

**Solution:**
This is normal if you don't have an NVIDIA GPU. The system will use CPU (slower but works).

To use GPU:
```bash
pip install torch torchvision --index-url https://download.pytorch.org/whl/cu118
```

### Issue 4: Low FPS / Slow Performance

**Solution:**
1. Use smaller model: `yolov8n` instead of `yolov8m`
2. Reduce resolution in config
3. Increase frame skip: `frame_skip: 3` in config
4. Close other applications

### Issue 5: "Failed to load model"

**Solution:**
```bash
# Re-download models
python scripts/download_models.py --model yolov8n
```

---

## Next Steps

### For Production Deployment

1. **Collect Your Own Dataset**
   - See [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md)
   - Record videos from your actual classrooms
   - Annotate and train custom model

2. **Setup Multiple Cameras**
   - Configure all cameras in `config/config.yaml`
   - Setup ESP32-CAM modules
   - Test each camera individually

3. **Configure Notifications**
   - Setup email (Gmail, Outlook, etc.)
   - Setup Telegram bot
   - Setup SMS (Twilio)

4. **Fine-tune Detection**
   - Adjust confidence thresholds
   - Define detection zones
   - Set appropriate time thresholds

5. **Deploy on Server**
   - Use dedicated computer or server
   - Setup as system service
   - Configure automatic startup

### For Development

1. **Read Full Documentation**
   - [README.md](README.md) - Complete overview
   - [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md) - Dataset guide
   - [ESP32_CAM_SETUP_GUIDE.md](ESP32_CAM_SETUP_GUIDE.md) - Hardware guide

2. **Explore Code**
   - `src/models/` - Detection models
   - `src/tracking/` - Object tracking
   - `src/notifications/` - Alert system

3. **Run Tests**
   ```bash
   pytest tests/
   ```

---

## Testing Checklist

- [ ] Python and dependencies installed
- [ ] Models downloaded
- [ ] System runs with webcam
- [ ] Objects are detected
- [ ] Objects are tracked
- [ ] Left-behind detection works (with short threshold)
- [ ] Threat detection runs (even if not detecting threats)
- [ ] Configuration file understood
- [ ] Ready for next steps

---

## Getting Help

If you encounter issues:

1. Check the [Troubleshooting](#common-issues-and-solutions) section above
2. Review the full [README.md](README.md)
3. Check system logs: `logs/system.log`
4. Open an issue on GitHub

---

## Quick Reference Commands

```bash
# Run with webcam
python main.py --camera TEST --source 0

# Run with video file
python main.py --camera TEST --source video.mp4

# Test specific camera
python scripts/test_camera.py --camera CAM_001

# Download models
python scripts/download_models.py --model yolov8n

# Train custom model
python scripts/train_object_detector.py --data dataset.yaml --epochs 100
```

---

**Congratulations!** 🎉 You now have a working school security system!

Next: Read [DATASET_PREPARATION_GUIDE.md](DATASET_PREPARATION_GUIDE.md) to learn how to prepare datasets for your specific environment.

