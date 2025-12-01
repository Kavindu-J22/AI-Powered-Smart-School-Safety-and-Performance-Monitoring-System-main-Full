"""
Main Threat Detector Module
Combines non-speech and speech threat detection with privacy preservation
Professional-grade detection with false positive reduction
"""
import numpy as np
import time
from typing import Dict, Optional, Tuple, List
from collections import deque
import os
import sys

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import ModelConfig, AudioConfig
from utils.audio_processor import AudioProcessor
from utils.feature_extractor import FeatureExtractor
from utils.noise_profiler import NoiseProfiler
from models.non_speech_model import NonSpeechThreatModel
from models.speech_threat_model import SpeechThreatDetector


class ThreatDetector:
    """
    Main threat detection system combining non-speech and speech analysis.
    Implements professional-grade detection with false positive reduction.
    """

    def __init__(self):
        self.audio_processor = AudioProcessor()
        self.feature_extractor = FeatureExtractor()
        self.noise_profiler = NoiseProfiler()
        self.non_speech_model = NonSpeechThreatModel()
        self.speech_detector = SpeechThreatDetector()

        # Higher thresholds for professional detection
        self.non_speech_threshold = 0.92  # Very high base threshold
        self.speech_threshold = ModelConfig.SPEECH_THREAT_THRESHOLD
        self.max_latency = ModelConfig.MAX_LATENCY

        # Consecutive detection tracking (reduces false positives)
        self.detection_history: deque = deque(maxlen=5)
        self.consecutive_required = 3  # Must detect threat 3 times in a row

        # Energy-based filtering
        self.min_energy_threshold = 0.03  # Minimum audio energy to process
        self.high_energy_threshold = 0.20  # High energy = potential threat

        # Class-specific thresholds (very high to reduce false positives)
        self.class_thresholds = {
            'crying': 0.88,       # High threshold
            'screaming': 0.94,    # Very high - often confused with speech
            'shouting': 0.95,     # Very high - often confused with normal talking
            'glass_breaking': 0.85,  # Lower threshold - distinctive sound
            'normal': 0.0         # Always allow normal
        }

        # Load models
        self._load_models()
    
    def _load_models(self) -> None:
        """Load pre-trained models if available"""
        try:
            # Check for saved model and load it
            import os
            model_path = str(ModelConfig.NON_SPEECH_MODEL_PATH).replace('.h5', '.pth')
            if os.path.exists(model_path):
                print(f"Loading trained model from: {model_path}")
                # Build model first, then load weights
                self.non_speech_model.build_model()
                self.non_speech_model.load_model()
                print(f"Model loaded. Classes: {self.non_speech_model.classes}")
            else:
                print("No trained model found. Building new model...")
                self.non_speech_model.build_model()
                print("WARNING: Model not trained! Run 'python run_training.py' to train.")
        except Exception as e:
            print(f"Error loading non-speech model: {e}")
            self.non_speech_model.build_model()
    
    def _calculate_audio_energy(self, audio: np.ndarray) -> float:
        """Calculate RMS energy of audio signal"""
        return float(np.sqrt(np.mean(audio ** 2)))

    def _check_consecutive_detection(self, class_name: str, is_threat: bool) -> bool:
        """
        Check if threat was detected consecutively to reduce false positives.
        Returns True only if threat detected multiple times in a row.
        """
        self.detection_history.append({
            'class': class_name,
            'is_threat': is_threat
        })

        if not is_threat:
            return False

        # Count recent consecutive threat detections of the same class
        consecutive_count = 0
        for detection in reversed(self.detection_history):
            if detection['is_threat'] and detection['class'] == class_name:
                consecutive_count += 1
            else:
                break

        return consecutive_count >= self.consecutive_required

    def analyze_audio(self, audio_data: np.ndarray,
                      enable_speech: bool = True,
                      enable_non_speech: bool = True) -> Dict:
        """
        Analyze audio for threats (both speech and non-speech).
        Raw audio is discarded after feature extraction for privacy.
        Implements professional-grade detection with false positive reduction.
        """
        start_time = time.time()

        result = {
            'is_threat': False,
            'threat_type': None,
            'threat_level': 'none',
            'confidence': 0.0,
            'non_speech_result': None,
            'speech_result': None,
            'processing_time': 0.0,
            'latency_ok': True,
            'details': {}
        }

        try:
            # Preprocess audio
            processed_audio = self.audio_processor.preprocess_audio(audio_data)

            # Calculate audio energy for filtering
            audio_energy = self._calculate_audio_energy(processed_audio)
            result['details']['audio_energy'] = round(audio_energy, 4)

            # Skip very low energy audio (silence/background noise)
            if audio_energy < self.min_energy_threshold:
                result['details']['skipped'] = 'Audio energy too low (silence/background)'
                self.detection_history.append({'class': 'normal', 'is_threat': False})
                return result

            # Check if audio is significant (not just noise)
            if self.noise_profiler.is_calibrated:
                if not self.noise_profiler.is_significant_audio(processed_audio):
                    result['details']['skipped'] = 'Audio below noise threshold'
                    self.detection_history.append({'class': 'normal', 'is_threat': False})
                    return result

                # Apply noise reduction
                processed_audio = self.noise_profiler.denoise_audio(processed_audio)

            # Extract features (privacy: raw audio can be discarded after this)
            features = self.feature_extractor.extract_fixed_length_features(processed_audio)
            features_normalized, _, _ = self.feature_extractor.normalize_features(features)

            # Transpose for model input (batch, time, features)
            model_input = features_normalized.T

            # Non-speech threat detection
            if enable_non_speech:
                class_name, confidence, all_probs = self.non_speech_model.predict(model_input)

                # Get class-specific threshold
                class_threshold = self.class_thresholds.get(class_name, self.non_speech_threshold)

                # Apply adaptive threshold based on noise profile
                adaptive_threshold = self.noise_profiler.get_adaptive_threshold(class_threshold)

                # Additional check: for screaming/shouting, require higher energy
                if class_name in ['screaming', 'shouting']:
                    if audio_energy < self.high_energy_threshold:
                        # Low energy + screaming/shouting prediction = likely false positive
                        adaptive_threshold = min(0.95, adaptive_threshold + 0.1)

                # Initial threat determination
                initial_is_threat = (
                    class_name != 'normal' and
                    confidence >= adaptive_threshold
                )

                # Apply consecutive detection check to reduce false positives
                confirmed_threat = self._check_consecutive_detection(class_name, initial_is_threat)

                result['non_speech_result'] = {
                    'detected_class': class_name,
                    'confidence': confidence,
                    'is_threat': confirmed_threat,
                    'initial_detection': initial_is_threat,
                    'consecutive_confirmed': confirmed_threat,
                    'all_probabilities': dict(zip(
                        self.non_speech_model.classes,
                        [round(p, 4) for p in all_probs]
                    )),
                    'threshold_used': adaptive_threshold,
                    'class_threshold': class_threshold
                }

                if confirmed_threat:
                    result['is_threat'] = True
                    result['threat_type'] = 'non_speech'
                    result['confidence'] = confidence
                    result['details']['non_speech_class'] = class_name
            
            # Speech threat detection
            if enable_speech:
                speech_result = self.speech_detector.analyze_audio(
                    processed_audio,
                    AudioConfig.SAMPLE_RATE
                )

                # Get transcription info including any errors
                transcription = speech_result.get('transcription', {})

                result['speech_result'] = {
                    'text': speech_result.get('text', ''),
                    'is_threat': speech_result.get('is_threat', False),
                    'threat_level': speech_result.get('threat_level', 'none'),
                    'threat_score': speech_result.get('threat_score', 0.0),
                    'detected_keywords': speech_result.get('threat_analysis', {}).get('detected_keywords', []),
                    'engine': transcription.get('engine', 'none'),
                    'transcription_error': transcription.get('error')
                }

                # Speech threats don't need consecutive detection - immediate alert
                if speech_result.get('is_threat', False):
                    result['is_threat'] = True
                    if result['threat_type'] is None:
                        result['threat_type'] = 'speech'
                    else:
                        result['threat_type'] = 'combined'

                    speech_score = speech_result.get('threat_score', 0)
                    result['confidence'] = max(result['confidence'], speech_score)
                    result['details']['detected_text'] = speech_result.get('text', '')
                    result['details']['detected_keywords'] = speech_result.get('threat_analysis', {}).get('detected_keywords', [])
            
            # Determine overall threat level
            if result['is_threat']:
                if result['confidence'] >= 0.8:
                    result['threat_level'] = 'critical'
                elif result['confidence'] >= 0.6:
                    result['threat_level'] = 'high'
                elif result['confidence'] >= 0.4:
                    result['threat_level'] = 'medium'
                else:
                    result['threat_level'] = 'low'
        
        except Exception as e:
            result['details']['error'] = str(e)
        
        # Calculate processing time
        processing_time = time.time() - start_time
        result['processing_time'] = round(processing_time, 3)
        result['latency_ok'] = processing_time < self.max_latency
        
        # Privacy: At this point, raw audio should be discarded
        # Only features and results are retained
        
        return result
    
    def update_noise_profile(self, audio_data: np.ndarray) -> Dict:
        """Update noise profile with ambient audio"""
        self.noise_profiler.update_noise_profile(audio_data)
        return self.noise_profiler.get_status()

    def reset_noise_profile(self) -> None:
        """Reset the noise profiler"""
        self.noise_profiler.reset()

    def reset_detection_history(self) -> None:
        """Reset detection history - call when starting new detection session"""
        self.detection_history.clear()

    def set_sensitivity(self, level: str = 'normal') -> Dict:
        """
        Adjust detection sensitivity.

        Args:
            level: 'low' (fewer false positives), 'normal', or 'high' (more sensitive)

        Returns:
            Current sensitivity settings
        """
        if level == 'low':
            # Minimal false positives - only very clear threats
            self.consecutive_required = 4
            self.class_thresholds = {
                'crying': 0.94,
                'screaming': 0.96,
                'shouting': 0.97,
                'glass_breaking': 0.92,
                'normal': 0.0
            }
            self.min_energy_threshold = 0.05
            self.high_energy_threshold = 0.25
        elif level == 'high':
            # More sensitive, some false positives possible
            self.consecutive_required = 2
            self.class_thresholds = {
                'crying': 0.80,
                'screaming': 0.85,
                'shouting': 0.88,
                'glass_breaking': 0.75,
                'normal': 0.0
            }
            self.min_energy_threshold = 0.02
            self.high_energy_threshold = 0.15
        else:  # normal - balanced
            self.consecutive_required = 3
            self.class_thresholds = {
                'crying': 0.88,
                'screaming': 0.94,
                'shouting': 0.95,
                'glass_breaking': 0.85,
                'normal': 0.0
            }
            self.min_energy_threshold = 0.03
            self.high_energy_threshold = 0.20

        return self.get_sensitivity_settings()

    def get_sensitivity_settings(self) -> Dict:
        """Get current sensitivity settings"""
        return {
            'consecutive_required': self.consecutive_required,
            'class_thresholds': self.class_thresholds,
            'min_energy_threshold': self.min_energy_threshold,
            'high_energy_threshold': self.high_energy_threshold
        }

    def get_status(self) -> Dict:
        """Get detector status"""
        return {
            'non_speech_model_loaded': self.non_speech_model.model is not None,
            'noise_profiler': self.noise_profiler.get_status(),
            'thresholds': {
                'non_speech': self.non_speech_threshold,
                'speech': self.speech_threshold
            },
            'sensitivity': self.get_sensitivity_settings(),
            'max_latency': self.max_latency
        }

