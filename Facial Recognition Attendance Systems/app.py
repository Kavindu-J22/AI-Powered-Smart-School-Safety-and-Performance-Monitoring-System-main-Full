"""
Facial Recognition Attendance System
=====================================
Main Flask Application Entry Point

A high-performance face recognition system for automated attendance marking.
"""

import os
import logging
from flask import Flask, jsonify
from flask_cors import CORS

from config.settings import get_config
from utils.logger import setup_logging

# Initialize logging
logger = logging.getLogger(__name__)


def create_app(config_env: str = None) -> Flask:
    """
    Application factory for creating Flask app.
    
    Args:
        config_env: Configuration environment ('development', 'production', 'testing')
        
    Returns:
        Configured Flask application
    """
    # Load configuration
    config = get_config(config_env)
    
    # Setup logging
    setup_logging(
        log_level=config.LOG_LEVEL,
        log_dir=str(config.LOGS_DIR),
        log_file=config.LOG_FILE
    )
    
    logger.info("Initializing Facial Recognition Attendance System...")
    
    # Create Flask app
    app = Flask(__name__)
    app.config['SECRET_KEY'] = config.SECRET_KEY
    app.config['DEBUG'] = config.DEBUG
    
    # Enable CORS for all routes (allowing Dashboard to call the API)
    CORS(app, resources={
        r"/*": {
            "origins": "*",
            "methods": ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
            "allow_headers": ["Content-Type", "Authorization", "X-CSRF-TOKEN"]
        }
    })
    
    # Store config on app
    app.app_config = config
    
    # Initialize components
    _initialize_components(app, config)
    
    # Register blueprints
    _register_blueprints(app)
    
    # Register error handlers
    _register_error_handlers(app)
    
    logger.info("Application initialized successfully")
    
    return app


def _initialize_components(app: Flask, config):
    """Initialize all application components."""
    
    logger.info("Initializing face detection...")
    from core.face_detector import FaceDetector
    app.detector = FaceDetector(
        backend=config.DETECTION_MODEL,
        confidence_threshold=config.DETECTION_CONFIDENCE,
        min_face_size=config.MIN_FACE_SIZE,
        device='cuda' if config.USE_GPU else 'cpu'
    )
    
    logger.info("Initializing face recognition...")
    from core.face_recognizer import FaceRecognizer
    app.recognizer = FaceRecognizer(
        backend=config.RECOGNITION_MODEL,
        device='cuda' if config.USE_GPU else 'cpu',
        similarity_threshold=config.RECOGNITION_THRESHOLD
    )
    
    logger.info("Initializing face aligner...")
    from core.face_aligner import FaceAligner
    app.aligner = FaceAligner(target_size=config.FACE_IMAGE_SIZE)
    
    logger.info("Initializing face database...")
    from database.face_database import FaceDatabase
    app.face_database = FaceDatabase(
        embeddings_dir=str(config.EMBEDDINGS_DIR),
        faces_dir=str(config.FACES_DIR)
    )
    
    logger.info("Initializing attendance database...")
    from database.attendance_db import AttendanceDB
    app.attendance_db = AttendanceDB(config.DATABASE_URL)
    
    logger.info("Initializing attendance engine...")
    from core.attendance_engine import AttendanceEngine
    app.attendance_engine = AttendanceEngine(
        face_database=app.face_database,
        detection_backend=config.DETECTION_MODEL,
        recognition_backend=config.RECOGNITION_MODEL,
        device='cuda' if config.USE_GPU else 'cpu',
        recognition_threshold=config.RECOGNITION_THRESHOLD,
        enable_anti_spoof=config.ANTI_SPOOF_ENABLED,
        attendance_cooldown=config.ATTENDANCE_COOLDOWN_SECONDS
    )
    app.attendance_engine.load_face_database(app.face_database)
    
    logger.info("Initializing registration service...")
    from services.registration_service import RegistrationService
    app.registration_service = RegistrationService(
        face_database=app.face_database,
        face_detector=app.detector,
        attendance_db=app.attendance_db,
        min_capture_count=10,
        max_capture_count=config.CAPTURE_COUNT * 2
    )
    
    logger.info("Initializing face trainer...")
    from training.face_trainer import FaceTrainer
    app.face_trainer = FaceTrainer(
        face_database=app.face_database,
        face_detector=app.detector,
        face_recognizer=app.recognizer,
        face_aligner=app.aligner,
        attendance_db=app.attendance_db,
        augmentation_enabled=config.AUGMENTATION_ENABLED,
        augmentation_multiplier=config.AUGMENTATION_MULTIPLIER
    )
    
    logger.info("Initializing attendance service...")
    from services.attendance_service import AttendanceService
    app.attendance_service = AttendanceService(
        attendance_engine=app.attendance_engine,
        attendance_db=app.attendance_db,
        dashboard_api_url=config.DASHBOARD_API_URL,
        dashboard_api_key=config.DASHBOARD_API_KEY,
        webhook_url=config.WEBHOOK_URL,
        cooldown_seconds=config.ATTENDANCE_COOLDOWN_SECONDS
    )
    
    # Initialize camera service (optional)
    try:
        logger.info("Initializing camera service...")
        from services.camera_service import CameraService
        app.camera_service = CameraService(
            camera_index=config.CAMERA_INDEX,
            width=config.CAMERA_WIDTH,
            height=config.CAMERA_HEIGHT,
            fps=config.CAMERA_FPS
        )
    except Exception as e:
        logger.warning(f"Camera service not initialized: {e}")
        app.camera_service = None
    
    logger.info(f"Loaded {app.face_database.count()} registered faces")


def _register_blueprints(app: Flask):
    """Register API blueprints."""
    from api import api_bp
    app.register_blueprint(api_bp)
    
    logger.info("API blueprints registered")


def _register_error_handlers(app: Flask):
    """Register error handlers."""
    
    @app.errorhandler(400)
    def bad_request(error):
        return jsonify({'error': 'Bad request', 'message': str(error)}), 400
    
    @app.errorhandler(404)
    def not_found(error):
        return jsonify({'error': 'Not found', 'message': str(error)}), 404
    
    @app.errorhandler(500)
    def internal_error(error):
        logger.error(f"Internal error: {error}")
        return jsonify({'error': 'Internal server error'}), 500
    
    @app.errorhandler(Exception)
    def handle_exception(error):
        logger.error(f"Unhandled exception: {error}", exc_info=True)
        return jsonify({
            'error': 'Internal server error',
            'message': str(error) if app.debug else 'An error occurred'
        }), 500


# Create default app instance
app = create_app()


@app.route('/')
def index():
    """Root endpoint."""
    return jsonify({
        'name': 'Facial Recognition Attendance System',
        'version': '1.0.0',
        'status': 'running',
        'endpoints': {
            'health': '/api/health',
            'registration': '/api/registration',
            'training': '/api/training',
            'attendance': '/api/attendance',
            'recognize_face': '/recognize_face',
            'register_student': '/register_student'
        }
    })


# ============================================
# ROOT-LEVEL DASHBOARD ENDPOINTS
# ============================================
# These endpoints are at root level for easy Dashboard integration

import cv2
import numpy as np
from flask import request

@app.route('/recognize_face', methods=['POST'])
def recognize_face_root():
    """
    Root-level face recognition endpoint for Dashboard.
    """
    if 'image' not in request.files:
        return jsonify({
            'success': False,
            'message': 'No image file provided',
            'student_id': None,
            'confidence': 0,
            'student_name': 'Unknown'
        }), 400
    
    file = request.files['image']
    image_bytes = file.read()
    nparr = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    if image is None:
        return jsonify({
            'success': False,
            'message': 'Invalid image file',
            'student_id': None,
            'confidence': 0,
            'student_name': 'Unknown'
        }), 400
    
    try:
        # Preprocess image for better recognition
        # Apply CLAHE for better contrast in various lighting
        lab = cv2.cvtColor(image, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        l = clahe.apply(l)
        lab = cv2.merge([l, a, b])
        image = cv2.cvtColor(lab, cv2.COLOR_LAB2BGR)
        
        # Use process_frame method which returns RecognitionResult objects
        results = app.attendance_engine.process_frame(image, mark_attendance=False)
        
        if not results or len(results) == 0:
            return jsonify({
                'success': False,
                'message': 'No face detected',
                'student_id': None,
                'confidence': 0,
                'student_name': 'Unknown',
                'bbox': None,
                'face_detected': False
            })
        
        best_result = results[0]
        
        # RecognitionResult is a dataclass, access attributes directly
        if not best_result.is_recognized or best_result.student_id is None or best_result.student_id == 'unknown':
            # Still return bbox even if not recognized
            bbox = None
            if hasattr(best_result, 'face_bbox') and best_result.face_bbox:
                x1, y1, x2, y2 = best_result.face_bbox
                bbox = {
                    'x': int(x1),
                    'y': int(y1),
                    'width': int(x2 - x1),
                    'height': int(y2 - y1)
                }
            conf_val = best_result.confidence if hasattr(best_result, 'confidence') else 0
            return jsonify({
                'success': False,
                'message': 'Face not recognized',
                'student_id': None,
                'confidence': float(conf_val) if conf_val else 0.0,
                'student_name': 'Unknown',
                'bbox': bbox,
                'face_detected': True
            })
        
        # Get dashboard student ID from the stored student_id
        student_id = best_result.student_id
        student_name = best_result.student_name if hasattr(best_result, 'student_name') else 'Unknown'
        confidence = best_result.confidence if hasattr(best_result, 'confidence') else 0
        
        # Extract dashboard ID from student_id (e.g., "DASH_stu-00000078" -> lookup in DB)
        dashboard_id = None
        try:
            # Try to get from face database
            student_info = app.face_database.get_student(student_id)
            if student_info:
                dashboard_id = student_info.get('dashboard_student_id')
                if not student_name or student_name == 'Unknown':
                    student_name = student_info.get('name', 'Unknown')
            
            # If still no dashboard_id, try to extract from student_id format
            if dashboard_id is None:
                if student_id.startswith('DASH_'):
                    # Format: DASH_stu-00000078 -> need to find in dashboard DB
                    dashboard_id = student_id  # Let dashboard handle the lookup
                else:
                    dashboard_id = student_id
        except Exception as e:
            logger.warning(f"Could not get dashboard ID: {e}")
            dashboard_id = student_id
        
        logger.info(f"Face recognized: {student_name} (ID: {student_id}, Dashboard: {dashboard_id}, Confidence: {confidence:.2f})")
        
        # Get bounding box if available
        bbox = None
        if hasattr(best_result, 'face_bbox') and best_result.face_bbox:
            x1, y1, x2, y2 = best_result.face_bbox
            bbox = {
                'x': int(x1),
                'y': int(y1),
                'width': int(x2 - x1),
                'height': int(y2 - y1)
            }
        
        # Convert numpy float32 to Python float for JSON serialization
        confidence_float = float(confidence) if confidence else 0.0
        
        return jsonify({
            'success': True,
            'student_id': dashboard_id,
            'confidence': confidence_float,
            'student_name': student_name,
            'bbox': bbox,
            'face_detected': True
        })
        
    except Exception as e:
        logger.error(f"Face recognition error: {e}")
        return jsonify({
            'success': False,
            'message': str(e),
            'student_id': None,
            'confidence': 0,
            'student_name': 'Unknown'
        }), 500


@app.route('/register_student', methods=['POST'])
def register_student_root():
    """
    Root-level student face registration endpoint for Dashboard.
    Captures face images and auto-trains when enough images are collected.
    
    Accepts either:
    - Form data with image file upload (single image)
    - JSON with student_id, name, and images array (batch of base64 images)
    """
    import base64
    
    # Check if JSON request (batch upload from student form)
    if request.is_json:
        data = request.get_json()
        student_id = data.get('student_id')
        student_name = data.get('name', f'Student {student_id}')
        images_b64 = data.get('images', [])
        
        if not student_id:
            return jsonify({
                'success': False,
                'message': 'student_id is required'
            }), 400
        
        if not images_b64 or len(images_b64) == 0:
            return jsonify({
                'success': False,
                'message': 'No images provided'
            }), 400
        
        try:
            internal_student_id = f"DASH_{student_id}"
            
            # Start capture session
            session_result = app.registration_service.start_capture_session(
                student_id=internal_student_id,
                student_name=student_name,
                target_count=len(images_b64),
                dashboard_student_id=str(student_id)
            )
            session_id = session_result.get('session_id')
            
            captured_count = 0
            for img_b64 in images_b64:
                # Remove data URL prefix if present
                if ',' in img_b64:
                    img_b64 = img_b64.split(',')[1]
                
                # Decode base64 to image
                img_bytes = base64.b64decode(img_b64)
                nparr = np.frombuffer(img_bytes, np.uint8)
                image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                
                if image is not None:
                    capture_result = app.registration_service.capture_frame(session_id, image)
                    if capture_result.get('success', False):
                        captured_count += 1
            
            # Train if we have enough faces
            if captured_count >= 10:
                train_result = app.face_trainer.train_student(internal_student_id)
                
                if train_result.get('success'):
                    app.registration_service.complete_registration(session_id, auto_train=False)
                    app.attendance_engine.load_face_database(app.face_database)
                    
                    logger.info(f"Student {student_id} registered and trained with {captured_count} faces")
                    
                    return jsonify({
                        'success': True,
                        'message': f'Face registered and trained successfully with {captured_count} images',
                        'face_count': captured_count,
                        'trained': True
                    })
                else:
                    return jsonify({
                        'success': False,
                        'message': train_result.get('message', 'Training failed'),
                        'face_count': captured_count,
                        'trained': False
                    })
            else:
                return jsonify({
                    'success': False,
                    'message': f'Only captured {captured_count} valid faces. Need at least 10.',
                    'face_count': captured_count,
                    'trained': False
                })
                
        except Exception as e:
            logger.error(f"Batch face registration error: {e}")
            return jsonify({
                'success': False,
                'message': str(e)
            }), 500
    
    # Form data with single image upload (legacy)
    student_id = request.form.get('student_id')
    student_name = request.form.get('student_name', f'Student {student_id}')
    
    if not student_id:
        return jsonify({
            'success': False,
            'message': 'student_id is required'
        }), 400
    
    if 'image' not in request.files:
        return jsonify({
            'success': False,
            'message': 'No image file provided'
        }), 400
    
    file = request.files['image']
    image_bytes = file.read()
    nparr = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    if image is None:
        return jsonify({
            'success': False,
            'message': 'Invalid image file'
        }), 400
    
    try:
        internal_student_id = f"DASH_{student_id}"
        
        # Get or create capture session
        session = app.registration_service.get_session_by_student(internal_student_id)
        
        if not session:
            session_result = app.registration_service.start_capture_session(
                student_id=internal_student_id,
                student_name=student_name,
                target_count=20,
                dashboard_student_id=int(student_id)
            )
            session_id = session_result.get('session_id')
        else:
            session_id = session.get('session_id')
        
        # Capture the face
        capture_result = app.registration_service.capture_frame(session_id, image)
        
        if not capture_result.get('success', False):
            return jsonify({
                'success': False,
                'message': capture_result.get('message', 'Failed to capture face'),
                'face_count': capture_result.get('current_count', 0)
            })
        
        current_count = capture_result.get('current_count', 0)
        
        # Auto-train when we have enough faces (10+)
        if current_count >= 10:
            train_result = app.face_trainer.train_student(internal_student_id)
            
            if train_result.get('success'):
                app.registration_service.complete_registration(session_id, auto_train=False)
                app.attendance_engine.load_face_database(app.face_database)
                
                logger.info(f"Student {student_id} registered and trained with {current_count} faces")
                
                return jsonify({
                    'success': True,
                    'message': f'Face registered and trained successfully with {current_count} images',
                    'face_count': current_count,
                    'trained': True
                })
        
        return jsonify({
            'success': True,
            'message': f'Face captured ({current_count}/20). Need {20 - current_count} more for training.',
            'face_count': current_count,
            'trained': False
        })
        
    except Exception as e:
        logger.error(f"Student face registration error: {e}")
        return jsonify({
            'success': False,
            'message': str(e)
        }), 500


@app.route('/health', methods=['GET'])
def health_root():
    """Root-level health check."""
    return jsonify({
        'status': 'healthy',
        'service': 'Facial Recognition Attendance System',
        'registered_faces': app.face_database.count()
    })


@app.route('/api/students/register', methods=['POST'])
def api_students_register():
    """
    API endpoint for student registration with face images.
    This is the endpoint called from the Dashboard student registration form.
    
    Expected JSON:
    {
        "student_id": "STU001",
        "name": "John Doe",
        "images": ["base64_image_1", "base64_image_2", ...]
    }
    """
    import base64
    
    if not request.is_json:
        return jsonify({
            'success': False,
            'error': 'Content-Type must be application/json'
        }), 400
    
    data = request.get_json()
    student_id = data.get('student_id')
    student_name = data.get('name', f'Student {student_id}')
    images_b64 = data.get('images', [])
    
    if not student_id:
        return jsonify({
            'success': False,
            'error': 'student_id is required'
        }), 400
    
    if not images_b64 or len(images_b64) < 10:
        return jsonify({
            'success': False,
            'error': f'At least 10 images are required, got {len(images_b64)}'
        }), 400
    
    try:
        internal_student_id = f"DASH_{student_id}"
        
        logger.info(f"Starting face registration for student: {student_id} ({student_name}) with {len(images_b64)} images")
        
        # Start capture session
        session_result = app.registration_service.start_capture_session(
            student_id=internal_student_id,
            student_name=student_name,
            target_count=len(images_b64),
            dashboard_student_id=str(student_id)
        )
        session_id = session_result.get('session_id')
        
        captured_count = 0
        failed_count = 0
        
        for idx, img_b64 in enumerate(images_b64):
            try:
                # Remove data URL prefix if present (e.g., "data:image/jpeg;base64,")
                if ',' in img_b64:
                    img_b64 = img_b64.split(',')[1]
                
                # Decode base64 to image
                img_bytes = base64.b64decode(img_b64)
                nparr = np.frombuffer(img_bytes, np.uint8)
                image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                
                if image is not None:
                    capture_result = app.registration_service.capture_frame(session_id, image)
                    if capture_result.get('success', False):
                        captured_count += 1
                    else:
                        failed_count += 1
                        logger.debug(f"Failed to capture face from image {idx + 1}: {capture_result.get('message', 'Unknown error')}")
                else:
                    failed_count += 1
                    logger.debug(f"Failed to decode image {idx + 1}")
            except Exception as img_error:
                failed_count += 1
                logger.debug(f"Error processing image {idx + 1}: {img_error}")
        
        logger.info(f"Captured {captured_count} faces, {failed_count} failed for student {student_id}")
        
        # Train if we have enough faces
        if captured_count >= 10:
            logger.info(f"Training face model for student {student_id}...")
            train_result = app.face_trainer.train_student(internal_student_id)
            
            if train_result.get('success'):
                app.registration_service.complete_registration(session_id, auto_train=False)
                app.attendance_engine.load_face_database(app.face_database)
                
                logger.info(f"✓ Student {student_id} registered and trained successfully with {captured_count} faces")
                
                return jsonify({
                    'success': True,
                    'message': f'Face recognition model trained successfully with {captured_count} images',
                    'face_count': captured_count,
                    'failed_count': failed_count,
                    'trained': True,
                    'student_id': student_id
                })
            else:
                error_msg = train_result.get('message', 'Training failed')
                logger.error(f"Training failed for student {student_id}: {error_msg}")
                return jsonify({
                    'success': False,
                    'error': error_msg,
                    'face_count': captured_count,
                    'trained': False
                }), 500
        else:
            return jsonify({
                'success': False,
                'error': f'Only captured {captured_count} valid faces. Need at least 10.',
                'face_count': captured_count,
                'failed_count': failed_count,
                'trained': False
            }), 400
            
    except Exception as e:
        logger.error(f"Face registration error for student {student_id}: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/retrain_all', methods=['POST'])
def retrain_all_students():
    """
    Retrain all registered students with improved multi-embedding approach.
    Use this after updating the training algorithm.
    """
    try:
        logger.info("Starting retrain of all students...")
        
        result = app.face_trainer.train_all()
        
        # Reload embeddings into attendance engine
        app.attendance_engine.load_face_database(app.face_database)
        
        return jsonify({
            'success': result.get('success', False),
            'message': 'Retrained all students with multi-embedding support',
            'total_students': result.get('total_students', 0),
            'processed_students': result.get('processed_students', 0),
            'total_images': result.get('total_images', 0),
            'training_time': result.get('training_time_seconds', 0)
        })
        
    except Exception as e:
        logger.error(f"Retrain error: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/retrain_student/<student_id>', methods=['POST'])
def retrain_single_student(student_id: str):
    """
    Retrain a single student with multi-embedding approach.
    """
    try:
        logger.info(f"Retraining student: {student_id}")
        
        result = app.face_trainer.retrain_student(student_id)
        
        # Reload embeddings into attendance engine
        app.attendance_engine.load_face_database(app.face_database)
        
        return jsonify({
            'success': result.get('success', False),
            'student_id': student_id,
            'images_processed': result.get('images_processed', 0),
            'embeddings_generated': result.get('embeddings_generated', 0),
            'quality_score': result.get('quality_score', 0)
        })
        
    except Exception as e:
        logger.error(f"Retrain error for {student_id}: {e}", exc_info=True)
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


if __name__ == '__main__':
    config = get_config()
    
    print(f"""
    ╔══════════════════════════════════════════════════════════════╗
    ║     Facial Recognition Attendance System                     ║
    ║     Version: 1.0.0                                           ║
    ╠══════════════════════════════════════════════════════════════╣
    ║     Server: http://{config.HOST}:{config.PORT}                           ║
    ║     Debug: {config.DEBUG}                                              ║
    ║     Registered Faces: {app.face_database.count()}                                    ║
    ╚══════════════════════════════════════════════════════════════╝
    """)
    
    app.run(
        host=config.HOST,
        port=config.PORT,
        debug=config.DEBUG,
        threaded=True
    )
