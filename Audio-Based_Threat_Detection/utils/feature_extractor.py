"""
Feature Extractor Module
Extracts rich acoustic features for audio threat detection:
  - MFCC (40) + delta (40) + delta2 (40) = 120
  - Chroma (12)  — critical for glass-break vs. cry discrimination
  - Spectral Centroid (1), Bandwidth (1), Rolloff (1), ZCR (1), RMS (1)
  - Spectral Contrast (7 bands)
  - Spectral Flatness (1)  — noise vs. tonal sound
  Total = 144 features per time-frame
Using torchaudio for Python 3.14 compatibility
"""
import numpy as np
import torch
import torchaudio
import torchaudio.transforms as T
from typing import Dict, Tuple
import os
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import AudioConfig

# ─── Number of features produced by extract_all_features ──────────────────
# MFCC*3(120) + Chroma(12) + Contrast(7) + Centroid/BW/Rolloff/ZCR/RMS(5) + Flatness(1) = 145
N_FEATURES = 145


class FeatureExtractor:
    """Extract rich acoustic features for threat detection models using torchaudio"""

    def __init__(self):
        self.sample_rate = AudioConfig.SAMPLE_RATE
        self.n_mfcc = AudioConfig.N_MFCC
        self.n_fft = AudioConfig.N_FFT
        self.hop_length = AudioConfig.HOP_LENGTH
        self.n_mels = AudioConfig.N_MELS
        self.fmax = AudioConfig.FMAX
        self.n_chroma = 12

        # Initialize torchaudio transforms
        self.mfcc_transform = T.MFCC(
            sample_rate=self.sample_rate,
            n_mfcc=self.n_mfcc,
            melkwargs={
                'n_fft': self.n_fft,
                'hop_length': self.hop_length,
                'n_mels': self.n_mels,
                'f_max': self.fmax
            }
        )

        self.mel_transform = T.MelSpectrogram(
            sample_rate=self.sample_rate,
            n_fft=self.n_fft,
            hop_length=self.hop_length,
            n_mels=self.n_mels,
            f_max=self.fmax
        )

        # Pre-compute frequency → chroma bin mapping once
        self._chroma_filterbank = self._build_chroma_filterbank()

    # ──────────────────────────────────────────────────────────────────────
    #  Helpers
    # ──────────────────────────────────────────────────────────────────────

    def _build_chroma_filterbank(self) -> np.ndarray:
        """
        Build a (n_chroma, n_fft//2+1) filterbank that maps each STFT
        frequency bin to its nearest chroma class.  Returns a binary
        indicator matrix so chroma = filterbank @ magnitude_spectrum.
        """
        n_freq = self.n_fft // 2 + 1
        freqs = np.fft.rfftfreq(self.n_fft, d=1.0 / self.sample_rate)
        filterbank = np.zeros((self.n_chroma, n_freq), dtype=np.float32)
        for i, f in enumerate(freqs):
            if f <= 0:
                continue
            # Convert frequency to chroma index (0-11)
            chroma_idx = int(round(12 * np.log2(f / 440.0))) % self.n_chroma
            filterbank[chroma_idx, i] += 1.0
        # L1-normalise each chroma row so values are comparable
        row_sum = filterbank.sum(axis=1, keepdims=True) + 1e-8
        filterbank /= row_sum
        return filterbank

    def _compute_delta(self, features: np.ndarray, order: int = 1) -> np.ndarray:
        """Compute delta features manually"""
        if order == 1:
            padded = np.pad(features, ((0, 0), (1, 1)), mode='edge')
            delta = (padded[:, 2:] - padded[:, :-2]) / 2
        else:
            first_delta = self._compute_delta(features, 1)
            delta = self._compute_delta(first_delta, 1)
        return delta

    def extract_mfcc(self, audio: np.ndarray) -> np.ndarray:
        """Extract MFCC features using torchaudio"""
        # Convert to tensor
        if isinstance(audio, np.ndarray):
            waveform = torch.FloatTensor(audio).unsqueeze(0)
        else:
            waveform = audio.unsqueeze(0) if audio.dim() == 1 else audio

        # Extract MFCC
        mfcc = self.mfcc_transform(waveform).squeeze(0).numpy()

        # Add delta and delta-delta features
        mfcc_delta = self._compute_delta(mfcc, order=1)
        mfcc_delta2 = self._compute_delta(mfcc, order=2)

        # Stack features
        features = np.vstack([mfcc, mfcc_delta, mfcc_delta2])
        return features

    def extract_chroma(self, magnitude: np.ndarray) -> np.ndarray:
        """
        Compute chroma (12 pitch-class) features from a pre-computed
        magnitude spectrogram  shape (n_freq, T).
        Returns (12, T).
        """
        n_freq = magnitude.shape[0]
        fb = self._chroma_filterbank[:, :n_freq]   # guard against size mismatch
        chroma = fb @ magnitude                     # (12, T)
        # normalise per frame
        frame_norm = chroma.max(axis=0, keepdims=True) + 1e-8
        chroma = chroma / frame_norm
        return chroma.astype(np.float32)

    def extract_spectral_features(self, audio: np.ndarray) -> Dict[str, np.ndarray]:
        """Extract rich spectral features including chroma and spectral flatness"""
        features = {}

        waveform = torch.FloatTensor(audio)

        # STFT with Hann window to reduce spectral leakage
        spectrogram = torch.stft(
            waveform,
            n_fft=self.n_fft,
            hop_length=self.hop_length,
            window=torch.hann_window(self.n_fft),
            return_complex=True
        )
        magnitude = torch.abs(spectrogram).numpy()   # (n_freq, T)
        T = magnitude.shape[1]

        freqs = np.fft.rfftfreq(self.n_fft, 1.0 / self.sample_rate)[:magnitude.shape[0]]
        norm = magnitude.sum(axis=0) + 1e-8

        # Spectral Centroid
        features['spectral_centroid'] = (np.sum(freqs[:, None] * magnitude, axis=0) / norm)

        # Spectral Bandwidth
        centroid = features['spectral_centroid']
        features['spectral_bandwidth'] = np.sqrt(
            np.sum(((freqs[:, None] - centroid) ** 2) * magnitude, axis=0) / norm
        )

        # Spectral Rolloff (85 %)
        cumsum = np.cumsum(magnitude, axis=0)
        rolloff_idx = np.argmax(cumsum >= 0.85 * cumsum[-1], axis=0)
        features['spectral_rolloff'] = freqs[np.clip(rolloff_idx, 0, len(freqs) - 1)]

        # Zero Crossing Rate (scalar → broadcast to T frames)
        zcr = float(np.abs(np.diff(np.sign(audio))).sum() / max(len(audio), 1))
        features['zero_crossing_rate'] = np.full(T, zcr, dtype=np.float32)

        # RMS Energy per frame
        frames = [audio[i:i + self.n_fft]
                  for i in range(0, len(audio) - self.n_fft + 1, self.hop_length)]
        if frames:
            rms = np.sqrt(np.mean(np.array(frames) ** 2, axis=1))
            if len(rms) < T:
                rms = np.pad(rms, (0, T - len(rms)), mode='edge')
            rms = rms[:T]
        else:
            rms = np.zeros(T, dtype=np.float32)
        features['rms'] = rms

        # Spectral Contrast (7 sub-bands)
        n_bands = 7
        band_size = max(1, magnitude.shape[0] // n_bands)
        contrast_rows = []
        for i in range(n_bands):
            band = magnitude[i * band_size:(i + 1) * band_size]
            if band.size > 0:
                contrast_rows.append(np.max(band, axis=0) - np.min(band, axis=0))
            else:
                contrast_rows.append(np.zeros(T, dtype=np.float32))
        features['spectral_contrast'] = np.array(contrast_rows)  # (7, T)

        # ── NEW: Spectral Flatness ──────────────────────────────────────────
        # Ratio of geometric mean to arithmetic mean of the spectrum.
        # High → noise-like (glass breaking); Low → tonal (crying, screaming).
        log_mag = np.log(magnitude + 1e-8)
        geo_mean = np.exp(log_mag.mean(axis=0))
        arith_mean = magnitude.mean(axis=0) + 1e-8
        features['spectral_flatness'] = geo_mean / arith_mean   # (T,)

        # ── NEW: Chroma (12 pitch-classes) ─────────────────────────────────
        features['chroma'] = self.extract_chroma(magnitude)     # (12, T)

        return features

    def extract_mel_spectrogram(self, audio: np.ndarray) -> np.ndarray:
        """Extract mel spectrogram (dB scale)"""
        waveform = torch.FloatTensor(audio).unsqueeze(0)
        mel_spec = self.mel_transform(waveform).squeeze(0)
        mel_spec_db = torchaudio.transforms.AmplitudeToDB()(mel_spec)
        return mel_spec_db.numpy()

    def extract_all_features(self, audio: np.ndarray) -> np.ndarray:
        """
        Extract combined 144-feature vector per time-frame for CNN-LSTM model.
        Layout:
          [0:120]   MFCC + Δ + ΔΔ          (40×3)
          [120:132] Chroma                  (12)
          [132]     Spectral Centroid
          [133]     Spectral Bandwidth
          [134]     Spectral Rolloff
          [135]     ZCR
          [136]     RMS
          [137:144] Spectral Contrast       (7)
          [144]     Spectral Flatness  ← wait, that gives 145. Re-count:
        Actual layout (144 rows):
          [0:120]   MFCC*3      = 120
          [120:132] Chroma      = 12
          [132:139] Contrast    = 7
          [139]     Centroid    = 1
          [140]     Bandwidth   = 1
          [141]     Rolloff     = 1
          [142]     ZCR         = 1
          [143]     RMS         = 1
          Total = 144  ✓
        """
        mfcc_features = self.extract_mfcc(audio)           # (120, T_m)
        spectral = self.extract_spectral_features(audio)    # various (T_s)

        T_m = mfcc_features.shape[1]
        T_s = spectral['spectral_centroid'].shape[0]
        T   = min(T_m, T_s)

        mfcc_features = mfcc_features[:, :T]

        spectral_combined = np.vstack([
            spectral['chroma'][:, :T],                              # 12 rows
            spectral['spectral_contrast'][:, :T],                   # 7  rows
            spectral['spectral_centroid'][:T].reshape(1, -1),       # 1
            spectral['spectral_bandwidth'][:T].reshape(1, -1),      # 1
            spectral['spectral_rolloff'][:T].reshape(1, -1),        # 1
            spectral['zero_crossing_rate'][:T].reshape(1, -1),      # 1
            spectral['rms'][:T].reshape(1, -1),                     # 1
        ])  # shape: (23, T)

        all_features = np.vstack([mfcc_features, spectral_combined])  # (143, T)

        # Add spectral flatness as last row → 144 total
        flatness = spectral['spectral_flatness'][:T].reshape(1, -1)
        all_features = np.vstack([all_features, flatness])            # (144, T)

        assert all_features.shape[0] == N_FEATURES, \
            f"Feature dim mismatch: got {all_features.shape[0]}, expected {N_FEATURES}"

        return all_features

    def extract_fixed_length_features(self, audio: np.ndarray, target_length: int = 128) -> np.ndarray:
        """Extract features with fixed time dimension for model input"""
        features = self.extract_all_features(audio)

        # Pad or truncate to target length
        current_length = features.shape[1]
        if current_length < target_length:
            # Pad with zeros
            pad_width = target_length - current_length
            features = np.pad(features, ((0, 0), (0, pad_width)), mode='constant')
        else:
            # Truncate
            features = features[:, :target_length]

        return features

    def normalize_features(self, features: np.ndarray, mean: np.ndarray = None,
                          std: np.ndarray = None) -> Tuple[np.ndarray, np.ndarray, np.ndarray]:
        """Normalize features using z-score normalization"""
        if mean is None:
            mean = np.mean(features, axis=1, keepdims=True)
        if std is None:
            std = np.std(features, axis=1, keepdims=True) + 1e-8

        normalized = (features - mean) / std
        return normalized, mean, std

