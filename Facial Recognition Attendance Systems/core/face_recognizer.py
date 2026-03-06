"""
Face Recognition Module
========================
High-performance face recognition using ArcFace/FaceNet embeddings.
Optimized for fast inference and high accuracy.
"""

import cv2
import numpy as np
from typing import List, Tuple, Optional, Union
from pathlib import Path
import logging
from abc import ABC, abstractmethod

logger = logging.getLogger(__name__)


class BaseFaceRecognizer(ABC):
    """Abstract base class for face recognizers."""
    
    @abstractmethod
    def get_embedding(self, face_image: np.ndarray) -> np.ndarray:
        """Extract embedding from a single face image."""
        pass
    
    @abstractmethod
    def get_embeddings_batch(self, face_images: List[np.ndarray]) -> np.ndarray:
        """Extract embeddings from multiple face images."""
        pass


class ArcFaceRecognizer(BaseFaceRecognizer):
    """
    ArcFace-based face recognizer.
    Uses InsightFace's ArcFace model for state-of-the-art accuracy.
    """
    
    def __init__(
        self,
        model_name: str = 'buffalo_l',
        device: str = 'cpu'
    ):
        self.model_name = model_name
        self.device = device
        self._model = None
        self._initialize()
    
    def _initialize(self):
        """Initialize ArcFace model."""
        try:
            from insightface.app import FaceAnalysis
            
            # Initialize InsightFace
            self._app = FaceAnalysis(
                name=self.model_name,
                providers=['CUDAExecutionProvider', 'CPUExecutionProvider']
                if self.device == 'cuda' else ['CPUExecutionProvider']
            )
            self._app.prepare(ctx_id=0 if self.device == 'cuda' else -1)
            
            logger.info(f"ArcFace recognizer initialized with {self.model_name}")
        except ImportError:
            logger.warning("InsightFace not available, falling back to FaceNet")
            self._app = None
    
    def get_embedding(self, face_image: np.ndarray) -> Optional[np.ndarray]:
        """Extract ArcFace embedding from face image."""
        if self._app is None:
            return None
        
        try:
            # InsightFace expects BGR image
            if len(face_image.shape) == 2:
                face_image = cv2.cvtColor(face_image, cv2.COLOR_GRAY2BGR)
            
            faces = self._app.get(face_image)
            
            if faces and len(faces) > 0:
                return faces[0].embedding
            
            return None
            
        except Exception as e:
            logger.error(f"ArcFace embedding error: {e}")
            return None
    
    def get_embeddings_batch(self, face_images: List[np.ndarray]) -> List[Optional[np.ndarray]]:
        """Extract embeddings from batch of faces."""
        return [self.get_embedding(img) for img in face_images]


class FaceNetRecognizer(BaseFaceRecognizer):
    """
    FaceNet-based face recognizer using facenet-pytorch.
    Good balance of speed and accuracy.
    """
    
    def __init__(
        self,
        model_name: str = 'vggface2',  # or 'casia-webface'
        device: str = 'cpu',
        input_size: int = 160
    ):
        self.model_name = model_name
        self.device = device
        self.input_size = input_size
        self._model = None
        self._initialize()
    
    def _initialize(self):
        """Initialize FaceNet model."""
        try:
            import torch
            from facenet_pytorch import InceptionResnetV1
            
            device = torch.device(
                'cuda' if self.device == 'cuda' and torch.cuda.is_available() else 'cpu'
            )
            
            self._model = InceptionResnetV1(
                pretrained=self.model_name
            ).eval().to(device)
            
            self._device = device
            logger.info(f"FaceNet recognizer initialized on {device}")
            
        except ImportError:
            logger.error("facenet-pytorch not installed")
            raise
    
    def _preprocess(self, face_image: np.ndarray) -> 'torch.Tensor':
        """Preprocess face image for FaceNet."""
        import torch
        
        # Resize if needed
        if face_image.shape[:2] != (self.input_size, self.input_size):
            face_image = cv2.resize(face_image, (self.input_size, self.input_size))
        
        # Convert BGR to RGB
        rgb = cv2.cvtColor(face_image, cv2.COLOR_BGR2RGB)
        
        # Normalize
        normalized = (rgb.astype(np.float32) - 127.5) / 128.0
        
        # Convert to tensor (C, H, W)
        tensor = torch.from_numpy(normalized).permute(2, 0, 1).unsqueeze(0)
        
        return tensor.to(self._device)
    
    def get_embedding(self, face_image: np.ndarray) -> Optional[np.ndarray]:
        """Extract FaceNet embedding from face image."""
        if self._model is None:
            return None
        
        try:
            import torch
            
            with torch.no_grad():
                tensor = self._preprocess(face_image)
                embedding = self._model(tensor)
                return embedding.cpu().numpy().flatten()
                
        except Exception as e:
            logger.error(f"FaceNet embedding error: {e}")
            return None
    
    def get_embeddings_batch(self, face_images: List[np.ndarray]) -> List[Optional[np.ndarray]]:
        """Extract embeddings from batch of faces (optimized)."""
        if self._model is None or len(face_images) == 0:
            return [None] * len(face_images)
        
        try:
            import torch
            
            # Preprocess all images
            tensors = [self._preprocess(img) for img in face_images]
            batch = torch.cat(tensors, dim=0)
            
            with torch.no_grad():
                embeddings = self._model(batch)
                return [emb.cpu().numpy() for emb in embeddings]
                
        except Exception as e:
            logger.error(f"Batch embedding error: {e}")
            return [self.get_embedding(img) for img in face_images]


class FaceRecognizer:
    """
    Unified face recognizer with embedding comparison and matching.
    
    Usage:
        recognizer = FaceRecognizer(backend='facenet')
        embedding = recognizer.get_embedding(face_image)
        similarity = recognizer.compare(embedding1, embedding2)
    """
    
    BACKENDS = {
        'arcface': ArcFaceRecognizer,
        'facenet': FaceNetRecognizer
    }
    
    def __init__(
        self,
        backend: str = 'facenet',
        device: str = 'cpu',
        similarity_threshold: float = 0.65,
        **kwargs
    ):
        """
        Initialize face recognizer.
        
        Args:
            backend: Recognition backend ('arcface', 'facenet')
            device: 'cpu' or 'cuda'
            similarity_threshold: Threshold for face matching
            **kwargs: Backend-specific arguments
        """
        self.backend_name = backend.lower()
        self.device = device
        self.similarity_threshold = similarity_threshold
        
        self._initialize_backend(**kwargs)
    
    def _initialize_backend(self, **kwargs):
        """Initialize the selected backend."""
        # Try primary backend, fall back to facenet if not available
        try:
            if self.backend_name == 'arcface':
                self._recognizer = ArcFaceRecognizer(device=self.device, **kwargs)
                if self._recognizer._app is None:
                    raise ImportError("ArcFace not available")
            else:
                self._recognizer = FaceNetRecognizer(device=self.device, **kwargs)
        except Exception as e:
            logger.warning(f"{self.backend_name} failed, falling back to facenet: {e}")
            self._recognizer = FaceNetRecognizer(device=self.device)
            self.backend_name = 'facenet'
        
        logger.info(f"FaceRecognizer initialized with {self.backend_name} backend")
    
    def get_embedding(self, face_image: np.ndarray) -> Optional[np.ndarray]:
        """
        Extract face embedding from image.
        
        Args:
            face_image: Aligned face image (BGR, ideally 160x160)
            
        Returns:
            512-dimensional embedding vector or None if failed
        """
        embedding = self._recognizer.get_embedding(face_image)
        
        if embedding is not None:
            # L2 normalize embedding
            embedding = embedding / np.linalg.norm(embedding)
        
        return embedding
    
    def get_embeddings_batch(self, face_images: List[np.ndarray]) -> List[Optional[np.ndarray]]:
        """Extract embeddings from multiple faces efficiently."""
        embeddings = self._recognizer.get_embeddings_batch(face_images)
        
        # Normalize all embeddings
        normalized = []
        for emb in embeddings:
            if emb is not None:
                normalized.append(emb / np.linalg.norm(emb))
            else:
                normalized.append(None)
        
        return normalized
    
    @staticmethod
    def cosine_similarity(embedding1: np.ndarray, embedding2: np.ndarray) -> float:
        """
        Compute cosine similarity between two embeddings.
        
        Args:
            embedding1: First embedding vector
            embedding2: Second embedding vector
            
        Returns:
            Similarity score in range [-1, 1], higher is more similar
        """
        if embedding1 is None or embedding2 is None:
            return 0.0
        
        # Embeddings should already be normalized, but ensure it
        norm1 = np.linalg.norm(embedding1)
        norm2 = np.linalg.norm(embedding2)
        
        if norm1 == 0 or norm2 == 0:
            return 0.0
        
        return float(np.dot(embedding1, embedding2) / (norm1 * norm2))
    
    @staticmethod
    def euclidean_distance(embedding1: np.ndarray, embedding2: np.ndarray) -> float:
        """
        Compute Euclidean distance between two embeddings.
        
        Args:
            embedding1: First embedding vector
            embedding2: Second embedding vector
            
        Returns:
            Distance score, lower is more similar
        """
        if embedding1 is None or embedding2 is None:
            return float('inf')
        
        return float(np.linalg.norm(embedding1 - embedding2))
    
    def compare(
        self,
        embedding1: np.ndarray,
        embedding2: np.ndarray,
        metric: str = 'cosine'
    ) -> Tuple[float, bool]:
        """
        Compare two embeddings.
        
        Args:
            embedding1: First embedding
            embedding2: Second embedding
            metric: 'cosine' or 'euclidean'
            
        Returns:
            (similarity_score, is_match)
        """
        if metric == 'cosine':
            similarity = self.cosine_similarity(embedding1, embedding2)
            is_match = similarity >= self.similarity_threshold
        else:
            distance = self.euclidean_distance(embedding1, embedding2)
            # Convert distance to similarity-like score
            similarity = 1.0 / (1.0 + distance)
            is_match = distance <= (1.0 - self.similarity_threshold)
        
        return similarity, is_match
    
    def find_best_match(
        self,
        embedding: np.ndarray,
        database_embeddings: dict,
        top_k: int = 1
    ) -> List[Tuple[str, float]]:
        """
        Find best matching identities from database.
        
        Args:
            embedding: Query embedding
            database_embeddings: Dict of {student_id: embedding}
            top_k: Number of top matches to return
            
        Returns:
            List of (student_id, similarity) tuples, sorted by similarity
        """
        if embedding is None or not database_embeddings:
            return []
        
        similarities = []
        
        for student_id, db_embedding in database_embeddings.items():
            if db_embedding is not None:
                sim = self.cosine_similarity(embedding, db_embedding)
                similarities.append((student_id, sim))
        
        # Sort by similarity (highest first)
        similarities.sort(key=lambda x: x[1], reverse=True)
        
        return similarities[:top_k]
    
    def find_best_match_fast(
        self,
        embedding: np.ndarray,
        embedding_matrix: np.ndarray,
        student_ids: List[str],
        top_k: int = 1
    ) -> List[Tuple[str, float]]:
        """
        Find best match using vectorized operations (much faster for large databases).
        
        Args:
            embedding: Query embedding (1D array)
            embedding_matrix: Pre-stacked embeddings matrix (N x dim)
            student_ids: List of student IDs corresponding to matrix rows
            top_k: Number of top matches to return
            
        Returns:
            List of (student_id, similarity) tuples
        """
        if embedding is None or embedding_matrix.size == 0:
            return []
        
        # Compute all similarities at once using matrix multiplication
        similarities = np.dot(embedding_matrix, embedding)
        
        # Get top-k indices
        if top_k == 1:
            best_idx = np.argmax(similarities)
            return [(student_ids[best_idx], float(similarities[best_idx]))]
        else:
            top_indices = np.argsort(similarities)[-top_k:][::-1]
            return [
                (student_ids[idx], float(similarities[idx]))
                for idx in top_indices
            ]
    
    def verify(
        self,
        face_image: np.ndarray,
        reference_embedding: np.ndarray
    ) -> Tuple[bool, float]:
        """
        Verify if face matches a reference embedding.
        
        Args:
            face_image: Face image to verify
            reference_embedding: Reference embedding to compare against
            
        Returns:
            (is_verified, confidence)
        """
        embedding = self.get_embedding(face_image)
        
        if embedding is None:
            return False, 0.0
        
        similarity = self.cosine_similarity(embedding, reference_embedding)
        is_verified = similarity >= self.similarity_threshold
        
        return is_verified, similarity


class EmbeddingAggregator:
    """
    Aggregates multiple embeddings for more robust recognition.
    
    When multiple face images are captured during registration,
    this class computes a representative embedding.
    """
    
    @staticmethod
    def mean_embedding(embeddings: List[np.ndarray]) -> Optional[np.ndarray]:
        """
        Compute mean embedding from multiple embeddings.
        
        Args:
            embeddings: List of embedding vectors
            
        Returns:
            Mean embedding, L2 normalized
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if not valid_embeddings:
            return None
        
        mean = np.mean(valid_embeddings, axis=0)
        return mean / np.linalg.norm(mean)
    
    @staticmethod
    def median_embedding(embeddings: List[np.ndarray]) -> Optional[np.ndarray]:
        """
        Compute median embedding (more robust to outliers).
        
        Args:
            embeddings: List of embedding vectors
            
        Returns:
            Median embedding, L2 normalized
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if not valid_embeddings:
            return None
        
        median = np.median(valid_embeddings, axis=0)
        return median / np.linalg.norm(median)
    
    @staticmethod
    def weighted_mean_embedding(
        embeddings: List[np.ndarray],
        weights: Optional[List[float]] = None
    ) -> Optional[np.ndarray]:
        """
        Compute weighted mean embedding.
        
        Args:
            embeddings: List of embedding vectors
            weights: Optional weights for each embedding
            
        Returns:
            Weighted mean embedding, L2 normalized
        """
        valid_embeddings = []
        valid_weights = []
        
        for i, emb in enumerate(embeddings):
            if emb is not None:
                valid_embeddings.append(emb)
                if weights is not None and i < len(weights):
                    valid_weights.append(weights[i])
                else:
                    valid_weights.append(1.0)
        
        if not valid_embeddings:
            return None
        
        weights_arr = np.array(valid_weights)
        weights_arr = weights_arr / weights_arr.sum()
        
        weighted_mean = np.average(valid_embeddings, axis=0, weights=weights_arr)
        return weighted_mean / np.linalg.norm(weighted_mean)
    
    @staticmethod
    def cluster_and_select(
        embeddings: List[np.ndarray],
        n_clusters: int = 1
    ) -> Optional[np.ndarray]:
        """
        Cluster embeddings and return centroid of largest cluster.
        
        Useful for filtering out poor quality or misaligned faces.
        
        Args:
            embeddings: List of embedding vectors
            n_clusters: Number of clusters (1 for best representative)
            
        Returns:
            Representative embedding
        """
        valid_embeddings = [e for e in embeddings if e is not None]
        
        if len(valid_embeddings) < 3:
            return EmbeddingAggregator.mean_embedding(valid_embeddings)
        
        try:
            from sklearn.cluster import KMeans
            
            embeddings_matrix = np.array(valid_embeddings)
            
            kmeans = KMeans(n_clusters=min(n_clusters, len(valid_embeddings)))
            labels = kmeans.fit_predict(embeddings_matrix)
            
            # Find largest cluster
            unique, counts = np.unique(labels, return_counts=True)
            largest_cluster_label = unique[np.argmax(counts)]
            
            # Get centroid of largest cluster
            centroid = kmeans.cluster_centers_[largest_cluster_label]
            return centroid / np.linalg.norm(centroid)
            
        except ImportError:
            logger.warning("sklearn not available, using mean embedding")
            return EmbeddingAggregator.mean_embedding(valid_embeddings)
