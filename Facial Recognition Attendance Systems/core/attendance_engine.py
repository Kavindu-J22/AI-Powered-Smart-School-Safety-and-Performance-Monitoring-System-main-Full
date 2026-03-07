"""
Attendance Engine
==================
Main engine that orchestrates face detection, recognition, and attendance marking.
Optimized for real-time performance.
"""

import cv2
import numpy as np
from typing import Dict, List, Optional, Tuple, Any
from dataclasses import dataclass
from datetime import datetime, timedelta
import logging
import time
from threading import Lock
from collections import defaultdict  # kept for backward compatibility with any external imports

from .face_detector import FaceDetector, FaceDetection
from .face_recognizer import FaceRecognizer
from .face_aligner import FaceAligner
from .anti_spoof import AntiSpoofDetector, QuickLivenessChecker

logger = logging.getLogger(__name__)


@dataclass
class RecognitionResult:
    """Result of face recognition."""
    student_id: Optional[str]
    student_name: Optional[str]
    confidence: float
    is_recognized: bool
    face_bbox: Tuple[int, int, int, int]
    is_live: bool = True
    liveness_score: float = 1.0
    processing_time_ms: float = 0.0


@dataclass
class AttendanceRecord:
    """Attendance record for a student."""
    student_id: str
    student_name: str
    timestamp: datetime
    confidence: float
    status: str  # 'present', 'late', 'early_leave'
    image_path: Optional[str] = None


class AttendanceEngine:
    """
    Main attendance processing engine.
    
    Handles the complete pipeline:
    1. Face detection
    2. Face alignment
    3. Anti-spoofing check
    4. Face recognition
    5. Attendance marking
    """
    
    def __init__(
        self,
        face_database: 'FaceDatabase' = None,
        detection_backend: str = 'mtcnn',
        recognition_backend: str = 'facenet',
        device: str = 'cpu',
        recognition_threshold: float = 0.65,  # Higher threshold for better accuracy
        enable_anti_spoof: bool = False,  # Disable for faster processing
        attendance_cooldown: int = 300  # seconds
    ):
        """
        Initialize the attendance engine.
        
        Args:
            face_database: Database of registered face embeddings
            detection_backend: Face detection backend
            recognition_backend: Face recognition backend
            device: 'cpu' or 'cuda'
            recognition_threshold: Minimum similarity for recognition
            enable_anti_spoof: Whether to enable liveness detection
            attendance_cooldown: Minimum seconds between attendance marks
        """
        self.device = device
        self.recognition_threshold = recognition_threshold
        self.enable_anti_spoof = enable_anti_spoof
        self.attendance_cooldown = attendance_cooldown
        
        # Initialize components
        self.detector = FaceDetector(
            backend=detection_backend,
            confidence_threshold=0.90,  # Stricter threshold for better precision
            device=device
        )
        
        self.recognizer = FaceRecognizer(
            backend=recognition_backend,
            device=device,
            similarity_threshold=recognition_threshold
        )
        
        self.aligner = FaceAligner(target_size=(160, 160))
        
        if enable_anti_spoof:
            # Threshold lowered 0.6 → 0.42: single-image DFT/LBP liveness checks
            # are unreliable for webcam stills and were falsely rejecting real students.
            self.anti_spoof = QuickLivenessChecker(threshold=0.42)
        else:
            self.anti_spoof = None
        
        # Face database
        self.face_database = face_database
        self._embedding_matrix = None
        self._student_ids = []
        
        # Attendance tracking
        self.attendance_records: Dict[str, AttendanceRecord] = {}
        self.last_attendance_time: Dict[str, datetime] = {}
        self._lock = Lock()
        
        # Performance metrics
        self._processing_times = []
        
        # Multi-embedding storage for better matching
        self._multi_embeddings: Dict[str, List[np.ndarray]] = {}
        
        logger.info("AttendanceEngine initialized")
    
    def load_face_database(self, face_database: 'FaceDatabase'):
        """
        Load face database and prepare for fast matching.
        
        Args:
            face_database: FaceDatabase instance with embeddings
        """
        self.face_database = face_database
        self._prepare_embedding_matrix()
    
    def _prepare_embedding_matrix(self):
        """Prepare embedding matrix for fast vectorized matching."""
        if self.face_database is None:
            self._embedding_matrix = np.array([])
            self._student_ids = []
            self._multi_embeddings = {}
            return
        
        embeddings = self.face_database.get_all_embeddings()
        
        if embeddings:
            self._student_ids = list(embeddings.keys())
            self._embedding_matrix = np.array([
                embeddings[sid] for sid in self._student_ids
            ])
            
            # Load multi-embeddings for better matching
            self._multi_embeddings = self.face_database.get_all_multi_embeddings()
            
            logger.info(f"Loaded {len(self._student_ids)} faces for matching")
            logger.info(f"Multi-embeddings available for {len(self._multi_embeddings)} students")
        else:
            self._embedding_matrix = np.array([])
            self._student_ids = []
            self._multi_embeddings = {}
    
    def process_frame(
        self,
        frame: np.ndarray,
        mark_attendance: bool = True
    ) -> List[RecognitionResult]:
        """
        Process a single frame for face recognition.
        
        Args:
            frame: Input frame (BGR)
            mark_attendance: Whether to mark attendance for recognized faces
            
        Returns:
            List of RecognitionResult for each detected face
        """
        start_time = time.time()
        results = []
        
        # Step 1: Detect faces
        detections = self.detector.detect(frame)
        
        if not detections:
            return results
        
        # Step 2: Process each detected face
        for detection in detections:
            result = self._process_single_face(frame, detection)
            
            # Step 3: Mark attendance if recognized
            if mark_attendance and result.is_recognized and result.is_live:
                self._mark_attendance(result)
            
            results.append(result)
        
        # Track processing time
        total_time = (time.time() - start_time) * 1000
        self._processing_times.append(total_time)
        
        if len(self._processing_times) > 100:
            self._processing_times = self._processing_times[-100:]
        
        return results
    
    def _process_single_face(
        self,
        frame: np.ndarray,
        detection: FaceDetection
    ) -> RecognitionResult:
        """Process a single detected face."""
        start_time = time.time()
        
        # Check liveness
        is_live = True
        liveness_score = 1.0
        
        if self.enable_anti_spoof and self.anti_spoof:
            is_live, liveness_score = self.anti_spoof.check(detection.face_image)
        
        if not is_live:
            return RecognitionResult(
                student_id=None,
                student_name=None,
                confidence=0.0,
                is_recognized=False,
                face_bbox=detection.bbox,
                is_live=False,
                liveness_score=liveness_score,
                processing_time_ms=(time.time() - start_time) * 1000
            )
        
        # Apply CLAHE lighting normalisation before alignment so that the
        # recognition embedding is produced from the same preprocessing used
        # during registration (where CLAHE is now applied before saving).
        frame_preprocessed = frame.copy()
        try:
            lab = cv2.cvtColor(frame_preprocessed, cv2.COLOR_BGR2LAB)
            l_ch, a_ch, b_ch = cv2.split(lab)
            clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(4, 4))
            l_ch = clahe.apply(l_ch)
            frame_preprocessed = cv2.cvtColor(cv2.merge([l_ch, a_ch, b_ch]), cv2.COLOR_LAB2BGR)
        except Exception:
            frame_preprocessed = frame  # fallback: continue without CLAHE
        
        # Align face
        aligned_face = self.aligner.align(
            frame_preprocessed,
            detection.landmarks,
            detection.bbox
        )
        
        # Get embedding
        embedding = self.recognizer.get_embedding(aligned_face)
        
        if embedding is None:
            return RecognitionResult(
                student_id=None,
                student_name=None,
                confidence=0.0,
                is_recognized=False,
                face_bbox=detection.bbox,
                is_live=is_live,
                liveness_score=liveness_score,
                processing_time_ms=(time.time() - start_time) * 1000
            )
        
        # Find match
        student_id, student_name, confidence = self._find_match(embedding)
        
        is_recognized = confidence >= self.recognition_threshold
        
        return RecognitionResult(
            student_id=student_id if is_recognized else None,
            student_name=student_name if is_recognized else None,
            confidence=confidence,
            is_recognized=is_recognized,
            face_bbox=detection.bbox,
            is_live=is_live,
            liveness_score=liveness_score,
            processing_time_ms=(time.time() - start_time) * 1000
        )
    
    def _find_match(
        self,
        embedding: np.ndarray
    ) -> Tuple[Optional[str], Optional[str], float]:
        """
        Find best matching student for a query embedding.

        Strategy:
        - When multi-embeddings are stored for a student, use the top-3 mean
          cosine similarity across all their stored embeddings.  This covers
          the full range of captured poses/lighting far better than a single
          average vector.
        - For any student without multi-embeddings, fall back to the single
          mean embedding stored in the matrix.
        - The two paths are EXCLUSIVE per student (no double-counting) to
          keep scores comparable and avoid inflated confidence.
        """
        if self._embedding_matrix is None or len(self._embedding_matrix) == 0:
            return None, None, 0.0
        
        # Normalise query embedding
        norm = np.linalg.norm(embedding)
        if norm > 0:
            embedding = embedding / norm
        
        student_best_scores: dict = {}

        # --- Path A: multi-embedding matching (preferred) ---
        if hasattr(self, '_multi_embeddings') and self._multi_embeddings:
            for student_id, multi_embs in self._multi_embeddings.items():
                if not multi_embs:
                    continue
                sims = sorted(
                    [float(np.dot(embedding, e)) for e in multi_embs],
                    reverse=True
                )
                top_n = min(len(sims), 3)
                student_best_scores[student_id] = float(np.mean(sims[:top_n]))

        # --- Path B: single mean-embedding fallback for students not in Path A ---
        for idx, student_id in enumerate(self._student_ids):
            if student_id in student_best_scores:
                continue  # already scored via multi-embeddings — skip
            student_best_scores[student_id] = float(
                np.dot(self._embedding_matrix[idx], embedding)
            )

        if not student_best_scores:
            return None, None, 0.0

        # Sort descending by score
        ranked = sorted(student_best_scores.items(), key=lambda x: x[1], reverse=True)
        best_student_id, best_confidence = ranked[0]

        # Small ambiguity penalty when top-2 are very close
        if len(ranked) > 1:
            _, second_confidence = ranked[1]
            if (best_confidence - second_confidence) < 0.04:
                best_confidence *= 0.95
                logger.debug(
                    f"Ambiguity detected: {ranked[0][0]} ({best_confidence:.3f}) "
                    f"vs {ranked[1][0]} ({second_confidence:.3f})"
                )

        if best_confidence >= self.recognition_threshold:
            student_name = self.face_database.get_student_name(best_student_id)
            return best_student_id, student_name, best_confidence

        return None, None, best_confidence
    
    def recognize_single(
        self,
        image: np.ndarray,
        return_annotated: bool = False
    ) -> Tuple[List[RecognitionResult], Optional[np.ndarray]]:
        """
        Recognize faces in a single image.
        
        Args:
            image: Input image (BGR)
            return_annotated: Whether to return annotated image
            
        Returns:
            (results, annotated_image or None)
        """
        results = self.process_frame(image, mark_attendance=True)
        
        annotated = None
        if return_annotated:
            annotated = self.draw_results(image, results)
        
        return results, annotated
    
    def verify_student(
        self,
        image: np.ndarray,
        student_id: str
    ) -> Tuple[bool, float]:
        """
        Verify if face matches a specific student.
        
        Args:
            image: Face image
            student_id: Student ID to verify against
            
        Returns:
            (is_verified, confidence)
        """
        if self.face_database is None:
            return False, 0.0
        
        reference_embedding = self.face_database.get_embedding(student_id)
        if reference_embedding is None:
            return False, 0.0
        
        # Detect and align face
        detection = self.detector.detect_largest(image)
        if detection is None:
            return False, 0.0
        
        aligned = self.aligner.align(image, detection.landmarks, detection.bbox)
        
        # Get embedding and compare
        return self.recognizer.verify(aligned, reference_embedding)
    
    def draw_results(
        self,
        image: np.ndarray,
        results: List[RecognitionResult],
        show_confidence: bool = True,
        show_fps: bool = True
    ) -> np.ndarray:
        """
        Draw recognition results on image.
        
        Args:
            image: Input image
            results: Recognition results
            show_confidence: Show confidence scores
            show_fps: Show FPS counter
            
        Returns:
            Annotated image
        """
        output = image.copy()
        
        for result in results:
            x1, y1, x2, y2 = result.face_bbox
            
            # Choose color based on recognition
            if not result.is_live:
                color = (0, 0, 255)  # Red for spoof
                label = "SPOOF"
            elif result.is_recognized:
                color = (0, 255, 0)  # Green for recognized
                label = result.student_name or result.student_id
            else:
                color = (255, 165, 0)  # Orange for unknown
                label = "Unknown"
            
            # Draw bounding box
            cv2.rectangle(output, (x1, y1), (x2, y2), color, 2)
            
            # Draw label background
            label_text = label
            if show_confidence and result.confidence > 0:
                label_text = f"{label} ({result.confidence:.2f})"
            
            (text_width, text_height), baseline = cv2.getTextSize(
                label_text,
                cv2.FONT_HERSHEY_SIMPLEX,
                0.6,
                2
            )
            
            cv2.rectangle(
                output,
                (x1, y1 - text_height - 10),
                (x1 + text_width, y1),
                color,
                -1
            )
            
            cv2.putText(
                output,
                label_text,
                (x1, y1 - 5),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.6,
                (255, 255, 255),
                2
            )
        
        # Show FPS
        if show_fps and self._processing_times:
            avg_time = np.mean(self._processing_times[-30:])
            fps = 1000.0 / avg_time if avg_time > 0 else 0
            cv2.putText(
                output,
                f"FPS: {fps:.1f}",
                (10, 30),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.8,
                (0, 255, 0),
                2
            )
        
        return output
    
    def get_todays_attendance(self) -> List[Dict[str, Any]]:
        """Get all attendance records for today."""
        today = datetime.now().date()
        records = []
        
        for student_id, record in self.attendance_records.items():
            if record.timestamp.date() == today:
                records.append({
                    'student_id': record.student_id,
                    'student_name': record.student_name,
                    'timestamp': record.timestamp.isoformat(),
                    'confidence': record.confidence,
                    'status': record.status
                })
        
        return sorted(records, key=lambda x: x['timestamp'])
    
    def get_performance_stats(self) -> Dict[str, float]:
        """Get performance statistics."""
        if not self._processing_times:
            return {'avg_ms': 0, 'min_ms': 0, 'max_ms': 0, 'fps': 0}
        
        times = self._processing_times[-100:]
        avg_time = np.mean(times)
        
        return {
            'avg_ms': avg_time,
            'min_ms': np.min(times),
            'max_ms': np.max(times),
            'fps': 1000.0 / avg_time if avg_time > 0 else 0
        }
    
    def refresh_database(self):
        """Refresh the face database (call after adding new faces)."""
        self._prepare_embedding_matrix()
