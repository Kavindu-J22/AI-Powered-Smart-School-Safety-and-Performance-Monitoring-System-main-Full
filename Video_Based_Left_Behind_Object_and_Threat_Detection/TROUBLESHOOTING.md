# Troubleshooting Guide

## NumPy Compatibility Error

### Problem
```
A module that was compiled using NumPy 1.x cannot be run in NumPy 2.2.6
ImportError: cannot import name 'YOLO' from 'ultralytics'
```

### Cause
PyTorch and Ultralytics were compiled with NumPy 1.x, but your environment has NumPy 2.x installed.

### Solution

**Option 1: Use the Fix Script (Recommended)**
```bash
# Windows
fix_dependencies.bat

# Linux/Mac
chmod +x fix_dependencies.sh
./fix_dependencies.sh
```

**Option 2: Manual Fix**
```bash
# Step 1: Uninstall NumPy 2.x
pip uninstall numpy -y

# Step 2: Install NumPy 1.26.4
pip install numpy==1.26.4

# Step 3: Reinstall Ultralytics
pip install --upgrade --force-reinstall ultralytics

# Step 4: Verify
python verify_installation.py
```

**Option 3: Fresh Virtual Environment**
```bash
# Create new virtual environment
python -m venv venv_video_threat

# Activate it
# Windows:
venv_video_threat\Scripts\activate
# Linux/Mac:
source venv_video_threat/bin/activate

# Install dependencies
pip install -r requirements.txt

# Verify
python verify_installation.py
```

## Other Common Issues

### Issue: "No module named 'torch'"

**Solution:**
```bash
pip install torch torchvision
```

### Issue: "No module named 'ultralytics'"

**Solution:**
```bash
pip install ultralytics
```

### Issue: "No module named 'cv2'"

**Solution:**
```bash
pip install opencv-python opencv-contrib-python
```

### Issue: "No module named 'flask'"

**Solution:**
```bash
pip install flask flask-cors
```

### Issue: "Model file not found"

**Solution:**
The YOLOv8 model will be downloaded automatically on first run. Ensure you have internet connection.

### Issue: "Port 5003 already in use"

**Solution:**
```bash
# Find process using port 5003
# Windows:
netstat -ano | findstr :5003

# Kill the process (replace PID with actual process ID)
taskkill /PID <PID> /F

# Or change the port in app.py
```

### Issue: "CUDA out of memory"

**Solution:**
1. Use CPU instead of GPU (automatic fallback)
2. Reduce batch size
3. Use smaller model (yolov8n instead of yolov8x)

### Issue: "Camera access denied"

**Solution:**
1. Use HTTPS (required by modern browsers)
2. Check browser permissions
3. Try different browser

## Verification

After fixing any issue, verify the installation:

```bash
python verify_installation.py
```

Expected output:
```
✓ NumPy               - Version: 1.26.4
✓ OpenCV              - Version: 4.x.x
✓ PyTorch             - Version: 2.x.x
✓ Ultralytics YOLO    - Version: 8.x.x
✓ All dependencies are installed correctly!
```

## Getting Help

If issues persist:

1. Check Python version: `python --version` (should be 3.8+)
2. Check pip version: `pip --version`
3. Update pip: `python -m pip install --upgrade pip`
4. Clear pip cache: `pip cache purge`
5. Try fresh virtual environment

## Contact

For additional support, check:
- Video Detection README
- Integration documentation
- Laravel logs: `storage/logs/laravel.log`
- Flask logs: Terminal output

