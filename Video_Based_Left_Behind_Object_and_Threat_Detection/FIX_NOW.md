# 🔧 QUICK FIX - Do This Now!

## The Problem
You have NumPy 2.x and OpenCV 4.12, but PyTorch/Ultralytics need NumPy 1.x and OpenCV 4.9.

## The Solution (Copy & Paste These Commands)

### Option 1: Automated Fix (RECOMMENDED)

Just run this one command:

```powershell
.\fix_dependencies.bat
```

This will automatically:
- Remove incompatible packages
- Install correct versions
- Verify everything works

**Then run:**
```powershell
python app.py
```

---

### Option 2: Manual Fix (If Option 1 Fails)

Copy and paste these commands **one at a time**:

```powershell
# 1. Remove incompatible packages
pip uninstall numpy opencv-python opencv-contrib-python -y

# 2. Install NumPy 1.26.4
pip install numpy==1.26.4

# 3. Install OpenCV 4.9.0.80
pip install opencv-python==4.9.0.80 opencv-contrib-python==4.9.0.80

# 4. Reinstall Ultralytics
pip install --upgrade --force-reinstall ultralytics

# 5. Verify it works
python verify_installation.py

# 6. Run the app
python app.py
```

---

## Expected Output

After running `python app.py`, you should see:

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

 * Running on http://127.0.0.1:5003
```

✅ **If you see this, it's working!**

---

## What If It Still Doesn't Work?

### Try a Fresh Virtual Environment

```powershell
# Create new virtual environment
python -m venv venv_video

# Activate it
.\venv_video\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Run app
python app.py
```

---

## Quick Test

Once the server is running, open your browser:

**Test URL:** http://127.0.0.1:5003/api/video/health

**Expected Response:**
```json
{
  "status": "healthy",
  "message": "Video threat detection API is running"
}
```

---

## Next Steps After Flask API is Running

1. **Open a NEW terminal** (keep Flask running)

2. **Start Laravel:**
   ```powershell
   cd ..\AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
   php artisan serve
   ```

3. **Open Dashboard:**
   http://127.0.0.1:8000/admin/management/video-threat

4. **Start Detection:**
   - Click "Start Detection"
   - Allow camera access
   - See real-time detections!

---

## Summary

**Just run this:**
```powershell
.\fix_dependencies.bat
python app.py
```

**That's it!** 🎉

---

## Still Having Issues?

Check these files:
- `TROUBLESHOOTING.md` - Detailed troubleshooting
- `SETUP_INSTRUCTIONS.md` - Complete setup guide
- `verify_installation.py` - Check what's installed

Or create a fresh virtual environment (see above).

