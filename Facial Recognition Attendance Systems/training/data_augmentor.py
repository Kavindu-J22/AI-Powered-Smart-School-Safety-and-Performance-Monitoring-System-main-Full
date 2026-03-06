"""
Data Augmentation Module
=========================
Augments face images for more robust training.
"""

import cv2
import numpy as np
from typing import List, Tuple
import logging

logger = logging.getLogger(__name__)


class DataAugmentor:
    """
    Augments face images to improve recognition robustness.
    
    Applies various transformations to create diverse training samples.
    """
    
    def __init__(
        self,
        flip_horizontal: bool = True,
        rotate_range: Tuple[float, float] = (-15, 15),
        brightness_range: Tuple[float, float] = (0.7, 1.3),
        contrast_range: Tuple[float, float] = (0.8, 1.2),
        blur_probability: float = 0.2,
        noise_probability: float = 0.2,
        scale_range: Tuple[float, float] = (0.9, 1.1)
    ):
        """
        Initialize augmentor with augmentation parameters.
        
        Args:
            flip_horizontal: Apply horizontal flip
            rotate_range: Range for rotation in degrees
            brightness_range: Range for brightness adjustment
            contrast_range: Range for contrast adjustment
            blur_probability: Probability of applying blur
            noise_probability: Probability of adding noise
            scale_range: Range for scale transformation
        """
        self.flip_horizontal = flip_horizontal
        self.rotate_range = rotate_range
        self.brightness_range = brightness_range
        self.contrast_range = contrast_range
        self.blur_probability = blur_probability
        self.noise_probability = noise_probability
        self.scale_range = scale_range
    
    def augment(
        self,
        image: np.ndarray,
        count: int = 5
    ) -> List[np.ndarray]:
        """
        Generate augmented versions of an image.
        
        Args:
            image: Input image (BGR)
            count: Number of augmented images to generate
            
        Returns:
            List of augmented images
        """
        augmented = [image]  # Include original
        
        for _ in range(count - 1):
            aug = image.copy()
            
            # Apply random transformations
            if np.random.random() < 0.5 and self.flip_horizontal:
                aug = self._flip_horizontal(aug)
            
            if np.random.random() < 0.5:
                aug = self._rotate(aug)
            
            if np.random.random() < 0.5:
                aug = self._adjust_brightness(aug)
            
            if np.random.random() < 0.5:
                aug = self._adjust_contrast(aug)
            
            if np.random.random() < self.blur_probability:
                aug = self._apply_blur(aug)
            
            if np.random.random() < self.noise_probability:
                aug = self._add_noise(aug)
            
            augmented.append(aug)
        
        return augmented
    
    def _flip_horizontal(self, image: np.ndarray) -> np.ndarray:
        """Apply horizontal flip."""
        return cv2.flip(image, 1)
    
    def _rotate(self, image: np.ndarray) -> np.ndarray:
        """Apply random rotation."""
        angle = np.random.uniform(*self.rotate_range)
        h, w = image.shape[:2]
        center = (w // 2, h // 2)
        
        matrix = cv2.getRotationMatrix2D(center, angle, 1.0)
        rotated = cv2.warpAffine(
            image,
            matrix,
            (w, h),
            borderMode=cv2.BORDER_REPLICATE
        )
        
        return rotated
    
    def _adjust_brightness(self, image: np.ndarray) -> np.ndarray:
        """Adjust image brightness."""
        factor = np.random.uniform(*self.brightness_range)
        
        hsv = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)
        hsv = hsv.astype(np.float32)
        hsv[:, :, 2] = np.clip(hsv[:, :, 2] * factor, 0, 255)
        hsv = hsv.astype(np.uint8)
        
        return cv2.cvtColor(hsv, cv2.COLOR_HSV2BGR)
    
    def _adjust_contrast(self, image: np.ndarray) -> np.ndarray:
        """Adjust image contrast."""
        factor = np.random.uniform(*self.contrast_range)
        
        mean = np.mean(image, axis=(0, 1), keepdims=True)
        adjusted = np.clip((image - mean) * factor + mean, 0, 255)
        
        return adjusted.astype(np.uint8)
    
    def _apply_blur(self, image: np.ndarray) -> np.ndarray:
        """Apply Gaussian blur."""
        kernel_size = np.random.choice([3, 5])
        return cv2.GaussianBlur(image, (kernel_size, kernel_size), 0)
    
    def _add_noise(self, image: np.ndarray) -> np.ndarray:
        """Add Gaussian noise."""
        noise = np.random.normal(0, 10, image.shape).astype(np.float32)
        noisy = np.clip(image.astype(np.float32) + noise, 0, 255)
        return noisy.astype(np.uint8)
    
    def _scale(self, image: np.ndarray) -> np.ndarray:
        """Apply random scale."""
        scale = np.random.uniform(*self.scale_range)
        h, w = image.shape[:2]
        
        new_h, new_w = int(h * scale), int(w * scale)
        scaled = cv2.resize(image, (new_w, new_h))
        
        # Crop or pad to original size
        if scale > 1:
            # Crop center
            start_h = (new_h - h) // 2
            start_w = (new_w - w) // 2
            scaled = scaled[start_h:start_h+h, start_w:start_w+w]
        else:
            # Pad
            pad_h = (h - new_h) // 2
            pad_w = (w - new_w) // 2
            padded = np.zeros((h, w, 3), dtype=np.uint8)
            padded[pad_h:pad_h+new_h, pad_w:pad_w+new_w] = scaled
            scaled = padded
        
        return scaled
    
    def create_training_set(
        self,
        images: List[np.ndarray],
        augmentation_multiplier: int = 5
    ) -> List[np.ndarray]:
        """
        Create augmented training set from original images.
        
        Args:
            images: List of original images
            augmentation_multiplier: How many augmented versions per image
            
        Returns:
            Augmented training set
        """
        training_set = []
        
        for image in images:
            augmented = self.augment(image, count=augmentation_multiplier)
            training_set.extend(augmented)
        
        logger.info(
            f"Created training set: {len(images)} images -> "
            f"{len(training_set)} augmented images"
        )
        
        return training_set
