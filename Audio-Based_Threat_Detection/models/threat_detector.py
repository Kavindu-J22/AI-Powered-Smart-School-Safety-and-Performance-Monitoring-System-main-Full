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
from models.pretrained_audio_detector import PANNsAudioDetector


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

        # PANNS CNN14 pre-trained detector (primary non-speech engine)
        print("[ThreatDetector] Initializing PANNS CNN14 pre-trained detector …")
        self.panns_detector = PANNsAudioDetector()
        self.panns_available = self.panns_detector.initialize()
        if self.panns_available:
            print("[ThreatDetector] PANNS detector is active (primary).")
        else:
            print("[ThreatDetector] PANNS unavailable — falling back to custom model.")

        # Base threshold (fallback) and per-class overrides below
        self.non_speech_threshold = 0.70
        self.speech_threshold = ModelConfig.SPEECH_THREAT_THRESHOLD
        self.max_latency = ModelConfig.MAX_LATENCY

        # Consecutive detection tracking (reduces false positives)
        # Set to 1 so that a single confident detection is reported immediately.
        # Users can increase via set_sensitivity('low') if false-positives are a problem.
        self.detection_history: deque = deque(maxlen=5)
        self.consecutive_required = 1  # 1 = report on first detection (most responsive)

        # Energy-based filtering
        self.min_energy_threshold = 0.010  # Low enough to catch crying/glass_breaking
        self.high_energy_threshold = 0.18  # Threshold for screaming/shouting energy check

        # Class-specific confidence thresholds.
        # PANNS CNN14 (primary) outputs AudioSet probabilities spread across 527
        # classes, so raw scores for threat events are typically 0.05–0.40.
        # These thresholds are calibrated to PANNS output scale.
        # Custom model fallback uses higher scores (0.5–0.9) but we raise its
        # thresholds dynamically inside analyze_audio when panns_available=False.
        self.class_thresholds = {
            'crying':         0.06,   # Soft sounds — very sensitive threshold
            'screaming':      0.08,   # High-energy but score still modest in AudioSet
            'shouting':       0.08,   # Same as screaming
            'glass_breaking': 0.08,   # Distinctive transient
            'normal':         0.0     # Always allow normal (no threshold)
        }

        # Custom-model thresholds (used when PANNS is unavailable)
        self.custom_class_thresholds = {
            'crying':         0.50,
            'screaming':      0.60,
            'shouting':       0.60,
            'glass_breaking': 0.50,
            'normal':         0.0
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

            # ── Non-speech threat detection ────────────────────────────────
            # Primary: PANNS CNN14 (pre-trained on AudioSet, no feature extraction needed)
            # Fallback: custom CNN-BiLSTM model (uses MFCC features)
            if enable_non_speech:
                if self.panns_available:
                    # PANNS path: feed raw preprocessed audio directly
                    class_name, confidence, all_probs_dict = self.panns_detector.detect(
                        processed_audio, AudioConfig.SAMPLE_RATE
                    )
                    all_probs_display = {k: round(v, 4) for k, v in all_probs_dict.items()}
                    detector_used = 'panns_cnn14'
                else:
                    # Fallback: extract MFCC features and run custom model
                    features = self.feature_extractor.extract_fixed_length_features(processed_audio)
                    features_normalized, _, _ = self.feature_extractor.normalize_features(features)
                    model_input = features_normalized.T
                    class_name, confidence, all_probs_list = self.non_speech_model.predict(model_input)
                    all_probs_display = dict(zip(
                        self.non_speech_model.classes,
                        [round(p, 4) for p in all_probs_list]
                    ))
                    detector_used = 'custom_cnn_bilstm'

                # Get class-specific threshold.
                # PANNS output is on AudioSet probability scale (0.05–0.40 typical).
                # Custom model output is on softmax scale (0.50–0.95 typical).
                if self.panns_available:
                    thresholds = self.class_thresholds
                else:
                    thresholds = self.custom_class_thresholds
                class_threshold = thresholds.get(class_name, self.non_speech_threshold)

                # Apply adaptive threshold based on noise profile
                adaptive_threshold = self.noise_profiler.get_adaptive_threshold(class_threshold)

                # For screaming/shouting on the custom model only: guard against
                # low-energy false positives.  PANNS already handles this internally
                # (it evaluates all 527 AudioSet classes and won't fire on silence).
                if not self.panns_available and class_name in ['screaming', 'shouting']:
                    if audio_energy < self.high_energy_threshold:
                        adaptive_threshold = min(0.99, adaptive_threshold + 0.15)
                    elif audio_energy < self.high_energy_threshold * 1.3:
                        adaptive_threshold = min(0.98, adaptive_threshold + 0.05)

                # Initial threat determination
                initial_is_threat = (
                    class_name != 'normal' and
                    confidence >= adaptive_threshold
                )

                # Consecutive detection check to reduce false positives
                confirmed_threat = self._check_consecutive_detection(class_name, initial_is_threat)

                result['non_speech_result'] = {
                    'detected_class': class_name,
                    'confidence': confidence,
                    'is_threat': confirmed_threat,
                    'initial_detection': initial_is_threat,
                    'consecutive_confirmed': confirmed_threat,
                    'all_probabilities': all_probs_display,
                    'threshold_used': round(adaptive_threshold, 4),
                    'class_threshold': class_threshold,
                    'detector': detector_used,
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
            
            # Determine overall threat level.
            # For 'combined' threats both speech and non-speech fired — take the
            # HIGHEST level from either source so a "High" speech threat is never
            # silently downgraded to "Medium" by a lower-scoring PANNS result.
            if result['is_threat']:
                LEVEL_ORDER = {'none': 0, 'low': 1, 'medium': 2, 'high': 3, 'critical': 4}
                computed_level = 'low'  # floor for any confirmed threat

                # ── Speech threat level ───────────────────────────────────────
                if result['threat_type'] in ('speech', 'combined') and result.get('speech_result'):
                    speech_threat_level = result['speech_result'].get('threat_level', 'none')
                    if speech_threat_level in LEVEL_ORDER and speech_threat_level != 'none':
                        speech_lvl = speech_threat_level
                    else:
                        # Fallback: derive level from threat_score when speech
                        # detector didn't set a level (edge case)
                        s_score = result['speech_result'].get('threat_score', 0.0)
                        if s_score >= 0.6:
                            speech_lvl = 'high'
                        elif s_score >= 0.4:
                            speech_lvl = 'medium'
                        else:
                            speech_lvl = 'low'
                    if LEVEL_ORDER[speech_lvl] > LEVEL_ORDER[computed_level]:
                        computed_level = speech_lvl

                # ── Non-speech threat level ───────────────────────────────────
                # PANNS probabilities are on AudioSet scale (typically 0.05–0.95).
                # Normalise against each class's threshold so that "just detectable"
                # → low and a strong detection → high/critical regardless of scale.
                if result['threat_type'] in ('non_speech', 'combined') and result.get('non_speech_result'):
                    ns_conf = result['non_speech_result']['confidence']
                    ns_cls  = result['details'].get('non_speech_class', 'normal')
                    if self.panns_available:
                        base_thr = self.class_thresholds.get(ns_cls, 0.08)
                    else:
                        base_thr = self.custom_class_thresholds.get(ns_cls, 0.50)
                    ratio = ns_conf / max(base_thr, 1e-6)
                    if ratio >= 10.0:
                        ns_lvl = 'critical'
                    elif ratio >= 5.0:
                        ns_lvl = 'high'
                    elif ratio >= 2.0:
                        ns_lvl = 'medium'
                    else:
                        ns_lvl = 'low'
                    if LEVEL_ORDER[ns_lvl] > LEVEL_ORDER[computed_level]:
                        computed_level = ns_lvl

                result['threat_level'] = computed_level
        
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
        # PANNS and custom-model thresholds are on different probability scales.
        # We maintain separate sets and pick the active one based on panns_available.
        if level == 'low':
            # Fewer false positives — require higher confidence
            self.consecutive_required = 2
            self.class_thresholds = {            # PANNS scale
                'crying':         0.15,
                'screaming':      0.20,
                'shouting':       0.20,
                'glass_breaking': 0.20,
                'normal':         0.0
            }
            self.custom_class_thresholds = {     # custom model scale
                'crying':         0.70,
                'screaming':      0.80,
                'shouting':       0.80,
                'glass_breaking': 0.65,
                'normal':         0.0
            }
            self.min_energy_threshold = 0.025
            self.high_energy_threshold = 0.25
        elif level == 'high':
            # More sensitive — catch more threats, accept some false positives
            self.consecutive_required = 1
            self.class_thresholds = {            # PANNS scale
                'crying':         0.04,
                'screaming':      0.05,
                'shouting':       0.05,
                'glass_breaking': 0.05,
                'normal':         0.0
            }
            self.custom_class_thresholds = {     # custom model scale
                'crying':         0.40,
                'screaming':      0.45,
                'shouting':       0.45,
                'glass_breaking': 0.40,
                'normal':         0.0
            }
            self.min_energy_threshold = 0.008
            self.high_energy_threshold = 0.12
        else:  # normal — balanced (default)
            self.consecutive_required = 1
            self.class_thresholds = {            # PANNS scale
                'crying':         0.06,
                'screaming':      0.08,
                'shouting':       0.08,
                'glass_breaking': 0.08,
                'normal':         0.0
            }
            self.custom_class_thresholds = {     # custom model scale
                'crying':         0.50,
                'screaming':      0.60,
                'shouting':       0.60,
                'glass_breaking': 0.50,
                'normal':         0.0
            }
            self.min_energy_threshold = 0.010
            self.high_energy_threshold = 0.18

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
            'panns_available': self.panns_available,
            'active_detector': 'panns_cnn14' if self.panns_available else 'custom_cnn_bilstm',
            'noise_profiler': self.noise_profiler.get_status(),
            'thresholds': {
                'non_speech': self.non_speech_threshold,
                'speech': self.speech_threshold,
                'class_thresholds': self.class_thresholds,
            },
            'sensitivity': self.get_sensitivity_settings(),
            'max_latency': self.max_latency
        }

