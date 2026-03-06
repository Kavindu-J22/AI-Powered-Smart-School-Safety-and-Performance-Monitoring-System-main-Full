"""
Training API Routes
====================
Endpoints for model training operations.
"""

from flask import Blueprint, request, jsonify, current_app
import logging

training_bp = Blueprint('training', __name__, url_prefix='/training')
logger = logging.getLogger(__name__)


@training_bp.route('/train', methods=['POST'])
def train_all():
    """
    Train on all registered students.
    
    This will process all face images and generate embeddings.
    
    Request JSON (optional):
        {
            "async": false  // Run in background
        }
    
    Returns:
        Training result or status
    """
    app = current_app._get_current_object()
    
    data = request.get_json() or {}
    run_async = data.get('async', False)
    
    # Check if already training
    if app.face_trainer.is_training:
        return jsonify({
            'status': 'already_training',
            'progress': app.face_trainer.progress,
            'current_student': app.face_trainer.current_student
        }), 409
    
    if run_async:
        # Start training in background
        def on_complete(result):
            logger.info(f"Training completed: {result}")
            app.attendance_engine.refresh_database()
        
        app.face_trainer.train_async(completion_callback=on_complete)
        
        return jsonify({
            'status': 'started',
            'message': 'Training started in background'
        })
    else:
        # Run synchronously
        result = app.face_trainer.train_all()
        
        # Refresh attendance engine
        app.attendance_engine.refresh_database()
        
        return jsonify(result)


@training_bp.route('/train/<student_id>', methods=['POST'])
def train_student(student_id):
    """
    Train on a single student.
    
    Args:
        student_id: Student identifier
    
    Returns:
        Training result
    """
    app = current_app._get_current_object()
    
    # Get student name
    info = app.face_database.get_student_info(student_id)
    name = info.get('name') if info else None
    
    result = app.face_trainer.train_student(student_id, student_name=name)
    
    if result.get('success'):
        app.attendance_engine.refresh_database()
    
    return jsonify(result)


@training_bp.route('/retrain/<student_id>', methods=['POST'])
def retrain_student(student_id):
    """
    Retrain a single student (regenerate embedding).
    
    Args:
        student_id: Student identifier
    
    Returns:
        Training result
    """
    app = current_app._get_current_object()
    
    result = app.face_trainer.retrain_student(student_id)
    
    if result.get('success'):
        app.attendance_engine.refresh_database()
    
    return jsonify(result)


@training_bp.route('/status', methods=['GET'])
def get_training_status():
    """
    Get current training status.
    
    Returns:
        Training status
    """
    app = current_app._get_current_object()
    
    status = app.face_trainer.get_training_status()
    
    # Add database stats
    status['total_students'] = app.face_database.count()
    status['faces_dir'] = str(app.face_database.faces_dir)
    
    return jsonify(status)


@training_bp.route('/stop', methods=['POST'])
def stop_training():
    """
    Stop ongoing training.
    
    Returns:
        Stop result
    """
    app = current_app._get_current_object()
    
    if not app.face_trainer.is_training:
        return jsonify({'message': 'No training in progress'})
    
    app.face_trainer.stop_training()
    
    return jsonify({
        'message': 'Training stop requested',
        'status': 'stopping'
    })


@training_bp.route('/validate/<student_id>', methods=['GET'])
def validate_student_images(student_id):
    """
    Validate face images for a student without training.
    
    Args:
        student_id: Student identifier
    
    Returns:
        Validation result
    """
    app = current_app._get_current_object()
    
    result = app.face_trainer.validate_student_images(student_id)
    
    return jsonify(result)


@training_bp.route('/logs', methods=['GET'])
def get_training_logs():
    """
    Get training history.
    
    Returns:
        List of training logs
    """
    app = current_app._get_current_object()
    
    if not hasattr(app, 'attendance_db') or not app.attendance_db:
        return jsonify({'logs': []})
    
    # Get latest log
    latest = app.attendance_db.get_latest_training_log()
    
    if latest:
        return jsonify({
            'latest': latest.to_dict()
        })
    
    return jsonify({'logs': []})


@training_bp.route('/export', methods=['GET'])
def export_embeddings():
    """
    Export face embeddings for backup.
    
    Returns:
        Export file path
    """
    app = current_app._get_current_object()
    
    backup_path = app.face_database.backup()
    
    return jsonify({
        'success': True,
        'backup_path': backup_path
    })


@training_bp.route('/import', methods=['POST'])
def import_embeddings():
    """
    Import face embeddings from backup.
    
    Request JSON:
        {
            "path": "/path/to/backup.pkl",
            "merge": true  // Merge with existing or replace
        }
    
    Returns:
        Import result
    """
    data = request.get_json()
    
    if not data or 'path' not in data:
        return jsonify({'error': 'path is required'}), 400
    
    app = current_app._get_current_object()
    
    success = app.face_database.import_embeddings(
        data['path'],
        merge=data.get('merge', True)
    )
    
    if success:
        app.attendance_engine.refresh_database()
        return jsonify({'success': True, 'message': 'Embeddings imported'})
    else:
        return jsonify({'error': 'Import failed'}), 400
