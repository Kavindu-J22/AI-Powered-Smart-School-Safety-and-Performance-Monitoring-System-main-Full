"""
Unit Tests for Audio Threat Detection Models
Using PyTorch backend
"""
import sys
import os
import unittest
import numpy as np

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

# Only import what we're testing - avoid heavy imports if not needed
from utils.audio_processor import AudioProcessor
from utils.feature_extractor import FeatureExtractor
from utils.noise_profiler import NoiseProfiler
from models.speech_threat_model import SpeechThreatDetector


class TestAudioProcessor(unittest.TestCase):
    """Test AudioProcessor class"""
    
    def setUp(self):
        self.processor = AudioProcessor()
    
    def test_normalize_audio(self):
        """Test audio normalization"""
        audio = np.array([0.5, -0.5, 1.0, -1.0])
        normalized = self.processor.normalize_audio(audio)
        self.assertAlmostEqual(np.max(np.abs(normalized)), 1.0)
    
    def test_calculate_energy(self):
        """Test energy calculation"""
        audio = np.ones(1000) * 0.5
        energy = self.processor.calculate_energy(audio)
        self.assertAlmostEqual(energy, 0.5, places=2)
    
    def test_is_silent(self):
        """Test silence detection"""
        silent_audio = np.zeros(1000)
        loud_audio = np.ones(1000) * 0.5
        
        self.assertTrue(self.processor.is_silent(silent_audio))
        self.assertFalse(self.processor.is_silent(loud_audio))
    
    def test_split_into_chunks(self):
        """Test audio chunking"""
        audio = np.random.randn(48000)  # 3 seconds at 16kHz
        chunks = self.processor.split_into_chunks(audio)
        
        self.assertGreater(len(chunks), 0)
        for chunk in chunks:
            self.assertEqual(len(chunk), int(self.processor.chunk_duration * self.processor.sample_rate))


class TestFeatureExtractor(unittest.TestCase):
    """Test FeatureExtractor class"""
    
    def setUp(self):
        self.extractor = FeatureExtractor()
        self.test_audio = np.random.randn(32000)  # 2 seconds
    
    def test_extract_mfcc(self):
        """Test MFCC extraction"""
        mfcc = self.extractor.extract_mfcc(self.test_audio)
        
        self.assertEqual(mfcc.shape[0], self.extractor.n_mfcc * 3)  # MFCC + delta + delta2
        self.assertGreater(mfcc.shape[1], 0)
    
    def test_extract_spectral_features(self):
        """Test spectral feature extraction"""
        features = self.extractor.extract_spectral_features(self.test_audio)
        
        expected_keys = ['spectral_centroid', 'spectral_bandwidth', 
                        'spectral_rolloff', 'zero_crossing_rate', 'rms']
        
        for key in expected_keys:
            self.assertIn(key, features)
            self.assertGreater(len(features[key]), 0)
    
    def test_extract_fixed_length_features(self):
        """Test fixed-length feature extraction"""
        target_length = 128
        features = self.extractor.extract_fixed_length_features(
            self.test_audio, target_length=target_length
        )
        
        self.assertEqual(features.shape[1], target_length)


class TestNoiseProfiler(unittest.TestCase):
    """Test NoiseProfiler class"""
    
    def setUp(self):
        self.profiler = NoiseProfiler()
    
    def test_initial_state(self):
        """Test initial state"""
        self.assertFalse(self.profiler.is_calibrated)
        self.assertIsNone(self.profiler.current_noise_floor)
    
    def test_update_noise_profile(self):
        """Test noise profile update"""
        noise = np.random.randn(16000) * 0.01  # Low amplitude noise
        
        # Add multiple samples
        for _ in range(5):
            self.profiler.update_noise_profile(noise)
        
        self.assertTrue(self.profiler.is_calibrated)
        self.assertIsNotNone(self.profiler.current_noise_floor)
    
    def test_calculate_snr(self):
        """Test SNR calculation"""
        noise = np.random.randn(16000) * 0.01
        
        # Calibrate
        for _ in range(5):
            self.profiler.update_noise_profile(noise)
        
        # Test with louder signal
        signal = np.random.randn(16000) * 0.5
        snr = self.profiler.calculate_snr(signal)
        
        self.assertGreater(snr, 0)
    
    def test_reset(self):
        """Test profile reset"""
        noise = np.random.randn(16000) * 0.01
        for _ in range(5):
            self.profiler.update_noise_profile(noise)
        
        self.profiler.reset()
        
        self.assertFalse(self.profiler.is_calibrated)
        self.assertIsNone(self.profiler.current_noise_floor)


class TestSpeechThreatDetector(unittest.TestCase):
    """Test SpeechThreatDetector class"""
    
    def setUp(self):
        self.detector = SpeechThreatDetector()
    
    def test_detect_english_threats(self):
        """Test English threat detection"""
        threat_text = "I will hurt you if you don't stop"
        result = self.detector.detect_threats(threat_text, 'english')
        
        self.assertTrue(result['is_threat'])
        self.assertGreater(result['threat_score'], 0)
    
    def test_detect_no_threat(self):
        """Test safe text detection"""
        safe_text = "Hello, how are you today?"
        result = self.detector.detect_threats(safe_text, 'english')
        
        self.assertFalse(result['is_threat'])
        self.assertEqual(result['threat_level'], 'none')
    
    def test_detect_sinhala_threats(self):
        """Test Sinhala threat detection"""
        threat_text = "මම ඔයාව මරනවා"
        result = self.detector.detect_threats(threat_text, 'sinhala')
        
        self.assertTrue(result['is_threat'])


if __name__ == '__main__':
    unittest.main()

