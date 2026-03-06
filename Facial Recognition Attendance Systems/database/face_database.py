"""
Face Database Module
=====================
Manages face embeddings storage and retrieval.
Optimized for fast lookup during recognition.
Supports multiple embeddings per student for better accuracy.
"""

import os
import pickle
import numpy as np
from typing import Dict, List, Optional, Tuple
from pathlib import Path
from datetime import datetime
import logging
import shutil
from threading import Lock

logger = logging.getLogger(__name__)


class FaceDatabase:
    """
    Face embeddings database for fast recognition.
    
    Stores MULTIPLE embeddings per student for better accuracy.
    Uses max similarity among all embeddings for matching.
    """
    
    def __init__(
        self,
        embeddings_dir: str,
        faces_dir: str,
        auto_save: bool = True,
        max_embeddings_per_student: int = 10  # Store top 10 representative embeddings
    ):
        """
        Initialize face database.
        
        Args:
            embeddings_dir: Directory for embedding storage
            faces_dir: Directory for face images
            auto_save: Automatically save after modifications
            max_embeddings_per_student: Max embeddings to store per student
        """
        self.embeddings_dir = Path(embeddings_dir)
        self.faces_dir = Path(faces_dir)
        self.auto_save = auto_save
        self.max_embeddings_per_student = max_embeddings_per_student
        
        # Create directories
        self.embeddings_dir.mkdir(parents=True, exist_ok=True)
        self.faces_dir.mkdir(parents=True, exist_ok=True)
        
        # In-memory storage - now stores list of embeddings per student
        self._embeddings: Dict[str, np.ndarray] = {}  # Legacy single embedding
        self._multi_embeddings: Dict[str, List[np.ndarray]] = {}  # Multiple embeddings
        self._student_info: Dict[str, Dict] = {}
        self._lock = Lock()
        
        # Paths
        self._embeddings_file = self.embeddings_dir / "face_embeddings.pkl"
        self._multi_embeddings_file = self.embeddings_dir / "face_multi_embeddings.pkl"
        self._info_file = self.embeddings_dir / "student_info.pkl"
        
        # Load existing data
        self._load()
        
        logger.info(f"FaceDatabase initialized with {len(self._embeddings)} faces")
    
    def _load(self):
        """Load embeddings from disk."""
        try:
            if self._embeddings_file.exists():
                with open(self._embeddings_file, 'rb') as f:
                    self._embeddings = pickle.load(f)
                logger.info(f"Loaded {len(self._embeddings)} embeddings from disk")
            
            # Load multi-embeddings if available
            if self._multi_embeddings_file.exists():
                with open(self._multi_embeddings_file, 'rb') as f:
                    self._multi_embeddings = pickle.load(f)
                logger.info(f"Loaded multi-embeddings for {len(self._multi_embeddings)} students")
            
            if self._info_file.exists():
                with open(self._info_file, 'rb') as f:
                    self._student_info = pickle.load(f)
        except Exception as e:
            logger.error(f"Error loading database: {e}")
            self._embeddings = {}
            self._multi_embeddings = {}
            self._student_info = {}
    
    def _save(self):
        """Save embeddings to disk."""
        try:
            with self._lock:
                # Backup existing files
                if self._embeddings_file.exists():
                    backup = self._embeddings_file.with_suffix('.pkl.bak')
                    shutil.copy(self._embeddings_file, backup)
                
                # Save embeddings
                with open(self._embeddings_file, 'wb') as f:
                    pickle.dump(self._embeddings, f)
                
                # Save multi-embeddings
                with open(self._multi_embeddings_file, 'wb') as f:
                    pickle.dump(self._multi_embeddings, f)
                
                # Save student info
                with open(self._info_file, 'wb') as f:
                    pickle.dump(self._student_info, f)
                
                logger.info(f"Saved {len(self._embeddings)} embeddings to disk")
        except Exception as e:
            logger.error(f"Error saving database: {e}")
    
    def add_student(
        self,
        student_id: str,
        name: str,
        embedding: np.ndarray,
        additional_info: Dict = None,
        multi_embeddings: List[np.ndarray] = None
    ) -> bool:
        """
        Add or update a student's face embedding.
        
        Args:
            student_id: Unique student identifier
            name: Student name
            embedding: Primary face embedding vector (mean)
            additional_info: Additional student information
            multi_embeddings: List of multiple embeddings for better matching
            
        Returns:
            True if successful
        """
        if embedding is None:
            logger.error(f"Cannot add student {student_id}: embedding is None")
            return False
        
        with self._lock:
            # Normalize and store primary embedding
            normalized = embedding / np.linalg.norm(embedding)
            self._embeddings[student_id] = normalized
            
            # Store multiple embeddings if provided
            if multi_embeddings:
                normalized_multi = []
                for emb in multi_embeddings[:self.max_embeddings_per_student]:
                    if emb is not None:
                        norm_emb = emb / np.linalg.norm(emb)
                        normalized_multi.append(norm_emb)
                self._multi_embeddings[student_id] = normalized_multi
                logger.info(f"Stored {len(normalized_multi)} embeddings for {student_id}")
            
            # Store info
            self._student_info[student_id] = {
                'name': name,
                'registered_at': datetime.now().isoformat(),
                'num_embeddings': len(multi_embeddings) if multi_embeddings else 1,
                **(additional_info or {})
            }
        
        if self.auto_save:
            self._save()
        
        logger.info(f"Added student: {student_id} ({name})")
        return True
    
    def update_embedding(
        self,
        student_id: str,
        embedding: np.ndarray
    ) -> bool:
        """
        Update existing student's embedding.
        
        Args:
            student_id: Student identifier
            embedding: New face embedding
            
        Returns:
            True if successful
        """
        if student_id not in self._embeddings:
            logger.error(f"Student {student_id} not found")
            return False
        
        with self._lock:
            normalized = embedding / np.linalg.norm(embedding)
            self._embeddings[student_id] = normalized
            self._student_info[student_id]['updated_at'] = datetime.now().isoformat()
        
        if self.auto_save:
            self._save()
        
        return True
    
    def remove_student(self, student_id: str) -> bool:
        """
        Remove a student from the database.
        
        Args:
            student_id: Student identifier
            
        Returns:
            True if successful
        """
        with self._lock:
            if student_id in self._embeddings:
                del self._embeddings[student_id]
            
            if student_id in self._multi_embeddings:
                del self._multi_embeddings[student_id]
            
            if student_id in self._student_info:
                del self._student_info[student_id]
            
            # Remove face images
            student_faces_dir = self.faces_dir / student_id
            if student_faces_dir.exists():
                shutil.rmtree(student_faces_dir)
        
        if self.auto_save:
            self._save()
        
        logger.info(f"Removed student: {student_id}")
        return True
    
    def get_embedding(self, student_id: str) -> Optional[np.ndarray]:
        """Get primary embedding for a student."""
        return self._embeddings.get(student_id)
    
    def get_multi_embeddings(self, student_id: str) -> Optional[List[np.ndarray]]:
        """Get all embeddings for a student."""
        return self._multi_embeddings.get(student_id)
    
    def get_all_multi_embeddings(self) -> Dict[str, List[np.ndarray]]:
        """Get all multi-embeddings for all students."""
        return self._multi_embeddings.copy()
    
    def get_student_name(self, student_id: str) -> Optional[str]:
        """Get student name."""
        info = self._student_info.get(student_id)
        return info.get('name') if info else None
    
    def get_student_info(self, student_id: str) -> Optional[Dict]:
        """Get all student information."""
        return self._student_info.get(student_id)
    
    def get_all_embeddings(self) -> Dict[str, np.ndarray]:
        """Get all embeddings."""
        return self._embeddings.copy()
    
    def get_all_students(self) -> List[Dict]:
        """Get all registered students."""
        students = []
        for student_id, info in self._student_info.items():
            students.append({
                'student_id': student_id,
                **info
            })
        return students
    
    def get_embedding_matrix(self) -> Tuple[np.ndarray, List[str]]:
        """
        Get embeddings as matrix for fast matching.
        
        Returns:
            (embedding_matrix, student_ids)
        """
        if not self._embeddings:
            return np.array([]), []
        
        student_ids = list(self._embeddings.keys())
        matrix = np.array([self._embeddings[sid] for sid in student_ids])
        
        return matrix, student_ids
    
    def student_exists(self, student_id: str) -> bool:
        """Check if student exists in database."""
        return student_id in self._embeddings
    
    def count(self) -> int:
        """Get number of registered students."""
        return len(self._embeddings)
    
    def save_face_image(
        self,
        student_id: str,
        image: np.ndarray,
        index: int = None
    ) -> str:
        """
        Save a face image for a student.
        
        Args:
            student_id: Student identifier
            image: Face image (BGR)
            index: Image index (auto-incremented if None)
            
        Returns:
            Path to saved image
        """
        import cv2
        
        student_dir = self.faces_dir / student_id
        student_dir.mkdir(parents=True, exist_ok=True)
        
        if index is None:
            existing = list(student_dir.glob("*.jpg"))
            index = len(existing)
        
        image_path = student_dir / f"face_{index:04d}.jpg"
        cv2.imwrite(str(image_path), image)
        
        return str(image_path)
    
    def get_face_images(self, student_id: str) -> List[str]:
        """Get all face image paths for a student."""
        student_dir = self.faces_dir / student_id
        if not student_dir.exists():
            return []
        
        images = sorted(student_dir.glob("*.jpg"))
        return [str(img) for img in images]
    
    def get_face_images_count(self, student_id: str) -> int:
        """Get number of face images for a student."""
        return len(self.get_face_images(student_id))
    
    def clear_face_images(self, student_id: str) -> bool:
        """Clear all face images for a student."""
        student_dir = self.faces_dir / student_id
        if student_dir.exists():
            shutil.rmtree(student_dir)
            student_dir.mkdir(parents=True, exist_ok=True)
            return True
        return False
    
    def export_embeddings(self, output_path: str) -> bool:
        """
        Export embeddings to a file.
        
        Args:
            output_path: Output file path
            
        Returns:
            True if successful
        """
        try:
            data = {
                'embeddings': self._embeddings,
                'student_info': self._student_info,
                'exported_at': datetime.now().isoformat()
            }
            
            with open(output_path, 'wb') as f:
                pickle.dump(data, f)
            
            logger.info(f"Exported embeddings to {output_path}")
            return True
        except Exception as e:
            logger.error(f"Export failed: {e}")
            return False
    
    def import_embeddings(self, input_path: str, merge: bool = True) -> bool:
        """
        Import embeddings from a file.
        
        Args:
            input_path: Input file path
            merge: If True, merge with existing. If False, replace.
            
        Returns:
            True if successful
        """
        try:
            with open(input_path, 'rb') as f:
                data = pickle.load(f)
            
            with self._lock:
                if merge:
                    self._embeddings.update(data.get('embeddings', {}))
                    self._student_info.update(data.get('student_info', {}))
                else:
                    self._embeddings = data.get('embeddings', {})
                    self._student_info = data.get('student_info', {})
            
            if self.auto_save:
                self._save()
            
            logger.info(f"Imported embeddings from {input_path}")
            return True
        except Exception as e:
            logger.error(f"Import failed: {e}")
            return False
    
    def backup(self, backup_dir: str = None) -> str:
        """
        Create a backup of the database.
        
        Args:
            backup_dir: Backup directory (default: embeddings_dir/backups)
            
        Returns:
            Backup path
        """
        if backup_dir is None:
            backup_dir = self.embeddings_dir / "backups"
        
        backup_path = Path(backup_dir)
        backup_path.mkdir(parents=True, exist_ok=True)
        
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        backup_file = backup_path / f"backup_{timestamp}.pkl"
        
        self.export_embeddings(str(backup_file))
        
        return str(backup_file)

    def clear_database(self) -> bool:
        """
        Clear all data from the database, including embeddings and face images.
        """
        with self._lock:
            self._embeddings = {}
            self._multi_embeddings = {}
            self._student_info = {}
            
            # Remove face images directory and recreate it
            if self.faces_dir.exists():
                shutil.rmtree(self.faces_dir)
            self.faces_dir.mkdir(parents=True, exist_ok=True)
            
            # Remove embedding files
            for f in [self._embeddings_file, self._multi_embeddings_file, self._info_file]:
                if f.exists():
                    try:
                        f.unlink()
                    except Exception as e:
                        logger.error(f"Could not delete {f}: {e}")
                
                # Also remove backups
                backup = f.with_suffix('.pkl.bak')
                if backup.exists():
                    try:
                        backup.unlink()
                    except Exception as e:
                        logger.error(f"Could not delete backup {backup}: {e}")
        
        logger.info("Face database cleared successfully")
        return True
