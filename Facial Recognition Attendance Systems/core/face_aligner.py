"""
Face Alignment Module
======================
Aligns detected faces for better recognition accuracy.
Uses facial landmarks to perform affine transformation.
"""

import cv2
import numpy as np
from typing import Tuple, Optional, Dict
import logging

logger = logging.getLogger(__name__)


# Standard landmark positions for aligned face (112x112)
REFERENCE_LANDMARKS_112 = np.array([
    [38.2946, 51.6963],   # Left eye
    [73.5318, 51.5014],   # Right eye
    [56.0252, 71.7366],   # Nose
    [41.5493, 92.3655],   # Mouth left
    [70.7299, 92.2041]    # Mouth right
], dtype=np.float32)

# Standard landmark positions for aligned face (160x160)
REFERENCE_LANDMARKS_160 = np.array([
    [54.7063, 73.8511],   # Left eye
    [105.0454, 73.5734],  # Right eye
    [80.0360, 102.4810],  # Nose
    [59.3562, 131.9512],  # Mouth left
    [101.0471, 131.7344]  # Mouth right
], dtype=np.float32)


class FaceAligner:
    """
    Aligns faces using affine transformation based on facial landmarks.
    
    This significantly improves recognition accuracy by normalizing
    face orientation and scale.
    """
    
    def __init__(
        self,
        target_size: Tuple[int, int] = (160, 160),
        use_landmark_alignment: bool = True
    ):
        """
        Initialize face aligner.
        
        Args:
            target_size: Output face size (width, height)
            use_landmark_alignment: If True, use landmarks for alignment.
                                  If False, just crop and resize.
        """
        self.target_size = target_size
        self.use_landmark_alignment = use_landmark_alignment
        
        # Set reference landmarks based on target size
        if target_size[0] == 112:
            self.reference_landmarks = REFERENCE_LANDMARKS_112
        elif target_size[0] == 160:
            self.reference_landmarks = REFERENCE_LANDMARKS_160
        else:
            # Scale reference landmarks
            scale = target_size[0] / 112.0
            self.reference_landmarks = REFERENCE_LANDMARKS_112 * scale
        
        logger.info(f"FaceAligner initialized with target size {target_size}")
    
    def align(
        self,
        image: np.ndarray,
        landmarks: Dict[str, Tuple[int, int]],
        bbox: Optional[Tuple[int, int, int, int]] = None
    ) -> np.ndarray:
        """
        Align a face using facial landmarks.
        
        Args:
            image: Input image (BGR)
            landmarks: Dictionary with landmark positions
            bbox: Optional bounding box (x1, y1, x2, y2)
            
        Returns:
            Aligned face image of target_size
        """
        if not self.use_landmark_alignment or landmarks is None:
            return self._simple_crop_resize(image, bbox)
        
        try:
            # Extract source landmarks
            src_landmarks = self._extract_landmarks(landmarks)
            
            if src_landmarks is None:
                return self._simple_crop_resize(image, bbox)
            
            # Compute similarity transform
            transform_matrix = self._compute_similarity_transform(
                src_landmarks,
                self.reference_landmarks
            )
            
            # Apply transformation
            aligned = cv2.warpAffine(
                image,
                transform_matrix,
                self.target_size,
                borderMode=cv2.BORDER_REPLICATE
            )
            
            return aligned
            
        except Exception as e:
            logger.warning(f"Alignment failed: {e}, falling back to simple crop")
            return self._simple_crop_resize(image, bbox)
    
    def _extract_landmarks(
        self,
        landmarks: Dict[str, Tuple[int, int]]
    ) -> Optional[np.ndarray]:
        """Extract 5-point landmarks from dictionary."""
        required_keys = ['left_eye', 'right_eye', 'nose']
        
        if not all(key in landmarks for key in required_keys):
            return None
        
        # Handle different landmark formats
        if 'mouth_left' in landmarks and 'mouth_right' in landmarks:
            return np.array([
                landmarks['left_eye'],
                landmarks['right_eye'],
                landmarks['nose'],
                landmarks['mouth_left'],
                landmarks['mouth_right']
            ], dtype=np.float32)
        elif 'mouth_center' in landmarks:
            # Estimate mouth corners from center
            mouth_center = landmarks['mouth_center']
            offset = 15  # Approximate half-width of mouth
            return np.array([
                landmarks['left_eye'],
                landmarks['right_eye'],
                landmarks['nose'],
                (mouth_center[0] - offset, mouth_center[1]),
                (mouth_center[0] + offset, mouth_center[1])
            ], dtype=np.float32)
        
        return None
    
    def _compute_similarity_transform(
        self,
        src_points: np.ndarray,
        dst_points: np.ndarray
    ) -> np.ndarray:
        """
        Compute similarity transformation matrix.
        
        Uses partial affine transform that preserves aspect ratio.
        """
        # Use cv2.estimateAffinePartial2D for similarity transform
        transform, _ = cv2.estimateAffinePartial2D(
            src_points.reshape(-1, 1, 2),
            dst_points.reshape(-1, 1, 2)
        )
        
        if transform is None:
            # Fallback to full affine if partial fails
            transform = cv2.getAffineTransform(
                src_points[:3].astype(np.float32),
                dst_points[:3].astype(np.float32)
            )
        
        return transform
    
    def _simple_crop_resize(
        self,
        image: np.ndarray,
        bbox: Optional[Tuple[int, int, int, int]]
    ) -> np.ndarray:
        """Simple crop and resize fallback."""
        if bbox is not None:
            x1, y1, x2, y2 = bbox
            # Add some margin
            h, w = image.shape[:2]
            margin_x = int((x2 - x1) * 0.1)
            margin_y = int((y2 - y1) * 0.1)
            x1 = max(0, x1 - margin_x)
            y1 = max(0, y1 - margin_y)
            x2 = min(w, x2 + margin_x)
            y2 = min(h, y2 + margin_y)
            face = image[y1:y2, x1:x2]
        else:
            face = image
        
        if face.size == 0:
            return np.zeros((*self.target_size, 3), dtype=np.uint8)
        
        return cv2.resize(face, self.target_size)
    
    def align_eyes(
        self,
        image: np.ndarray,
        left_eye: Tuple[int, int],
        right_eye: Tuple[int, int],
        desired_left_eye: Tuple[float, float] = (0.35, 0.35),
        face_width: int = 160
    ) -> np.ndarray:
        """
        Align face based only on eye positions.
        
        Useful when only eye landmarks are available.
        
        Args:
            image: Input image
            left_eye: Left eye position (x, y)
            right_eye: Right eye position (x, y)
            desired_left_eye: Desired position of left eye in output (fraction)
            face_width: Output image width (height calculated automatically)
            
        Returns:
            Aligned face image
        """
        # Compute the angle between eyes
        dY = right_eye[1] - left_eye[1]
        dX = right_eye[0] - left_eye[0]
        angle = np.degrees(np.arctan2(dY, dX))
        
        # Compute the desired right eye position
        desired_right_eye_x = 1.0 - desired_left_eye[0]
        
        # Compute the scale
        dist = np.sqrt((dX ** 2) + (dY ** 2))
        desired_dist = (desired_right_eye_x - desired_left_eye[0]) * face_width
        scale = desired_dist / dist
        
        # Compute eye center
        eyes_center = (
            (left_eye[0] + right_eye[0]) // 2,
            (left_eye[1] + right_eye[1]) // 2
        )
        
        # Get rotation matrix
        M = cv2.getRotationMatrix2D(eyes_center, angle, scale)
        
        # Update translation component
        face_height = int(face_width * 1.2)  # Slightly taller for full face
        tX = face_width * 0.5
        tY = face_height * desired_left_eye[1]
        M[0, 2] += (tX - eyes_center[0])
        M[1, 2] += (tY - eyes_center[1])
        
        # Apply transformation
        output = cv2.warpAffine(
            image,
            M,
            (face_width, face_height),
            flags=cv2.INTER_CUBIC,
            borderMode=cv2.BORDER_REPLICATE
        )
        
        return output
    
    def preprocess_for_recognition(
        self,
        aligned_face: np.ndarray,
        normalize: bool = True
    ) -> np.ndarray:
        """
        Preprocess aligned face for recognition model.
        
        Args:
            aligned_face: Aligned face image (BGR)
            normalize: Whether to normalize pixel values
            
        Returns:
            Preprocessed face ready for embedding extraction
        """
        # Convert BGR to RGB
        rgb_face = cv2.cvtColor(aligned_face, cv2.COLOR_BGR2RGB)
        
        if normalize:
            # Normalize to [-1, 1] (common for face recognition models)
            rgb_face = (rgb_face.astype(np.float32) - 127.5) / 128.0
        
        return rgb_face
