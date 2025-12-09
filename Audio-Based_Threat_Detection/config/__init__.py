# Audio-Based Threat Detection Configuration
import os
from pathlib import Path

# Base paths
BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "Non Speech Dataset"
MODELS_DIR = BASE_DIR / "models" / "saved"
LOGS_DIR = BASE_DIR / "logs"

# Audio Configuration
class AudioConfig:
    SAMPLE_RATE = 16000  # 16kHz for speech recognition
    CHUNK_DURATION = 2.0  # 2 seconds per chunk
    OVERLAP = 0.5  # 50% overlap
    N_MFCC = 40  # Number of MFCC coefficients
    N_FFT = 2048
    HOP_LENGTH = 512
    N_MELS = 128
    FMAX = 8000

# Model Configuration
class ModelConfig:
    # Non-Speech Model
    NON_SPEECH_CLASSES = ['crying', 'screaming', 'shouting', 'glass_breaking', 'normal']
    NON_SPEECH_MODEL_PATH = MODELS_DIR / "non_speech_threat_model.h5"
    
    # Speech Model
    SPEECH_THREAT_MODEL_PATH = MODELS_DIR / "speech_threat_model.h5"
    
    # Training Parameters
    BATCH_SIZE = 32
    EPOCHS = 50
    LEARNING_RATE = 0.001
    VALIDATION_SPLIT = 0.2
    
    # Detection Thresholds
    NON_SPEECH_THRESHOLD = 0.7
    SPEECH_THREAT_THRESHOLD = 0.6
    
    # Latency Target (seconds)
    MAX_LATENCY = 3.0

# Threat Keywords for Speech Detection
class ThreatKeywords:
    ENGLISH_THREATS = [
        "i'll hurt you", "i will hurt you", "kill", "murder", "die",
        "attack", "shoot", "gun", "weapon", "bomb", "fight",
        "beat you", "punch", "destroy", "help", "danger", "emergency",
        "threat", "violence", "assault", "harm"
    ]
    
    SINHALA_THREATS = [
        "මරනවා", "ගහනවා", "මරන්න", "කපනවා", "වෙඩි",
        "පහර", "බය", "අන්තරාය", "උදව්", "සටන",
        "පිස්තෝලය", "බෝම්බය", "මරුගුල"
    ]
    
    PROFANITY_ENGLISH = [
        "damn", "hell", "bastard", "idiot", "stupid"
    ]
    
    PROFANITY_SINHALA = [
         "බල්ලා"
    ]

# Flask API Configuration
class FlaskConfig:
    SECRET_KEY = os.environ.get('SECRET_KEY', 'audio-threat-detection-secret-key-2024')
    DEBUG = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'
    HOST = os.environ.get('FLASK_HOST', '127.0.0.1')
    PORT = int(os.environ.get('FLASK_PORT', 5002))
    
    # CORS Settings
    CORS_ORIGINS = ['http://localhost:8000', 'http://127.0.0.1:8000']

# Noise Profiling Configuration
class NoiseConfig:
    ADAPTIVE_THRESHOLD = True
    NOISE_FLOOR_SAMPLES = 50
    NOISE_UPDATE_INTERVAL = 10  # seconds
    SNR_MINIMUM = 10  # dB

# Create directories
os.makedirs(MODELS_DIR, exist_ok=True)
os.makedirs(LOGS_DIR, exist_ok=True)

