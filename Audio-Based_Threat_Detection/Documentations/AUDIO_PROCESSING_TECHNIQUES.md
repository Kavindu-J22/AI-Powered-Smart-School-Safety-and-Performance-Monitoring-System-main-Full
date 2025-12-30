# 🎵 Audio Processing Techniques

**Document Version**: 1.0  
**Last Updated**: December 30, 2025  
**System**: AI-Powered Smart School Safety - Audio Threat Detection

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Audio Preprocessing Pipeline](#audio-preprocessing-pipeline)
3. [Feature Extraction Techniques](#feature-extraction-techniques)
4. [Signal Processing Methods](#signal-processing-methods)
5. [Implementation Details](#implementation-details)

---

## 🎯 Overview

The Audio Threat Detection System uses advanced digital signal processing (DSP) techniques to extract meaningful features from raw audio for threat classification.

### **Processing Pipeline**:
```
Raw Audio → Preprocessing → Feature Extraction → Model Input
```

### **Key Technologies**:
- **PyTorch** & **Torchaudio**: Deep learning and audio processing
- **NumPy**: Numerical computations
- **SoundFile**: Audio I/O
- **Pydub**: Format conversion

---

## 🔧 Audio Preprocessing Pipeline

### **File**: `utils/audio_processor.py`

### **1. Audio Loading** (`load_audio()`)

**Purpose**: Load audio files in various formats and convert to standard format.

**Implementation**:
```python
def load_audio(self, file_path: str) -> Tuple[np.ndarray, int]:
    # Try torchaudio first (good format support)
    waveform, sr = torchaudio.load(file_path)
    
    # Convert to mono if stereo
    if waveform.shape[0] > 1:
        waveform = torch.mean(waveform, dim=0, keepdim=True)
    
    # Resample if needed
    if sr != self.sample_rate:
        resampler = torchaudio.transforms.Resample(sr, self.sample_rate)
        waveform = resampler(waveform)
    
    return waveform.squeeze().numpy(), self.sample_rate
```

**Techniques Used**:
- **Stereo to Mono Conversion**: Average left and right channels
- **Resampling**: Convert to 16,000 Hz standard sample rate
- **Format Support**: WAV, MP3, WebM, OGG, FLAC

**Location**: `audio_processor.py:28-55`

---

### **2. Audio Normalization** (`normalize_audio()`)

**Purpose**: Scale audio amplitude to [-1, 1] range for consistent processing.

**Implementation**:
```python
def normalize_audio(self, audio: np.ndarray) -> np.ndarray:
    max_val = np.max(np.abs(audio))
    if max_val > 0:
        audio = audio / max_val
    return audio
```

**Mathematical Formula**:
```
normalized_audio = audio / max(|audio|)
```

**Benefits**:
- Consistent amplitude across different recordings
- Prevents numerical overflow
- Improves model performance

**Location**: `audio_processor.py:93-98`

---

### **3. Silence Trimming** (`_trim_silence()`)

**Purpose**: Remove silent portions from beginning and end of audio.

**Implementation**:
```python
def _trim_silence(self, audio: np.ndarray, top_db: float = 30) -> np.ndarray:
    # Calculate energy threshold
    ref = np.max(np.abs(audio))
    threshold = ref * (10 ** (-top_db / 20))
    
    # Find non-silent indices
    non_silent = np.abs(audio) > threshold
    
    # Find start and end
    start = np.argmax(non_silent)
    end = len(audio) - np.argmax(non_silent[::-1])
    
    return audio[start:end]
```

**Mathematical Formula**:
```
threshold = max(|audio|) × 10^(-top_db/20)
```

**Parameters**:
- `top_db = 30`: Silence threshold (30 dB below peak)

**Benefits**:
- Removes leading/trailing silence
- Focuses on actual audio content
- Reduces processing time

**Location**: `audio_processor.py:72-91`

---

### **4. Audio Chunking** (`split_into_chunks()`)

**Purpose**: Split long audio into overlapping chunks for processing.

**Implementation**:
```python
def split_into_chunks(self, audio: np.ndarray) -> list:
    chunk_samples = int(self.chunk_duration * self.sample_rate)  # 4 seconds
    overlap_samples = int(chunk_samples * self.overlap)  # 50% overlap
    step = chunk_samples - overlap_samples
    
    chunks = []
    for start in range(0, len(audio) - chunk_samples + 1, step):
        chunk = audio[start:start + chunk_samples]
        chunks.append(chunk)
    
    return chunks
```

**Parameters**:
- **Chunk Duration**: 4 seconds (64,000 samples at 16 kHz)
- **Overlap**: 50% (2 seconds)
- **Step Size**: 2 seconds (32,000 samples)

**Benefits**:
- Handles long audio files
- Overlap ensures no threats missed at boundaries
- Consistent input size for model

**Location**: `audio_processor.py:100-124`

---

### **5. Energy Calculation** (`calculate_energy()`)

**Purpose**: Calculate Root Mean Square (RMS) energy of audio.

**Implementation**:
```python
def calculate_energy(self, audio: np.ndarray) -> float:
    return float(np.sqrt(np.mean(audio**2)))
```

**Mathematical Formula**:
```
RMS = √(1/N × Σ(x[i]²))
```

**Use Cases**:
- Detect silent audio
- Measure audio loudness
- Noise floor estimation

**Location**: `audio_processor.py:164-166`

---

## 🎼 Feature Extraction Techniques

### **File**: `utils/feature_extractor.py`

### **1. MFCC (Mel-Frequency Cepstral Coefficients)**

**Purpose**: Extract perceptually-relevant frequency features.

**Implementation**:
```python
def extract_mfcc(self, audio: np.ndarray) -> np.ndarray:
    waveform = torch.FloatTensor(audio).unsqueeze(0)
    
    # Extract MFCC
    mfcc = self.mfcc_transform(waveform).squeeze(0).numpy()
    
    # Add delta and delta-delta features
    mfcc_delta = self._compute_delta(mfcc, order=1)
    mfcc_delta2 = self._compute_delta(mfcc, order=2)
    
    # Stack features
    features = np.vstack([mfcc, mfcc_delta, mfcc_delta2])
    return features
```

**Parameters**:
- **n_mfcc**: 13 coefficients
- **n_fft**: 2048 samples
- **hop_length**: 512 samples
- **n_mels**: 128 mel bands

**Output Shape**: `(39, time_steps)` - 13 MFCC + 13 delta + 13 delta-delta

**Why MFCC?**:
- Mimics human auditory perception
- Captures timbral characteristics
- Effective for speech and non-speech sounds
- Widely used in audio classification

**Location**: `feature_extractor.py:58-75`

---

### **2. Delta Features** (`_compute_delta()`)

**Purpose**: Capture temporal dynamics of audio features.

**Implementation**:
```python
def _compute_delta(self, features: np.ndarray, order: int = 1) -> np.ndarray:
    if order == 1:
        padded = np.pad(features, ((0, 0), (1, 1)), mode='edge')
        delta = (padded[:, 2:] - padded[:, :-2]) / 2
    else:  # order == 2 (delta-delta)
        first_delta = self._compute_delta(features, 1)
        delta = self._compute_delta(first_delta, 1)
    return delta
```

**Mathematical Formula**:
```
Δ[t] = (features[t+1] - features[t-1]) / 2
ΔΔ[t] = (Δ[t+1] - Δ[t-1]) / 2
```

**Benefits**:
- Captures rate of change
- Improves classification accuracy
- Adds temporal context

**Location**: `feature_extractor.py:48-56`

---

### **3. Spectral Centroid**

**Purpose**: Measure the "center of mass" of the spectrum.

**Implementation**:
```python
# Frequency bins
freqs = np.fft.rfftfreq(self.n_fft, 1/self.sample_rate)

# Spectral Centroid
norm = magnitude.sum(axis=0) + 1e-8
spectral_centroid = np.sum(freqs[:, None] * magnitude, axis=0) / norm
```

**Mathematical Formula**:
```
Centroid = Σ(f[i] × magnitude[i]) / Σ(magnitude[i])
```

**Interpretation**:
- **High centroid**: Bright, high-frequency sounds (screaming, glass breaking)
- **Low centroid**: Dark, low-frequency sounds (normal speech)

**Location**: `feature_extractor.py:98-99`

---

### **4. Spectral Bandwidth**

**Purpose**: Measure the spread of frequencies around the centroid.

**Implementation**:
```python
spectral_bandwidth = np.sqrt(
    np.sum(((freqs[:, None] - centroid) ** 2) * magnitude, axis=0) / norm
)
```

**Mathematical Formula**:
```
Bandwidth = √(Σ((f[i] - centroid)² × magnitude[i]) / Σ(magnitude[i]))
```

**Interpretation**:
- **High bandwidth**: Wide frequency spread (shouting, screaming)
- **Low bandwidth**: Narrow frequency spread (normal speech)

**Location**: `feature_extractor.py:102-105`

---

### **5. Spectral Rolloff**

**Purpose**: Frequency below which 85% of spectral energy is contained.

**Implementation**:
```python
cumsum = np.cumsum(magnitude, axis=0)
threshold = 0.85 * cumsum[-1]
rolloff_idx = np.argmax(cumsum >= threshold, axis=0)
spectral_rolloff = freqs[np.clip(rolloff_idx, 0, len(freqs)-1)]
```

**Interpretation**:
- **High rolloff**: Energy in high frequencies (screaming, glass breaking)
- **Low rolloff**: Energy in low frequencies (normal speech)

**Location**: `feature_extractor.py:108-111`

---

### **6. Zero Crossing Rate (ZCR)**

**Purpose**: Measure how often the signal crosses zero amplitude.

**Implementation**:
```python
zcr = np.abs(np.diff(np.sign(audio))).sum() / len(audio)
```

**Mathematical Formula**:
```
ZCR = (1/N) × Σ|sign(x[i]) - sign(x[i-1])|
```

**Interpretation**:
- **High ZCR**: Noisy, high-frequency sounds (glass breaking)
- **Low ZCR**: Tonal, low-frequency sounds (normal speech)

**Location**: `feature_extractor.py:114-115`

---

### **7. RMS Energy (per frame)**

**Purpose**: Measure energy in each time frame.

**Implementation**:
```python
frames = np.array([audio[i:i+frame_length] 
                   for i in range(0, len(audio)-frame_length+1, hop_length)])
rms = np.sqrt(np.mean(frames**2, axis=1))
```

**Mathematical Formula**:
```
RMS[frame] = √(1/N × Σ(x[i]²))
```

**Use Cases**:
- Detect loud events (screaming, shouting)
- Measure audio intensity over time

**Location**: `feature_extractor.py:118-129`

---

### **8. Spectral Contrast**

**Purpose**: Measure difference between peaks and valleys in spectrum.

**Implementation**:
```python
n_bands = 7
band_size = magnitude.shape[0] // n_bands
contrast = []
for i in range(n_bands):
    band = magnitude[i*band_size:(i+1)*band_size]
    contrast.append(np.max(band, axis=0) - np.min(band, axis=0))
```

**Interpretation**:
- **High contrast**: Clear harmonic structure (speech, crying)
- **Low contrast**: Noisy, broadband sounds (glass breaking)

**Location**: `feature_extractor.py:132-139`

---

### **9. Mel Spectrogram**

**Purpose**: Time-frequency representation on mel scale.

**Implementation**:
```python
mel_spec = self.mel_transform(waveform).squeeze(0)
mel_spec_db = torchaudio.transforms.AmplitudeToDB()(mel_spec)
```

**Parameters**:
- **n_mels**: 128 mel bands
- **n_fft**: 2048 samples
- **hop_length**: 512 samples

**Output**: 2D array (128 mel bands × time steps)

**Location**: `feature_extractor.py:143-150`

---

### **10. Feature Normalization** (`normalize_features()`)

**Purpose**: Standardize features using z-score normalization.

**Implementation**:
```python
def normalize_features(self, features: np.ndarray) -> Tuple:
    mean = np.mean(features, axis=1, keepdims=True)
    std = np.std(features, axis=1, keepdims=True) + 1e-8
    normalized = (features - mean) / std
    return normalized, mean, std
```

**Mathematical Formula**:
```
normalized = (features - μ) / σ
```

**Benefits**:
- Zero mean, unit variance
- Improves model convergence
- Prevents feature dominance

**Location**: `feature_extractor.py:196-205`

---

## 📊 Complete Feature Vector

### **Combined Features** (`extract_all_features()`)

**Output Shape**: `(51, 128)` - 51 features × 128 time steps

**Feature Breakdown**:
```
MFCC Features:           13 × 3 = 39 features
  - MFCC:                13
  - Delta MFCC:          13
  - Delta-Delta MFCC:    13

Spectral Features:       12 features
  - Spectral Centroid:   1
  - Spectral Bandwidth:  1
  - Spectral Rolloff:    1
  - Zero Crossing Rate:  1
  - RMS Energy:          1
  - Spectral Contrast:   7

Total:                   51 features
```

**Location**: `feature_extractor.py:152-178`

---

## 🔍 Summary Table

| Technique | Purpose | Output | Location |
|-----------|---------|--------|----------|
| **Audio Loading** | Load and convert formats | NumPy array | `audio_processor.py:28-55` |
| **Normalization** | Scale to [-1, 1] | NumPy array | `audio_processor.py:93-98` |
| **Silence Trimming** | Remove silence | NumPy array | `audio_processor.py:72-91` |
| **Chunking** | Split into segments | List of arrays | `audio_processor.py:100-124` |
| **MFCC** | Frequency features | (13, T) | `feature_extractor.py:58-75` |
| **Delta Features** | Temporal dynamics | (13, T) | `feature_extractor.py:48-56` |
| **Spectral Centroid** | Frequency center | (1, T) | `feature_extractor.py:98-99` |
| **Spectral Bandwidth** | Frequency spread | (1, T) | `feature_extractor.py:102-105` |
| **Spectral Rolloff** | High-frequency cutoff | (1, T) | `feature_extractor.py:108-111` |
| **Zero Crossing Rate** | Signal noisiness | (1, T) | `feature_extractor.py:114-115` |
| **RMS Energy** | Audio loudness | (1, T) | `feature_extractor.py:118-129` |
| **Spectral Contrast** | Harmonic structure | (7, T) | `feature_extractor.py:132-139` |
| **Feature Normalization** | Standardization | (51, 128) | `feature_extractor.py:196-205` |

---

**These techniques work together to extract robust, discriminative features for accurate threat detection.**

