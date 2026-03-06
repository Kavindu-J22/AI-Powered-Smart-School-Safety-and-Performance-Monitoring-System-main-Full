"""
Attendance API Routes
======================
Endpoints for face recognition and attendance marking.
"""

from flask import Blueprint, request, jsonify, current_app, Response
import cv2
import numpy as np
import base64
import logging
import time

attendance_bp = Blueprint('attendance', __name__, url_prefix='/attendance')
logger = logging.getLogger(__name__)


@attendance_bp.route('/recognize', methods=['POST'])
def recognize_face():
    """
    Recognize face(s) in an image and mark attendance.
    
    Request:
        - Form data with 'image' file
        - OR JSON with 'image' as base64
    
    Returns:
        Recognition results with attendance marking
    """
    app = current_app._get_current_object()
    image = None
    
    # Handle file upload
    if 'image' in request.files:
        file = request.files['image']
        image_bytes = file.read()
        nparr = np.frombuffer(image_bytes, np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    # Handle base64 image
    elif request.is_json:
        data = request.get_json()
        if 'image' in data:
            base64_image = data['image']
            if ',' in base64_image:
                base64_image = base64_image.split(',')[1]
            
            image_bytes = base64.b64decode(base64_image)
            nparr = np.frombuffer(image_bytes, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    if image is None:
        return jsonify({'error': 'No valid image provided'}), 400
    
    # Process image
    start_time = time.time()
    
    result = app.attendance_service.mark_attendance_from_image(
        image,
        location=request.form.get('location') or (request.get_json() or {}).get('location')
    )
    
    result['processing_time_ms'] = (time.time() - start_time) * 1000
    
    return jsonify(result)


@attendance_bp.route('/verify', methods=['POST'])
def verify_student():
    """
    Verify if face matches a specific student.
    
    Request JSON:
        {
            "student_id": "STU001",
            "image": "base64..."
        }
    
    Returns:
        Verification result
    """
    data = request.get_json()
    
    if not data:
        return jsonify({'error': 'No data provided'}), 400
    
    student_id = data.get('student_id')
    image_data = data.get('image')
    
    if not student_id or not image_data:
        return jsonify({'error': 'student_id and image are required'}), 400
    
    # Decode image
    if ',' in image_data:
        image_data = image_data.split(',')[1]
    
    image_bytes = base64.b64decode(image_data)
    nparr = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    
    if image is None:
        return jsonify({'error': 'Invalid image'}), 400
    
    app = current_app._get_current_object()
    
    is_verified, confidence = app.attendance_engine.verify_student(
        image, student_id
    )
    
    return jsonify({
        'student_id': student_id,
        'verified': is_verified,
        'confidence': confidence
    })


@attendance_bp.route('/stream', methods=['GET'])
def video_stream():
    """
    MJPEG video stream with face recognition overlay.
    
    Returns:
        MJPEG stream
    """
    app = current_app._get_current_object()
    
    if not hasattr(app, 'camera_service') or not app.camera_service.is_opened():
        return jsonify({'error': 'Camera not available'}), 503
    
    def process_frame(frame):
        # Run recognition
        results = app.attendance_engine.process_frame(frame, mark_attendance=True)
        
        # Draw results
        return app.attendance_engine.draw_results(frame, results)
    
    return Response(
        app.camera_service.generate_frames(processor=process_frame),
        mimetype='multipart/x-mixed-replace; boundary=frame'
    )


@attendance_bp.route('/stream/start', methods=['POST'])
def start_stream():
    """
    Start camera stream.
    
    Returns:
        Stream status
    """
    app = current_app._get_current_object()
    
    if hasattr(app, 'camera_service'):
        success = app.camera_service.start()
        return jsonify({
            'success': success,
            'message': 'Camera started' if success else 'Failed to start camera'
        })
    
    return jsonify({'error': 'Camera service not configured'}), 503


@attendance_bp.route('/stream/stop', methods=['POST'])
def stop_stream():
    """
    Stop camera stream.
    
    Returns:
        Stream status
    """
    app = current_app._get_current_object()
    
    if hasattr(app, 'camera_service'):
        app.camera_service.stop()
        return jsonify({'success': True, 'message': 'Camera stopped'})
    
    return jsonify({'error': 'Camera service not configured'}), 503


@attendance_bp.route('/today', methods=['GET'])
def get_today_attendance():
    """
    Get today's attendance records.
    
    Returns:
        Today's attendance summary
    """
    app = current_app._get_current_object()
    
    result = app.attendance_service.get_today_attendance()
    
    return jsonify(result)


@attendance_bp.route('/date/<date_str>', methods=['GET'])
def get_attendance_by_date(date_str):
    """
    Get attendance for a specific date.
    
    Args:
        date_str: Date in YYYY-MM-DD format
    
    Returns:
        Attendance records for the date
    """
    app = current_app._get_current_object()
    
    result = app.attendance_service.get_attendance_by_date(date_str)
    
    return jsonify(result)


@attendance_bp.route('/student/<student_id>', methods=['GET'])
def get_student_attendance(student_id):
    """
    Get attendance history for a student.
    
    Args:
        student_id: Student identifier
    
    Query params:
        days: Number of days to look back (default 30)
    
    Returns:
        Student's attendance history
    """
    app = current_app._get_current_object()
    
    days = request.args.get('days', 30, type=int)
    
    result = app.attendance_service.get_student_attendance(student_id, days)
    
    return jsonify(result)


@attendance_bp.route('/stats', methods=['GET'])
def get_attendance_stats():
    """
    Get attendance statistics.
    
    Query params:
        start_date: Start date (YYYY-MM-DD)
        end_date: End date (YYYY-MM-DD)
    
    Returns:
        Attendance statistics
    """
    app = current_app._get_current_object()
    
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    
    result = app.attendance_service.get_attendance_stats(start_date, end_date)
    
    return jsonify(result)


@attendance_bp.route('/sync', methods=['POST'])
def sync_to_dashboard():
    """
    Sync attendance records to dashboard.
    
    Returns:
        Sync result
    """
    app = current_app._get_current_object()
    
    result = app.attendance_service.sync_to_dashboard()
    
    return jsonify(result)


@attendance_bp.route('/export', methods=['GET'])
def export_attendance():
    """
    Export attendance report.
    
    Query params:
        start_date: Start date (YYYY-MM-DD) - required
        end_date: End date (YYYY-MM-DD) - required
        format: 'json' or 'csv' (default: json)
    
    Returns:
        Attendance report
    """
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    format_type = request.args.get('format', 'json')
    
    if not start_date or not end_date:
        return jsonify({'error': 'start_date and end_date are required'}), 400
    
    app = current_app._get_current_object()
    
    result = app.attendance_service.export_attendance_report(
        start_date, end_date, format_type
    )
    
    if format_type == 'csv':
        return Response(
            result,
            mimetype='text/csv',
            headers={'Content-Disposition': f'attachment;filename=attendance_{start_date}_{end_date}.csv'}
        )
    
    return jsonify(result)


@attendance_bp.route('/camera/status', methods=['GET'])
def camera_status():
    """
    Get camera status.
    
    Returns:
        Camera info
    """
    app = current_app._get_current_object()
    
    if hasattr(app, 'camera_service'):
        return jsonify(app.camera_service.get_info())
    
    return jsonify({'error': 'Camera not configured'}), 503


@attendance_bp.route('/performance', methods=['GET'])
def get_performance():
    """
    Get recognition performance statistics.
    
    Returns:
        Performance stats
    """
    app = current_app._get_current_object()
    
    stats = app.attendance_engine.get_performance_stats()
    
    return jsonify(stats)


# ============================================
# Dashboard Integration Endpoints
# ============================================
# These endpoints are designed to work with the
# Smart-School-Safety-and-Performance-Monitoring-System Dashboard

@attendance_bp.route('/recognize_face', methods=['POST'])
def recognize_face_for_dashboard():
    """
    Recognize face from uploaded image - Dashboard compatible endpoint.
    
    This endpoint is called by the Laravel Dashboard's autoFaceRecognition function.
    
    Request:
        - multipart/form-data with 'image' file
    
    Returns:
        {
            "success": true/false,
            "student_id": 123,  // Dashboard student ID
            "confidence": 0.95,
            "student_name": "John Doe"
        }
    """
    app = current_app._get_current_object()
    
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
    
    # Recognize face
    try:
        results = app.attendance_engine.recognize_faces(image)
        
        if not results or len(results) == 0:
            return jsonify({
                'success': False,
                'message': 'No face detected',
                'student_id': None,
                'confidence': 0,
                'student_name': 'Unknown'
            })
        
        # Get the best match
        best_result = results[0]
        
        if best_result.get('student_id') is None or best_result.get('student_id') == 'unknown':
            return jsonify({
                'success': False,
                'message': 'Face not recognized',
                'student_id': None,
                'confidence': best_result.get('confidence', 0),
                'student_name': 'Unknown'
            })
        
        # Get dashboard student ID from face database
        student_info = app.face_database.get_student(best_result['student_id'])
        dashboard_id = student_info.get('dashboard_student_id') if student_info else None
        
        # If no dashboard_id stored, try to parse from student_id (if it's numeric)
        if dashboard_id is None:
            try:
                dashboard_id = int(best_result['student_id'])
            except (ValueError, TypeError):
                dashboard_id = None
        
        logger.info(f"Face recognized: {best_result['student_name']} (ID: {dashboard_id}, confidence: {best_result['confidence']:.2f})")
        
        return jsonify({
            'success': True,
            'student_id': dashboard_id,
            'confidence': best_result.get('confidence', 0),
            'student_name': best_result.get('student_name', 'Unknown'),
            'internal_student_id': best_result['student_id']
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


@attendance_bp.route('/register_student', methods=['POST'])
def register_student_for_dashboard():
    """
    Register a student's face - Dashboard compatible endpoint.
    
    This endpoint is called by the Laravel Dashboard's registerStudentFace function.
    
    Request:
        - multipart/form-data with:
            - student_id: integer (Dashboard student ID)
            - image: file
    
    Returns:
        {
            "success": true/false,
            "message": "Face registered successfully",
            "face_count": 1
        }
    """
    app = current_app._get_current_object()
    
    student_id = request.form.get('student_id')
    
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
        # Use student_id as both internal and dashboard ID for simplicity
        internal_student_id = f"DASH_{student_id}"
        
        # Check if registration session exists, if not create one
        session = app.registration_service.get_session_by_student(internal_student_id)
        
        if not session:
            # Start new capture session
            session_result = app.registration_service.start_capture_session(
                student_id=internal_student_id,
                student_name=f"Student {student_id}",
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
        
        # Check if we have enough faces to train
        current_count = capture_result.get('current_count', 0)
        
        # Auto-train if we have minimum faces
        if current_count >= 10:
            # Train the model for this student
            train_result = app.face_trainer.train_student(internal_student_id)
            
            if train_result.get('success'):
                # Complete the session
                app.registration_service.complete_session(session_id)
                
                # Reload face database
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
