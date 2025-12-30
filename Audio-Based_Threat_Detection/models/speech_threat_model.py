"""
Speech Threat Detection Model
Speech-to-text with threat keyword detection for English and Sinhala
"""
import numpy as np
import os
import sys
import re
from typing import Dict, List, Tuple, Optional

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import ThreatKeywords, ModelConfig

# Try to import speech recognition libraries
try:
    import speech_recognition as sr
    SPEECH_RECOGNITION_AVAILABLE = True
except ImportError:
    SPEECH_RECOGNITION_AVAILABLE = False

try:
    from vosk import Model as VoskModel, KaldiRecognizer
    import json
    VOSK_AVAILABLE = True
except ImportError:
    VOSK_AVAILABLE = False


class SpeechThreatDetector:
    """Speech-to-text with threat keyword detection"""

    def __init__(self):
        self.recognizer = None
        if SPEECH_RECOGNITION_AVAILABLE:
            self.recognizer = sr.Recognizer()
            # Adjust recognizer settings for better accuracy
            self.recognizer.energy_threshold = 200  # Lowered from 300 for better sensitivity
            self.recognizer.dynamic_energy_threshold = True  # Auto-adjust
            self.recognizer.pause_threshold = 0.6  # Reduced from 0.8 for faster response
            self.recognizer.phrase_threshold = 0.3  # Minimum seconds of speaking audio before phrase
            self.recognizer.non_speaking_duration = 0.5  # Seconds of non-speaking audio to keep on both sides

        self.vosk_model = None
        self.english_threats = [t.lower() for t in ThreatKeywords.ENGLISH_THREATS]
        self.sinhala_threats = ThreatKeywords.SINHALA_THREATS
        self.english_profanity = [t.lower() for t in ThreatKeywords.PROFANITY_ENGLISH]
        self.sinhala_profanity = ThreatKeywords.PROFANITY_SINHALA
        self.threshold = ModelConfig.SPEECH_THREAT_THRESHOLD
        
    def transcribe_audio(self, audio_data: np.ndarray, sample_rate: int = 16000) -> Dict:
        """Convert audio to text using multiple engines"""
        results = {
            'text': '',
            'language': 'unknown',
            'confidence': 0.0,
            'engine': 'none',
            'error': None
        }

        # Check minimum audio length (need at least 1.5 seconds for reliable transcription)
        min_samples = int(sample_rate * 1.5)  # 1.5 second minimum
        if len(audio_data) < min_samples:
            results['error'] = f'Audio too short ({len(audio_data)/sample_rate:.1f}s < 1.5s)'
            return results

        # Check if audio has enough energy (not silence)
        audio_energy = float(np.sqrt(np.mean(audio_data ** 2)))
        if audio_energy < 0.005:
            results['error'] = 'Silence detected'
            return results

        # Normalize audio for better recognition
        max_val = np.max(np.abs(audio_data))
        if max_val > 0:
            audio_data = audio_data / max_val * 0.9  # Normalize to 90% to avoid clipping

        # Try Google Speech Recognition first
        if SPEECH_RECOGNITION_AVAILABLE:
            try:
                # Convert numpy array to AudioData (16-bit PCM)
                audio_int16 = (audio_data * 32767).astype(np.int16)
                audio_bytes = audio_int16.tobytes()
                audio = sr.AudioData(audio_bytes, sample_rate, 2)  # 2 bytes per sample

                # Try BOTH English and Sinhala in parallel for better detection
                english_text = None
                sinhala_text = None

                # Try English first
                try:
                    english_text = self.recognizer.recognize_google(audio, language='en-US', show_all=False)
                    if english_text:
                        results = {
                            'text': english_text,
                            'language': 'english',
                            'confidence': 0.85,
                            'engine': 'google',
                            'error': None
                        }
                        print(f"[Speech] Transcribed (English): '{english_text}'")
                except sr.UnknownValueError:
                    pass  # Try Sinhala
                except sr.RequestError as e:
                    results['error'] = f'Google API error: {str(e)}'
                except Exception as e:
                    results['error'] = f'English transcription failed: {str(e)}'

                # Try Sinhala (always try, even if English succeeded, to catch mixed language)
                try:
                    sinhala_text = self.recognizer.recognize_google(audio, language='si-LK', show_all=False)
                    if sinhala_text:
                        # If we got both, combine them
                        if english_text:
                            combined_text = f"{english_text} {sinhala_text}"
                            results = {
                                'text': combined_text,
                                'language': 'mixed',
                                'confidence': 0.8,
                                'engine': 'google',
                                'error': None
                            }
                            print(f"[Speech] Transcribed (Mixed): '{combined_text}'")
                        else:
                            results = {
                                'text': sinhala_text,
                                'language': 'sinhala',
                                'confidence': 0.82,  # Slightly higher confidence for Sinhala
                                'engine': 'google',
                                'error': None
                            }
                            print(f"[Speech] Transcribed (Sinhala): '{sinhala_text}'")
                except sr.UnknownValueError:
                    if not english_text:
                        results['error'] = 'Could not understand audio in English or Sinhala'
                except sr.RequestError as e:
                    if not english_text:
                        results['error'] = f'Google API error: {str(e)}'
                except Exception as e:
                    if not english_text:
                        results['error'] = f'Sinhala transcription failed: {str(e)}'
            except Exception as e:
                results['error'] = f'Speech recognition error: {str(e)}'
        else:
            results['error'] = 'Speech recognition not available'

        # Fallback to Vosk for offline recognition
        if not results['text'] and VOSK_AVAILABLE and self.vosk_model:
            try:
                rec = KaldiRecognizer(self.vosk_model, sample_rate)
                audio_bytes = (audio_data * 32767).astype(np.int16).tobytes()
                rec.AcceptWaveform(audio_bytes)
                vosk_result = json.loads(rec.FinalResult())
                if vosk_result.get('text'):
                    results = {
                        'text': vosk_result['text'],
                        'language': 'english',
                        'confidence': 0.7,
                        'engine': 'vosk',
                        'error': None
                    }
            except Exception as e:
                pass  # Vosk is optional fallback

        return results
    
    def detect_threats(self, text: str, language: str = 'english') -> Dict:
        """Detect threatening content in transcribed text"""
        if not text:
            return {
                'is_threat': False,
                'threat_level': 'none',
                'detected_keywords': [],
                'threat_score': 0.0
            }

        text_lower = text.lower()
        detected_keywords = []
        threat_score = 0.0

        # Check English threats - use word boundary matching for better accuracy
        for threat in self.english_threats:
            threat_lower = threat.lower()
            # Use word boundaries for single words, substring match for phrases
            if ' ' in threat_lower:
                # Multi-word phrase - use substring match
                if threat_lower in text_lower:
                    detected_keywords.append({'keyword': threat, 'type': 'threat', 'language': 'english'})
                    # Higher score for severe threats
                    if any(word in threat_lower for word in ['kill', 'murder', 'die', 'shoot', 'gun', 'bomb']):
                        threat_score += 0.5
                    else:
                        threat_score += 0.3
            else:
                # Single word - use word boundary
                if re.search(r'\b' + re.escape(threat_lower) + r'\b', text_lower):
                    detected_keywords.append({'keyword': threat, 'type': 'threat', 'language': 'english'})
                    if any(word in threat_lower for word in ['kill', 'murder', 'die', 'shoot', 'gun', 'bomb']):
                        threat_score += 0.5
                    else:
                        threat_score += 0.3

        # Also check individual dangerous words with word boundaries
        dangerous_words = ['kill', 'murder', 'shoot', 'gun', 'bomb', 'weapon', 'stab', 'hurt', 'attack']
        for word in dangerous_words:
            if re.search(r'\b' + re.escape(word) + r'\b', text_lower) and not any(word in kw['keyword'].lower() for kw in detected_keywords):
                detected_keywords.append({'keyword': word, 'type': 'threat', 'language': 'english'})
                threat_score += 0.4

        # Check English profanity with word boundaries
        for profanity in self.english_profanity:
            if re.search(r'\b' + re.escape(profanity) + r'\b', text_lower):
                detected_keywords.append({'keyword': profanity, 'type': 'profanity', 'language': 'english'})
                threat_score += 0.15

        # Check Sinhala threats - use substring matching (Sinhala doesn't have clear word boundaries)
        for threat in self.sinhala_threats:
            if threat in text:
                detected_keywords.append({'keyword': threat, 'type': 'threat', 'language': 'sinhala'})
                # Higher score for Sinhala threats (they're more specific)
                threat_score += 0.45

        # Check Sinhala profanity
        for profanity in self.sinhala_profanity:
            if profanity in text:
                detected_keywords.append({'keyword': profanity, 'type': 'profanity', 'language': 'sinhala'})
                threat_score += 0.15

        # Cap threat score at 1.0
        threat_score = min(threat_score, 1.0)

        # Determine threat level - more sensitive thresholds
        if threat_score >= 0.5:
            threat_level = 'high'
        elif threat_score >= 0.3:
            threat_level = 'medium'
        elif threat_score > 0:
            threat_level = 'low'
        else:
            threat_level = 'none'

        # Lower threshold for detection - any threat keyword should trigger
        is_threat = threat_score > 0 or len(detected_keywords) > 0

        return {
            'is_threat': is_threat,
            'threat_level': threat_level,
            'detected_keywords': detected_keywords,
            'threat_score': threat_score
        }
    
    def analyze_audio(self, audio_data: np.ndarray, sample_rate: int = 16000) -> Dict:
        """Full pipeline: transcribe audio and detect threats"""
        # Transcribe
        transcription = self.transcribe_audio(audio_data, sample_rate)
        
        # Detect threats
        threat_analysis = self.detect_threats(
            transcription['text'], 
            transcription['language']
        )
        
        return {
            'transcription': transcription,
            'threat_analysis': threat_analysis,
            'text': transcription['text'],
            'is_threat': threat_analysis['is_threat'],
            'threat_level': threat_analysis['threat_level'],
            'threat_score': threat_analysis['threat_score']
        }

