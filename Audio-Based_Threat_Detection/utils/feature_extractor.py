"""
Feature Extractor Module
Extracts MFCC and spectral features for audio threat detection
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


class FeatureExtractor:
    """Extract acoustic features for threat detection models using torchaudio"""

    def __init__(self):
        self.sample_rate = AudioConfig.SAMPLE_RATE
        self.n_mfcc = AudioConfig.N_MFCC
        self.n_fft = AudioConfig.N_FFT
        self.hop_length = AudioConfig.HOP_LENGTH
        self.n_mels = AudioConfig.N_MELS
        self.fmax = AudioConfig.FMAX

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

    def extract_spectral_features(self, audio: np.ndarray) -> Dict[str, np.ndarray]:
        """Extract various spectral features"""
        features = {}

        # Convert to tensor
        waveform = torch.FloatTensor(audio)

        # Compute spectrogram
        spectrogram = torch.stft(
            waveform,
            n_fft=self.n_fft,
            hop_length=self.hop_length,
            return_complex=True
        )
        magnitude = torch.abs(spectrogram).numpy()

        # Frequency bins
        freqs = np.fft.rfftfreq(self.n_fft, 1/self.sample_rate)[:magnitude.shape[0]]

        # Spectral Centroid
        norm = magnitude.sum(axis=0) + 1e-8
        features['spectral_centroid'] = np.sum(freqs[:, None] * magnitude, axis=0) / norm

        # Spectral Bandwidth
        centroid = features['spectral_centroid']
        features['spectral_bandwidth'] = np.sqrt(
            np.sum(((freqs[:, None] - centroid) ** 2) * magnitude, axis=0) / norm
        )

        # Spectral Rolloff (85%)
        cumsum = np.cumsum(magnitude, axis=0)
        threshold = 0.85 * cumsum[-1]
        rolloff_idx = np.argmax(cumsum >= threshold, axis=0)
        features['spectral_rolloff'] = freqs[np.clip(rolloff_idx, 0, len(freqs)-1)]

        # Zero Crossing Rate
        zcr = np.abs(np.diff(np.sign(audio))).sum() / len(audio)
        features['zero_crossing_rate'] = np.full(magnitude.shape[1], zcr)

        # RMS Energy
        frame_length = self.n_fft
        frames = np.array([audio[i:i+frame_length] for i in range(0, len(audio)-frame_length+1, self.hop_length)])
        if len(frames) > 0:
            rms = np.sqrt(np.mean(frames**2, axis=1))
            # Pad or truncate to match magnitude time dimension
            if len(rms) < magnitude.shape[1]:
                rms = np.pad(rms, (0, magnitude.shape[1] - len(rms)), mode='edge')
            else:
                rms = rms[:magnitude.shape[1]]
            features['rms'] = rms
        else:
            features['rms'] = np.zeros(magnitude.shape[1])

        # Spectral Contrast (simplified - using 7 bands)
        n_bands = 7
        band_size = magnitude.shape[0] // n_bands
        contrast = []
        for i in range(n_bands):
            band = magnitude[i*band_size:(i+1)*band_size]
            if band.size > 0:
                contrast.append(np.max(band, axis=0) - np.min(band, axis=0))
        features['spectral_contrast'] = np.array(contrast) if contrast else np.zeros((n_bands, magnitude.shape[1]))

        return features

    def extract_mel_spectrogram(self, audio: np.ndarray) -> np.ndarray:
        """Extract mel spectrogram"""
        waveform = torch.FloatTensor(audio).unsqueeze(0)
        mel_spec = self.mel_transform(waveform).squeeze(0)

        # Convert to dB scale
        mel_spec_db = torchaudio.transforms.AmplitudeToDB()(mel_spec)
        return mel_spec_db.numpy()

    def extract_all_features(self, audio: np.ndarray) -> np.ndarray:
        """Extract combined feature vector for CNN-LSTM model"""
        # Get MFCC features (n_mfcc * 3 x time_steps)
        mfcc_features = self.extract_mfcc(audio)

        # Get spectral features
        spectral = self.extract_spectral_features(audio)

        # Combine spectral features
        spectral_combined = np.vstack([
            spectral['spectral_centroid'].reshape(1, -1),
            spectral['spectral_bandwidth'].reshape(1, -1),
            spectral['spectral_rolloff'].reshape(1, -1),
            spectral['zero_crossing_rate'].reshape(1, -1),
            spectral['rms'].reshape(1, -1),
            spectral['spectral_contrast']
        ])

        # Ensure same time dimension
        min_time = min(mfcc_features.shape[1], spectral_combined.shape[1])
        mfcc_features = mfcc_features[:, :min_time]
        spectral_combined = spectral_combined[:, :min_time]

        # Combine all features
        all_features = np.vstack([mfcc_features, spectral_combined])

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

