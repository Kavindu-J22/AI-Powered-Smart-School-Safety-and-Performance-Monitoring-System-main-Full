"""
Test camera connectivity and stream quality
"""

import cv2
import argparse
import yaml
import logging
from pathlib import Path

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def test_camera(camera_id: str, config_path: str = "config/config.yaml"):
    """
    Test camera connection and display stream
    
    Args:
        camera_id: Camera identifier
        config_path: Path to configuration file
    """
    # Load configuration
    with open(config_path, 'r') as f:
        config = yaml.safe_load(f)
    
    # Find camera
    camera = None
    for cam in config['cameras']:
        if cam['id'] == camera_id:
            camera = cam
            break
    
    if not camera:
        logger.error(f"Camera {camera_id} not found in configuration")
        return
    
    logger.info(f"Testing camera: {camera['name']}")
    logger.info(f"Location: {camera['location']}")
    
    # Determine source
    if 'stream_url' in camera:
        source = camera['stream_url']
    elif 'ip' in camera:
        source = f"http://{camera['ip']}/stream"
    else:
        logger.error("No stream URL or IP configured")
        return
    
    logger.info(f"Connecting to: {source}")
    
    # Open video stream
    cap = cv2.VideoCapture(source)
    
    if not cap.isOpened():
        logger.error("Failed to open camera stream")
        logger.info("Troubleshooting tips:")
        logger.info("1. Check if camera is powered on")
        logger.info("2. Verify network connectivity (ping the IP)")
        logger.info("3. Check if stream URL is correct")
        logger.info("4. Ensure firewall allows connection")
        return
    
    logger.info("✓ Camera connected successfully!")
    
    # Get stream properties
    width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    fps = int(cap.get(cv2.CAP_PROP_FPS))
    
    logger.info(f"Stream properties:")
    logger.info(f"  Resolution: {width}x{height}")
    logger.info(f"  FPS: {fps}")
    
    logger.info("\nDisplaying stream... Press 'q' to quit")
    
    frame_count = 0
    
    try:
        while True:
            ret, frame = cap.read()
            
            if not ret:
                logger.warning("Failed to read frame")
                break
            
            frame_count += 1
            
            # Add info overlay
            cv2.putText(frame, f"Camera: {camera['name']}", (10, 30),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
            cv2.putText(frame, f"Frame: {frame_count}", (10, 60),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
            cv2.putText(frame, f"Resolution: {width}x{height}", (10, 90),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
            
            cv2.imshow(f"Camera Test - {camera_id}", frame)
            
            if cv2.waitKey(1) & 0xFF == ord('q'):
                break
                
    except KeyboardInterrupt:
        logger.info("\nTest interrupted by user")
    finally:
        cap.release()
        cv2.destroyAllWindows()
        logger.info(f"Test complete. Processed {frame_count} frames")


def main():
    parser = argparse.ArgumentParser(description="Test camera connectivity")
    parser.add_argument(
        '--camera',
        type=str,
        required=True,
        help='Camera ID to test'
    )
    parser.add_argument(
        '--config',
        type=str,
        default='config/config.yaml',
        help='Path to configuration file'
    )
    
    args = parser.parse_args()
    
    test_camera(args.camera, args.config)


if __name__ == "__main__":
    main()

