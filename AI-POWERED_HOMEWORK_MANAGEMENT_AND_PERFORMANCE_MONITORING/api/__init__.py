"""
Flask API Module for Homework Management System
"""
from flask import Flask
from flask_cors import CORS

def create_app():
    """Create and configure the Flask application"""
    app = Flask(__name__)
    CORS(app)
    
    # Load configuration
    from config import FlaskConfig
    app.config['SECRET_KEY'] = FlaskConfig.SECRET_KEY
    app.config['DEBUG'] = FlaskConfig.DEBUG
    
    # Register blueprints
    from .routes import lesson_bp, homework_bp, evaluation_bp, report_bp, performance_bp
    
    app.register_blueprint(lesson_bp, url_prefix='/api/lessons')
    app.register_blueprint(homework_bp, url_prefix='/api/homework')
    app.register_blueprint(evaluation_bp, url_prefix='/api/evaluation')
    app.register_blueprint(report_bp, url_prefix='/api/reports')
    app.register_blueprint(performance_bp, url_prefix='/api/performance')
    
    return app

