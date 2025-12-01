"""
Configuration settings for AI-Powered Homework Management System
"""
import os
from pathlib import Path

# Base directories
BASE_DIR = Path(__file__).resolve().parent.parent
DATA_DIR = BASE_DIR / "datasets"
MODEL_DIR = BASE_DIR / "models" / "saved"
REPORTS_DIR = BASE_DIR / "reports"

# Create directories if they don't exist
MODEL_DIR.mkdir(parents=True, exist_ok=True)
REPORTS_DIR.mkdir(parents=True, exist_ok=True)

# Flask Configuration
class FlaskConfig:
    SECRET_KEY = os.environ.get('SECRET_KEY', 'homework-management-secret-key-2024')
    DEBUG = os.environ.get('FLASK_DEBUG', 'True').lower() == 'true'
    HOST = os.environ.get('FLASK_HOST', '0.0.0.0')
    PORT = int(os.environ.get('FLASK_PORT', 5001))

# Database Configuration (Laravel MySQL)
class DatabaseConfig:
    HOST = os.environ.get('DB_HOST', 'localhost')
    PORT = int(os.environ.get('DB_PORT', 3306))
    DATABASE = os.environ.get('DB_DATABASE', 'school_management')
    USERNAME = os.environ.get('DB_USERNAME', 'root')
    PASSWORD = os.environ.get('DB_PASSWORD', '')
    
    @classmethod
    def get_connection_string(cls):
        return f"mysql+pymysql://{cls.USERNAME}:{cls.PASSWORD}@{cls.HOST}:{cls.PORT}/{cls.DATABASE}"

# Model Configuration
class ModelConfig:
    # Question Generation Model
    QUESTION_GEN_MODEL = "google/flan-t5-base"  # Lightweight T5 for question generation
    EMBEDDING_MODEL = "sentence-transformers/all-MiniLM-L6-v2"
    
    # Model parameters
    MAX_LENGTH = 512
    MIN_LENGTH = 20
    NUM_BEAMS = 4
    TEMPERATURE = 0.7
    TOP_P = 0.9
    
    # Answer evaluation thresholds
    MCQ_PASS_THRESHOLD = 0.5
    SHORT_ANSWER_SIMILARITY_THRESHOLD = 0.6
    DESCRIPTIVE_SIMILARITY_THRESHOLD = 0.5

# Subject Configuration
SUPPORTED_SUBJECTS = ['science', 'history', 'english', 'health_science']

# Grade Configuration
SUPPORTED_GRADES = [6, 7, 8, 9, 10, 11]

# Homework Configuration
class HomeworkConfig:
    ASSIGNMENTS_PER_WEEK = 2
    QUESTIONS_PER_ASSIGNMENT = 5
    QUESTION_TYPE_DISTRIBUTION = {
        'MCQ': 2,
        'SHORT_ANSWER': 2,
        'DESCRIPTIVE': 1
    }
    
    # Grading weights
    MCQ_MARKS = 1
    SHORT_ANSWER_MARKS = 3
    DESCRIPTIVE_MARKS = 5
    
    # Due date settings
    DEFAULT_DUE_DAYS = 3

# Report Configuration
class ReportConfig:
    MONTHLY_REPORT_DAY = 1  # Generate on 1st of each month
    REPORT_FORMAT = 'pdf'
    INCLUDE_VISUALIZATIONS = True
    
    # Performance thresholds
    EXCELLENT_THRESHOLD = 85
    GOOD_THRESHOLD = 70
    AVERAGE_THRESHOLD = 50
    NEEDS_IMPROVEMENT_THRESHOLD = 35

# NLP Processing Configuration
class NLPConfig:
    # Keyword extraction
    MAX_KEYWORDS = 10
    MIN_KEYWORD_FREQ = 2
    
    # Text similarity
    SIMILARITY_MODEL = "sentence-transformers/all-MiniLM-L6-v2"
    
    # Answer grading
    SEMANTIC_WEIGHT = 0.6
    KEYWORD_WEIGHT = 0.4

# Logging Configuration
class LogConfig:
    LOG_DIR = BASE_DIR / "logs"
    LOG_LEVEL = os.environ.get('LOG_LEVEL', 'INFO')
    LOG_FORMAT = '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    
LogConfig.LOG_DIR.mkdir(parents=True, exist_ok=True)

