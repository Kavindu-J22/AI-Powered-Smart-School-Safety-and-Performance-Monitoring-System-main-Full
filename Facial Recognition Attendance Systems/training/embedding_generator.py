"""
Embedding Generator Module
===========================
Generates face embeddings from images for storage and matching.
"""

import cv2
import numpy as np
from pathlib import Path
from typing import List, Optional, Tuple, Dict
import logging
from concurrent.futures import ThreadPoolExecutor

logger = logging.getLogger(__name__)


class EmbeddingGenerator:
    """
    Generates face embeddings for a collection of face images.
    
    Used during training to create embeddings from captured face images.
    """
    
    def __init__(
        self,
        face_recognizer: 'FaceRecognizer',
        face_aligner: 'FaceAligner' = None,
        target_size: Tuple[int, int] = (160, 160)
    ):
        """
        Initialize embedding generator.
        
        Args:
            face_recognizer: FaceRecognizer instance for embedding extraction
            face_aligner: Optional FaceAligner for preprocessing
            target_size: Size to resize faces before embedding
        """
        self.recognizer = face_recognizer
        self.aligner = face_aligner
        self.target_size = target_size
        # Lowered 80 → 35: webcam face crops at 160×160 typically have Laplacian
        # variance of 30–80.  Threshold of 80 was silently discarding most images,
        # leaving too few samples for reliable embedding generation.
        self.blur_threshold = 35.0
    
    @staticmethod
    def is_image_blurry(image: np.ndarray, threshold: float = 80.0) -> Tuple[bool, float]:
        """
        Check if an image is blurry using the Variance of Laplacian method.
        Higher variance means more sharp details.
        """
        if len(image.shape) == 3:
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        else:
            gray = image
            
        variance = cv2.Laplacian(gray, cv2.CV_64F).var()
        return variance < threshold, variance
    
    def generate_from_image(
        self,
        image: np.ndarray,
        preprocess: bool = True
    ) -> Optional[np.ndarray]:
        """
        Generate embedding from a single face image.
        
        Args:
            image: Face image (BGR)
            preprocess: Whether to preprocess (resize, normalize)
            
        Returns:
            Face embedding or None if failed
        """
        try:
            # Check for blur
            is_blurry, variance = self.is_image_blurry(image, self.blur_threshold)
            if is_blurry:
                logger.warning(f"Skipping blurry image (variance: {variance:.2f})")
                return None

            if preprocess:
                # Resize if needed
                if image.shape[:2] != self.target_size:
                    image = cv2.resize(image, self.target_size)
            
            embedding = self.recognizer.get_embedding(image)
            return embedding
            
        except Exception as e:
            logger.error(f"Error generating embedding: {e}")
            return None
    
    def generate_from_images(
        self,
        images: List[np.ndarray],
        use_batch: bool = True
    ) -> List[Optional[np.ndarray]]:
        """
        Generate embeddings from multiple images.
        
        Args:
            images: List of face images
            use_batch: Use batch processing for efficiency
            
        Returns:
            List of embeddings
        """
        if not images:
            return []
        
        # Preprocess and filter blurry images
        processed = []
        for img in images:
            # Skip blurry images in batch
            is_blurry, _ = self.is_image_blurry(img, self.blur_threshold)
            if is_blurry:
                continue
                
            if img.shape[:2] != self.target_size:
                img = cv2.resize(img, self.target_size)
            processed.append(img)
        
        if use_batch:
            return self.recognizer.get_embeddings_batch(processed)
        else:
            return [self.generate_from_image(img, preprocess=False) for img in processed]
    
    def generate_from_directory(
        self,
        directory: str,
        extensions: Tuple[str, ...] = ('.jpg', '.jpeg', '.png')
    ) -> Tuple[List[np.ndarray], List[str]]:
        """
        Generate embeddings from all images in a directory.
        
        Args:
            directory: Directory containing face images
            extensions: Valid image file extensions
            
        Returns:
            (list of embeddings, list of file paths)
        """
        dir_path = Path(directory)
        
        if not dir_path.exists():
            logger.error(f"Directory not found: {directory}")
            return [], []
        
        # Find all images
        image_files = []
        for ext in extensions:
            image_files.extend(dir_path.glob(f"*{ext}"))
            image_files.extend(dir_path.glob(f"*{ext.upper()}"))
        
        image_files = sorted(image_files)
        
        if not image_files:
            logger.warning(f"No images found in {directory}")
            return [], []
        
        # Load and process images
        images = []
        valid_paths = []
        
        for img_path in image_files:
            img = cv2.imread(str(img_path))
            if img is not None:
                images.append(img)
                valid_paths.append(str(img_path))
        
        # Generate embeddings
        embeddings = self.generate_from_images(images)
        
        # Filter out failed embeddings
        valid_embeddings = []
        valid_image_paths = []
        
        for emb, path in zip(embeddings, valid_paths):
            if emb is not None:
                valid_embeddings.append(emb)
                valid_image_paths.append(path)
        
        logger.info(
            f"Generated {len(valid_embeddings)}/{len(image_files)} embeddings "
            f"from {directory}"
        )
        
        return valid_embeddings, valid_image_paths
    
    def aggregate_embeddings(
        self,
        embeddings: List[np.ndarray],
        method: str = 'mean'
    ) -> Optional[np.ndarray]:
        """
        Aggregate multiple embeddings into a single representative embedding.
        
        Args:
            embeddings: List of embeddings
            method: Aggregation method ('mean', 'median', 'weighted')
            
        Returns:
            Aggregated embedding
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if not valid_embeddings:
            return None
        
        if method == 'mean':
            aggregated = np.mean(valid_embeddings, axis=0)
        elif method == 'median':
            aggregated = np.median(valid_embeddings, axis=0)
        elif method == 'weighted':
            # Give more weight to embeddings closer to the center
            center = np.mean(valid_embeddings, axis=0)
            weights = []
            for emb in valid_embeddings:
                dist = np.linalg.norm(emb - center)
                weights.append(1.0 / (1.0 + dist))
            
            weights = np.array(weights) / sum(weights)
            aggregated = np.average(valid_embeddings, axis=0, weights=weights)
        else:
            aggregated = np.mean(valid_embeddings, axis=0)
        
        # Normalize
        return aggregated / np.linalg.norm(aggregated)
    
    def compute_quality_score(
        self,
        embeddings: List[np.ndarray]
    ) -> float:
        """
        Compute quality score for a set of embeddings.
        
        Higher score means more consistent/reliable embeddings.
        
        Args:
            embeddings: List of embeddings
            
        Returns:
            Quality score (0 to 1)
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if len(valid_embeddings) < 2:
            return 0.5
        
        # Compute pairwise similarities
        similarities = []
        for i in range(len(valid_embeddings)):
            for j in range(i + 1, len(valid_embeddings)):
                sim = np.dot(valid_embeddings[i], valid_embeddings[j])
                similarities.append(sim)
        
        # High mean similarity = consistent embeddings
        mean_sim = np.mean(similarities)
        std_sim = np.std(similarities)
        
        # Penalize high variance
        quality = mean_sim * (1 - std_sim)
        
        return max(0, min(1, quality))
    
    def filter_outliers(
        self,
        embeddings: List[np.ndarray],
        threshold: float = 2.0
    ) -> List[np.ndarray]:
        """
        Filter outlier embeddings that are too different from the rest.
        
        Args:
            embeddings: List of embeddings
            threshold: Z-score threshold for outlier detection
            
        Returns:
            Filtered list of embeddings
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if len(valid_embeddings) < 3:
            return valid_embeddings
        
        # Compute center
        center = np.mean(valid_embeddings, axis=0)
        
        # Compute distances to center
        distances = [np.linalg.norm(e - center) for e in valid_embeddings]
        
        mean_dist = np.mean(distances)
        std_dist = np.std(distances)
        
        if std_dist == 0:
            return valid_embeddings
        
        # Filter based on z-score
        filtered = []
        for emb, dist in zip(valid_embeddings, distances):
            z_score = (dist - mean_dist) / std_dist
            if abs(z_score) <= threshold:
                filtered.append(emb)
        
        logger.info(
            f"Filtered {len(valid_embeddings) - len(filtered)} outliers "
            f"({len(filtered)} remaining)"
        )
        
        return filtered
