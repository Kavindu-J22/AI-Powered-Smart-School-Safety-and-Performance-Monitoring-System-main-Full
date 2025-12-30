# 🎯 Calibration Button - Complete Explanation

## 📍 What is the Calibrate Button?

The **Calibrate** button in the Audio Threat Detection dashboard is used to **teach the system about the normal background noise** in your environment (classroom, hallway, etc.).

**Location**: Top-right corner of the Audio Threat Detection page, next to "Start Detection" button.

---

## 🎯 Why Do We Need Calibration?

### The Problem:
Different environments have different background noise levels:
- **Quiet library**: Very low noise (fans, AC)
- **Busy classroom**: Medium noise (students talking, chairs moving)
- **Near cafeteria**: High noise (kitchen equipment, many voices)
- **Computer lab**: Fan noise from computers

**Without calibration**, the system doesn't know what's "normal" for your environment, leading to:
- ❌ **False positives**: Detecting threats when it's just normal background noise
- ❌ **Missed threats**: Real threats masked by loud background noise

### The Solution:
**Calibration** creates a "noise profile" of your environment, so the system can:
- ✅ **Ignore normal background noise** (fans, AC, ambient chatter)
- ✅ **Detect real threats** above the noise floor
- ✅ **Adapt detection thresholds** based on noise level

---

## 🔧 How Does Calibration Work?

### Step-by-Step Process:

#### 1. **User Clicks "Calibrate" Button**
   - Opens calibration modal
   - Shows instructions

#### 2. **User Clicks "Start Calibration"**
   - Records **5 seconds** of ambient audio
   - Progress bar shows recording status
   - **Important**: Room should be in normal state (not silent, not unusually loud)

#### 3. **Audio is Sent to Backend**
   - Frontend converts audio to Base64
   - Sends to Laravel backend via POST request
   - Laravel forwards to Python API

#### 4. **Python API Processes the Audio**
   ```python
   # In NoiseProfiler.update_noise_profile()
   
   # Calculate noise energy (RMS)
   energy = sqrt(mean(audio²))
   
   # Calculate noise spectrum (frequency content)
   spectrum = abs(fft(audio))
   
   # Store in circular buffer (keeps last 5 samples)
   noise_samples.append({energy, spectrum})
   
   # After 5 samples, calculate noise floor
   noise_floor = percentile(energies, 75%)  # 75th percentile
   noise_spectrum = percentile(spectrums, 75%)
   
   # Mark as calibrated
   is_calibrated = True
   ```

#### 5. **Noise Profile is Saved**
   - Stored in memory (not persistent - resets on server restart)
   - Used for all subsequent detections

---

## 🎨 What Does Calibration Actually Do?

### 1. **Adaptive Threshold Adjustment**
The system increases detection thresholds in noisy environments:

```python
# In NoiseProfiler.get_adaptive_threshold()

if noise_floor is high:
    # Increase threshold to prevent false positives
    adaptive_threshold = base_threshold * (1 + noise_factor * 0.35)
    # Example: 82% → 95% in noisy environment
else:
    # Keep normal threshold in quiet environment
    adaptive_threshold = base_threshold
```

**Example**:
- **Quiet room** (noise_floor = 0.05): Crying threshold stays at 82%
- **Noisy room** (noise_floor = 0.20): Crying threshold increases to 92%

### 2. **Spectral Noise Subtraction**
Removes background noise from incoming audio:

```python
# In NoiseProfiler.denoise_audio()

# Subtract noise spectrum from audio spectrum
clean_magnitude = max(audio_magnitude - noise_magnitude * 1.5, audio_magnitude * 0.1)

# This removes constant background noise (fans, AC)
# While preserving sudden sounds (screaming, glass breaking)
```

### 3. **Signal-to-Noise Ratio (SNR) Calculation**
Determines if audio is significant:

```python
# In NoiseProfiler.calculate_snr()

snr_db = 20 * log10(signal_energy / noise_energy)

# If SNR < minimum (e.g., 6 dB), audio is too quiet to analyze
# This prevents analyzing pure noise
```

---

## 📊 Calibration Data Stored

After calibration, the system stores:

```json
{
  "is_calibrated": true,
  "noise_floor": 0.15,              // Average background energy
  "noise_spectrum": [0.02, 0.03, ...], // Frequency profile
  "samples_collected": 5,
  "samples_required": 5
}
```

---

## 🚀 When Should You Calibrate?

### ✅ **Calibrate When**:
1. **First time using the system** in a new location
2. **Environment changes significantly**:
   - AC/heating turned on/off
   - Room becomes much noisier/quieter
   - Different time of day (morning vs afternoon)
3. **Getting too many false positives** (detecting threats when there are none)
4. **After server restart** (calibration is not persistent)

### ❌ **Don't Calibrate When**:
1. **During an actual threat** (screaming, emergency)
2. **Room is unusually quiet** (everyone silent for calibration)
3. **Room is unusually loud** (temporary noise like construction)

### 🎯 **Best Practice**:
Calibrate when the room is in its **typical normal state**:
- Students present and doing normal activities
- Normal background noise (AC, fans, ambient chatter)
- No unusual events

---

## 🔍 Is It Working Properly?

### ✅ **Signs Calibration is Working**:

1. **Calibration Status Badge Changes**:
   - Before: "Noise Profile: Not Calibrated" (gray)
   - After: "Noise Profile: Calibrated" (green)

2. **Fewer False Positives**:
   - System stops detecting threats from normal background noise
   - Fan noise, AC, ambient chatter ignored

3. **Backend Logs Show**:
   ```
   Noise floor: 0.15
   Adaptive threshold: 0.92 (increased from 0.82)
   ```

### ❌ **Signs of Problems**:

1. **Calibration Fails**:
   - Error message in modal
   - Check browser console for errors
   - Check Python API is running

2. **Still Getting False Positives After Calibration**:
   - **Possible causes**:
     - Calibrated during unusually quiet period
     - Model is overfitted (see MODEL_OVERFITTING_FIX.md)
     - Need to recalibrate

3. **Missing Real Threats After Calibration**:
   - **Possible causes**:
     - Calibrated during unusually loud period
     - Threshold increased too much
     - Need to recalibrate

---

## 🧪 Testing Calibration

### Test Script:
```python
# In Audio-Based_Threat_Detection/
python test_detection_debug.py
```

This shows:
- Current calibration status
- Noise floor value
- Adaptive thresholds being used

---

## 🔧 Technical Flow

```
[User Clicks Calibrate]
        ↓
[Modal Opens - Shows Instructions]
        ↓
[User Clicks "Start Calibration"]
        ↓
[Browser Records 5 Seconds of Audio]
        ↓
[Audio → Base64 Encoding]
        ↓
[POST to Laravel: /admin/management/audio-threat/calibrate]
        ↓
[Laravel → Python API: /api/audio/calibrate]
        ↓
[NoiseProfiler.update_noise_profile(audio)]
        ↓
[Calculate: energy, spectrum, noise_floor]
        ↓
[Store in circular buffer (5 samples)]
        ↓
[Mark as calibrated]
        ↓
[Return status to frontend]
        ↓
[Update UI: "Calibrated" badge]
```

---

## 📝 Summary

| Aspect | Details |
|--------|---------|
| **Purpose** | Learn normal background noise of environment |
| **Duration** | 5 seconds of recording |
| **Frequency** | Once per session, or when environment changes |
| **Effect** | Adjusts detection thresholds, removes background noise |
| **Persistence** | In-memory only (lost on server restart) |
| **Required?** | Optional but **highly recommended** |

**Bottom Line**: Calibration makes the system **smarter** by teaching it what's normal in your specific environment, dramatically reducing false positives while maintaining high detection accuracy for real threats.

