"""
API Module - Facial Recognition Attendance System
=================================================
Main API blueprint that aggregates all route modules.
"""

from flask import Blueprint

# Create main API blueprint
api_bp = Blueprint('api', __name__, url_prefix='/api')

# Import and register route blueprints
from api.routes.health import health_bp
from api.routes.registration import registration_bp
from api.routes.training import training_bp
from api.routes.attendance import attendance_bp

# Register sub-blueprints
api_bp.register_blueprint(health_bp)
api_bp.register_blueprint(registration_bp)
api_bp.register_blueprint(training_bp)
api_bp.register_blueprint(attendance_bp)

__all__ = ['api_bp']
