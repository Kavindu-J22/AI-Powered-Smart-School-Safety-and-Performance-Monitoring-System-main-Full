"""
Face Trainer Module
====================
Orchestrates the training pipeline for face recognition.
"""

import cv2
import numpy as np
from pathlib import Path
from typing import Dict, List, Optional, Tuple, Callable
from datetime import datetime
import logging
from threading import Thread, Event
import time

logger = logging.getLogger(__name__)


class FaceTrainer:
    """
    Orchestrates face training from captured images to stored embeddings.
    
    Training Pipeline:
    1. Load face images for each student
    2. Detect and align faces
    3. Apply data augmentation
    4. Generate embeddings
    5. Aggregate and store embeddings
    """
    
    def __init__(
        self,
        face_database: 'FaceDatabase',
        face_detector: 'FaceDetector',
        face_recognizer: 'FaceRecognizer',
        face_aligner: 'FaceAligner',
        attendance_db: 'AttendanceDB' = None,
        augmentation_enabled: bool = True,
        augmentation_multiplier: int = 3
    ):
        """
        Initialize face trainer.
        
        Args:
            face_database: Database for storing embeddings
            face_detector: Face detection module
            face_recognizer: Face recognition module
            face_aligner: Face alignment module
            attendance_db: Attendance database for logging
            augmentation_enabled: Whether to use data augmentation
            augmentation_multiplier: Augmentation factor per image
        """
        self.face_database = face_database
        self.detector = face_detector
        self.recognizer = face_recognizer
        self.aligner = face_aligner
        self.attendance_db = attendance_db
        
        self.augmentation_enabled = augmentation_enabled
        self.augmentation_multiplier = augmentation_multiplier
        
        # Training state
        self._is_training = False
        self._stop_event = Event()
        self._progress = 0.0
        self._current_student = ""
        
        # Initialize augmentor
        if augmentation_enabled:
            from .data_augmentor import DataAugmentor
            self.augmentor = DataAugmentor()
        else:
            self.augmentor = None
        
        # Initialize embedding generator
        from .embedding_generator import EmbeddingGenerator
        self.embedding_generator = EmbeddingGenerator(
            face_recognizer=face_recognizer,
            face_aligner=face_aligner
        )
        
        logger.info("FaceTrainer initialized")
    
    @property
    def is_training(self) -> bool:
        """Check if training is in progress."""
        return self._is_training
    
    @property
    def progress(self) -> float:
        """Get training progress (0-100)."""
        return self._progress
    
    @property
    def current_student(self) -> str:
        """Get currently training student."""
        return self._current_student
    
    def train_all(
        self,
        progress_callback: Callable[[float, str], None] = None
    ) -> Dict:
        """
        Train on all registered students.
        
        Args:
            progress_callback: Callback function(progress, message)
            
        Returns:
            Training result statistics
        """
        if self._is_training:
            return {'error': 'Training already in progress'}
        
        self._is_training = True
        self._stop_event.clear()
        self._progress = 0.0
        
        start_time = time.time()
        stats = {
            'total_students': 0,
            'processed_students': 0,
            'total_images': 0,
            'total_embeddings': 0,
            'failed_students': [],
            'success': True
        }
        
        # Create training log
        training_log = None
        if self.attendance_db:
            training_log = self.attendance_db.create_training_log()
        
        try:
            # Get all students with face images
            faces_dir = self.face_database.faces_dir
            student_dirs = [d for d in faces_dir.iterdir() if d.is_dir()]
            
            stats['total_students'] = len(student_dirs)
            
            if not student_dirs:
                logger.warning("No students to train")
                return stats
            
            # Process each student
            for idx, student_dir in enumerate(student_dirs):
                if self._stop_event.is_set():
                    logger.info("Training stopped by user")
                    break
                
                student_id = student_dir.name
                self._current_student = student_id
                self._progress = (idx / len(student_dirs)) * 100
                
                if progress_callback:
                    progress_callback(
                        self._progress,
                        f"Training {student_id} ({idx + 1}/{len(student_dirs)})"
                    )
                
                # Train single student
                result = self.train_student(student_id)
                
                if result['success']:
                    stats['processed_students'] += 1
                    stats['total_images'] += result.get('images_processed', 0)
                    stats['total_embeddings'] += 1
                else:
                    stats['failed_students'].append(student_id)
            
            self._progress = 100.0
            
            # Update training log
            if training_log and self.attendance_db:
                self.attendance_db.update_training_log(
                    training_log.id,
                    status='completed',
                    total_students=stats['total_students'],
                    total_images=stats['total_images'],
                    total_embeddings=stats['total_embeddings']
                )
            
            stats['training_time_seconds'] = time.time() - start_time
            logger.info(f"Training completed: {stats}")
            
        except Exception as e:
            logger.error(f"Training failed: {e}")
            stats['success'] = False
            stats['error'] = str(e)
            
            if training_log and self.attendance_db:
                self.attendance_db.update_training_log(
                    training_log.id,
                    status='failed',
                    error_message=str(e)
                )
        
        finally:
            self._is_training = False
            self._current_student = ""
        
        return stats
    
    def train_student(
        self,
        student_id: str,
        student_name: str = None
    ) -> Dict:
        """
        Train on a single student's face images.
        
        Args:
            student_id: Student identifier
            student_name: Student name (fetched from DB if not provided)
            
        Returns:
            Training result for this student
        """
        result = {
            'student_id': student_id,
            'success': False,
            'images_processed': 0,
            'embeddings_generated': 0,
            'quality_score': 0.0
        }
        
        # Get image paths
        image_paths = self.face_database.get_face_images(student_id)
        
        if not image_paths:
            result['error'] = 'No face images found'
            logger.warning(f"No images for student {student_id}")
            return result
        
        logger.info(f"Training student {student_id} with {len(image_paths)} images")
        
        # Load images
        images = []
        for path in image_paths:
            img = cv2.imread(path)
            if img is not None:
                images.append(img)
        
        if not images:
            result['error'] = 'Failed to load images'
            return result
        
        result['images_processed'] = len(images)
        
        # Process images: align if landmarks available, otherwise use directly.
        # IMPORTANT: images are already 160×160 face crops from the capture step —
        # re-running full detection here often fails on the tight crop and silently
        # discards most training images.  We attempt detection for alignment (better
        # embedding quality) but always fall back to the raw crop so no image is lost.
        processed_faces = []
        
        for img in images:
            # Ensure 160×160
            if img.shape[:2] != (160, 160):
                img = cv2.resize(img, (160, 160))
            
            # Try to detect + align for better landmark-based alignment
            detection = self.detector.detect_largest(img)
            
            if detection is not None:
                aligned = self.aligner.align(
                    img,
                    detection.landmarks,
                    detection.bbox
                )
                processed_faces.append(aligned)
            else:
                # Image is already a face crop — use it directly rather than
                # discarding it (the old code silently dropped these, causing
                # "No faces detected in images" failures after registration).
                processed_faces.append(img)
        
        # Apply augmentation
        if self.augmentation_enabled and self.augmentor:
            augmented = []
            for face in processed_faces:
                augmented.extend(
                    self.augmentor.augment(face, count=self.augmentation_multiplier)
                )
            processed_faces = augmented
        
        # Generate embeddings
        embeddings = self.embedding_generator.generate_from_images(processed_faces)
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if not valid_embeddings:
            result['error'] = 'Failed to generate embeddings'
            return result
        
        result['embeddings_generated'] = len(valid_embeddings)
        
        # Filter outliers
        filtered_embeddings = self.embedding_generator.filter_outliers(valid_embeddings)
        
        # Compute quality score
        quality = float(self.embedding_generator.compute_quality_score(filtered_embeddings))
        result['quality_score'] = quality
        
        # Select diverse representative embeddings for multi-embedding matching.
        # Increased max_count 10 → 20: more anchors = better coverage of face
        # variation (pose, expression, lighting).
        representative_embeddings = self._select_representative_embeddings(
            filtered_embeddings,
            max_count=20
        )
        
        # Aggregate to single primary embedding using weighted mean — gives more
        # weight to embeddings near the cluster center, reducing outlier influence.
        # Using 'weighted' instead of 'mean' for a more robust representative vector.
        final_embedding = self.embedding_generator.aggregate_embeddings(
            filtered_embeddings,
            method='weighted'
        )
        
        if final_embedding is None:
            result['error'] = 'Failed to aggregate embeddings'
            return result
        
        # Get student name if not provided
        if student_name is None:
            student_info = self.face_database.get_student_info(student_id)
            student_name = student_info.get('name', student_id) if student_info else student_id
        
        # Store in database with multiple embeddings
        self.face_database.add_student(
            student_id=student_id,
            name=student_name,
            embedding=final_embedding,
            multi_embeddings=representative_embeddings,
            additional_info={
                'trained_at': datetime.now().isoformat(),
                'images_count': len(images),
                'quality_score': float(quality),
                'num_embeddings': len(representative_embeddings)
            }
        )
        
        result['success'] = True
        logger.info(
            f"Trained student {student_id}: "
            f"{len(images)} images, {len(representative_embeddings)} embeddings, quality={quality:.2f}"
        )
        
        return result
    
    def _select_representative_embeddings(
        self,
        embeddings: List[np.ndarray],
        max_count: int = 10
    ) -> List[np.ndarray]:
        """
        Select diverse representative embeddings from a list.
        Uses k-means-like clustering to find diverse samples.
        
        Args:
            embeddings: List of all embeddings
            max_count: Maximum number of representative embeddings
            
        Returns:
            List of diverse representative embeddings
        """
        if not embeddings or len(embeddings) <= max_count:
            return embeddings
        
        # Stack embeddings into matrix
        emb_matrix = np.vstack(embeddings)
        
        # Use simple k-means-like selection for diversity
        selected_indices = [0]  # Start with first
        selected_embeddings = [embeddings[0]]
        
        while len(selected_indices) < max_count:
            # Find embedding most distant from all selected ones
            max_min_dist = -1
            best_idx = -1
            
            for i in range(len(embeddings)):
                if i in selected_indices:
                    continue
                
                # Calculate minimum distance to any selected embedding
                min_dist = float('inf')
                for sel_emb in selected_embeddings:
                    dist = 1 - np.dot(embeddings[i], sel_emb)
                    min_dist = min(min_dist, dist)
                
                if min_dist > max_min_dist:
                    max_min_dist = min_dist
                    best_idx = i
            
            if best_idx >= 0:
                selected_indices.append(best_idx)
                selected_embeddings.append(embeddings[best_idx])
            else:
                break
        
        return selected_embeddings
    
    def retrain_student(self, student_id: str) -> Dict:
        """
        Retrain a single student (clear and regenerate embedding).
        
        Args:
            student_id: Student identifier
            
        Returns:
            Training result
        """
        # Get current name
        info = self.face_database.get_student_info(student_id)
        name = info.get('name') if info else None
        
        # Retrain
        return self.train_student(student_id, student_name=name)
    
    def train_async(
        self,
        progress_callback: Callable[[float, str], None] = None,
        completion_callback: Callable[[Dict], None] = None
    ):
        """
        Start training in background thread.
        
        Args:
            progress_callback: Called with (progress, message)
            completion_callback: Called with result dict when done
        """
        def _train_thread():
            result = self.train_all(progress_callback)
            if completion_callback:
                completion_callback(result)
        
        thread = Thread(target=_train_thread, daemon=True)
        thread.start()
    
    def stop_training(self):
        """Stop ongoing training."""
        self._stop_event.set()
        logger.info("Training stop requested")
    
    def get_training_status(self) -> Dict:
        """Get current training status."""
        return {
            'is_training': self._is_training,
            'progress': self._progress,
            'current_student': self._current_student
        }
    
    def validate_student_images(
        self,
        student_id: str
    ) -> Dict:
        """
        Validate face images for a student without training.
        
        Args:
            student_id: Student identifier
            
        Returns:
            Validation result
        """
        result = {
            'student_id': student_id,
            'valid': False,
            'total_images': 0,
            'valid_images': 0,
            'issues': []
        }
        
        image_paths = self.face_database.get_face_images(student_id)
        result['total_images'] = len(image_paths)
        
        if not image_paths:
            result['issues'].append('No images found')
            return result
        
        for path in image_paths:
            img = cv2.imread(path)
            if img is None:
                result['issues'].append(f'Cannot read: {path}')
                continue
            
            detection = self.detector.detect_largest(img)
            if detection is None:
                result['issues'].append(f'No face detected: {path}')
                continue
            
            if detection.confidence < 0.9:
                result['issues'].append(f'Low confidence: {path}')
                continue
            
            result['valid_images'] += 1
        
        result['valid'] = result['valid_images'] >= 3  # Minimum 3 good images
        
        return result
