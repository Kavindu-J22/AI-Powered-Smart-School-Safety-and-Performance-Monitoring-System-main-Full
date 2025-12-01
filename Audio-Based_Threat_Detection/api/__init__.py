# Audio Threat Detection API
from flask import Flask
from flask_cors import CORS
import os
import sys

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import FlaskConfig


def create_app():
    """Create and configure the Flask application"""
    app = Flask(__name__)
    CORS(app, origins=['*'])  # Allow all origins for development

    app.config['SECRET_KEY'] = FlaskConfig.SECRET_KEY
    app.config['DEBUG'] = FlaskConfig.DEBUG

    # Register blueprints
    from .routes import audio_bp, detection_bp

    app.register_blueprint(audio_bp, url_prefix='/api/audio')
    app.register_blueprint(detection_bp, url_prefix='/api/detection')

    return app

