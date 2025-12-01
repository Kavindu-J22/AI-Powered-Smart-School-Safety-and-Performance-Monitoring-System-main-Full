"""
API Routes for Homework Management System
"""
from .lesson_routes import lesson_bp
from .homework_routes import homework_bp
from .evaluation_routes import evaluation_bp
from .report_routes import report_bp
from .performance_routes import performance_bp

__all__ = ['lesson_bp', 'homework_bp', 'evaluation_bp', 'report_bp', 'performance_bp']

