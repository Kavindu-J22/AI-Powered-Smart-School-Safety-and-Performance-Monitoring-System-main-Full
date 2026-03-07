"""
Face Detection Module
======================
High-performance face detection using MTCNN, RetinaFace, or MediaPipe.
Optimized for speed and accuracy in attendance scenarios.
"""

import cv2
import numpy as np
from typing import List, Tuple, Optional, Dict, Any
from dataclasses import dataclass
from abc import ABC, abstractmethod
import logging

logger = logging.getLogger(__name__)


@dataclass
class FaceDetection:
    """Represents a detected face with bounding box and landmarks."""
    bbox: Tuple[int, int, int, int]  # (x1, y1, x2, y2)
    confidence: float
    landmarks: Optional[Dict[str, Tuple[int, int]]] = None
    face_image: Optional[np.ndarray] = None
    
    @property
    def x1(self) -> int:
        return self.bbox[0]
    
    @property
    def y1(self) -> int:
        return self.bbox[1]
    
    @property
    def x2(self) -> int:
        return self.bbox[2]
    
    @property
    def y2(self) -> int:
        return self.bbox[3]
    
    @property
    def width(self) -> int:
        return self.x2 - self.x1
    
    @property
    def height(self) -> int:
        return self.y2 - self.y1
    
    @property
    def area(self) -> int:
        return self.width * self.height
    
    @property
    def center(self) -> Tuple[int, int]:
        return ((self.x1 + self.x2) // 2, (self.y1 + self.y2) // 2)


class BaseFaceDetector(ABC):
    """Abstract base class for face detectors."""
    
    @abstractmethod
    def detect(self, image: np.ndarray) -> List[FaceDetection]:
        """Detect faces in an image."""
        pass
    
    @abstractmethod
    def detect_largest(self, image: np.ndarray) -> Optional[FaceDetection]:
        """Detect the largest face in an image."""
        pass


class MTCNNDetector(BaseFaceDetector):
    """MTCNN-based face detector - good balance of speed and accuracy."""
    
    def __init__(
        self,
        min_face_size: int = 40,  # Lowered: 80px missed faces at normal webcam distance
        confidence_threshold: float = 0.85,  # Lowered: 0.95 was silently dropping real faces
        scale_factor: float = 0.709,
        device: str = 'cpu'
    ):
        self.min_face_size = min_face_size
        self.confidence_threshold = confidence_threshold
        self.scale_factor = scale_factor
        self.device = device
        self._detector = None
        self._initialize()
    
    def _initialize(self):
        """Initialize MTCNN detector."""
        try:
            from facenet_pytorch import MTCNN
            import torch
            
            device = torch.device(
                'cuda' if self.device == 'cuda' and torch.cuda.is_available() else 'cpu'
            )
            
            self._detector = MTCNN(
                image_size=160,
                margin=20,
                min_face_size=self.min_face_size,
                thresholds=[0.6, 0.7, 0.85],  # Relaxed: [0.7,0.8,0.9] was stage-1 too strict
                factor=0.709,
                post_process=False,
                device=device,
                keep_all=True
            )
            logger.info(f"MTCNN detector initialized on {device}")
        except ImportError:
            logger.error("facenet-pytorch not installed. Run: pip install facenet-pytorch")
            raise
    
    def detect(self, image: np.ndarray) -> List[FaceDetection]:
        """
        Detect all faces in an image.
        
        Args:
            image: BGR or RGB image as numpy array
            
        Returns:
            List of FaceDetection objects
        """
        if image is None or image.size == 0:
            return []
        
        # Convert BGR to RGB if needed
        if len(image.shape) == 3 and image.shape[2] == 3:
            rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        else:
            rgb_image = image
        
        try:
            boxes, probs, landmarks = self._detector.detect(rgb_image, landmarks=True)
            
            if boxes is None:
                return []
            
            detections = []
            for i, (box, prob) in enumerate(zip(boxes, probs)):
                if prob < self.confidence_threshold:
                    continue
                
                x1, y1, x2, y2 = map(int, box)
                
                # Ensure bounds are valid
                x1 = max(0, x1)
                y1 = max(0, y1)
                x2 = min(image.shape[1], x2)
                y2 = min(image.shape[0], y2)
                
                # Extract landmarks
                face_landmarks = None
                if landmarks is not None and landmarks[i] is not None:
                    lm = landmarks[i]
                    face_landmarks = {
                        'left_eye': (int(lm[0][0]), int(lm[0][1])),
                        'right_eye': (int(lm[1][0]), int(lm[1][1])),
                        'nose': (int(lm[2][0]), int(lm[2][1])),
                        'mouth_left': (int(lm[3][0]), int(lm[3][1])),
                        'mouth_right': (int(lm[4][0]), int(lm[4][1]))
                    }
                
                # Extract face region
                face_image = image[y1:y2, x1:x2].copy()
                
                detections.append(FaceDetection(
                    bbox=(x1, y1, x2, y2),
                    confidence=float(prob),
                    landmarks=face_landmarks,
                    face_image=face_image
                ))
            
            # Sort by area (largest first)
            detections.sort(key=lambda x: x.area, reverse=True)
            return detections
            
        except Exception as e:
            logger.error(f"Face detection error: {e}")
            return []
    
    def detect_largest(self, image: np.ndarray) -> Optional[FaceDetection]:
        """Detect the largest face in the image."""
        detections = self.detect(image)
        return detections[0] if detections else None


class RetinaFaceDetector(BaseFaceDetector):
    """RetinaFace detector - higher accuracy, slightly slower."""
    
    def __init__(
        self,
        confidence_threshold: float = 0.95,
        gpu_id: int = 0
    ):
        self.confidence_threshold = confidence_threshold
        self.gpu_id = gpu_id
        self._detector = None
        self._initialize()
    
    def _initialize(self):
        """Initialize RetinaFace detector."""
        try:
            from retinaface import RetinaFace
            self._detector = RetinaFace
            logger.info("RetinaFace detector initialized")
        except ImportError:
            logger.error("retinaface not installed. Run: pip install retinaface")
            raise
    
    def detect(self, image: np.ndarray) -> List[FaceDetection]:
        """Detect all faces using RetinaFace."""
        if image is None or image.size == 0:
            return []
        
        try:
            faces = self._detector.detect_faces(image)
            
            if not faces:
                return []
            
            detections = []
            for face_id, face_data in faces.items():
                confidence = face_data.get('score', 0)
                if confidence < self.confidence_threshold:
                    continue
                
                x1, y1, w, h = face_data['facial_area']
                x2, y2 = x1 + w, y1 + h
                
                # Extract landmarks
                landmarks_data = face_data.get('landmarks', {})
                face_landmarks = {
                    'left_eye': tuple(map(int, landmarks_data.get('left_eye', (0, 0)))),
                    'right_eye': tuple(map(int, landmarks_data.get('right_eye', (0, 0)))),
                    'nose': tuple(map(int, landmarks_data.get('nose', (0, 0)))),
                    'mouth_left': tuple(map(int, landmarks_data.get('mouth_left', (0, 0)))),
                    'mouth_right': tuple(map(int, landmarks_data.get('mouth_right', (0, 0))))
                }
                
                # Extract face region
                face_image = image[y1:y2, x1:x2].copy()
                
                detections.append(FaceDetection(
                    bbox=(x1, y1, x2, y2),
                    confidence=float(confidence),
                    landmarks=face_landmarks,
                    face_image=face_image
                ))
            
            detections.sort(key=lambda x: x.area, reverse=True)
            return detections
            
        except Exception as e:
            logger.error(f"RetinaFace detection error: {e}")
            return []
    
    def detect_largest(self, image: np.ndarray) -> Optional[FaceDetection]:
        """Detect the largest face."""
        detections = self.detect(image)
        return detections[0] if detections else None


class MediaPipeDetector(BaseFaceDetector):
    """MediaPipe face detector - fastest option."""
    
    def __init__(
        self,
        confidence_threshold: float = 0.95,
        model_selection: int = 1  # 0 for short-range, 1 for full-range
    ):
        self.confidence_threshold = confidence_threshold
        self.model_selection = model_selection
        self._detector = None
        self._initialize()
    
    def _initialize(self):
        """Initialize MediaPipe detector."""
        try:
            import mediapipe as mp
            self._mp_face = mp.solutions.face_detection
            self._detector = self._mp_face.FaceDetection(
                model_selection=self.model_selection,
                min_detection_confidence=self.confidence_threshold
            )
            logger.info("MediaPipe detector initialized")
        except ImportError:
            logger.error("mediapipe not installed. Run: pip install mediapipe")
            raise
    
    def detect(self, image: np.ndarray) -> List[FaceDetection]:
        """Detect faces using MediaPipe."""
        if image is None or image.size == 0:
            return []
        
        try:
            rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
            results = self._detector.process(rgb_image)
            
            if not results.detections:
                return []
            
            height, width = image.shape[:2]
            detections = []
            
            for detection in results.detections:
                confidence = detection.score[0]
                if confidence < self.confidence_threshold:
                    continue
                
                bbox = detection.location_data.relative_bounding_box
                x1 = int(bbox.xmin * width)
                y1 = int(bbox.ymin * height)
                x2 = int((bbox.xmin + bbox.width) * width)
                y2 = int((bbox.ymin + bbox.height) * height)
                
                # Ensure valid bounds
                x1, y1 = max(0, x1), max(0, y1)
                x2, y2 = min(width, x2), min(height, y2)
                
                # Extract key points as landmarks
                keypoints = detection.location_data.relative_keypoints
                face_landmarks = None
                if len(keypoints) >= 6:
                    face_landmarks = {
                        'right_eye': (int(keypoints[0].x * width), int(keypoints[0].y * height)),
                        'left_eye': (int(keypoints[1].x * width), int(keypoints[1].y * height)),
                        'nose': (int(keypoints[2].x * width), int(keypoints[2].y * height)),
                        'mouth_center': (int(keypoints[3].x * width), int(keypoints[3].y * height)),
                    }
                
                face_image = image[y1:y2, x1:x2].copy()
                
                detections.append(FaceDetection(
                    bbox=(x1, y1, x2, y2),
                    confidence=float(confidence),
                    landmarks=face_landmarks,
                    face_image=face_image
                ))
            
            detections.sort(key=lambda x: x.area, reverse=True)
            return detections
            
        except Exception as e:
            logger.error(f"MediaPipe detection error: {e}")
            return []
    
    def detect_largest(self, image: np.ndarray) -> Optional[FaceDetection]:
        """Detect the largest face."""
        detections = self.detect(image)
        return detections[0] if detections else None


class FaceDetector:
    """
    Unified face detector interface with automatic backend selection.
    
    Usage:
        detector = FaceDetector(backend='mtcnn')
        faces = detector.detect(image)
    """
    
    BACKENDS = {
        'mtcnn': MTCNNDetector,
        'retinaface': RetinaFaceDetector,
        'mediapipe': MediaPipeDetector
    }
    
    def __init__(
        self,
        backend: str = 'mtcnn',
        confidence_threshold: float = 0.95,
        min_face_size: int = 80,
        device: str = 'cpu',
        **kwargs
    ):
        """
        Initialize face detector.
        
        Args:
            backend: Detection backend ('mtcnn', 'retinaface', 'mediapipe')
            confidence_threshold: Minimum confidence for detection
            min_face_size: Minimum face size in pixels
            device: 'cpu' or 'cuda'
            **kwargs: Additional backend-specific arguments
        """
        self.backend_name = backend.lower()
        self.confidence_threshold = confidence_threshold
        self.min_face_size = min_face_size
        self.device = device
        
        if self.backend_name not in self.BACKENDS:
            raise ValueError(f"Unknown backend: {backend}. Choose from {list(self.BACKENDS.keys())}")
        
        self._initialize_backend(**kwargs)
    
    def _initialize_backend(self, **kwargs):
        """Initialize the selected backend."""
        backend_class = self.BACKENDS[self.backend_name]
        
        if self.backend_name == 'mtcnn':
            self._detector = backend_class(
                min_face_size=self.min_face_size,
                confidence_threshold=self.confidence_threshold,
                device=self.device,
                **kwargs
            )
        elif self.backend_name == 'retinaface':
            self._detector = backend_class(
                confidence_threshold=self.confidence_threshold,
                **kwargs
            )
        elif self.backend_name == 'mediapipe':
            self._detector = backend_class(
                confidence_threshold=self.confidence_threshold,
                **kwargs
            )
        
        logger.info(f"FaceDetector initialized with {self.backend_name} backend")
    
    def detect(self, image: np.ndarray) -> List[FaceDetection]:
        """
        Detect all faces in an image.
        
        Args:
            image: Input image (BGR format)
            
        Returns:
            List of FaceDetection objects sorted by size (largest first)
        """
        return self._detector.detect(image)
    
    def detect_largest(self, image: np.ndarray) -> Optional[FaceDetection]:
        """
        Detect only the largest face.
        
        Args:
            image: Input image (BGR format)
            
        Returns:
            FaceDetection for the largest face, or None if no face found
        """
        return self._detector.detect_largest(image)
    
    def detect_with_crop(
        self,
        image: np.ndarray,
        margin: float = 0.2,
        target_size: Tuple[int, int] = (160, 160)
    ) -> List[Tuple[FaceDetection, np.ndarray]]:
        """
        Detect faces and return cropped, resized face images.
        
        Args:
            image: Input image
            margin: Margin around face as fraction of face size
            target_size: Size to resize cropped faces
            
        Returns:
            List of (FaceDetection, cropped_face) tuples
        """
        detections = self.detect(image)
        results = []
        
        for detection in detections:
            # Add margin to bounding box
            margin_x = int(detection.width * margin)
            margin_y = int(detection.height * margin)
            
            x1 = max(0, detection.x1 - margin_x)
            y1 = max(0, detection.y1 - margin_y)
            x2 = min(image.shape[1], detection.x2 + margin_x)
            y2 = min(image.shape[0], detection.y2 + margin_y)
            
            # Crop and resize
            face_crop = image[y1:y2, x1:x2]
            if face_crop.size > 0:
                face_resized = cv2.resize(face_crop, target_size)
                results.append((detection, face_resized))
        
        return results
    
    def draw_detections(
        self,
        image: np.ndarray,
        detections: List[FaceDetection],
        color: Tuple[int, int, int] = (0, 255, 0),
        thickness: int = 2,
        show_landmarks: bool = True,
        show_confidence: bool = True
    ) -> np.ndarray:
        """
        Draw detection results on image.
        
        Args:
            image: Input image
            detections: List of FaceDetection objects
            color: Box color (BGR)
            thickness: Line thickness
            show_landmarks: Whether to draw landmarks
            show_confidence: Whether to show confidence score
            
        Returns:
            Image with drawn detections
        """
        output = image.copy()
        
        for detection in detections:
            # Draw bounding box
            cv2.rectangle(
                output,
                (detection.x1, detection.y1),
                (detection.x2, detection.y2),
                color,
                thickness
            )
            
            # Draw confidence
            if show_confidence:
                label = f"{detection.confidence:.2f}"
                cv2.putText(
                    output,
                    label,
                    (detection.x1, detection.y1 - 10),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.6,
                    color,
                    thickness
                )
            
            # Draw landmarks
            if show_landmarks and detection.landmarks:
                for name, point in detection.landmarks.items():
                    cv2.circle(output, point, 3, (0, 0, 255), -1)
        
        return output
