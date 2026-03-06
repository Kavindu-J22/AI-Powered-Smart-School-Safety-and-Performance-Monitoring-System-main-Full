"""
Configuration settings for Seating Arrangement API
"""

import os

# API Configuration
API_HOST = os.getenv('SEATING_API_HOST', '0.0.0.0')
API_PORT = int(os.getenv('SEATING_API_PORT', 5003))  # Different port from performance prediction
API_DEBUG = os.getenv('SEATING_API_DEBUG', 'False').lower() == 'true'

# Model Configuration
DEFAULT_SEATS_PER_ROW = 5
DEFAULT_ROWS = 6
MAX_STUDENTS_PER_CLASS = 40

# Seating Strategy
MIXING_STRATEGY = 'high_low_pair'  # Pair high performers with low performers
SEAT_NUMBER_PREFIX = 'S'  # Seat numbers will be like S1, S2, S3...
