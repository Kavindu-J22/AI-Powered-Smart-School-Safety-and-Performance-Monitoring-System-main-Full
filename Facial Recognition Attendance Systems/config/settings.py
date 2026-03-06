"""
Application Configuration Settings
===================================
Centralized configuration for the Facial Recognition Attendance System.
"""

import os
from pathlib import Path
from dataclasses import dataclass, field
from typing import List, Tuple

# Base directory
BASE_DIR = Path(__file__).resolve().parent.parent


@dataclass
class Config:
    """Main configuration class with all settings."""
    
    # ===========================================
    # Server Configuration
    # ===========================================
    HOST: str = "0.0.0.0"
    PORT: int = 5004  # Dashboard compatible - set FACE_RECOGNITION_API_URL=http://localhost:5004
    DEBUG: bool = os.getenv("DEBUG", "False").lower() == "true"
    SECRET_KEY: str = os.getenv("SECRET_KEY", "your-secret-key-change-in-production")
    
    # ===========================================
    # Paths Configuration
    # ===========================================
    BASE_DIR: Path = BASE_DIR
    DATA_DIR: Path = BASE_DIR / "data"
    FACES_DIR: Path = BASE_DIR / "data" / "faces"
    EMBEDDINGS_DIR: Path = BASE_DIR / "data" / "embeddings"
    MODELS_DIR: Path = BASE_DIR / "data" / "models"
    LOGS_DIR: Path = BASE_DIR / "logs"
    
    # ===========================================
    # Face Detection Settings
    # ===========================================
    # Detection model: 'mtcnn', 'retinaface', 'mediapipe'
    DETECTION_MODEL: str = "mtcnn"
    DETECTION_CONFIDENCE: float = 0.95
    MIN_FACE_SIZE: int = 80  # Minimum face size in pixels
    FACE_DETECTION_SCALE: float = 1.0  # Full scale for better accuracy at distances
    
    # ===========================================
    # Face Recognition Settings
    # ===========================================
    # Recognition model: 'arcface', 'facenet', 'facenet512'
    RECOGNITION_MODEL: str = "arcface"
    EMBEDDING_SIZE: int = 512  # ArcFace embedding dimension
    RECOGNITION_THRESHOLD: float = 0.65  # Cosine similarity threshold (higher = stricter)
    UNKNOWN_THRESHOLD: float = 0.50  # Below this, definitely unknown
    
    # ===========================================
    # Face Capture Settings
    # ===========================================
    CAPTURE_COUNT: int = 25  # Number of images to capture per person
    CAPTURE_INTERVAL_MS: int = 200  # Milliseconds between captures
    FACE_IMAGE_SIZE: Tuple[int, int] = (160, 160)  # Standard face crop size
    ALIGNMENT_ENABLED: bool = True  # Enable face alignment
    
    # ===========================================
    # Anti-Spoofing Settings
    # ===========================================
    ANTI_SPOOF_ENABLED: bool = True
    LIVENESS_THRESHOLD: float = 0.7
    BLINK_DETECTION: bool = True
    
    # ===========================================
    # Camera Settings
    # ===========================================
    CAMERA_INDEX: int = 0
    CAMERA_WIDTH: int = 1280
    CAMERA_HEIGHT: int = 720
    CAMERA_FPS: int = 30
    
    # ===========================================
    # Performance Settings
    # ===========================================
    USE_GPU: bool = True
    GPU_MEMORY_LIMIT: float = 0.5  # Fraction of GPU memory to use
    BATCH_SIZE: int = 32  # For training
    NUM_WORKERS: int = 4  # Data loading workers
    
    # ===========================================
    # Database Settings
    # ===========================================
    DATABASE_URL: str = os.getenv(
        "DATABASE_URL",
        f"sqlite:///{BASE_DIR}/data/attendance.db"
    )
    
    # ===========================================
    # Dashboard Integration
    # ===========================================
    DASHBOARD_API_URL: str = os.getenv(
        "DASHBOARD_API_URL",
        "http://localhost:8000/api"
    )
    DASHBOARD_API_KEY: str = os.getenv("DASHBOARD_API_KEY", "")
    WEBHOOK_ENABLED: bool = True
    WEBHOOK_URL: str = os.getenv(
        "WEBHOOK_URL",
        "http://localhost:8000/api/attendance/webhook"
    )
    
    # ===========================================
    # Training Settings
    # ===========================================
    AUGMENTATION_ENABLED: bool = True
    AUGMENTATION_MULTIPLIER: int = 5  # Create 5x augmented images
    TRAINING_EPOCHS: int = 10
    LEARNING_RATE: float = 0.001
    
    # ===========================================
    # Attendance Settings
    # ===========================================
    ATTENDANCE_COOLDOWN_SECONDS: int = 300  # 5 minutes between re-marking
    ATTENDANCE_START_HOUR: int = 7
    ATTENDANCE_END_HOUR: int = 18
    MARK_LATE_AFTER_MINUTES: int = 15
    
    # ===========================================
    # Logging Settings
    # ===========================================
    LOG_LEVEL: str = os.getenv("LOG_LEVEL", "INFO")
    LOG_FORMAT: str = "%(asctime)s - %(name)s - %(levelname)s - %(message)s"
    LOG_FILE: str = "facial_recognition.log"
    
    def __post_init__(self):
        """Create necessary directories after initialization."""
        self.DATA_DIR.mkdir(parents=True, exist_ok=True)
        self.FACES_DIR.mkdir(parents=True, exist_ok=True)
        self.EMBEDDINGS_DIR.mkdir(parents=True, exist_ok=True)
        self.MODELS_DIR.mkdir(parents=True, exist_ok=True)
        self.LOGS_DIR.mkdir(parents=True, exist_ok=True)


@dataclass
class DevelopmentConfig(Config):
    """Development environment configuration."""
    DEBUG: bool = True
    LOG_LEVEL: str = "DEBUG"


@dataclass
class ProductionConfig(Config):
    """Production environment configuration."""
    DEBUG: bool = False
    LOG_LEVEL: str = "WARNING"
    ANTI_SPOOF_ENABLED: bool = True


@dataclass
class TestingConfig(Config):
    """Testing environment configuration."""
    DEBUG: bool = True
    DATABASE_URL: str = "sqlite:///:memory:"
    CAPTURE_COUNT: int = 5


# Configuration factory
_configs = {
    "development": DevelopmentConfig,
    "production": ProductionConfig,
    "testing": TestingConfig,
}


def get_config(env: str = None) -> Config:
    """
    Get configuration based on environment.
    
    Args:
        env: Environment name ('development', 'production', 'testing')
             If None, uses FLASK_ENV environment variable.
    
    Returns:
        Configuration object for the specified environment.
    """
    if env is None:
        env = os.getenv("FLASK_ENV", "development")
    
    config_class = _configs.get(env.lower(), DevelopmentConfig)
    return config_class()


# Global config instance
config = get_config()
