"""
Threat Detection Model
Uses video action recognition to detect aggressive/violent behavior
"""

import torch
import torch.nn as nn
import cv2
import numpy as np
from typing import List, Dict, Tuple, Optional
from collections import deque
import logging

logger = logging.getLogger(__name__)


class ThreatDetector:
    """
    Detects threatening behavior (fighting, aggression) in video streams
    Uses temporal information from multiple frames
    """
    
    def __init__(
        self,
        model_path: Optional[str] = None,
        model_type: str = "slowfast",
        confidence_threshold: float = 0.7,
        clip_length: int = 32,
        device: str = "cuda" if torch.cuda.is_available() else "cpu"
    ):
        """
        Initialize threat detector
        
        Args:
            model_path: Path to trained model weights
            model_type: Type of model ('slowfast', 'x3d', 'i3d')
            confidence_threshold: Minimum confidence for threat detection
            clip_length: Number of frames to analyze together
            device: Device to run inference on
        """
        self.model_path = model_path
        self.model_type = model_type
        self.confidence_threshold = confidence_threshold
        self.clip_length = clip_length
        self.device = device
        
        # Frame buffer for temporal analysis
        self.frame_buffer = deque(maxlen=clip_length)
        
        # Threat classes
        self.threat_classes = [
            'fighting',
            'hitting',
            'pushing',
            'aggressive_behavior',
            'weapon_detected'
        ]
        
        self.normal_class = 'normal'
        
        # Load model
        self.model = self._load_model()
        
        logger.info(f"Threat detector initialized with {model_type} on {device}")
    
    def _load_model(self):
        """Load the threat detection model"""
        if self.model_type == "slowfast":
            return self._load_slowfast_model()
        elif self.model_type == "x3d":
            return self._load_x3d_model()
        elif self.model_type == "i3d":
            return self._load_i3d_model()
        else:
            raise ValueError(f"Unknown model type: {self.model_type}")
    
    def _load_slowfast_model(self):
        """Load SlowFast model for action recognition"""
        try:
            import pytorchvideo.models as pv_models
            
            # Load pre-trained SlowFast model
            model = pv_models.slowfast.create_slowfast(
                model_num_class=len(self.threat_classes) + 1,  # +1 for normal class
                slowfast_channel_reduction_ratio=8,
                slowfast_conv_channel_fusion_ratio=2,
                slowfast_fusion_conv_kernel_size=(7, 1, 1),
            )
            
            # Load custom weights if provided
            if self.model_path:
                logger.info(f"Loading weights from {self.model_path}")
                checkpoint = torch.load(self.model_path, map_location=self.device)
                model.load_state_dict(checkpoint['model_state_dict'])
            
            model = model.to(self.device)
            model.eval()
            
            return model
            
        except ImportError:
            logger.warning("pytorchvideo not available, using fallback model")
            return self._create_fallback_model()
    
    def _load_x3d_model(self):
        """Load X3D model"""
        try:
            import pytorchvideo.models as pv_models
            
            model = pv_models.x3d.create_x3d(
                input_clip_length=self.clip_length,
                input_crop_size=224,
                model_num_class=len(self.threat_classes) + 1,
            )
            
            if self.model_path:
                checkpoint = torch.load(self.model_path, map_location=self.device)
                model.load_state_dict(checkpoint['model_state_dict'])
            
            model = model.to(self.device)
            model.eval()
            
            return model
            
        except ImportError:
            logger.warning("pytorchvideo not available, using fallback model")
            return self._create_fallback_model()
    
    def _load_i3d_model(self):
        """Load I3D model"""
        # Placeholder for I3D implementation
        logger.warning("I3D model not fully implemented, using fallback")
        return self._create_fallback_model()
    
    def _create_fallback_model(self):
        """Create a simple 3D CNN fallback model"""
        class Simple3DCNN(nn.Module):
            def __init__(self, num_classes):
                super().__init__()
                self.features = nn.Sequential(
                    nn.Conv3d(3, 64, kernel_size=(3, 3, 3), padding=1),
                    nn.ReLU(),
                    nn.MaxPool3d(kernel_size=(1, 2, 2)),
                    
                    nn.Conv3d(64, 128, kernel_size=(3, 3, 3), padding=1),
                    nn.ReLU(),
                    nn.MaxPool3d(kernel_size=(2, 2, 2)),
                    
                    nn.Conv3d(128, 256, kernel_size=(3, 3, 3), padding=1),
                    nn.ReLU(),
                    nn.MaxPool3d(kernel_size=(2, 2, 2)),
                )
                
                self.classifier = nn.Sequential(
                    nn.AdaptiveAvgPool3d((1, 1, 1)),
                    nn.Flatten(),
                    nn.Linear(256, 128),
                    nn.ReLU(),
                    nn.Dropout(0.5),
                    nn.Linear(128, num_classes)
                )

            def forward(self, x):
                x = self.features(x)
                x = self.classifier(x)
                return x

        model = Simple3DCNN(num_classes=len(self.threat_classes) + 1)

        if self.model_path:
            checkpoint = torch.load(self.model_path, map_location=self.device)
            model.load_state_dict(checkpoint['model_state_dict'])

        model = model.to(self.device)
        model.eval()

        return model

    def preprocess_frame(self, frame: np.ndarray, size: Tuple[int, int] = (224, 224)) -> np.ndarray:
        """
        Preprocess a single frame

        Args:
            frame: Input frame (BGR)
            size: Target size (height, width)

        Returns:
            Preprocessed frame
        """
        # Resize
        frame = cv2.resize(frame, size)

        # Convert BGR to RGB
        frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)

        # Normalize to [0, 1]
        frame = frame.astype(np.float32) / 255.0

        # Normalize with ImageNet stats
        mean = np.array([0.485, 0.456, 0.406])
        std = np.array([0.229, 0.224, 0.225])
        frame = (frame - mean) / std

        return frame

    def add_frame(self, frame: np.ndarray):
        """Add a frame to the buffer"""
        preprocessed = self.preprocess_frame(frame)
        self.frame_buffer.append(preprocessed)

    def detect(self, frame: Optional[np.ndarray] = None) -> Dict:
        """
        Detect threats in current frame buffer

        Args:
            frame: Optional new frame to add before detection

        Returns:
            Detection result containing:
                - is_threat: bool
                - threat_type: str or None
                - confidence: float
                - all_scores: dict of all class scores
        """
        if frame is not None:
            self.add_frame(frame)

        # Need full buffer for detection
        if len(self.frame_buffer) < self.clip_length:
            return {
                'is_threat': False,
                'threat_type': None,
                'confidence': 0.0,
                'all_scores': {},
                'status': 'buffering'
            }

        # Prepare input tensor
        # Shape: (batch, channels, time, height, width)
        frames = np.stack(list(self.frame_buffer), axis=0)  # (T, H, W, C)
        frames = np.transpose(frames, (3, 0, 1, 2))  # (C, T, H, W)
        frames = np.expand_dims(frames, axis=0)  # (1, C, T, H, W)

        # Convert to tensor
        frames_tensor = torch.from_numpy(frames).float().to(self.device)

        # Run inference
        with torch.no_grad():
            if self.model_type == "slowfast":
                # SlowFast requires two pathways
                slow_pathway = frames_tensor
                fast_pathway = frames_tensor[:, :, ::2, :, :]  # Sample every 2nd frame
                outputs = self.model([slow_pathway, fast_pathway])
            else:
                outputs = self.model(frames_tensor)

            # Get probabilities
            probs = torch.softmax(outputs, dim=1)[0]
            probs = probs.cpu().numpy()

        # Get all class scores
        all_classes = self.threat_classes + [self.normal_class]
        all_scores = {cls: float(probs[i]) for i, cls in enumerate(all_classes)}

        # Find highest threat score
        max_threat_idx = -1
        max_threat_score = 0.0

        for i, threat_class in enumerate(self.threat_classes):
            if probs[i] > max_threat_score:
                max_threat_score = probs[i]
                max_threat_idx = i

        # Determine if threat detected
        is_threat = max_threat_score >= self.confidence_threshold
        threat_type = self.threat_classes[max_threat_idx] if is_threat else None

        return {
            'is_threat': is_threat,
            'threat_type': threat_type,
            'confidence': float(max_threat_score),
            'all_scores': all_scores,
            'status': 'detected' if is_threat else 'normal'
        }

    def reset_buffer(self):
        """Clear the frame buffer"""
        self.frame_buffer.clear()

    def visualize_result(
        self,
        frame: np.ndarray,
        result: Dict
    ) -> np.ndarray:
        """
        Draw detection result on frame

        Args:
            frame: Input frame
            result: Detection result from detect()

        Returns:
            Annotated frame
        """
        annotated = frame.copy()
        h, w = annotated.shape[:2]

        # Determine color and text
        if result['is_threat']:
            color = (0, 0, 255)  # Red for threat
            status_text = f"THREAT: {result['threat_type']}"
            conf_text = f"Confidence: {result['confidence']:.2%}"
        else:
            color = (0, 255, 0)  # Green for normal
            status_text = "NORMAL"
            conf_text = f"Confidence: {result['confidence']:.2%}"

        # Draw status banner
        cv2.rectangle(annotated, (0, 0), (w, 80), color, -1)

        # Draw text
        cv2.putText(
            annotated, status_text, (10, 35),
            cv2.FONT_HERSHEY_SIMPLEX, 1.0, (255, 255, 255), 2
        )
        cv2.putText(
            annotated, conf_text, (10, 65),
            cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2
        )

        return annotated


if __name__ == "__main__":
    # Example usage
    detector = ThreatDetector(
        model_type="slowfast",
        confidence_threshold=0.7,
        clip_length=32
    )

    # Simulate video stream
    cap = cv2.VideoCapture(0)  # Use webcam

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        # Detect threats
        result = detector.detect(frame)

        # Visualize
        annotated = detector.visualize_result(frame, result)
        cv2.imshow("Threat Detection", annotated)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()


