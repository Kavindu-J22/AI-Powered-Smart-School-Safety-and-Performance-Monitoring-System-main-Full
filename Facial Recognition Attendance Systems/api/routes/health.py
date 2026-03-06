"""
Health Check API Routes
========================
System health and status endpoints.
"""

from flask import Blueprint, jsonify, current_app
import psutil
import time

health_bp = Blueprint('health', __name__)


@health_bp.route('/health', methods=['GET'])
def health_check():
    """
    Basic health check endpoint.
    
    Returns:
        Health status
    """
    return jsonify({
        'status': 'healthy',
        'service': 'Facial Recognition Attendance System',
        'timestamp': time.time()
    })


@health_bp.route('/health/detailed', methods=['GET'])
def detailed_health():
    """
    Detailed health check with system metrics.
    
    Returns:
        Detailed health status
    """
    # Get system metrics
    cpu_percent = psutil.cpu_percent(interval=0.1)
    memory = psutil.virtual_memory()
    disk = psutil.disk_usage('/')
    
    # Get app components status
    app = current_app._get_current_object()
    
    components = {
        'face_detector': 'unknown',
        'face_recognizer': 'unknown',
        'face_database': 'unknown',
        'attendance_db': 'unknown'
    }
    
    if hasattr(app, 'detector') and app.detector:
        components['face_detector'] = 'ready'
    
    if hasattr(app, 'recognizer') and app.recognizer:
        components['face_recognizer'] = 'ready'
    
    if hasattr(app, 'face_database') and app.face_database:
        components['face_database'] = 'ready'
        components['registered_faces'] = app.face_database.count()
    
    if hasattr(app, 'attendance_db') and app.attendance_db:
        components['attendance_db'] = 'ready'
    
    # Get performance stats
    performance = {}
    if hasattr(app, 'attendance_engine') and app.attendance_engine:
        performance = app.attendance_engine.get_performance_stats()
    
    return jsonify({
        'status': 'healthy',
        'timestamp': time.time(),
        'system': {
            'cpu_percent': cpu_percent,
            'memory_percent': memory.percent,
            'memory_available_gb': round(memory.available / (1024**3), 2),
            'disk_percent': disk.percent
        },
        'components': components,
        'performance': performance
    })


@health_bp.route('/ready', methods=['GET'])
def readiness_check():
    """
    Readiness check for load balancers.
    
    Returns:
        Ready status
    """
    app = current_app._get_current_object()
    
    # Check if all critical components are initialized
    is_ready = all([
        hasattr(app, 'detector') and app.detector,
        hasattr(app, 'recognizer') and app.recognizer,
        hasattr(app, 'face_database') and app.face_database
    ])
    
    if is_ready:
        return jsonify({'status': 'ready'}), 200
    else:
        return jsonify({'status': 'not_ready'}), 503


@health_bp.route('/version', methods=['GET'])
def version():
    """
    Get API version.
    
    Returns:
        Version info
    """
    return jsonify({
        'version': '1.0.0',
        'api_version': 'v1',
        'name': 'Facial Recognition Attendance System'
    })
