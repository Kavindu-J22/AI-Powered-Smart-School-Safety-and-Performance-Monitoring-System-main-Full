# 🔊 DOCUMENT 2: Audio-Based Threat Detection System
### AI-Powered Smart School Safety and Performance Monitoring System
---

## 1. EXECUTIVE SUMMARY

The Audio-Based Threat Detection System is a real-time AI safety module that continuously monitors audio streams from school environments to detect threatening sound events. It uses a **dual-engine detection architecture**: a **PANNS CNN14 pre-trained model** (primary, trained on 527 AudioSet classes) and a custom **1D-CNN + SE + BiLSTM + Multi-head Attention** model (fallback). It also includes a **Speech Threat Detector** using speech-to-text + keyword analysis for detecting verbal threats in real time.

**Detected Threat Classes:**
- 🔴 Screaming
- 🔴 Shouting
- 🔴 Crying
- 🔴 Glass Breaking
- 🟢 Normal (no threat)

**Speech Threats:** Verbal threats detected via keyword analysis on transcribed speech (e.g., weapon mentions, danger words).

---

## 2. TECHNOLOGY STACK

| Component | Technology |
|---|---|
| Primary Model | PANNS CNN14 (pre-trained on AudioSet, 527 classes) |
| Custom Fallback Model | 1D-CNN + Squeeze-and-Excitation + BiLSTM + Multi-head Attention (PyTorch) |
| Speech Detection | SpeechRecognition / Whisper (speech-to-text) + keyword analysis |
| Training Loss | Focal Loss (γ=2.0) + inverse-frequency class weights |
| Optimizer | AdamW + CosineAnnealingWarmRestarts |
| Audio Processing | NumPy, Librosa, SciPy |
| Feature Extraction | MFCC (40) + Delta + Delta-Delta + Spectral features = 144 features |
| API | Python Flask REST API |
| Web Integration | Laravel PHP → Flask via HTTP |
| Noise Handling | Adaptive noise profiling + spectral subtraction denoising |

---

## 3. SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────────┐
│                     Audio Input (Microphone / Stream)                │
└──────────────────────────────┬──────────────────────────────────────┘
                               │  Raw PCM Audio
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        AudioProcessor                                │
│  - Resample to 16kHz mono                                           │
│  - Normalize amplitude                                              │
│  - Remove DC offset                                                 │
│  - Apply pre-emphasis filter                                        │
└──────────────────────────┬──────────────────────────────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
              ▼                         ▼
   ┌──────────────────┐      ┌─────────────────────────────────┐
   │  Energy Filter   │      │       NoiseProfiler              │
   │  (min 0.010 RMS) │      │  - Calibrate on ambient audio    │
   │  Skip silence    │      │  - Spectral subtraction denoise  │
   └──────┬───────────┘      │  - Adaptive threshold per class  │
          │                  └───────────────┬─────────────────┘
          │                                  │
          └──────────────┬───────────────────┘
                         │  Processed Audio
                         ▼
          ┌──────────────────────────────────────────┐
          │         DUAL ENGINE DETECTION             │
          │                                          │
          │  PRIMARY (PANNS CNN14)                   │
          │  - Input: raw waveform                   │
          │  - Output: 527-class AudioSet probs       │
          │  - Threshold: 0.06–0.08 (PANNS scale)    │
          │                                          │
          │  FALLBACK (Custom CNN-BiLSTM)            │
          │  - Input: MFCC 144 features × 128 frames │
          │  - Output: 5-class softmax probs          │
          │  - Threshold: 0.50–0.60 (softmax scale)  │
          └──────────────┬───────────────────────────┘
                         │
                         ▼
          ┌──────────────────────────────────────────┐
          │        Speech Threat Detector             │
          │  - Speech-to-text transcription           │
          │  - Keyword threat analysis                │
          │  - Threat scoring (0.0 – 1.0)             │
          └──────────────┬───────────────────────────┘
                         │
                         ▼
          ┌──────────────────────────────────────────┐
          │         Consecutive Detection Filter      │
          │  (consecutive_required=1, default)        │
          │  Reduces false positives                  │
          └──────────────┬───────────────────────────┘
                         │
                         ▼
          ┌──────────────────────────────────────────┐
          │         Threat Level Determination        │
          │  none → low → medium → high → critical    │
          │  Based on: confidence / threshold ratio   │
          └──────────────┬───────────────────────────┘
                         │
                         ▼
          ┌──────────────────────────────────────────┐
          │  Flask API Response → Laravel Controller  │
          │  {is_threat, threat_type, threat_level,   │
          │   confidence, speech_result, processing_t}│
          └──────────────────────────────────────────┘
```

---

## 4. IMPLEMENTATION DETAILS

### 4.1 Core Files and Their Roles

| File | Role |
|---|---|
| `models/threat_detector.py` | Main orchestrator: combines non-speech + speech detection |
| `models/non_speech_model.py` | Custom CNN+SE+BiLSTM+Attention PyTorch model |
| `models/pretrained_audio_detector.py` | PANNS CNN14 pre-trained wrapper |
| `models/speech_threat_model.py` | Speech-to-text + keyword threat analysis |
| `utils/audio_processor.py` | Audio preprocessing: resample, normalize, pre-emphasis |
| `utils/feature_extractor.py` | MFCC + spectral feature extraction (144 features/frame) |
| `utils/noise_profiler.py` | Adaptive noise profile, denoising, adaptive thresholds |
| `training/trainer.py` | Training pipeline: Focal Loss, AdamW, early stopping |
| `training/data_loader.py` | Dataset loading with class-balanced sampling |
| `api/routes/` | Flask REST API routes for audio analysis |
| `AudioThreatController.php` | Laravel controller bridging web app to Flask API |

### 4.2 Custom CNN-BiLSTM-Attention Model Architecture

```
Input: (batch, time_steps=128, n_features=144)
        │
        ▼ Transpose → (batch, 144, 128)
┌───────────────────────────────────┐
│  CNN Block 1                      │
│  Conv1d(144→128, k=3) + BN + GELU │
│  Conv1d(128→128, k=3) + BN + GELU │
│  MaxPool1d(2) + Dropout(0.2)      │
│  SE Block (channel attention)      │
└───────────────────────────────────┘
        │ (batch, 128, 64)
        ▼
┌───────────────────────────────────┐
│  CNN Block 2                      │
│  Conv1d(128→256, k=3) + BN + GELU │
│  Conv1d(256→256, k=3) + BN + GELU │
│  MaxPool1d(2) + Dropout(0.2)      │
│  SE Block (channel attention)      │
└───────────────────────────────────┘
        │ (batch, 256, 32)
        ▼
┌───────────────────────────────────┐
│  CNN Block 3                      │
│  Conv1d(256→256, k=3) + BN + GELU │
│  Dropout(0.25)                    │
│  SE Block                         │
└───────────────────────────────────┘
        │
        ├─ Global Avg Pool → CNN_out (batch, 256)
        │
        ▼ Transpose → (batch, 32, 256)
┌───────────────────────────────────┐
│  BiLSTM                           │
│  2 layers, hidden=256, bidir      │
│  Dropout(0.3) between layers      │
│  Output: (batch, 32, 512)         │
└───────────────────────────────────┘
        │
        ▼
┌───────────────────────────────────┐
│  Multi-head Self-Attention        │
│  embed=512, heads=8, dropout=0.1  │
│  Residual connection + LayerNorm  │
│  Attention Pool → (batch, 512)    │
└───────────────────────────────────┘
        │
        ▼ Concatenate [CNN_out(256) | Attn(512)] = (batch, 768)
┌───────────────────────────────────┐
│  Dense Head                       │
│  Linear(768→384) + BN + GELU      │
│  Dropout(0.4)                     │
│  Linear(384→128) + BN + GELU      │
│  Dropout(0.3)                     │
│  Linear(128→5)  [5 classes]       │
└───────────────────────────────────┘
        │
        ▼ Softmax → [crying, screaming, shouting, glass_breaking, normal]
```

### 4.3 Audio Feature Extraction (144 Features/Frame)

| Feature Group | Count | Description |
|---|---|---|
| MFCC | 40 | Mel-frequency cepstral coefficients |
| MFCC Delta | 40 | First-order temporal derivatives |
| MFCC Delta-Delta | 40 | Second-order temporal derivatives |
| Spectral Centroid | 1 | Center of mass of spectrum |
| Spectral Bandwidth | 1 | Spread of the spectrum |
| Spectral Rolloff | 1 | Frequency below which 85% energy lies |
| Zero-Crossing Rate | 1 | Rate of sign changes in signal |
| RMS Energy | 1 | Root mean square energy per frame |
| Chroma Features | 12 | Pitch class distribution |
| Mel Spectrogram | 7 | Mel-scale spectral bands |
| **TOTAL** | **144** | Per time-step feature vector |

### 4.4 Training Strategy

```
Training Data:
  - Normal: ~500-800 audio clips
  - Crying:  ~200-400 clips
  - Screaming: ~1826 clips (majority class)
  - Shouting: ~31 clips (severe minority)
  - Glass Breaking: ~300-500 clips

Class Imbalance Handling:
  1. Focal Loss (γ=2.0): down-weights easy examples
  2. Inverse-frequency class weights: w_i = N / (K × n_i)
  3. WeightedRandomSampler: over-samples minority classes per batch

Optimizer: AdamW (lr=default config, weight_decay=2e-4)
Scheduler: CosineAnnealingWarmRestarts (T_0=20, T_mult=2, eta_min=1e-6)
Gradient Clipping: max_norm=1.0
Early Stopping: patience from ModelConfig.EARLY_STOPPING_PATIENCE
```

### 4.5 PANNS CNN14 Primary Engine

PANNS (Pre-trained Audio Neural Networks) CNN14 is pretrained on **AudioSet** — a Google dataset of 2 million 10-second audio clips covering 527 sound classes. The system maps PANNS classes to the 5 threat categories:
- `Screaming` → screaming
- `Shout` → shouting
- `Crying, sobbing` → crying
- `Glass` → glass_breaking
- All others → normal

PANNS thresholds are on a different scale (0.05–0.40 typical) vs. custom model softmax (0.50–0.95), hence separate `class_thresholds` and `custom_class_thresholds` dictionaries are maintained.

### 4.6 Adaptive Noise Profiling

```
NoiseProfiler.calibrate(ambient_audio, duration=10s)
        │
        ▼
Estimates: noise_mean, noise_std, noise_spectrum
        │
        ▼
is_significant_audio(audio) → RMS > noise_mean + 2×noise_std
        │
        ▼
denoise_audio(audio) → spectral subtraction removes noise floor
        │
        ▼
get_adaptive_threshold(base_threshold) → adjusts per noise level
```

---

## 5. THREAT LEVEL CLASSIFICATION

| Level | Condition (PANNS) | Condition (Custom Model) |
|---|---|---|
| **low** | ratio < 2.0 (confidence/threshold) | Just above threshold |
| **medium** | ratio 2.0–5.0 | Moderate confidence |
| **high** | ratio 5.0–10.0 | High confidence |
| **critical** | ratio ≥ 10.0 | Very high confidence |

Speech threats use a direct threat_score → level mapping:
- score ≥ 0.6 → high
- score ≥ 0.4 → medium
- score < 0.4 → low

Combined threats (both speech + non-speech) take the HIGHEST level from either source.

---

## 6. SENSITIVITY SETTINGS

| Mode | consecutive_required | PANNS crying threshold | PANNS screaming threshold |
|---|---|---|---|
| **High** (sensitive) | 1 | 0.04 | 0.05 |
| **Normal** (balanced) | 1 | 0.06 | 0.08 |
| **Low** (fewer FP) | 2 | 0.15 | 0.20 |

---

## 7. PRIVACY DESIGN

- Raw audio is **never stored or transmitted** — only extracted features and classification results
- Speech transcription results contain only threat-relevant text (not stored long-term)
- Documentation explicitly states: "Raw audio is discarded after feature extraction for privacy"
- GDPR-aligned: No audio recordings retained on server

---

## 8. LARAVEL INTEGRATION

```
Browser (Microphone) → Web Audio API (base64 encoding)
    │
    ▼
POST /admin/audio-threat/analyze (Laravel Route)
    │
    ▼
AudioThreatController::analyze()
    │  Validates: audio_data (required|string)
    ▼
Http::timeout(30)->post("flask_api/api/audio/analyze", [
    audio_data, format, sample_rate, session_id
])
    │
    ▼
If is_threat → Log::warning('Audio threat detected', [
    threat_type, threat_level, confidence, timestamp
])
    │
    ▼
Return JSON response to frontend dashboard
```

---

## 9. SYSTEM FLOW DIAGRAM

```
Microphone Input
     │ PCM audio stream (chunks)
     ▼
AudioProcessor.preprocess_audio()
     │ → Resample 16kHz, normalize, pre-emphasis
     ▼
Energy Check (RMS < 0.010 → skip as silence)
     ▼
NoiseProfiler.is_significant_audio() + denoise_audio()
     ▼
     ├──────────────────────────────────────────┐
     │                                          │
     ▼  Non-Speech Path                         ▼  Speech Path
PANNS CNN14.detect(audio, 16kHz)      SpeechThreatDetector.analyze()
     │ → 527-class probs                │ → STT transcription
     │ → Map to 5 threat classes        │ → Keyword threat analysis
     │ → class_threshold lookup         │ → threat_score (0-1)
     │ → adaptive_threshold             │ → is_threat (bool)
     │ → initial_is_threat              │ → threat_level
     │                                  │
     ▼                                  │
_check_consecutive_detection()         │
     │ (reduces false positives)        │
     ▼                                  │
     └──────────────────────────────────┘
                    │
                    ▼
     Combine Results: type=(non_speech|speech|combined)
                    │
                    ▼
     Threat Level Determination
     (ratio-based for non-speech, score-based for speech)
                    │
                    ▼
     API Response → Laravel → Dashboard Alert
     { is_threat, threat_type, threat_level, confidence,
       non_speech_result, speech_result, processing_time }
```

---

## 10. RESEARCH PANEL — QUESTION & ANSWER (Q&A)

---

**Q1. Why does the system use a dual-engine approach (PANNS + custom model)?**

> **A:** PANNS CNN14, pretrained on Google's 2-million-clip AudioSet dataset, provides exceptional generalization across 527 sound classes with no local training required. However, its probability scale (0.05–0.40) differs from custom softmax models (0.50–0.95). The custom CNN-BiLSTM-Attention model is trained specifically on school-relevant threat sounds (crying, shouting, glass breaking) for domain specificity. PANNS is primary for performance; the custom model is the fallback for offline or resource-limited environments.

---

**Q2. What is PANNS and how does it work?**

> **A:** PANNS (Pre-trained Audio Neural Networks) is a collection of deep learning models trained by Google on AudioSet — a large-scale audio dataset with 527 sound event classes. CNN14 is a 14-layer CNN architecture achieving state-of-the-art audio tagging. It takes raw waveform input and outputs a probability vector over all 527 classes simultaneously, making it extremely versatile for detecting any known sound event without domain-specific retraining.

---

**Q3. What is Focal Loss and why is it used in training the custom model?**

> **A:** Focal Loss (introduced by Lin et al., 2017 for object detection) modifies standard cross-entropy loss by adding a modulating factor `(1-pt)^γ`. This down-weights the loss contribution of easy examples (correctly classified with high confidence) and focuses training on hard examples (misclassified or borderline). With γ=2.0, it is ideal for the severely imbalanced dataset where shouting has only 31 samples vs. screaming with 1826 — preventing the model from ignoring minority classes.

---

**Q4. What are MFCC features and why are they used for audio classification?**

> **A:** Mel-Frequency Cepstral Coefficients (MFCCs) transform audio into a representation that mimics human auditory perception. The mel scale is non-linear (more resolution at low frequencies, less at high) — matching how humans hear. MFCCs (40 coefficients) capture the spectral shape of the audio, while Delta and Delta-Delta capture temporal dynamics (how the spectrum changes over time). This 120-dimensional base plus additional spectral features gives 144 features total per time frame.

---

**Q5. How does the Squeeze-and-Excitation (SE) block improve the CNN?**

> **A:** SE blocks add channel attention to convolutional layers. After each CNN block, SE computes a global average pool over the time dimension for each channel, passes it through two fully-connected layers (squeeze then excite) with a sigmoid activation, and multiplies the output back onto the feature maps. This lets the network selectively emphasize channels (feature maps) that are most informative for threat classification and suppress irrelevant ones — improving accuracy without adding many parameters.

---

**Q6. Why is Bidirectional LSTM used instead of a standard LSTM?**

> **A:** A standard LSTM processes audio sequences in only one direction (past → future). A Bidirectional LSTM (BiLSTM) processes the sequence in both forward and backward directions simultaneously, giving the model context from both before and after each time step. For audio, this means a brief cry can be confirmed by what follows it (e.g., subsequent shouting), improving detection accuracy. The two hidden states (forward + backward) are concatenated, doubling the output dimension from 256 to 512.

---

**Q7. How does multi-head self-attention help in this audio classification task?**

> **A:** Multi-head self-attention allows the model to attend to multiple different positions (time frames) simultaneously when making a classification decision. For threat detection, different time steps may contain the most discriminative information (e.g., the peak of a scream, the impact moment of glass breaking). By attending to these moments with 8 attention heads, the model captures diverse temporal patterns. The residual connection and LayerNorm prevent gradient vanishing.

---

**Q8. What is the adaptive noise threshold and why is it important for a school environment?**

> **A:** Schools are noisy environments — classroom chatter, hallway noise, outdoor sounds. A fixed threshold would produce too many false positives. The adaptive noise threshold starts by profiling ambient background noise (mean spectrum + standard deviation). Any audio must exceed `noise_mean + 2×noise_std` to be considered significant. The detection threshold per class is also adjusted upward proportionally to the ambient noise level, making the system self-calibrating across different environments.

---

**Q9. What is the consecutive detection filter and how does it reduce false positives?**

> **A:** The system maintains a `detection_history` deque of the last 5 classifications. `consecutive_required` (default=1) means a single detection is immediately reported. In "low sensitivity" mode, `consecutive_required=2` means the same threat class must be detected in 2 consecutive audio chunks to generate an alert. This temporal consistency check eliminates one-frame false positives caused by transient non-threatening sounds (door slam, paper tear) that might briefly resemble a threat sound.

---

**Q10. How are speech threats detected differently from non-speech threats?**

> **A:** Speech threats use a different pipeline: (1) Speech-to-text transcription converts audio to text, (2) Keyword analysis scans the transcript for threat-relevant terms (weapon names, danger commands, explicit threats), (3) A threat_score (0.0–1.0) is computed based on keyword severity and frequency, (4) If score exceeds threshold, it's flagged as a speech threat. Speech threats trigger immediate alerts without requiring consecutive confirmation — unlike non-speech where consecutive_required filters are applied.

---

**Q11. How does the threat level system work (low/medium/high/critical)?**

> **A:** For non-speech (PANNS): the ratio `confidence / base_threshold` determines severity: ratio < 2.0 → low, 2.0–5.0 → medium, 5.0–10.0 → high, ≥ 10.0 → critical. For speech: threat_score ≥ 0.6 → high, ≥ 0.4 → medium, otherwise → low. Combined threats (both engines fire) take the HIGHEST level from either source, ensuring speech threats are never downgraded by a lower-scoring non-speech result.

---

**Q12. What privacy protections are built into the audio system?**

> **A:** (1) Raw audio is discarded immediately after feature extraction — only the 144-dimension feature vectors and classification results are retained. (2) Speech transcription results contain only threat keywords, not full transcripts. (3) No audio recordings are stored on the server. (4) The system is designed to comply with GDPR principles. Documentation explicitly states: "Privacy: At this point, raw audio should be discarded. Only features and results are retained."

---

**Q13. How does the system handle the case where PANNS is not available?**

> **A:** The `ThreatDetector.__init__()` calls `self.panns_detector.initialize()` and stores the result in `self.panns_available`. Throughout all detection code, this flag is checked: if True, PANNS CNN14 processes raw audio directly; if False, the custom CNN-BiLSTM model is used with MFCC feature extraction. Custom model thresholds (`custom_class_thresholds`) are on a different scale and are switched in automatically. All sensitivity settings also maintain two parallel threshold sets.

---

**Q14. What datasets were used to train the custom non-speech model?**

> **A:** The `Non Speech Dataset` folder contains 5 categories: Normal, crying, glass_breaking, screaming, shouting. Screaming is the majority class (~1826 files) while shouting is a severe minority (~31 files). To handle this imbalance: Focal Loss with γ=2.0 down-weights easy examples, inverse-frequency class weights give stronger corrections to minority classes, and a `WeightedRandomSampler` over-samples minority classes in every batch to ensure they appear regularly during training.

---

**Q15. How does the system connect from the Laravel web application to the Python Flask API?**

> **A:** Laravel's `AudioThreatController` uses the `Http::timeout(30)->post()` method from Laravel's HTTP client (built on Guzzle). The browser captures audio via the Web Audio API, encodes it as base64, and POSTs it to `/admin/audio-threat/analyze`. The controller forwards it to the Flask API at `{apiBaseUrl}/api/audio/analyze` with additional parameters (format, sample_rate, session_id). The response (JSON with threat classification) is returned to the frontend dashboard in real time.

---

**Q16. How is audio quality controlled for reliable threat detection?**

> **A:** Multiple quality gates: (1) Audio must exceed minimum RMS energy (0.010) — silence is skipped. (2) After noise calibration, audio must exceed `noise_mean + 2σ` — background noise is skipped. (3) Spectral subtraction denoising removes the estimated noise floor. (4) Pre-emphasis filtering (high-pass) boosts high-frequency content that differentiates threat sounds. (5) Resampling to a fixed 16kHz ensures consistent feature extraction regardless of input sample rate.

---

**Q17. What is CosineAnnealingWarmRestarts and why use it as the learning rate scheduler?**

> **A:** CosineAnnealingWarmRestarts (SGDR) reduces the learning rate following a cosine curve from initial LR to `eta_min` over `T_0=20` epochs, then restarts. On restart, the period doubles (`T_mult=2`). This strategy helps escape local minima (the restarts act as "kick starts") while the cosine decay ensures stable convergence. For audio classification with imbalanced data, this scheduler has been shown to outperform simple step decay, helping the model find better generalizing solutions.

---

*End of Document 2*

