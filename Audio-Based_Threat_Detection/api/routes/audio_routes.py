"""
Audio Processing API Routes
Handles audio upload and processing endpoints
"""
from flask import Blueprint, request, jsonify
import numpy as np
import base64
import io
import os
import sys
import struct
import traceback
import warnings

sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))
from utils.audio_processor import AudioProcessor
from utils.audio_decoder import decode_audio_smart, detect_audio_format
from models.threat_detector import ThreatDetector

# Suppress pydub/FFmpeg warnings for cleaner logs
warnings.filterwarnings('ignore', category=RuntimeWarning, module='pydub')
os.environ['PYDUB_FFMPEG_SILENCE'] = '1'

audio_bp = Blueprint('audio', __name__)

# Initialize components
audio_processor = AudioProcessor()
threat_detector = ThreatDetector()


def decode_audio_from_base64(base64_data: str, audio_format: str = 'auto', sample_rate: int = 16000) -> np.ndarray:
    """Decode audio from base64 - handles multiple formats with smart detection and silent FFmpeg"""
    # Remove data URL prefix if present
    if ',' in base64_data:
        base64_data = base64_data.split(',')[1]

    audio_bytes = base64.b64decode(base64_data)

    # Handle raw PCM16 format (from JavaScript) - PRIORITY 1
    if audio_format == 'pcm16':
        audio_array = np.frombuffer(audio_bytes, dtype=np.int16)
        audio = audio_array.astype(np.float32) / 32768.0
        duration = len(audio) / sample_rate
        print(f"[Audio] ✓ PCM16: {len(audio)} samples ({duration:.2f}s)")
        return audio

    # Use smart decoder (silent FFmpeg)
    try:
        detected_format = detect_audio_format(audio_bytes)
        audio = decode_audio_smart(audio_bytes, sample_rate)
        duration = len(audio) / sample_rate
        print(f"[Audio] ✓ {detected_format.upper()}: {len(audio)} samples ({duration:.2f}s)")
        return audio
    except Exception as e:
        raise Exception(f"Audio decode failed: {str(e)}")


@audio_bp.route('/health', methods=['GET'])
def health_check():
    """API health check"""
    return jsonify({
        'status': 'healthy',
        'service': 'Audio Threat Detection API',
        'version': '1.0.0'
    })


@audio_bp.route('/status', methods=['GET'])
def get_status():
    """Get detector status"""
    return jsonify({
        'status': 'ok',
        'detector': threat_detector.get_status()
    })


@audio_bp.route('/analyze', methods=['POST'])
def analyze_audio():
    """
    Analyze uploaded audio for threats
    Accepts: base64 encoded audio or file upload
    """
    try:
        audio_data = None

        # Check for base64 data
        if request.is_json:
            data = request.get_json()
            audio_base64 = data.get('audio_data') or data.get('audio_base64')
            audio_format = data.get('format', 'auto')
            sample_rate = data.get('sample_rate', 16000)

            if audio_base64:
                try:
                    audio_data = decode_audio_from_base64(audio_base64, audio_format, sample_rate)
                except Exception as e:
                    print(f"Error decoding audio: {e}")
                    traceback.print_exc()
                    return jsonify({
                        'success': False,
                        'error': f'Failed to decode audio: {str(e)}',
                        'hint': 'Make sure FFmpeg is installed for full audio format support'
                    }), 400

        # Check for file upload
        elif 'audio' in request.files:
            file = request.files['audio']
            file_bytes = file.read()

            try:
                import soundfile as sf
                audio_buffer = io.BytesIO(file_bytes)
                audio_data, sr = sf.read(audio_buffer)
                if sr != audio_processor.sample_rate:
                    import torchaudio
                    import torch
                    waveform = torch.FloatTensor(audio_data).unsqueeze(0)
                    resampler = torchaudio.transforms.Resample(sr, audio_processor.sample_rate)
                    audio_data = resampler(waveform).squeeze().numpy()
            except Exception as e:
                return jsonify({
                    'success': False,
                    'error': f'Failed to process uploaded file: {str(e)}'
                }), 400

        if audio_data is None:
            return jsonify({'error': 'No audio data provided'}), 400

        # Ensure audio is valid
        if len(audio_data) < 1600:  # Less than 0.1 second at 16kHz
            return jsonify({
                'success': True,
                'result': {
                    'is_threat': False,
                    'skipped': True,
                    'reason': 'Audio too short'
                }
            })

        # Get options
        enable_speech = request.args.get('speech', 'true').lower() == 'true'
        enable_non_speech = request.args.get('non_speech', 'true').lower() == 'true'

        # Analyze
        result = threat_detector.analyze_audio(
            audio_data,
            enable_speech=enable_speech,
            enable_non_speech=enable_non_speech
        )

        return jsonify({
            'success': True,
            'result': result
        })

    except Exception as e:
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@audio_bp.route('/calibrate', methods=['POST'])
def calibrate_noise():
    """
    Calibrate noise profile with ambient audio
    """
    try:
        if not request.is_json:
            return jsonify({'error': 'JSON data required'}), 400
        
        data = request.get_json()
        
        if 'audio_data' not in data:
            return jsonify({'error': 'audio_data required'}), 400
        
        audio_data = audio_processor.decode_base64_audio(data['audio_data'])
        status = threat_detector.update_noise_profile(audio_data)
        
        return jsonify({
            'success': True,
            'noise_profile': status
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@audio_bp.route('/reset-calibration', methods=['POST'])
def reset_calibration():
    """Reset noise calibration"""
    threat_detector.reset_noise_profile()
    return jsonify({
        'success': True,
        'message': 'Noise profile reset'
    })


@audio_bp.route('/test', methods=['GET'])
def test_endpoint():
    """Test endpoint with sample data"""
    # Generate test audio (silence with some noise)
    test_audio = np.random.randn(16000 * 2) * 0.01  # 2 seconds of noise

    result = threat_detector.analyze_audio(
        test_audio,
        enable_speech=False,
        enable_non_speech=True
    )

    return jsonify({
        'success': True,
        'message': 'Test completed',
        'result': result
    })


@audio_bp.route('/sensitivity', methods=['GET', 'POST'])
def sensitivity():
    """Get or set detection sensitivity"""
    if request.method == 'GET':
        return jsonify({
            'success': True,
            'sensitivity': threat_detector.get_sensitivity_settings()
        })

    # POST - set sensitivity
    try:
        data = request.get_json() or {}
        level = data.get('level', 'normal')

        if level not in ['low', 'normal', 'high']:
            return jsonify({
                'success': False,
                'error': 'Invalid level. Use: low, normal, or high'
            }), 400

        settings = threat_detector.set_sensitivity(level)

        return jsonify({
            'success': True,
            'message': f'Sensitivity set to {level}',
            'sensitivity': settings
        })
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@audio_bp.route('/reset-session', methods=['POST'])
def reset_session():
    """Reset detection session - clears history for fresh start"""
    threat_detector.reset_detection_history()
    return jsonify({
        'success': True,
        'message': 'Detection session reset'
    })

