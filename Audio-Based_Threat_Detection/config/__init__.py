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
    # Direct Violence/Killing/Harming
    "i'll hurt you", "i will hurt you", "kill", "murder", "die",
    "attack", "shoot", "bomb", "fight", "beat you", "punch", "destroy",
    "harm", "violence", "assault", "stab", "choke", "strangle", "slaughter",
    "massacre", "execute", "maim", "injure", "torture", "rape", "kidnap",
    "suicide", "self-harm", "hang", "drown", "poison", "eliminate",
    
    # Weapons & Related Terms
    "gun", "weapon", "knife", "blade", "sword", "firearm", "rifle", 
    "pistol", "shotgun", "ammunition", "bullet", "grenade", "explosive", 
    "device", "trigger", "detonate", "razor", "ax", "machete", "taser",
    
    # Intent/Planning/Specific Threats
    "threat", "threatening", "intention to harm", "plan to attack", "gonna get you", 
    "come for you", "watch your back", "payback", "revenge", "doomsday", 
    "hostage", "terror", "jihad", "cult", "trap", "ambush", "blow up", 
    "burn down", "destroy all",
    
    # Distress/Emergency/Seeking Help
    "help", "danger", "emergency", "in trouble", "call police", "911", 
    "fire", "medic", "ambulance", "save me", "distress", "panic"
    ]
    
    SINHALA_THREATS = [
    # Direct Violence/Killing/Harming
    "මරනවා", "ගහනවා", "මරන්න", "කපනවා", "පහර", "සටන", 
    "පොලු", "තලනවා", "අතවර", "පහරදීම", "ඝාතනය", "තුවාල", 
    "වින්දනය", "විස", "දැවිල්ල", "පිළිස්සීම", "අල්ලා", 
    "අල්ලාගෙන", "බැඳලා", "පන්නනවා", "ගෙල සිර", "දුක්", 
    "නැතිකරනවා", "සමූලඝාතනය",
    
    # Weapons & Actions
    "වෙඩි", "පිස්තෝලය", "බෝම්බය", "මරුගුල", "කඩු", 
    "පිහිය", "තුවක්කුව", "ආයුධය", "පතරොම", "විසබීජ", 
    "විස", "පිපිරීම", "පුපුරවනවා", "ගිනි",
    
    # Intent/Planning/Specific Threats
    "බය", "තර්ජනය", "අන්තරාය", "අවදානම", "පලි", 
    "කුමන්ත්‍රණය", "ඇටවුම්", "බිහිසුණු", "ත්‍රස්ත", 
    "ප්‍රහාරය", "හමුදා", "පොලිසියට කියන්න",
    
    # Distress/Emergency/Seeking Help
    "උදව්", "අවශ්‍යයි", "ගලවගන්න", "බේරගන්න", "ගිලන්", 
    "ගිලන්රථ", "අමාරුවේ", "අසනීප", "මරණ"
    ]
    
    PROFANITY_ENGLISH = [
    "damn", "hell", "bastard", "idiot", "stupid", "ass", "bitch", 
    "crap", "fuck", "shit", "motherfucker", "cock", "pussy", 
    "cunt", "arsehole", "wanker", "moron", "loser", "jerk", 
    "douchebag", "prick", "slut", "whore", "tard", "nigger", 
    "faggot", "scum", "trash", "suck", "sucks", "go to hell", 
    "piss off", "bollocks", "shite"
    ]
    
    PROFANITY_SINHALA = [
     # General Insults/Slurs
    "බල්ලා", "බල්ලි", "ගොනා", "මෝඩයා", "පිස්සා", 
    "පිස්සු", "ලොන්ත", "වල්", "නපුරු", "බූරුවා", "හිපාට", "ගොබ්බයා",
    
    # Extreme Profanity (Direct Translations/Concepts)
    "හුකනවා", "පොන්නයා", "ගූ", "කැරි",
    "අම්මණ්ඩි", "කුපාඩි", "අසහන", "අවජාතක", "බැල්ලි", 
    "නාකි", "බේබදු"
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

