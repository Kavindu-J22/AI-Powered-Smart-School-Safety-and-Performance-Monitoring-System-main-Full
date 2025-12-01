"""
Audio Processor Module
Handles audio loading, preprocessing, and format conversion
Using torchaudio for Python 3.14 compatibility
"""
import numpy as np
import torch
import torchaudio
import soundfile as sf
from pydub import AudioSegment
import io
import base64
from typing import Tuple, Optional, Union
import os
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import AudioConfig


class AudioProcessor:
    """Handles audio loading and preprocessing for threat detection"""

    def __init__(self, sample_rate: int = AudioConfig.SAMPLE_RATE):
        self.sample_rate = sample_rate
        self.chunk_duration = AudioConfig.CHUNK_DURATION
        self.overlap = AudioConfig.OVERLAP

    def load_audio(self, file_path: str) -> Tuple[np.ndarray, int]:
        """Load audio file and return waveform and sample rate"""
        try:
            # Try torchaudio first (good format support)
            waveform, sr = torchaudio.load(file_path)

            # Convert to mono if stereo
            if waveform.shape[0] > 1:
                waveform = torch.mean(waveform, dim=0, keepdim=True)

            # Resample if needed
            if sr != self.sample_rate:
                resampler = torchaudio.transforms.Resample(sr, self.sample_rate)
                waveform = resampler(waveform)

            audio = waveform.squeeze().numpy()
            return audio, self.sample_rate

        except Exception as e:
            # Fallback to pydub for mp3 files
            try:
                audio_segment = AudioSegment.from_file(file_path)
                audio_segment = audio_segment.set_frame_rate(self.sample_rate).set_channels(1)
                samples = np.array(audio_segment.get_array_of_samples(), dtype=np.float32)
                samples = samples / (2**15)  # Normalize to [-1, 1]
                return samples, self.sample_rate
            except Exception as e2:
                raise Exception(f"Failed to load audio: {e2}")
    
    def preprocess_audio(self, audio: np.ndarray) -> np.ndarray:
        """Preprocess audio: normalize, remove silence, and trim"""
        # Normalize amplitude
        audio = self.normalize_audio(audio)

        # Remove silence from beginning and end (manual implementation)
        audio = self._trim_silence(audio, top_db=30)

        # Ensure minimum length
        min_samples = int(self.sample_rate * 0.5)  # At least 0.5 seconds
        if len(audio) < min_samples:
            audio = np.pad(audio, (0, min_samples - len(audio)), mode='constant')

        return audio

    def _trim_silence(self, audio: np.ndarray, top_db: float = 30) -> np.ndarray:
        """Trim silence from beginning and end of audio"""
        # Calculate energy threshold
        ref = np.max(np.abs(audio))
        if ref == 0:
            return audio

        threshold = ref * (10 ** (-top_db / 20))

        # Find non-silent indices
        non_silent = np.abs(audio) > threshold

        if not np.any(non_silent):
            return audio

        # Find start and end
        start = np.argmax(non_silent)
        end = len(audio) - np.argmax(non_silent[::-1])

        return audio[start:end]
    
    def normalize_audio(self, audio: np.ndarray) -> np.ndarray:
        """Normalize audio to [-1, 1] range"""
        max_val = np.max(np.abs(audio))
        if max_val > 0:
            audio = audio / max_val
        return audio
    
    def split_into_chunks(self, audio: np.ndarray) -> list:
        """Split audio into overlapping chunks for processing"""
        chunk_samples = int(self.chunk_duration * self.sample_rate)
        overlap_samples = int(chunk_samples * self.overlap)
        step = chunk_samples - overlap_samples
        
        chunks = []
        for start in range(0, len(audio) - chunk_samples + 1, step):
            chunk = audio[start:start + chunk_samples]
            chunks.append(chunk)
        
        # Handle remaining audio
        if len(audio) > chunk_samples and (len(audio) - chunk_samples) % step != 0:
            # Pad the last chunk if needed
            last_chunk = audio[-chunk_samples:]
            if len(last_chunk) < chunk_samples:
                last_chunk = np.pad(last_chunk, (0, chunk_samples - len(last_chunk)))
            chunks.append(last_chunk)
        
        # If audio is shorter than chunk duration, pad it
        if len(chunks) == 0:
            padded = np.pad(audio, (0, chunk_samples - len(audio)), mode='constant')
            chunks.append(padded)
        
        return chunks
    
    def decode_base64_audio(self, base64_data: str) -> np.ndarray:
        """Decode base64 audio data from browser"""
        try:
            # Remove data URL prefix if present
            if ',' in base64_data:
                base64_data = base64_data.split(',')[1]

            audio_bytes = base64.b64decode(base64_data)
            audio_buffer = io.BytesIO(audio_bytes)

            # Try to load with soundfile
            try:
                audio, sr = sf.read(audio_buffer)
                if sr != self.sample_rate:
                    # Resample using torchaudio
                    waveform = torch.FloatTensor(audio).unsqueeze(0)
                    resampler = torchaudio.transforms.Resample(sr, self.sample_rate)
                    audio = resampler(waveform).squeeze().numpy()
            except:
                # Try with pydub for webm/mp4
                audio_buffer.seek(0)
                audio_segment = AudioSegment.from_file(audio_buffer)
                audio_segment = audio_segment.set_frame_rate(self.sample_rate).set_channels(1)
                audio = np.array(audio_segment.get_array_of_samples(), dtype=np.float32)
                audio = audio / (2**15)

            return audio
        except Exception as e:
            raise Exception(f"Failed to decode base64 audio: {e}")
    
    def convert_webm_to_wav(self, webm_data: bytes) -> np.ndarray:
        """Convert WebM audio to WAV format numpy array"""
        audio_buffer = io.BytesIO(webm_data)
        audio_segment = AudioSegment.from_file(audio_buffer, format="webm")
        audio_segment = audio_segment.set_frame_rate(self.sample_rate).set_channels(1)
        samples = np.array(audio_segment.get_array_of_samples(), dtype=np.float32)
        return samples / (2**15)
    
    def calculate_energy(self, audio: np.ndarray) -> float:
        """Calculate RMS energy of audio"""
        return float(np.sqrt(np.mean(audio**2)))
    
    def is_silent(self, audio: np.ndarray, threshold: float = 0.01) -> bool:
        """Check if audio chunk is silent"""
        return self.calculate_energy(audio) < threshold

