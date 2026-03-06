"""
Registration Service
=====================
Handles student face registration workflow.
"""

import cv2
import numpy as np
from typing import Dict, List, Optional, Tuple
from datetime import datetime
import logging
import base64
import time
from threading import Thread, Event
from dataclasses import dataclass, field

logger = logging.getLogger(__name__)


@dataclass
class CaptureSession:
    """Active face capture session."""
    session_id: str
    student_id: str
    student_name: str
    target_count: int
    captured_count: int = 0
    captured_images: List[np.ndarray] = field(default_factory=list)
    started_at: datetime = field(default_factory=datetime.now)
    status: str = 'active'  # active, completed, cancelled
    

class RegistrationService:
    """
    Service for handling face registration workflow.
    
    Workflow:
    1. Start capture session
    2. Capture face images (from camera or uploaded)
    3. Validate captured images
    4. Complete registration
    5. Trigger training
    """
    
    def __init__(
        self,
        face_database: 'FaceDatabase',
        face_detector: 'FaceDetector',
        attendance_db: 'AttendanceDB' = None,
        min_capture_count: int = 10,
        max_capture_count: int = 50,
        capture_interval_ms: int = 200
    ):
        """
        Initialize registration service.
        
        Args:
            face_database: Face database instance
            face_detector: Face detector instance
            attendance_db: Attendance database instance
            min_capture_count: Minimum images to capture
            max_capture_count: Maximum images to capture
            capture_interval_ms: Interval between captures
        """
        self.face_database = face_database
        self.detector = face_detector
        self.attendance_db = attendance_db
        
        self.min_capture_count = min_capture_count
        self.max_capture_count = max_capture_count
        self.capture_interval_ms = capture_interval_ms
        
        # Active sessions
        self._sessions: Dict[str, CaptureSession] = {}
        self._stop_events: Dict[str, Event] = {}
    
    def start_capture_session(
        self,
        student_id: str,
        student_name: str,
        target_count: int = 25,
        dashboard_student_id: int = None
    ) -> Dict:
        """
        Start a new face capture session.
        
        Args:
            student_id: Unique student identifier
            student_name: Student's name
            target_count: Number of images to capture
            dashboard_student_id: ID from school dashboard
            
        Returns:
            Session info dict
        """
        # Validate target count
        target_count = max(self.min_capture_count, 
                          min(self.max_capture_count, target_count))
        
        # Generate session ID
        session_id = f"{student_id}_{int(time.time())}"
        
        # Create session
        session = CaptureSession(
            session_id=session_id,
            student_id=student_id,
            student_name=student_name,
            target_count=target_count
        )
        
        self._sessions[session_id] = session
        self._stop_events[session_id] = Event()
        
        # Clear existing images if any
        self.face_database.clear_face_images(student_id)
        
        # Add student to database if not exists
        if self.attendance_db:
            existing = self.attendance_db.get_student(student_id)
            if not existing:
                self.attendance_db.add_student(
                    student_id=student_id,
                    name=student_name,
                    dashboard_student_id=dashboard_student_id
                )
        
        logger.info(f"Started capture session: {session_id} for {student_name}")
        
        return {
            'session_id': session_id,
            'student_id': student_id,
            'student_name': student_name,
            'target_count': target_count,
            'status': 'active'
        }
    
    def capture_frame(
        self,
        session_id: str,
        frame: np.ndarray
    ) -> Dict:
        """
        Process a single frame for capture.
        
        Args:
            session_id: Active session ID
            frame: Camera frame (BGR)
            
        Returns:
            Capture result dict
        """
        session = self._sessions.get(session_id)
        
        if not session:
            return {'error': 'Session not found', 'success': False}
        
        if session.status != 'active':
            return {'error': f'Session is {session.status}', 'success': False}
        
        if session.captured_count >= session.target_count:
            return {
                'success': False,
                'message': 'Target count reached',
                'captured_count': session.captured_count
            }
        
        # Detect face
        detection = self.detector.detect_largest(frame)
        
        if detection is None:
            return {
                'success': False,
                'message': 'No face detected',
                'captured_count': session.captured_count
            }
        
        if detection.confidence < 0.95:
            return {
                'success': False,
                'message': 'Low confidence detection',
                'captured_count': session.captured_count
            }
        
        # Check face size (must be reasonably large)
        if detection.width < 100 or detection.height < 100:
            return {
                'success': False,
                'message': 'Face too small, move closer',
                'captured_count': session.captured_count
            }
        
        # Crop face with margin
        x1, y1, x2, y2 = detection.bbox
        margin = int(max(detection.width, detection.height) * 0.2)
        
        h, w = frame.shape[:2]
        x1 = max(0, x1 - margin)
        y1 = max(0, y1 - margin)
        x2 = min(w, x2 + margin)
        y2 = min(h, y2 + margin)
        
        face_crop = frame[y1:y2, x1:x2].copy()
        
        # Resize to standard size
        face_resized = cv2.resize(face_crop, (160, 160))
        
        # Save image
        image_path = self.face_database.save_face_image(
            session.student_id,
            face_resized,
            index=session.captured_count
        )
        
        session.captured_images.append(face_resized)
        session.captured_count += 1
        
        # Check if complete
        progress = (session.captured_count / session.target_count) * 100
        is_complete = session.captured_count >= session.target_count
        
        if is_complete:
            session.status = 'ready'
        
        return {
            'success': True,
            'captured_count': session.captured_count,
            'target_count': session.target_count,
            'progress': progress,
            'is_complete': is_complete,
            'face_bbox': detection.bbox,
            'image_path': image_path
        }
    
    def upload_images(
        self,
        session_id: str,
        images: List[np.ndarray]
    ) -> Dict:
        """
        Upload multiple face images.
        
        Args:
            session_id: Session ID
            images: List of face images
            
        Returns:
            Upload result
        """
        session = self._sessions.get(session_id)
        
        if not session:
            return {'error': 'Session not found', 'success': False}
        
        valid_count = 0
        
        for img in images:
            if session.captured_count >= session.target_count:
                break
            
            # Detect face in uploaded image
            detection = self.detector.detect_largest(img)
            
            if detection is None or detection.confidence < 0.9:
                continue
            
            # Crop and resize
            x1, y1, x2, y2 = detection.bbox
            face_crop = img[y1:y2, x1:x2]
            face_resized = cv2.resize(face_crop, (160, 160))
            
            # Save
            self.face_database.save_face_image(
                session.student_id,
                face_resized,
                index=session.captured_count
            )
            
            session.captured_images.append(face_resized)
            session.captured_count += 1
            valid_count += 1
        
        return {
            'success': True,
            'uploaded': len(images),
            'valid': valid_count,
            'captured_count': session.captured_count,
            'target_count': session.target_count
        }
    
    def upload_base64_image(
        self,
        session_id: str,
        base64_image: str
    ) -> Dict:
        """
        Upload a single base64 encoded image.
        
        Args:
            session_id: Session ID
            base64_image: Base64 encoded image
            
        Returns:
            Upload result
        """
        try:
            # Decode base64
            if ',' in base64_image:
                base64_image = base64_image.split(',')[1]
            
            image_bytes = base64.b64decode(base64_image)
            nparr = np.frombuffer(image_bytes, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            if image is None:
                return {'error': 'Failed to decode image', 'success': False}
            
            return self.capture_frame(session_id, image)
            
        except Exception as e:
            logger.error(f"Error processing base64 image: {e}")
            return {'error': str(e), 'success': False}
    
    def get_session_status(self, session_id: str) -> Dict:
        """Get capture session status."""
        session = self._sessions.get(session_id)
        
        if not session:
            return {'error': 'Session not found'}
        
        return {
            'session_id': session.session_id,
            'student_id': session.student_id,
            'student_name': session.student_name,
            'captured_count': session.captured_count,
            'target_count': session.target_count,
            'progress': (session.captured_count / session.target_count) * 100,
            'status': session.status,
            'started_at': session.started_at.isoformat()
        }
    
    def complete_registration(
        self,
        session_id: str,
        auto_train: bool = True
    ) -> Dict:
        """
        Complete registration and optionally trigger training.
        
        Args:
            session_id: Session ID
            auto_train: Whether to train immediately
            
        Returns:
            Completion result
        """
        session = self._sessions.get(session_id)
        
        if not session:
            return {'error': 'Session not found', 'success': False}
        
        if session.captured_count < self.min_capture_count:
            return {
                'error': f'Need at least {self.min_capture_count} images',
                'success': False,
                'captured_count': session.captured_count
            }
        
        session.status = 'completed'
        
        # Update database
        if self.attendance_db:
            self.attendance_db.mark_face_registered(
                session.student_id,
                session.captured_count
            )
        
        # Initialize student in face database (without embedding yet)
        self.face_database._student_info[session.student_id] = {
            'name': session.student_name,
            'registered_at': datetime.now().isoformat(),
            'images_count': session.captured_count
        }
        
        logger.info(
            f"Registration completed for {session.student_id}: "
            f"{session.captured_count} images"
        )
        
        result = {
            'success': True,
            'student_id': session.student_id,
            'student_name': session.student_name,
            'images_count': session.captured_count,
            'needs_training': True
        }
        
        # Cleanup session
        del self._sessions[session_id]
        if session_id in self._stop_events:
            del self._stop_events[session_id]
        
        return result
    
    def cancel_session(self, session_id: str) -> Dict:
        """Cancel capture session and cleanup."""
        session = self._sessions.get(session_id)
        
        if not session:
            return {'error': 'Session not found'}
        
        session.status = 'cancelled'
        
        # Clear captured images
        self.face_database.clear_face_images(session.student_id)
        
        # Cleanup
        del self._sessions[session_id]
        if session_id in self._stop_events:
            self._stop_events[session_id].set()
            del self._stop_events[session_id]
        
        logger.info(f"Session cancelled: {session_id}")
        
        return {'success': True, 'message': 'Session cancelled'}
    
    def get_active_sessions(self) -> List[Dict]:
        """Get all active sessions."""
        return [
            self.get_session_status(sid)
            for sid in self._sessions.keys()
        ]
    
    def update_student_from_dashboard(
        self,
        dashboard_student_id: int,
        student_id: str,
        name: str,
        **kwargs
    ) -> Dict:
        """
        Update student info from dashboard webhook.
        
        Args:
            dashboard_student_id: ID from dashboard
            student_id: School student ID
            name: Student name
            **kwargs: Additional student info
            
        Returns:
            Update result
        """
        if self.attendance_db:
            # Check if exists
            existing = self.attendance_db.get_student_by_dashboard_id(
                dashboard_student_id
            )
            
            if existing:
                # Update
                self.attendance_db.update_student(
                    existing.student_id,
                    name=name,
                    **kwargs
                )
                return {'success': True, 'action': 'updated'}
            else:
                # Create new
                self.attendance_db.add_student(
                    student_id=student_id,
                    name=name,
                    dashboard_student_id=dashboard_student_id,
                    **kwargs
                )
                return {'success': True, 'action': 'created'}
        
        return {'success': False, 'error': 'Database not available'}
