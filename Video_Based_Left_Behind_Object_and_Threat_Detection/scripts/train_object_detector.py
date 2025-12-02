"""
Train or fine-tune object detection model for left-behind objects
"""

import argparse
from pathlib import Path
import logging
from src.models.object_detector import LeftBehindObjectDetector

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def train_model(
    data_yaml: str,
    base_model: str = "yolov8n.pt",
    epochs: int = 100,
    imgsz: int = 640,
    batch: int = 16,
    project: str = "runs/train",
    name: str = "left_behind_detector"
):
    """
    Train object detection model
    
    Args:
        data_yaml: Path to dataset YAML file
        base_model: Base model to start from
        epochs: Number of training epochs
        imgsz: Input image size
        batch: Batch size
        project: Project directory
        name: Experiment name
    """
    logger.info("Starting training...")
    logger.info(f"Dataset: {data_yaml}")
    logger.info(f"Base model: {base_model}")
    logger.info(f"Epochs: {epochs}")
    logger.info(f"Image size: {imgsz}")
    logger.info(f"Batch size: {batch}")
    
    # Initialize detector
    detector = LeftBehindObjectDetector(model_path=base_model)
    
    # Train
    results = detector.train(
        data_yaml=data_yaml,
        epochs=epochs,
        imgsz=imgsz,
        batch=batch,
        project=project,
        name=name
    )
    
    logger.info("Training complete!")
    logger.info(f"Results saved to: {project}/{name}")
    
    # Export model
    logger.info("Exporting model...")
    detector.export_model(format="onnx")
    logger.info("Model exported successfully")


def main():
    parser = argparse.ArgumentParser(description="Train object detection model")
    parser.add_argument(
        '--data',
        type=str,
        required=True,
        help='Path to dataset YAML file'
    )
    parser.add_argument(
        '--model',
        type=str,
        default='yolov8n.pt',
        help='Base model to start from'
    )
    parser.add_argument(
        '--epochs',
        type=int,
        default=100,
        help='Number of training epochs'
    )
    parser.add_argument(
        '--imgsz',
        type=int,
        default=640,
        help='Input image size'
    )
    parser.add_argument(
        '--batch',
        type=int,
        default=16,
        help='Batch size'
    )
    parser.add_argument(
        '--project',
        type=str,
        default='runs/train',
        help='Project directory'
    )
    parser.add_argument(
        '--name',
        type=str,
        default='left_behind_detector',
        help='Experiment name'
    )
    
    args = parser.parse_args()
    
    # Validate dataset file exists
    if not Path(args.data).exists():
        logger.error(f"Dataset file not found: {args.data}")
        return
    
    train_model(
        data_yaml=args.data,
        base_model=args.model,
        epochs=args.epochs,
        imgsz=args.imgsz,
        batch=args.batch,
        project=args.project,
        name=args.name
    )


if __name__ == "__main__":
    main()

