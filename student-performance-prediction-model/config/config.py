"""
Configuration settings for the Student Performance Prediction System

IMPROVED: Added RandomForest hyperparameters and cross-validation settings
"""

import os

# Base directory
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Data paths
DATA_DIR = os.path.join(BASE_DIR, 'data')
DATASET_PATH = os.path.join(BASE_DIR, 'dataset', 'student_performance_updated_1000 (1).csv')
CLEANED_DATA_PATH = os.path.join(DATA_DIR, 'cleaned_data.csv')

# Model paths
MODELS_DIR = os.path.join(BASE_DIR, 'models')
MODEL_PATH = os.path.join(MODELS_DIR, 'performance_predictor.pkl')
SCALER_PATH = os.path.join(MODELS_DIR, 'scaler.pkl')

# API Configuration
API_HOST = '0.0.0.0'
API_PORT = 5002  # Performance Prediction API (changed from 5000 due to macOS AirPlay conflict)
API_DEBUG = True

# Model Configuration
RANDOM_STATE = 42
TEST_SIZE = 0.2

# ============================================
# NEW: RandomForest Hyperparameters
# ============================================
# WHY THESE VALUES:
# - n_estimators=200: More trees = more stable predictions, but diminishing returns after ~200
# - max_depth=12: Allows complex patterns without overfitting
#   Too shallow (e.g., 5) = underfitting, too deep (e.g., 20) = overfitting
RF_N_ESTIMATORS = 200
RF_MAX_DEPTH = 12

# ============================================
# NEW: Cross-Validation Settings
# ============================================
# WHY 5-FOLD: Standard choice balancing computation cost with reliable estimates
CV_FOLDS = 5

# Prediction thresholds
ATTENDANCE_MIN = 0
ATTENDANCE_MAX = 100
MARKS_MIN = 0
MARKS_MAX = 100

# Performance categories
PERFORMANCE_EXCELLENT = 85
PERFORMANCE_GOOD = 70
PERFORMANCE_AVERAGE = 55
PERFORMANCE_POOR = 40

# ============================================
# NEW: Confidence Interval Settings
# ============================================
CONFIDENCE_LEVEL = 0.95  # 95% confidence interval
