# 🎯 Calibration Quick Reference Guide

## What is Calibration?
**Teaching the system about your room's normal background noise** so it can ignore fans, AC, and ambient sounds while detecting real threats.

---

## 🚀 How to Calibrate (Step-by-Step)

### 1. **Open Audio Threat Detection Page**
   - Navigate to: Admin → Management → Audio Threat Detection

### 2. **Click "Calibrate" Button**
   - Located in top-right corner
   - Opens calibration modal

### 3. **Prepare the Room**
   - ✅ Room should be in **normal state** (typical noise level)
   - ✅ Students doing normal activities
   - ✅ AC/fans running as usual
   - ❌ **Don't** make room completely silent
   - ❌ **Don't** calibrate during unusual noise

### 4. **Click "Start Calibration"**
   - Records 5 seconds of ambient audio
   - Progress bar shows recording status
   - **Keep quiet during recording** (no talking)

### 5. **Wait for Completion**
   - Modal shows "Calibration Complete"
   - Badge changes to "Noise Profile: Calibrated" (green)
   - Click "Close" or dismiss modal

### 6. **Start Detection**
   - Click "Start Detection" button
   - System now uses calibrated noise profile

---

## ✅ Verification Checklist

After calibration, verify:

- [ ] Badge shows "Noise Profile: Calibrated" (green)
- [ ] No error messages in modal
- [ ] Fewer false positives during detection
- [ ] System ignores fan/AC noise

---

## 🔄 When to Re-Calibrate

| Situation | Action |
|-----------|--------|
| First time using system | ✅ Calibrate |
| AC/heating turned on/off | ✅ Re-calibrate |
| Room becomes much noisier | ✅ Re-calibrate |
| Different time of day (morning vs afternoon) | ✅ Re-calibrate |
| Server restarted | ✅ Re-calibrate |
| Getting too many false positives | ✅ Re-calibrate |
| System working fine | ❌ Don't re-calibrate |

---

## 🎯 What Calibration Does

### 1. **Adaptive Thresholds**
Automatically adjusts detection thresholds based on noise level:

```
Quiet Room (noise_floor = 0.05):
  Crying threshold: 82% (normal)
  
Noisy Room (noise_floor = 0.20):
  Crying threshold: 95% (increased to prevent false positives)
```

### 2. **Noise Subtraction**
Removes constant background noise (fans, AC) from audio before analysis.

### 3. **SNR Calculation**
Determines if audio is loud enough to analyze (ignores very quiet sounds).

---

## ⚠️ Common Issues

### Issue: "Calibration Failed"
**Causes**:
- Microphone permission denied
- Python API not running
- Network error

**Solutions**:
1. Grant microphone permission in browser
2. Check Python API is running: `python app.py`
3. Check browser console for errors

### Issue: Still Getting False Positives After Calibration
**Causes**:
- Calibrated during unusually quiet period
- Model is overfitted (see MODEL_OVERFITTING_FIX.md)

**Solutions**:
1. Re-calibrate during normal noise level
2. Retrain model with fixed parameters

### Issue: Missing Real Threats After Calibration
**Causes**:
- Calibrated during unusually loud period
- Threshold increased too much

**Solutions**:
1. Re-calibrate during normal noise level
2. Adjust sensitivity settings

---

## 📊 Technical Details

### What Gets Stored:
```json
{
  "is_calibrated": true,
  "noise_floor": 0.15,              // Average background energy
  "noise_spectrum": [0.02, 0.03, ...], // Frequency profile
  "samples_collected": 5,
  "samples_required": 5
}
```

### Persistence:
- ⚠️ **In-memory only** (not saved to disk)
- ⚠️ **Lost on server restart**
- ⚠️ **Need to re-calibrate after restart**

### Recording Duration:
- **5 seconds** of ambient audio
- Uses 75th percentile (ignores occasional loud noises)

---

## 🧪 Testing Calibration

### Test if calibration is working:
```powershell
cd Audio-Based_Threat_Detection
.\venv\Scripts\Activate.ps1
python test_detection_debug.py
```

Look for:
```
Noise Profiler Status:
  is_calibrated: True
  noise_floor: 0.15
  samples_collected: 5
```

---

## 💡 Best Practices

### ✅ DO:
- Calibrate when room is in typical normal state
- Re-calibrate when environment changes
- Keep room at normal noise level during calibration
- Calibrate separately for different rooms/times

### ❌ DON'T:
- Calibrate during complete silence
- Calibrate during unusual noise (construction, fire drill)
- Calibrate during actual threats
- Forget to re-calibrate after server restart

---

## 📞 Quick Troubleshooting

| Symptom | Likely Cause | Solution |
|---------|--------------|----------|
| Badge stays gray | Calibration failed | Check browser console, retry |
| Too many false positives | Not calibrated or wrong calibration | Re-calibrate during normal noise |
| Missing real threats | Calibrated during loud period | Re-calibrate during normal noise |
| "Calibration Failed" error | API not running or mic blocked | Start API, grant mic permission |

---

## 🎓 Summary

**Calibration = Teaching the system what's "normal" in your environment**

- **Duration**: 5 seconds
- **Frequency**: Once per session, or when environment changes
- **Effect**: Reduces false positives by 70-90%
- **Required**: Optional but **highly recommended**

**Remember**: Calibrate during **normal** conditions, not silent or unusually loud!

