"""
Adaptive Noise Profiler Module
Maintains noise profiles for accurate threat detection across varying acoustic environments
"""
import numpy as np
from collections import deque
from typing import Optional, Tuple
import os
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import NoiseConfig, AudioConfig

class NoiseProfiler:
    """Adaptive noise profiling for robust threat detection"""
    
    def __init__(self):
        self.sample_rate = AudioConfig.SAMPLE_RATE
        self.noise_samples = deque(maxlen=NoiseConfig.NOISE_FLOOR_SAMPLES)
        self.current_noise_floor = None
        self.current_noise_spectrum = None
        self.is_calibrated = False
        self.snr_minimum = NoiseConfig.SNR_MINIMUM
    
    def update_noise_profile(self, audio: np.ndarray) -> None:
        """Update noise profile with new ambient audio sample"""
        # Calculate noise energy
        energy = np.sqrt(np.mean(audio**2))
        
        # Calculate noise spectrum
        spectrum = np.abs(np.fft.rfft(audio))
        
        self.noise_samples.append({
            'energy': energy,
            'spectrum': spectrum
        })
        
        if len(self.noise_samples) >= 5:
            self._recalculate_noise_floor()
            self.is_calibrated = True
    
    def _recalculate_noise_floor(self) -> None:
        """Recalculate noise floor from collected samples"""
        energies = [s['energy'] for s in self.noise_samples]
        self.current_noise_floor = np.median(energies)
        
        # Average spectrum
        spectrums = [s['spectrum'] for s in self.noise_samples]
        min_len = min(len(s) for s in spectrums)
        spectrums = [s[:min_len] for s in spectrums]
        self.current_noise_spectrum = np.median(np.array(spectrums), axis=0)
    
    def denoise_audio(self, audio: np.ndarray) -> np.ndarray:
        """Apply spectral subtraction for noise reduction"""
        if not self.is_calibrated or self.current_noise_spectrum is None:
            return audio
        
        # Compute STFT
        n_fft = 2048
        hop_length = 512
        stft = np.fft.rfft(audio)
        
        # Ensure same length
        noise_len = len(self.current_noise_spectrum)
        stft_len = len(stft)
        min_len = min(noise_len, stft_len)
        
        # Spectral subtraction
        magnitude = np.abs(stft[:min_len])
        phase = np.angle(stft[:min_len])
        noise_mag = self.current_noise_spectrum[:min_len]
        
        # Subtract noise with flooring
        clean_magnitude = np.maximum(magnitude - noise_mag * 1.5, magnitude * 0.1)
        
        # Reconstruct
        clean_stft = clean_magnitude * np.exp(1j * phase)
        
        # Pad back to original length if needed
        if stft_len > min_len:
            clean_stft = np.pad(clean_stft, (0, stft_len - min_len))
        
        clean_audio = np.fft.irfft(clean_stft, n=len(audio))
        return clean_audio.astype(np.float32)
    
    def calculate_snr(self, audio: np.ndarray) -> float:
        """Calculate Signal-to-Noise Ratio"""
        if not self.is_calibrated or self.current_noise_floor is None:
            return float('inf')
        
        signal_energy = np.sqrt(np.mean(audio**2))
        noise_energy = self.current_noise_floor
        
        if noise_energy == 0:
            return float('inf')
        
        snr_linear = signal_energy / noise_energy
        snr_db = 20 * np.log10(snr_linear + 1e-10)
        
        return snr_db
    
    def is_significant_audio(self, audio: np.ndarray) -> bool:
        """Check if audio has significant signal above noise floor"""
        snr = self.calculate_snr(audio)
        return snr >= self.snr_minimum
    
    def get_adaptive_threshold(self, base_threshold: float) -> float:
        """Get adaptive detection threshold based on noise level"""
        if not self.is_calibrated or self.current_noise_floor is None:
            return base_threshold
        
        # Increase threshold in noisy environments
        noise_factor = min(self.current_noise_floor * 10, 1.5)
        adaptive_threshold = base_threshold * (1 + noise_factor * 0.2)
        
        return min(adaptive_threshold, 0.95)  # Cap at 95%
    
    def reset(self) -> None:
        """Reset noise profile"""
        self.noise_samples.clear()
        self.current_noise_floor = None
        self.current_noise_spectrum = None
        self.is_calibrated = False
    
    def get_status(self) -> dict:
        """Get current noise profiler status"""
        return {
            'is_calibrated': self.is_calibrated,
            'noise_floor': float(self.current_noise_floor) if self.current_noise_floor else None,
            'samples_collected': len(self.noise_samples),
            'samples_required': 5
        }

