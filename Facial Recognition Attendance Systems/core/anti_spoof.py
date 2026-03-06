"""
Anti-Spoofing Module
=====================
Detects presentation attacks (photo, video, mask) to prevent spoofing.
Uses multiple techniques for robust liveness detection.
"""

import cv2
import numpy as np
from typing import Tuple, Optional, List
from dataclasses import dataclass
import logging
from collections import deque

logger = logging.getLogger(__name__)


@dataclass
class LivenessResult:
    """Result of liveness detection."""
    is_live: bool
    confidence: float
    reason: str = ""
    details: dict = None


class AntiSpoofDetector:
    """
    Multi-method anti-spoofing detector.
    
    Combines multiple techniques:
    - Texture analysis (LBP-based)
    - Blink detection
    - Face movement analysis
    - Depth estimation (if depth camera available)
    """
    
    def __init__(
        self,
        liveness_threshold: float = 0.7,
        enable_blink_detection: bool = True,
        enable_texture_analysis: bool = True,
        enable_movement_analysis: bool = True,
        history_size: int = 30  # Frames to keep for temporal analysis
    ):
        self.liveness_threshold = liveness_threshold
        self.enable_blink_detection = enable_blink_detection
        self.enable_texture_analysis = enable_texture_analysis
        self.enable_movement_analysis = enable_movement_analysis
        
        # Temporal analysis history
        self.face_positions = deque(maxlen=history_size)
        self.blink_history = deque(maxlen=history_size)
        self.ear_history = deque(maxlen=history_size)
        
        # Initialize face mesh for blink detection
        self._init_face_mesh()
        
        logger.info("AntiSpoofDetector initialized")
    
    def _init_face_mesh(self):
        """Initialize MediaPipe face mesh for eye tracking."""
        try:
            import mediapipe as mp
            self._mp_face_mesh = mp.solutions.face_mesh
            self._face_mesh = self._mp_face_mesh.FaceMesh(
                max_num_faces=1,
                refine_landmarks=True,
                min_detection_confidence=0.5,
                min_tracking_confidence=0.5
            )
            self._mesh_available = True
        except ImportError:
            logger.warning("MediaPipe not available, blink detection disabled")
            self._mesh_available = False
    
    def check_liveness(
        self,
        frame: np.ndarray,
        face_bbox: Tuple[int, int, int, int] = None
    ) -> LivenessResult:
        """
        Check if the face in the frame is live (real person).
        
        Args:
            frame: Input frame (BGR)
            face_bbox: Optional face bounding box (x1, y1, x2, y2)
            
        Returns:
            LivenessResult with is_live flag and confidence
        """
        scores = []
        details = {}
        
        # Texture analysis
        if self.enable_texture_analysis and face_bbox:
            texture_score = self._analyze_texture(frame, face_bbox)
            scores.append(texture_score)
            details['texture_score'] = texture_score
        
        # Blink detection
        if self.enable_blink_detection and self._mesh_available:
            blink_detected, ear = self._detect_blink(frame)
            self.ear_history.append(ear)
            
            # Check for natural blink patterns
            if len(self.ear_history) >= 10:
                blink_score = self._analyze_blink_pattern()
                scores.append(blink_score)
                details['blink_score'] = blink_score
                details['blink_detected'] = blink_detected
        
        # Movement analysis
        if self.enable_movement_analysis and face_bbox:
            center = ((face_bbox[0] + face_bbox[2]) // 2, 
                     (face_bbox[1] + face_bbox[3]) // 2)
            self.face_positions.append(center)
            
            if len(self.face_positions) >= 10:
                movement_score = self._analyze_movement()
                scores.append(movement_score)
                details['movement_score'] = movement_score
        
        # Aggregate scores
        if scores:
            confidence = np.mean(scores)
            is_live = confidence >= self.liveness_threshold
        else:
            confidence = 0.5
            is_live = False
        
        reason = ""
        if not is_live:
            if details.get('texture_score', 1.0) < 0.5:
                reason = "Suspicious texture detected"
            elif details.get('movement_score', 1.0) < 0.3:
                reason = "Insufficient face movement"
            elif details.get('blink_score', 1.0) < 0.3:
                reason = "No natural blink pattern"
            else:
                reason = "Multiple liveness checks failed"
        
        return LivenessResult(
            is_live=is_live,
            confidence=confidence,
            reason=reason,
            details=details
        )
    
    def _analyze_texture(
        self,
        frame: np.ndarray,
        face_bbox: Tuple[int, int, int, int]
    ) -> float:
        """
        Analyze face texture using Local Binary Patterns.
        Real faces have more natural texture variation.
        """
        x1, y1, x2, y2 = face_bbox
        face_region = frame[y1:y2, x1:x2]
        
        if face_region.size == 0:
            return 0.5
        
        # Convert to grayscale
        gray = cv2.cvtColor(face_region, cv2.COLOR_BGR2GRAY)
        
        # Compute LBP
        lbp = self._compute_lbp(gray)
        
        # Compute histogram
        hist, _ = np.histogram(lbp.ravel(), bins=256, range=(0, 256))
        hist = hist.astype(float) / hist.sum()
        
        # Compute entropy - real faces have higher entropy
        entropy = -np.sum(hist * np.log2(hist + 1e-10))
        
        # Normalize entropy to [0, 1]
        max_entropy = np.log2(256)
        normalized_entropy = entropy / max_entropy
        
        # Real faces typically have entropy > 0.85
        # Photos/screens often have lower entropy
        score = min(1.0, normalized_entropy / 0.85)
        
        return score
    
    def _compute_lbp(self, gray: np.ndarray, radius: int = 1) -> np.ndarray:
        """Compute Local Binary Pattern."""
        rows, cols = gray.shape
        lbp = np.zeros_like(gray)
        
        for i in range(radius, rows - radius):
            for j in range(radius, cols - radius):
                center = gray[i, j]
                code = 0
                
                # 8 neighbors
                code |= (gray[i-1, j-1] >= center) << 7
                code |= (gray[i-1, j] >= center) << 6
                code |= (gray[i-1, j+1] >= center) << 5
                code |= (gray[i, j+1] >= center) << 4
                code |= (gray[i+1, j+1] >= center) << 3
                code |= (gray[i+1, j] >= center) << 2
                code |= (gray[i+1, j-1] >= center) << 1
                code |= (gray[i, j-1] >= center) << 0
                
                lbp[i, j] = code
        
        return lbp
    
    def _detect_blink(self, frame: np.ndarray) -> Tuple[bool, float]:
        """
        Detect eye blink using Eye Aspect Ratio (EAR).
        
        Returns:
            (blink_detected, ear_value)
        """
        if not self._mesh_available:
            return False, 0.0
        
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        results = self._face_mesh.process(rgb_frame)
        
        if not results.multi_face_landmarks:
            return False, 0.0
        
        landmarks = results.multi_face_landmarks[0]
        h, w = frame.shape[:2]
        
        # Get eye landmarks (MediaPipe face mesh indices)
        # Left eye: 33, 160, 158, 133, 153, 144
        # Right eye: 362, 385, 387, 263, 373, 380
        
        left_eye_indices = [33, 160, 158, 133, 153, 144]
        right_eye_indices = [362, 385, 387, 263, 373, 380]
        
        def get_ear(eye_indices):
            points = []
            for idx in eye_indices:
                lm = landmarks.landmark[idx]
                points.append((int(lm.x * w), int(lm.y * h)))
            
            # Compute EAR
            # Vertical distances
            v1 = np.linalg.norm(np.array(points[1]) - np.array(points[5]))
            v2 = np.linalg.norm(np.array(points[2]) - np.array(points[4]))
            # Horizontal distance
            h = np.linalg.norm(np.array(points[0]) - np.array(points[3]))
            
            if h == 0:
                return 0.0
            
            return (v1 + v2) / (2.0 * h)
        
        left_ear = get_ear(left_eye_indices)
        right_ear = get_ear(right_eye_indices)
        
        ear = (left_ear + right_ear) / 2.0
        
        # Typical blink threshold
        blink_threshold = 0.2
        blink_detected = ear < blink_threshold
        
        return blink_detected, ear
    
    def _analyze_blink_pattern(self) -> float:
        """
        Analyze blink pattern over time.
        Natural blinks have specific patterns.
        """
        if len(self.ear_history) < 10:
            return 0.5
        
        ear_array = np.array(self.ear_history)
        
        # Check for variation (live faces have EAR variation)
        std = np.std(ear_array)
        mean = np.mean(ear_array)
        
        if mean == 0:
            return 0.0
        
        cv = std / mean  # Coefficient of variation
        
        # Natural blinks cause notable EAR drops
        # If CV is too low, might be a static image
        if cv < 0.05:
            return 0.3  # Suspicious - too stable
        elif cv > 0.15:
            return 0.9  # Good - natural variation
        else:
            return 0.6  # Medium confidence
    
    def _analyze_movement(self) -> float:
        """
        Analyze face movement patterns.
        Real faces have natural micro-movements.
        """
        if len(self.face_positions) < 10:
            return 0.5
        
        positions = np.array(self.face_positions)
        
        # Calculate movement vectors
        movements = np.diff(positions, axis=0)
        distances = np.linalg.norm(movements, axis=1)
        
        # Check for natural movement patterns
        mean_movement = np.mean(distances)
        std_movement = np.std(distances)
        
        # Real faces have some movement but not too much
        if mean_movement < 0.5:
            # Too still - might be a photo
            return 0.3
        elif mean_movement > 50:
            # Too much movement - might be shaking photo
            return 0.4
        elif std_movement < 0.5:
            # Too consistent movement - might be mechanical
            return 0.4
        else:
            return 0.8
    
    def reset_history(self):
        """Reset temporal analysis history."""
        self.face_positions.clear()
        self.blink_history.clear()
        self.ear_history.clear()


class QuickLivenessChecker:
    """
    Fast liveness check for real-time applications.
    Uses simpler checks for speed while maintaining reasonable accuracy.
    """
    
    def __init__(self, threshold: float = 0.6):
        self.threshold = threshold
    
    def check(self, face_image: np.ndarray) -> Tuple[bool, float]:
        """
        Quick liveness check on face image.
        
        Args:
            face_image: Cropped face image
            
        Returns:
            (is_live, confidence)
        """
        if face_image is None or face_image.size == 0:
            return False, 0.0
        
        scores = []
        
        # Color distribution check
        color_score = self._check_color_distribution(face_image)
        scores.append(color_score)
        
        # Frequency analysis
        freq_score = self._check_frequency(face_image)
        scores.append(freq_score)
        
        # Reflection check
        reflection_score = self._check_reflections(face_image)
        scores.append(reflection_score)
        
        confidence = np.mean(scores)
        is_live = confidence >= self.threshold
        
        return is_live, confidence
    
    def _check_color_distribution(self, face: np.ndarray) -> float:
        """Check for natural skin color distribution."""
        # Convert to HSV
        hsv = cv2.cvtColor(face, cv2.COLOR_BGR2HSV)
        
        # Define skin color range
        lower = np.array([0, 20, 70])
        upper = np.array([20, 255, 255])
        
        mask = cv2.inRange(hsv, lower, upper)
        skin_ratio = np.sum(mask > 0) / mask.size
        
        # Real faces typically have 30-70% skin pixels
        if 0.3 <= skin_ratio <= 0.7:
            return 0.8
        elif 0.2 <= skin_ratio <= 0.8:
            return 0.6
        else:
            return 0.3
    
    def _check_frequency(self, face: np.ndarray) -> float:
        """Check frequency content - screens have different patterns."""
        gray = cv2.cvtColor(face, cv2.COLOR_BGR2GRAY)
        
        # Compute DFT
        dft = cv2.dft(np.float32(gray), flags=cv2.DFT_COMPLEX_OUTPUT)
        dft_shift = np.fft.fftshift(dft)
        
        magnitude = cv2.magnitude(dft_shift[:, :, 0], dft_shift[:, :, 1])
        magnitude = np.log(magnitude + 1)
        
        # Analyze high frequency content
        h, w = magnitude.shape
        center_h, center_w = h // 2, w // 2
        
        # High frequency region
        high_freq = magnitude.copy()
        high_freq[center_h-30:center_h+30, center_w-30:center_w+30] = 0
        
        high_freq_ratio = np.sum(high_freq) / np.sum(magnitude)
        
        # Real faces have moderate high frequency content
        if 0.3 <= high_freq_ratio <= 0.6:
            return 0.8
        else:
            return 0.5
    
    def _check_reflections(self, face: np.ndarray) -> float:
        """Check for screen reflections (bright spots)."""
        gray = cv2.cvtColor(face, cv2.COLOR_BGR2GRAY)
        
        # Find very bright spots
        _, bright = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY)
        bright_ratio = np.sum(bright > 0) / bright.size
        
        # Screens often have more bright reflections
        if bright_ratio > 0.1:
            return 0.4  # Suspicious
        else:
            return 0.8
