"""
Image Utilities
================
Helper functions for image processing.
"""

import cv2
import numpy as np
import base64
from typing import Tuple, Optional, Union
from pathlib import Path


class ImageUtils:
    """Utility class for image operations."""
    
    @staticmethod
    def load_image(path: Union[str, Path]) -> Optional[np.ndarray]:
        """
        Load image from file path.
        
        Args:
            path: Image file path
            
        Returns:
            Image array (BGR) or None
        """
        path = str(path)
        image = cv2.imread(path)
        return image
    
    @staticmethod
    def save_image(
        image: np.ndarray,
        path: Union[str, Path],
        quality: int = 95
    ) -> bool:
        """
        Save image to file.
        
        Args:
            image: Image array
            path: Output path
            quality: JPEG quality (1-100)
            
        Returns:
            True if successful
        """
        path = str(path)
        params = [cv2.IMWRITE_JPEG_QUALITY, quality]
        return cv2.imwrite(path, image, params)
    
    @staticmethod
    def resize(
        image: np.ndarray,
        size: Tuple[int, int],
        keep_aspect: bool = True
    ) -> np.ndarray:
        """
        Resize image.
        
        Args:
            image: Input image
            size: Target size (width, height)
            keep_aspect: Maintain aspect ratio
            
        Returns:
            Resized image
        """
        if not keep_aspect:
            return cv2.resize(image, size)
        
        h, w = image.shape[:2]
        target_w, target_h = size
        
        # Calculate scale
        scale = min(target_w / w, target_h / h)
        
        new_w = int(w * scale)
        new_h = int(h * scale)
        
        resized = cv2.resize(image, (new_w, new_h))
        
        # Pad if needed
        if new_w != target_w or new_h != target_h:
            canvas = np.zeros((target_h, target_w, 3), dtype=np.uint8)
            x_offset = (target_w - new_w) // 2
            y_offset = (target_h - new_h) // 2
            canvas[y_offset:y_offset+new_h, x_offset:x_offset+new_w] = resized
            return canvas
        
        return resized
    
    @staticmethod
    def to_base64(image: np.ndarray, format: str = '.jpg') -> str:
        """
        Convert image to base64 string.
        
        Args:
            image: Input image
            format: Image format (.jpg, .png)
            
        Returns:
            Base64 encoded string
        """
        _, buffer = cv2.imencode(format, image)
        return base64.b64encode(buffer).decode('utf-8')
    
    @staticmethod
    def from_base64(base64_string: str) -> Optional[np.ndarray]:
        """
        Convert base64 string to image.
        
        Args:
            base64_string: Base64 encoded image
            
        Returns:
            Image array or None
        """
        try:
            # Remove data URL prefix if present
            if ',' in base64_string:
                base64_string = base64_string.split(',')[1]
            
            image_bytes = base64.b64decode(base64_string)
            nparr = np.frombuffer(image_bytes, np.uint8)
            return cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        except Exception:
            return None
    
    @staticmethod
    def normalize(image: np.ndarray) -> np.ndarray:
        """
        Normalize image to [0, 1] range.
        
        Args:
            image: Input image
            
        Returns:
            Normalized image
        """
        return image.astype(np.float32) / 255.0
    
    @staticmethod
    def standardize(
        image: np.ndarray,
        mean: Tuple[float, float, float] = (0.5, 0.5, 0.5),
        std: Tuple[float, float, float] = (0.5, 0.5, 0.5)
    ) -> np.ndarray:
        """
        Standardize image (subtract mean, divide by std).
        
        Args:
            image: Input image (0-255 or 0-1)
            mean: Mean values for each channel
            std: Std values for each channel
            
        Returns:
            Standardized image
        """
        img = image.astype(np.float32)
        
        if img.max() > 1:
            img = img / 255.0
        
        img = (img - np.array(mean)) / np.array(std)
        
        return img
    
    @staticmethod
    def crop_face(
        image: np.ndarray,
        bbox: Tuple[int, int, int, int],
        margin: float = 0.2
    ) -> np.ndarray:
        """
        Crop face region from image with margin.
        
        Args:
            image: Input image
            bbox: Bounding box (x1, y1, x2, y2)
            margin: Margin as fraction of face size
            
        Returns:
            Cropped face image
        """
        x1, y1, x2, y2 = bbox
        h, w = image.shape[:2]
        
        face_w = x2 - x1
        face_h = y2 - y1
        
        margin_x = int(face_w * margin)
        margin_y = int(face_h * margin)
        
        x1 = max(0, x1 - margin_x)
        y1 = max(0, y1 - margin_y)
        x2 = min(w, x2 + margin_x)
        y2 = min(h, y2 + margin_y)
        
        return image[y1:y2, x1:x2].copy()
    
    @staticmethod
    def draw_bbox(
        image: np.ndarray,
        bbox: Tuple[int, int, int, int],
        label: str = "",
        color: Tuple[int, int, int] = (0, 255, 0),
        thickness: int = 2
    ) -> np.ndarray:
        """
        Draw bounding box with label on image.
        
        Args:
            image: Input image
            bbox: Bounding box (x1, y1, x2, y2)
            label: Text label
            color: Box color (BGR)
            thickness: Line thickness
            
        Returns:
            Image with drawn box
        """
        output = image.copy()
        x1, y1, x2, y2 = bbox
        
        cv2.rectangle(output, (x1, y1), (x2, y2), color, thickness)
        
        if label:
            # Draw label background
            (text_w, text_h), baseline = cv2.getTextSize(
                label, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2
            )
            cv2.rectangle(
                output,
                (x1, y1 - text_h - 10),
                (x1 + text_w, y1),
                color,
                -1
            )
            cv2.putText(
                output,
                label,
                (x1, y1 - 5),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.6,
                (255, 255, 255),
                2
            )
        
        return output
    
    @staticmethod
    def enhance_contrast(image: np.ndarray) -> np.ndarray:
        """
        Enhance image contrast using CLAHE.
        
        Args:
            image: Input image
            
        Returns:
            Enhanced image
        """
        # Convert to LAB color space
        lab = cv2.cvtColor(image, cv2.COLOR_BGR2LAB)
        
        # Apply CLAHE to L channel
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        lab[:, :, 0] = clahe.apply(lab[:, :, 0])
        
        # Convert back to BGR
        return cv2.cvtColor(lab, cv2.COLOR_LAB2BGR)
    
    @staticmethod
    def denoise(image: np.ndarray) -> np.ndarray:
        """
        Apply denoising to image.
        
        Args:
            image: Input image
            
        Returns:
            Denoised image
        """
        return cv2.fastNlMeansDenoisingColored(image, None, 10, 10, 7, 21)
