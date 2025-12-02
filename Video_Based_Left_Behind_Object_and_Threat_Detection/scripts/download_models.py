"""
Download pre-trained models for the security system
"""

import argparse
import os
from pathlib import Path
import urllib.request
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def download_file(url: str, destination: str):
    """Download file with progress bar"""
    def progress_hook(count, block_size, total_size):
        percent = int(count * block_size * 100 / total_size)
        print(f"\rDownloading: {percent}%", end='')
    
    urllib.request.urlretrieve(url, destination, progress_hook)
    print()  # New line after download


def download_yolov8(model_size: str = "n"):
    """
    Download YOLOv8 model
    
    Args:
        model_size: Model size (n, s, m, l, x)
    """
    models_dir = Path("models")
    models_dir.mkdir(exist_ok=True)
    
    model_name = f"yolov8{model_size}.pt"
    model_path = models_dir / model_name
    
    if model_path.exists():
        logger.info(f"{model_name} already exists")
        return
    
    logger.info(f"Downloading {model_name}...")
    
    # YOLOv8 will auto-download when first used
    from ultralytics import YOLO
    model = YOLO(model_name)
    
    logger.info(f"Downloaded {model_name} successfully")


def download_slowfast():
    """Download SlowFast model"""
    models_dir = Path("models")
    models_dir.mkdir(exist_ok=True)
    
    logger.info("SlowFast models will be downloaded automatically when first used")
    logger.info("Or you can download from: https://github.com/facebookresearch/SlowFast")


def main():
    parser = argparse.ArgumentParser(description="Download pre-trained models")
    parser.add_argument(
        '--model',
        type=str,
        choices=['yolov8n', 'yolov8s', 'yolov8m', 'yolov8l', 'yolov8x', 'slowfast', 'all'],
        default='yolov8n',
        help='Model to download'
    )
    
    args = parser.parse_args()
    
    if args.model == 'all':
        download_yolov8('n')
        download_slowfast()
    elif args.model.startswith('yolov8'):
        size = args.model[-1]
        download_yolov8(size)
    elif args.model == 'slowfast':
        download_slowfast()
    
    logger.info("Model download complete!")


if __name__ == "__main__":
    main()

