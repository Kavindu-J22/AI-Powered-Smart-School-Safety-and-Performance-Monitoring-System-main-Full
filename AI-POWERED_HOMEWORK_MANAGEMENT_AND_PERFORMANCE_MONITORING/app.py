"""
AI-Powered Homework Management and Performance Monitoring System
Main Flask Application Entry Point
"""
import os
import sys
import logging
from pathlib import Path

# Add project root to path
PROJECT_ROOT = Path(__file__).resolve().parent
sys.path.insert(0, str(PROJECT_ROOT))

from flask import Flask, jsonify
from flask_cors import CORS

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def create_app():
    """Create and configure the Flask application"""
    app = Flask(__name__)
    
    # Enable CORS for Laravel integration
    CORS(app, resources={
        r"/api/*": {
            "origins": ["http://localhost:8000", "http://127.0.0.1:8000"],
            "methods": ["GET", "POST", "PUT", "DELETE"],
            "allow_headers": ["Content-Type", "Authorization"]
        }
    })
    
    # Load configuration
    app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'homework-mgmt-secret-2024')
    app.config['DEBUG'] = os.environ.get('FLASK_DEBUG', 'True').lower() == 'true'
    
    # Register blueprints
    from api.routes.lesson_routes import lesson_bp
    from api.routes.homework_routes import homework_bp
    from api.routes.evaluation_routes import evaluation_bp
    from api.routes.report_routes import report_bp
    from api.routes.performance_routes import performance_bp
    
    app.register_blueprint(lesson_bp, url_prefix='/api/lessons')
    app.register_blueprint(homework_bp, url_prefix='/api/homework')
    app.register_blueprint(evaluation_bp, url_prefix='/api/evaluation')
    app.register_blueprint(report_bp, url_prefix='/api/reports')
    app.register_blueprint(performance_bp, url_prefix='/api/performance')
    
    # Health check endpoint
    @app.route('/api/health', methods=['GET'])
    def health_check():
        return jsonify({
            'status': 'healthy',
            'service': 'AI Homework Management API',
            'version': '1.0.0'
        })
    
    # API info endpoint
    @app.route('/api', methods=['GET'])
    def api_info():
        return jsonify({
            'name': 'AI-Powered Homework Management API',
            'version': '1.0.0',
            'endpoints': {
                'lessons': '/api/lessons',
                'homework': '/api/homework',
                'evaluation': '/api/evaluation',
                'reports': '/api/reports',
                'performance': '/api/performance'
            },
            'documentation': {
                'parse_lesson': 'POST /api/lessons/parse',
                'generate_questions': 'POST /api/lessons/generate-questions',
                'create_homework': 'POST /api/homework/create',
                'schedule_weekly': 'POST /api/homework/schedule-weekly',
                'evaluate_submission': 'POST /api/evaluation/evaluate',
                'student_report': 'GET /api/reports/monthly/student/<id>',
                'class_report': 'GET /api/reports/monthly/class/<id>',
                'student_performance': 'GET /api/performance/student/<id>',
                'class_performance': 'GET /api/performance/class/<id>'
            }
        })
    
    # Error handlers
    @app.errorhandler(404)
    def not_found(error):
        return jsonify({'error': 'Endpoint not found'}), 404
    
    @app.errorhandler(500)
    def server_error(error):
        logger.error(f"Server error: {str(error)}")
        return jsonify({'error': 'Internal server error'}), 500
    
    return app


# Create app instance
app = create_app()

if __name__ == '__main__':
    port = int(os.environ.get('FLASK_PORT', 5001))
    host = os.environ.get('FLASK_HOST', '0.0.0.0')
    debug = os.environ.get('FLASK_DEBUG', 'True').lower() == 'true'
    
    logger.info(f"Starting AI Homework Management API on {host}:{port}")
    app.run(host=host, port=port, debug=debug)

