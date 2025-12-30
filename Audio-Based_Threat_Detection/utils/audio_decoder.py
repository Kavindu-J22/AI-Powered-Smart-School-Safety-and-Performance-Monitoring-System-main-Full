"""
Audio Decoder with Silent FFmpeg
Handles audio decoding without verbose FFmpeg output
"""
import os
import sys
import subprocess
import io
import numpy as np
from typing import Optional

# Suppress FFmpeg output
DEVNULL = subprocess.DEVNULL if hasattr(subprocess, 'DEVNULL') else open(os.devnull, 'wb')


def decode_with_pydub_silent(audio_bytes: bytes, format: str = 'webm', sample_rate: int = 16000) -> Optional[np.ndarray]:
    """
    Decode audio using pydub with suppressed FFmpeg output
    
    Args:
        audio_bytes: Raw audio bytes
        format: Audio format (webm, wav, mp3, etc.)
        sample_rate: Target sample rate
        
    Returns:
        Numpy array of audio samples or None if failed
    """
    try:
        from pydub import AudioSegment
        from pydub.utils import mediainfo
        
        # Monkey-patch pydub to suppress FFmpeg stderr
        original_popen = subprocess.Popen
        
        def silent_popen(*args, **kwargs):
            # Redirect stderr to null
            kwargs['stderr'] = DEVNULL
            kwargs['stdout'] = subprocess.PIPE
            return original_popen(*args, **kwargs)
        
        # Temporarily replace Popen
        subprocess.Popen = silent_popen
        
        try:
            audio_buffer = io.BytesIO(audio_bytes)
            audio_segment = AudioSegment.from_file(audio_buffer, format=format)
            audio_segment = audio_segment.set_frame_rate(sample_rate).set_channels(1)
            samples = np.array(audio_segment.get_array_of_samples(), dtype=np.float32)
            audio = samples / 32768.0
            return audio
        finally:
            # Restore original Popen
            subprocess.Popen = original_popen
            
    except Exception:
        return None


def detect_audio_format(audio_bytes: bytes) -> str:
    """
    Detect audio format from magic bytes
    
    Args:
        audio_bytes: Raw audio bytes
        
    Returns:
        Format string (webm, wav, mp3, ogg, or unknown)
    """
    if len(audio_bytes) < 4:
        return 'unknown'
    
    # Check magic bytes
    if audio_bytes[:4] == b'\x1a\x45\xdf\xa3':
        return 'webm'
    elif audio_bytes[:4] == b'RIFF':
        return 'wav'
    elif audio_bytes[:4] == b'OggS':
        return 'ogg'
    elif audio_bytes[:3] == b'ID3' or audio_bytes[:2] == b'\xff\xfb':
        return 'mp3'
    elif audio_bytes[:4] == b'fLaC':
        return 'flac'
    
    return 'unknown'


def decode_audio_smart(audio_bytes: bytes, sample_rate: int = 16000) -> np.ndarray:
    """
    Smart audio decoder that tries multiple methods with minimal logging
    
    Args:
        audio_bytes: Raw audio bytes
        sample_rate: Target sample rate
        
    Returns:
        Numpy array of audio samples
        
    Raises:
        Exception if all decoding methods fail
    """
    # Detect format
    audio_format = detect_audio_format(audio_bytes)
    
    # If known format, try appropriate decoder
    if audio_format != 'unknown':
        # Try soundfile first (faster, quieter)
        try:
            import soundfile as sf
            audio_buffer = io.BytesIO(audio_bytes)
            audio, sr = sf.read(audio_buffer)
            if len(audio.shape) > 1:
                audio = audio.mean(axis=1)
            if sr != sample_rate:
                import torchaudio
                import torch
                waveform = torch.FloatTensor(audio).unsqueeze(0)
                resampler = torchaudio.transforms.Resample(sr, sample_rate)
                audio = resampler(waveform).squeeze().numpy()
            return audio.astype(np.float32)
        except Exception:
            pass
        
        # Try pydub with silent FFmpeg
        audio = decode_with_pydub_silent(audio_bytes, audio_format, sample_rate)
        if audio is not None:
            return audio
    
    # Fallback: Try raw PCM16
    if len(audio_bytes) % 2 == 0:
        try:
            audio_array = np.frombuffer(audio_bytes, dtype=np.int16)
            audio = audio_array.astype(np.float32) / 32768.0
            if np.max(np.abs(audio)) <= 1.5:
                return audio
        except Exception:
            pass
    
    # Last resort: Try raw PCM32
    if len(audio_bytes) % 4 == 0:
        try:
            audio = np.frombuffer(audio_bytes, dtype=np.float32)
            if np.max(np.abs(audio)) <= 2.0:
                return audio
        except Exception:
            pass
    
    raise Exception("Could not decode audio - unsupported format or corrupted data")

