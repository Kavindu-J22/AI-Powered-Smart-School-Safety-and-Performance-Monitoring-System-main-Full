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
import threading
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
    # Pose-based threat detection — enabled by default (no heavy dependencies)
    ENABLE_THREAT_DETECTION = os.environ.get('ENABLE_THREAT_DETECTION', 'True').lower() == 'true'

# Global instances
object_detector = None
threat_detector = None
object_tracker = None
config = None

# ── Result cache ────────────────────────────────────────────────────────────
# When ML inference is in progress (lock held) the next PHP request gets the
# cached result instantly instead of stacking up → no backlog, smooth UI.
_detection_lock  = threading.Lock()
_cached_response = None          # Last successful process-frame result dict

def initialize_models():
    """Initialize detection models"""
    global object_detector, threat_detector, object_tracker, config
    
    try:
        # Load configuration
        config_path = Path(__file__).parent / 'config' / 'config.yaml'
        with open(config_path, 'r', encoding='utf-8') as f:
            config = yaml.safe_load(f)

        # Initialize object detector
        # Primary: custom-trained model (Pen, Backpack/Tas-Ransel, Laptop …)
        # Secondary: COCO model (Book, Cell-phone, Keyboard …)
        logger.info("Initializing object detector (dual-model mode)...")

        obj_weights = config['object_detection']['model'].get('weights')
        if not obj_weights or not (Path(__file__).parent / obj_weights).exists():
            logger.warning(
                f"Primary model weights not found at '{obj_weights}', "
                "falling back to 'yolov8n.pt'"
            )
            obj_weights = 'yolov8n.pt'
        else:
            logger.info(f"Primary model: {obj_weights}")

        # Secondary model (COCO) – provides book, cell phone, scissors …
        secondary_weights = config['object_detection']['model'].get('secondary_weights', 'yolov8n.pt')
        secondary_path = Path(__file__).parent / secondary_weights
        # If secondary == primary (e.g. both fell back to yolov8n), skip it
        if str(secondary_weights) == str(obj_weights):
            secondary_weights = None
            logger.info("Primary and secondary model are the same – single-model mode")
        else:
            logger.info(f"Secondary (COCO) model: {secondary_weights}")

        secondary_conf = config['object_detection']['model'].get(
            'secondary_confidence_threshold', None
        )

        try:
            object_detector = LeftBehindObjectDetector(
                model_path=obj_weights,
                confidence_threshold=config['object_detection']['model']['confidence_threshold'],
                target_classes=config['object_detection']['target_classes'],
                secondary_model_path=secondary_weights,
                secondary_confidence_threshold=secondary_conf,
            )
        except Exception as inner_e:
            logger.error(f"Failed to initialize LeftBehindObjectDetector: {inner_e}")
            object_detector = None

        # Initialize threat detector (pose-based — no heavy 3D-CNN dependencies)
        if FlaskConfig.ENABLE_THREAT_DETECTION:
            logger.info("Initializing pose-based threat detector (YOLOv8n-pose)...")
            threat_cfg = config['threat_detection']['model']
            try:
                threat_detector = ThreatDetector(
                    model_path=None,           # pose model downloads automatically
                    model_type="pose",
                    confidence_threshold=threat_cfg.get('confidence_threshold', 0.55),
                    clip_length=threat_cfg.get('clip_length', 16),
                )
            except Exception as inner_e:
                logger.error(f"Failed to initialize ThreatDetector: {inner_e}")
                threat_detector = None
        else:
            logger.warning("Threat detection is DISABLED via ENABLE_THREAT_DETECTION=False")
            threat_detector = None

        logger.info("Initializing object tracker...")
        object_tracker = ObjectTracker(
            iou_threshold=config['tracking']['iou_threshold'],
            max_age=config['tracking']['max_age'],
            min_hits=config['tracking']['min_hits'],
            left_behind_threshold_minutes=config['object_detection']['left_behind_threshold']
        )

        logger.info("Model initialization complete (some components may be fallback or unavailable)")
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
            # Ensure object detector is available
            if object_detector is None:
                logger.error("Object detector not initialized")
                return jsonify({'success': False, 'error': 'Object detector not initialized'}), 503

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
            # Ensure threat detector is available
            if threat_detector is None:
                logger.error("Threat detector not initialized")
                return jsonify({'success': False, 'error': 'Threat detector not initialized'}), 503

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
        """Process frame for both objects and threats.

        Non-blocking design: if the ML pipeline is already busy processing a
        previous frame, the request immediately returns the last cached result
        instead of queuing up.  This keeps the PHP UI lag-free even on slow
        CPUs where each inference pass takes 300-600 ms.
        """
        global _cached_response

        # ── Guard: detectors must be ready ────────────────────────────────
        if object_detector is None and threat_detector is None:
            logger.error("No detectors initialized (objects and threats)")
            return jsonify({'success': False, 'error': 'No detectors initialized'}), 503

        data = request.get_json()
        if not data or 'frame' not in data:
            return jsonify({'success': False, 'error': 'No frame data provided'}), 400

        # ── Decode frame ───────────────────────────────────────────────────
        try:
            frame_data = base64.b64decode(data['frame'])
            nparr = np.frombuffer(frame_data, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        except Exception as decode_err:
            return jsonify({'success': False, 'error': f'Frame decode error: {decode_err}'}), 400

        if frame is None:
            return jsonify({'success': False, 'error': 'Invalid frame data'}), 400

        # ── Try to acquire the inference lock (non-blocking) ───────────────
        # If another thread is still running inference, skip this frame and
        # return the last cached result immediately.
        acquired = _detection_lock.acquire(blocking=False)
        if not acquired:
            if _cached_response is not None:
                cached = dict(_cached_response)
                cached['cached'] = True   # signal to PHP that this is a repeat
                return jsonify(cached)
            # No cache yet and lock is held — wait briefly then proceed
            _detection_lock.acquire(blocking=True)

        try:
            # ── Object detection ───────────────────────────────────────────
            detections = object_detector.detect(frame)
            min_size = config['object_detection']['min_object_size']
            detections = object_detector.filter_by_size(detections, min_size)
            tracked_objects = object_tracker.update(detections)
            left_behind = object_tracker.get_left_behind_objects()

            # ── Threat detection ───────────────────────────────────────────
            threat_result = {
                'is_threat': False,
                'threat_type': None,
                'confidence': 0.0,
                'all_scores': {},
                'status': 'disabled'
            }
            if threat_detector is not None:
                try:
                    threat_result = threat_detector.detect(frame)
                except Exception as threat_error:
                    logger.error(f"Threat detection failed: {threat_error}")
                    threat_result['status'] = 'error'
                    threat_result['error'] = str(threat_error)

            # ── Build result and update cache ──────────────────────────────
            result = {
                'success': True,
                'cached': False,
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
            _cached_response = result   # store for next request if ML is busy

            return jsonify(result)

        except Exception as e:
            logger.error(f"Error processing frame: {e}")
            return jsonify({'success': False, 'error': str(e)}), 500

        finally:
            _detection_lock.release()

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

