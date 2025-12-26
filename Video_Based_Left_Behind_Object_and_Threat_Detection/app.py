"""
Flask API for Video-Based Left Behind Object and Threat Detection System
Provides REST API endpoints for Laravel integration
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import logging
import os
import sys
from pathlib import Path
from datetime import datetime
import yaml

# Add src to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from src.models.object_detector import LeftBehindObjectDetector
from src.models.threat_detector import ThreatDetector
from src.tracking.object_tracker import ObjectTracker

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Flask Configuration
class FlaskConfig:
    SECRET_KEY = os.environ.get('SECRET_KEY', 'video-threat-detection-secret-key-2024')
    DEBUG = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'
    HOST = os.environ.get('FLASK_HOST', '127.0.0.1')
    PORT = int(os.environ.get('FLASK_PORT', 5003))
    CORS_ORIGINS = ['http://localhost:8000', 'http://127.0.0.1:8000']

# Global instances
object_detector = None
threat_detector = None
object_tracker = None
config = None

def initialize_models():
    """Initialize detection models"""
    global object_detector, threat_detector, object_tracker, config
    
    try:
        # Load configuration
        config_path = Path(__file__).parent / 'config' / 'config.yaml'
        with open(config_path, 'r') as f:
            config = yaml.safe_load(f)
        
        logger.info("Initializing object detector...")
        object_detector = LeftBehindObjectDetector(
            model_path=config['object_detection']['model']['weights'],
            confidence_threshold=config['object_detection']['model']['confidence_threshold'],
            target_classes=config['object_detection']['target_classes']
        )
        
        logger.info("Initializing threat detector...")
        threat_detector = ThreatDetector(
            model_path=config['threat_detection']['model']['weights'],
            model_type=config['threat_detection']['model']['type'],
            confidence_threshold=config['threat_detection']['model']['confidence_threshold'],
            clip_length=config['threat_detection']['model']['clip_length']
        )
        
        logger.info("Initializing object tracker...")
        object_tracker = ObjectTracker(
            iou_threshold=config['tracking']['iou_threshold'],
            max_age=config['tracking']['max_age'],
            min_hits=config['tracking']['min_hits'],
            left_behind_threshold_minutes=config['object_detection']['left_behind_threshold']
        )
        
        logger.info("Models initialized successfully!")
        return True
    except Exception as e:
        logger.error(f"Error initializing models: {e}")
        return False

def create_app():
    """Create and configure Flask application"""
    app = Flask(__name__)
    CORS(app, origins=FlaskConfig.CORS_ORIGINS)
    
    app.config['SECRET_KEY'] = FlaskConfig.SECRET_KEY
    app.config['DEBUG'] = FlaskConfig.DEBUG
    
    # Initialize models on startup
    with app.app_context():
        initialize_models()
    
    @app.route('/')
    def index():
        return jsonify({
            'service': 'Video-Based Threat Detection API',
            'version': '1.0.0',
            'status': 'running',
            'endpoints': {
                'health': 'GET /api/video/health',
                'status': 'GET /api/video/status',
                'detect_objects': 'POST /api/video/detect-objects',
                'detect_threats': 'POST /api/video/detect-threats',
                'process_frame': 'POST /api/video/process-frame'
            }
        })
    
    @app.route('/api/video/health', methods=['GET'])
    def health():
        """Health check endpoint"""
        return jsonify({
            'status': 'healthy',
            'service': 'Video Threat Detection API',
            'models_loaded': object_detector is not None and threat_detector is not None
        })
    
    @app.route('/api/video/status', methods=['GET'])
    def status():
        """Get system status"""
        return jsonify({
            'status': 'active',
            'object_detector_loaded': object_detector is not None,
            'threat_detector_loaded': threat_detector is not None,
            'tracker_active': object_tracker is not None,
            'config_loaded': config is not None
        })
    
    @app.route('/api/video/detect-objects', methods=['POST'])
    def detect_objects():
        """Detect left-behind objects in frame"""
        try:
            data = request.get_json()
            
            if not data or 'frame' not in data:
                return jsonify({'success': False, 'error': 'No frame data provided'}), 400
            
            # Decode base64 frame
            frame_data = base64.b64decode(data['frame'])
            nparr = np.frombuffer(frame_data, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            if frame is None:
                return jsonify({'success': False, 'error': 'Invalid frame data'}), 400
            
            # Detect objects
            detections = object_detector.detect(frame)

            # Filter by minimum size
            min_size = config['object_detection']['min_object_size']
            detections = object_detector.filter_by_size(detections, min_size)

            # Update tracker
            tracked_objects = object_tracker.update(detections)

            # Get left-behind objects
            left_behind = object_tracker.get_left_behind_objects()

            # Prepare response
            result = {
                'success': True,
                'detections': [
                    {
                        'bbox': obj.bbox.tolist() if hasattr(obj.bbox, 'tolist') else obj.bbox,
                        'class_name': obj.class_name,
                        'confidence': float(obj.confidence),
                        'track_id': obj.track_id,
                        'is_left_behind': obj.is_left_behind,
                        'time_stationary': obj.time_stationary
                    }
                    for obj in tracked_objects
                ],
                'left_behind_count': len(left_behind),
                'total_objects': len(tracked_objects)
            }

            return jsonify(result)

        except Exception as e:
            logger.error(f"Error detecting objects: {e}")
            return jsonify({'success': False, 'error': str(e)}), 500

    @app.route('/api/video/detect-threats', methods=['POST'])
    def detect_threats():
        """Detect threats in frame"""
        try:
            data = request.get_json()

            if not data or 'frame' not in data:
                return jsonify({'success': False, 'error': 'No frame data provided'}), 400

            # Decode base64 frame
            frame_data = base64.b64decode(data['frame'])
            nparr = np.frombuffer(frame_data, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

            if frame is None:
                return jsonify({'success': False, 'error': 'Invalid frame data'}), 400

            # Detect threats
            result = threat_detector.detect(frame)

            return jsonify({
                'success': True,
                'result': result
            })

        except Exception as e:
            logger.error(f"Error detecting threats: {e}")
            return jsonify({'success': False, 'error': str(e)}), 500

    @app.route('/api/video/process-frame', methods=['POST'])
    def process_frame():
        """Process frame for both objects and threats"""
        try:
            data = request.get_json()

            if not data or 'frame' not in data:
                return jsonify({'success': False, 'error': 'No frame data provided'}), 400

            # Decode base64 frame
            frame_data = base64.b64decode(data['frame'])
            nparr = np.frombuffer(frame_data, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

            if frame is None:
                return jsonify({'success': False, 'error': 'Invalid frame data'}), 400

            # Detect objects
            detections = object_detector.detect(frame)
            min_size = config['object_detection']['min_object_size']
            detections = object_detector.filter_by_size(detections, min_size)
            tracked_objects = object_tracker.update(detections)
            left_behind = object_tracker.get_left_behind_objects()

            # Detect threats
            threat_result = threat_detector.detect(frame)

            # Prepare response
            result = {
                'success': True,
                'objects': {
                    'detections': [
                        {
                            'bbox': obj.bbox.tolist() if hasattr(obj.bbox, 'tolist') else obj.bbox,
                            'class_name': obj.class_name,
                            'confidence': float(obj.confidence),
                            'track_id': obj.track_id,
                            'is_left_behind': obj.is_left_behind,
                            'time_stationary': obj.time_stationary
                        }
                        for obj in tracked_objects
                    ],
                    'left_behind_count': len(left_behind),
                    'total_objects': len(tracked_objects)
                },
                'threats': threat_result
            }

            return jsonify(result)

        except Exception as e:
            logger.error(f"Error processing frame: {e}")
            return jsonify({'success': False, 'error': str(e)}), 500

    @app.errorhandler(404)
    def not_found(error):
        return jsonify({'error': 'Endpoint not found'}), 404

    @app.errorhandler(500)
    def internal_error(error):
        return jsonify({'error': 'Internal server error'}), 500

    return app


if __name__ == '__main__':
    print("=" * 60)
    print("Video-Based Threat Detection API")
    print("=" * 60)
    print(f"Starting server on {FlaskConfig.HOST}:{FlaskConfig.PORT}")
    print("\nAvailable Endpoints:")
    print("   - GET  /api/video/health          Health Check")
    print("   - GET  /api/video/status          System Status")
    print("   - POST /api/video/detect-objects  Detect Objects")
    print("   - POST /api/video/detect-threats  Detect Threats")
    print("   - POST /api/video/process-frame   Process Complete Frame")
    print("=" * 60 + "\n")

    app = create_app()
    app.run(
        host=FlaskConfig.HOST,
        port=FlaskConfig.PORT,
        debug=FlaskConfig.DEBUG,
        threaded=True
    )

