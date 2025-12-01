"""
Configuration module for AI-Powered Homework Management System
"""
from .config import (
    FlaskConfig,
    DatabaseConfig,
    ModelConfig,
    HomeworkConfig,
    ReportConfig,
    NLPConfig,
    LogConfig,
    SUPPORTED_SUBJECTS,
    SUPPORTED_GRADES,
    BASE_DIR,
    DATA_DIR,
    MODEL_DIR,
    REPORTS_DIR
)

__all__ = [
    'FlaskConfig',
    'DatabaseConfig',
    'ModelConfig',
    'HomeworkConfig',
    'ReportConfig',
    'NLPConfig',
    'LogConfig',
    'SUPPORTED_SUBJECTS',
    'SUPPORTED_GRADES',
    'BASE_DIR',
    'DATA_DIR',
    'MODEL_DIR',
    'REPORTS_DIR'
]

