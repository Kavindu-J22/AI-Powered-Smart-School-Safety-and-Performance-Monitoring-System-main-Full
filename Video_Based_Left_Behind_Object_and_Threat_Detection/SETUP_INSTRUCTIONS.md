# Setup Instructions - Video Threat Detection

## Current Issue: NumPy Compatibility

You encountered a NumPy version compatibility issue. This is now **FIXED** in the requirements.txt file.

## Quick Fix (Do This Now)

### Step 1: Wait for NumPy Installation to Complete

The command `pip install numpy==1.26.4` is currently running. Wait for it to complete.

### Step 2: Reinstall Ultralytics

Once NumPy installation completes, run:

```powershell
pip install --upgrade --force-reinstall ultralytics
```

### Step 3: Verify Installation

```powershell
python verify_installation.py
```

You should see:
```
✓ NumPy               - Version: 1.26.4
✓ Ultralytics YOLO    - Version: 8.x.x
✓ All dependencies are installed correctly!
```

### Step 4: Run the Application

```powershell
python app.py
```

You should see:
```
============================================================
Video-Based Threat Detection API
============================================================
Starting server on 0.0.0.0:5003
...
```

## Alternative: Use the Automated Fix Script

If you prefer an automated solution:

```powershell
.\fix_dependencies.bat
```

This will:
1. Uninstall NumPy 2.x
2. Install NumPy 1.26.4
3. Reinstall Ultralytics
4. Verify the installation

## What Was Changed

### requirements.txt
Changed from:
```
numpy>=1.24.0
```

To:
```
numpy<2.0.0,>=1.24.0  # NumPy 1.x for compatibility with PyTorch/Ultralytics
```

This ensures NumPy 1.x is always installed, preventing the compatibility issue.

## Fresh Installation (If Needed)

If you want to start fresh:

### Option 1: Virtual Environment (Recommended)

```powershell
# Create virtual environment
python -m venv venv

# Activate it
.\venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Verify
python verify_installation.py

# Run app
python app.py
```

### Option 2: System-Wide Installation

```powershell
# Uninstall problematic packages
pip uninstall numpy ultralytics torch torchvision -y

# Install from requirements.txt
pip install -r requirements.txt

# Verify
python verify_installation.py

# Run app
python app.py
```

## Verification Checklist

Before running the app, verify:

- [ ] Python 3.8+ installed: `python --version`
- [ ] Pip is updated: `python -m pip install --upgrade pip`
- [ ] NumPy 1.x installed: `python -c "import numpy; print(numpy.__version__)"`
- [ ] Ultralytics works: `python -c "from ultralytics import YOLO; print('OK')"`
- [ ] All dependencies: `python verify_installation.py`

## Expected Output When Running app.py

```
============================================================
Video-Based Threat Detection API
============================================================
Starting server on 0.0.0.0:5003

Available Endpoints:
   - GET  /api/video/health          Health Check
   - GET  /api/video/status          System Status
   - POST /api/video/detect-objects  Detect Objects
   - POST /api/video/detect-threats  Detect Threats
   - POST /api/video/process-frame   Process Complete Frame
============================================================

 * Serving Flask app 'app'
 * Debug mode: off
WARNING: This is a development server. Do not use it in a production deployment.
 * Running on all addresses (0.0.0.0)
 * Running on http://127.0.0.1:5003
 * Running on http://192.168.x.x:5003
Press CTRL+C to quit
```

## Testing the API

Once the server is running, test it:

### Test 1: Health Check
Open browser: `http://127.0.0.1:5003/api/video/health`

Expected response:
```json
{
  "status": "healthy",
  "message": "Video threat detection API is running"
}
```

### Test 2: Status Check
Open browser: `http://127.0.0.1:5003/api/video/status`

Expected response:
```json
{
  "status": "active",
  "object_detector_loaded": true,
  "threat_detector_loaded": true,
  "tracker_active": true
}
```

## Next Steps

After the Flask API is running:

1. **Start Laravel Application**
   ```powershell
   cd ..\AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
   php artisan serve
   ```

2. **Access Dashboard**
   Open browser: `http://127.0.0.1:8000/admin/management/video-threat`

3. **Start Detection**
   - Click "Start Detection"
   - Allow camera access
   - Watch real-time detections!

## Troubleshooting

If you encounter any issues, see `TROUBLESHOOTING.md` for detailed solutions.

Common issues:
- NumPy compatibility → Run `fix_dependencies.bat`
- Missing modules → Run `pip install -r requirements.txt`
- Port in use → Change port in `app.py` or kill existing process
- Camera access → Use HTTPS or check browser permissions

## Support Files Created

- `fix_dependencies.bat` - Automated dependency fix
- `verify_installation.py` - Verify all dependencies
- `TROUBLESHOOTING.md` - Detailed troubleshooting guide
- `requirements.txt` - Updated with NumPy 1.x constraint

## Summary

**The NumPy issue has been fixed!** Just complete these steps:

1. Wait for current NumPy installation to finish
2. Run: `pip install --upgrade --force-reinstall ultralytics`
3. Run: `python verify_installation.py`
4. Run: `python app.py`

You're almost there! 🚀

