"""
Left-Behind Object Detection Model
Uses YOLOv8 for detecting objects left in classrooms
"""

import torch
import cv2
import numpy as np
from ultralytics import YOLO
from pathlib import Path
from typing import List, Dict, Tuple, Optional
import logging

logger = logging.getLogger(__name__)


class LeftBehindObjectDetector:
    """
    Detects left-behind objects in classroom environments using YOLOv8
    """
    
    def __init__(
        self,
        model_path: str = "yolov8n.pt",
        confidence_threshold: float = 0.5,
        iou_threshold: float = 0.45,
        target_classes: Optional[List[str]] = None,
        device: str = "cuda" if torch.cuda.is_available() else "cpu"
    ):
        """
        Initialize the object detector
        
        Args:
            model_path: Path to YOLOv8 model weights
            confidence_threshold: Minimum confidence for detections
            iou_threshold: IoU threshold for NMS
            target_classes: List of class names to detect (e.g., ['backpack', 'book'])
            device: Device to run inference on ('cuda' or 'cpu')
        """
        self.model_path = model_path
        self.confidence_threshold = confidence_threshold
        self.iou_threshold = iou_threshold
        self.device = device
        
        # Default target classes for left-behind objects
        if target_classes is None:
            self.target_classes = [
                'backpack', 'handbag', 'suitcase', 'book', 
                'bottle', 'umbrella', 'laptop', 'cell phone'
            ]
        else:
            self.target_classes = target_classes
        
        # Load model
        logger.info(f"Loading YOLOv8 model from {model_path}")
        self.model = YOLO(model_path)
        self.model.to(self.device)
        
        # Get class names from model
        self.class_names = self.model.names
        
        # Create mapping of target class names to indices
        self.target_class_indices = self._get_target_class_indices()
        
        logger.info(f"Model loaded successfully on {self.device}")
        logger.info(f"Target classes: {self.target_classes}")
        logger.info(f"Target class indices: {self.target_class_indices}")
    
    def _get_target_class_indices(self) -> List[int]:
        """Get indices of target classes from model class names"""
        indices = []
        for target_class in self.target_classes:
            for idx, class_name in self.class_names.items():
                if target_class.lower() in class_name.lower():
                    indices.append(idx)
                    break
        return indices
    
    def detect(
        self,
        frame: np.ndarray,
        filter_classes: bool = True
    ) -> List[Dict]:
        """
        Detect objects in a frame
        
        Args:
            frame: Input image (BGR format)
            filter_classes: Whether to filter only target classes
            
        Returns:
            List of detections, each containing:
                - bbox: [x1, y1, x2, y2]
                - confidence: float
                - class_id: int
                - class_name: str
        """
        # Run inference
        results = self.model(
            frame,
            conf=self.confidence_threshold,
            iou=self.iou_threshold,
            verbose=False
        )[0]
        
        detections = []
        
        # Process results
        if results.boxes is not None:
            boxes = results.boxes.xyxy.cpu().numpy()  # x1, y1, x2, y2
            confidences = results.boxes.conf.cpu().numpy()
            class_ids = results.boxes.cls.cpu().numpy().astype(int)
            
            for box, conf, class_id in zip(boxes, confidences, class_ids):
                # Filter by target classes if enabled
                if filter_classes and class_id not in self.target_class_indices:
                    continue
                
                detection = {
                    'bbox': box.tolist(),
                    'confidence': float(conf),
                    'class_id': int(class_id),
                    'class_name': self.class_names[class_id]
                }
                detections.append(detection)
        
        return detections
    
    def detect_batch(
        self,
        frames: List[np.ndarray],
        filter_classes: bool = True
    ) -> List[List[Dict]]:
        """
        Detect objects in multiple frames (batch processing)
        
        Args:
            frames: List of input images
            filter_classes: Whether to filter only target classes
            
        Returns:
            List of detection lists for each frame
        """
        # Run batch inference
        results = self.model(
            frames,
            conf=self.confidence_threshold,
            iou=self.iou_threshold,
            verbose=False
        )
        
        all_detections = []
        
        for result in results:
            detections = []
            
            if result.boxes is not None:
                boxes = result.boxes.xyxy.cpu().numpy()
                confidences = result.boxes.conf.cpu().numpy()
                class_ids = result.boxes.cls.cpu().numpy().astype(int)
                
                for box, conf, class_id in zip(boxes, confidences, class_ids):
                    if filter_classes and class_id not in self.target_class_indices:
                        continue
                    
                    detection = {
                        'bbox': box.tolist(),
                        'confidence': float(conf),
                        'class_id': int(class_id),
                        'class_name': self.class_names[class_id]
                    }
                    detections.append(detection)
            
            all_detections.append(detections)

        return all_detections

    def visualize_detections(
        self,
        frame: np.ndarray,
        detections: List[Dict],
        show_labels: bool = True,
        thickness: int = 2
    ) -> np.ndarray:
        """
        Draw bounding boxes and labels on frame

        Args:
            frame: Input image
            detections: List of detections from detect()
            show_labels: Whether to show class labels
            thickness: Line thickness for bounding boxes

        Returns:
            Annotated frame
        """
        annotated_frame = frame.copy()

        for det in detections:
            x1, y1, x2, y2 = map(int, det['bbox'])
            conf = det['confidence']
            class_name = det['class_name']

            # Draw bounding box
            color = (0, 255, 0)  # Green
            cv2.rectangle(annotated_frame, (x1, y1), (x2, y2), color, thickness)

            # Draw label
            if show_labels:
                label = f"{class_name}: {conf:.2f}"
                label_size, _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 1)

                # Draw label background
                cv2.rectangle(
                    annotated_frame,
                    (x1, y1 - label_size[1] - 10),
                    (x1 + label_size[0], y1),
                    color,
                    -1
                )

                # Draw label text
                cv2.putText(
                    annotated_frame,
                    label,
                    (x1, y1 - 5),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.5,
                    (0, 0, 0),
                    1
                )

        return annotated_frame

    def get_object_area(self, bbox: List[float]) -> float:
        """Calculate area of bounding box"""
        x1, y1, x2, y2 = bbox
        return (x2 - x1) * (y2 - y1)

    def filter_by_size(
        self,
        detections: List[Dict],
        min_area: int = 1000
    ) -> List[Dict]:
        """
        Filter detections by minimum bounding box area

        Args:
            detections: List of detections
            min_area: Minimum area in pixels

        Returns:
            Filtered detections
        """
        filtered = []
        for det in detections:
            area = self.get_object_area(det['bbox'])
            if area >= min_area:
                filtered.append(det)
        return filtered

    def train(
        self,
        data_yaml: str,
        epochs: int = 100,
        imgsz: int = 640,
        batch: int = 16,
        project: str = "runs/train",
        name: str = "left_behind_detector"
    ):
        """
        Train or fine-tune the model

        Args:
            data_yaml: Path to dataset YAML file
            epochs: Number of training epochs
            imgsz: Input image size
            batch: Batch size
            project: Project directory
            name: Experiment name
        """
        logger.info(f"Starting training with {data_yaml}")

        results = self.model.train(
            data=data_yaml,
            epochs=epochs,
            imgsz=imgsz,
            batch=batch,
            project=project,
            name=name,
            device=self.device
        )

        logger.info("Training completed")
        return results

    def export_model(
        self,
        format: str = "onnx",
        output_path: Optional[str] = None
    ):
        """
        Export model to different formats for deployment

        Args:
            format: Export format ('onnx', 'torchscript', 'tflite', 'edgetpu')
            output_path: Output path for exported model
        """
        logger.info(f"Exporting model to {format}")

        self.model.export(format=format)

        logger.info(f"Model exported successfully")


if __name__ == "__main__":
    # Example usage
    detector = LeftBehindObjectDetector(
        model_path="yolov8n.pt",
        confidence_threshold=0.5
    )

    # Test with an image
    test_image = cv2.imread("test_image.jpg")
    if test_image is not None:
        detections = detector.detect(test_image)
        print(f"Found {len(detections)} objects")

        # Visualize
        annotated = detector.visualize_detections(test_image, detections)
        cv2.imshow("Detections", annotated)
        cv2.waitKey(0)
        cv2.destroyAllWindows()


