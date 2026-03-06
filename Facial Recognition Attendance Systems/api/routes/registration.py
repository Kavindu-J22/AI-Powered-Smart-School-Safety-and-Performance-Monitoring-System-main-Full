"""
Registration API Routes
========================
Endpoints for student face registration.
"""

from flask import Blueprint, request, jsonify, current_app
import cv2
import numpy as np
import base64
import logging

registration_bp = Blueprint('registration', __name__, url_prefix='/registration')
logger = logging.getLogger(__name__)


@registration_bp.route('/start', methods=['POST'])
def start_registration():
    """
    Start a new face capture session.
    
    Request JSON:
        {
            "student_id": "STU001",
            "student_name": "John Doe",
            "capture_count": 25,
            "dashboard_student_id": 123  // optional
        }
    
    Returns:
        Session info
    """
    data = request.get_json()
    
    if not data:
        return jsonify({'error': 'No data provided'}), 400
    
    student_id = data.get('student_id')
    student_name = data.get('student_name')
    
    if not student_id or not student_name:
        return jsonify({'error': 'student_id and student_name are required'}), 400
    
    capture_count = data.get('capture_count', 25)
    dashboard_student_id = data.get('dashboard_student_id')
    
    app = current_app._get_current_object()
    
    result = app.registration_service.start_capture_session(
        student_id=student_id,
        student_name=student_name,
        target_count=capture_count,
        dashboard_student_id=dashboard_student_id
    )
    
    return jsonify(result)


@registration_bp.route('/capture', methods=['POST'])
def capture_frame():
    """
    Capture a single frame from camera or uploaded image.
    
    Request:
        - Form data with 'image' file
        - OR JSON with 'session_id' to capture from camera
        - OR JSON with 'image' as base64
    
    Returns:
        Capture result
    """
    app = current_app._get_current_object()
    
    # Get session ID
    session_id = request.form.get('session_id') or request.json.get('session_id') if request.is_json else None
    
    if not session_id:
        return jsonify({'error': 'session_id is required'}), 400
    
    # Check for file upload
    if 'image' in request.files:
        file = request.files['image']
        
        # Read image
        image_bytes = file.read()
        nparr = np.frombuffer(image_bytes, np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if image is None:
            return jsonify({'error': 'Invalid image file'}), 400
        
        result = app.registration_service.capture_frame(session_id, image)
        return jsonify(result)
    
    # Check for base64 image
    if request.is_json:
        data = request.get_json()
        
        if 'image' in data:
            result = app.registration_service.upload_base64_image(
                session_id, data['image']
            )
            return jsonify(result)
        
        # Capture from camera
        if hasattr(app, 'camera_service') and app.camera_service:
            frame = app.camera_service.get_frame()
            if frame is not None:
                result = app.registration_service.capture_frame(session_id, frame)
                return jsonify(result)
            else:
                return jsonify({'error': 'Camera not available'}), 503
    
    return jsonify({'error': 'No image provided'}), 400


@registration_bp.route('/upload-batch', methods=['POST'])
def upload_batch():
    """
    Upload multiple images at once.
    
    Request:
        Form data with multiple 'images' files
    
    Returns:
        Upload result
    """
    session_id = request.form.get('session_id')
    
    if not session_id:
        return jsonify({'error': 'session_id is required'}), 400
    
    if 'images' not in request.files:
        return jsonify({'error': 'No images provided'}), 400
    
    files = request.files.getlist('images')
    images = []
    
    for file in files:
        image_bytes = file.read()
        nparr = np.frombuffer(image_bytes, np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if image is not None:
            images.append(image)
    
    app = current_app._get_current_object()
    result = app.registration_service.upload_images(session_id, images)
    
    return jsonify(result)


@registration_bp.route('/status/<session_id>', methods=['GET'])
def get_session_status(session_id):
    """
    Get capture session status.
    
    Returns:
        Session status
    """
    app = current_app._get_current_object()
    result = app.registration_service.get_session_status(session_id)
    
    if 'error' in result:
        return jsonify(result), 404
    
    return jsonify(result)


@registration_bp.route('/complete', methods=['POST'])
def complete_registration():
    """
    Complete registration and optionally trigger training.
    
    Request JSON:
        {
            "session_id": "...",
            "auto_train": true
        }
    
    Returns:
        Completion result
    """
    data = request.get_json()
    
    if not data or 'session_id' not in data:
        return jsonify({'error': 'session_id is required'}), 400
    
    session_id = data['session_id']
    auto_train = data.get('auto_train', False)
    
    app = current_app._get_current_object()
    result = app.registration_service.complete_registration(
        session_id, auto_train=auto_train
    )
    
    if not result.get('success'):
        return jsonify(result), 400
    
    # Trigger training if requested
    if auto_train and result.get('needs_training'):
        # Train just this student
        train_result = app.face_trainer.train_student(
            result['student_id'],
            result['student_name']
        )
        result['training_result'] = train_result
        
        # Refresh database
        if train_result.get('success'):
            app.attendance_engine.refresh_database()
    
    return jsonify(result)


@registration_bp.route('/cancel', methods=['POST'])
def cancel_registration():
    """
    Cancel capture session.
    
    Request JSON:
        {
            "session_id": "..."
        }
    
    Returns:
        Cancellation result
    """
    data = request.get_json()
    
    if not data or 'session_id' not in data:
        return jsonify({'error': 'session_id is required'}), 400
    
    app = current_app._get_current_object()
    result = app.registration_service.cancel_session(data['session_id'])
    
    return jsonify(result)


@registration_bp.route('/sessions', methods=['GET'])
def list_sessions():
    """
    List all active capture sessions.
    
    Returns:
        List of active sessions
    """
    app = current_app._get_current_object()
    sessions = app.registration_service.get_active_sessions()
    
    return jsonify({
        'sessions': sessions,
        'count': len(sessions)
    })


@registration_bp.route('/students', methods=['GET'])
def list_registered_students():
    """
    List all registered students.
    
    Returns:
        List of students
    """
    app = current_app._get_current_object()
    students = app.face_database.get_all_students()
    
    return jsonify({
        'students': students,
        'count': len(students)
    })


@registration_bp.route('/students/<student_id>', methods=['GET'])
def get_student(student_id):
    """
    Get student details.
    
    Returns:
        Student info
    """
    app = current_app._get_current_object()
    
    info = app.face_database.get_student_info(student_id)
    
    if not info:
        return jsonify({'error': 'Student not found'}), 404
    
    info['student_id'] = student_id
    info['images_count'] = app.face_database.get_face_images_count(student_id)
    info['has_embedding'] = app.face_database.student_exists(student_id)
    
    return jsonify(info)


@registration_bp.route('/students/<student_id>', methods=['DELETE'])
def delete_student(student_id):
    """
    Delete a student and their face data.
    
    Returns:
        Deletion result
    """
    app = current_app._get_current_object()
    
    success = app.face_database.remove_student(student_id)
    
    if success:
        app.attendance_engine.refresh_database()
        return jsonify({'success': True, 'message': f'Student {student_id} deleted'})
    else:
        return jsonify({'error': 'Failed to delete student'}), 400


@registration_bp.route('/webhook/student', methods=['POST'])
def student_webhook():
    """
    Webhook endpoint for dashboard student updates.
    
    Called when student is created/updated in the school dashboard.
    
    Request JSON:
        {
            "event": "student_created" | "student_updated",
            "data": {
                "id": 123,
                "student_id": "STU001",
                "name": "John Doe",
                "email": "john@school.com",
                "grade": "10",
                "section": "A"
            }
        }
    
    Returns:
        Webhook handling result
    """
    data = request.get_json()
    
    if not data:
        return jsonify({'error': 'No data provided'}), 400
    
    event = data.get('event')
    student_data = data.get('data', {})
    
    if not student_data.get('student_id'):
        return jsonify({'error': 'student_id is required'}), 400
    
    app = current_app._get_current_object()
    
    result = app.registration_service.update_student_from_dashboard(
        dashboard_student_id=student_data.get('id'),
        student_id=student_data['student_id'],
        name=student_data.get('name', ''),
        email=student_data.get('email'),
        grade=student_data.get('grade'),
        section=student_data.get('section')
    )
    
    logger.info(f"Webhook processed: {event} - {student_data.get('student_id')}")
    
    return jsonify(result)
