"""
Main Application - Video-Based Left Behind Object and Threat Detection System
Integrates all components: object detection, threat detection, tracking, and alerts
"""

import cv2
import yaml
import logging
from pathlib import Path
from datetime import datetime
from typing import Dict, List
import argparse
from dotenv import load_dotenv
import os

from src.models.object_detector import LeftBehindObjectDetector
from src.models.threat_detector import ThreatDetector
from src.tracking.object_tracker import ObjectTracker
from src.notifications.alert_system import AlertSystem

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('logs/system.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)


class SchoolSecuritySystem:
    """
    Main system integrating all components
    """
    
    def __init__(self, config_path: str = "config/config.yaml"):
        """
        Initialize the security system
        
        Args:
            config_path: Path to configuration file
        """
        logger.info("Initializing School Security System...")
        
        # Load configuration
        with open(config_path, 'r') as f:
            self.config = yaml.safe_load(f)
        
        # Load environment variables
        load_dotenv()
        
        # Initialize object detector
        logger.info("Loading object detection model...")
        self.object_detector = LeftBehindObjectDetector(
            model_path=self.config['object_detection']['model']['weights'],
            confidence_threshold=self.config['object_detection']['model']['confidence_threshold'],
            target_classes=self.config['object_detection']['target_classes']
        )
        
        # Initialize threat detector
        logger.info("Loading threat detection model...")
        self.threat_detector = ThreatDetector(
            model_path=self.config['threat_detection']['model']['weights'],
            model_type=self.config['threat_detection']['model']['type'],
            confidence_threshold=self.config['threat_detection']['model']['confidence_threshold'],
            clip_length=self.config['threat_detection']['model']['clip_length']
        )
        
        # Initialize object tracker
        logger.info("Initializing object tracker...")
        self.object_tracker = ObjectTracker(
            iou_threshold=self.config['tracking']['iou_threshold'],
            max_age=self.config['tracking']['max_age'],
            min_hits=self.config['tracking']['min_hits'],
            left_behind_threshold_minutes=self.config['object_detection']['left_behind_threshold']
        )
        
        # Initialize alert system
        logger.info("Initializing alert system...")
        self.alert_system = AlertSystem(
            smtp_server=os.getenv('SMTP_SERVER'),
            smtp_port=int(os.getenv('SMTP_PORT', 587)),
            smtp_username=os.getenv('SMTP_USERNAME'),
            smtp_password=os.getenv('SMTP_PASSWORD'),
            from_email=os.getenv('SMTP_USERNAME')
        )
        
        # Camera configurations
        self.cameras = {cam['id']: cam for cam in self.config['cameras'] if cam['enabled']}
        
        # Frame skip for performance
        self.frame_skip = self.config['performance']['frame_skip']
        self.frame_count = 0
        
        logger.info("System initialized successfully!")
    
    def process_frame_for_objects(
        self,
        frame,
        camera_id: str
    ) -> List[Dict]:
        """
        Process frame for left-behind object detection
        
        Args:
            frame: Input frame
            camera_id: Camera identifier
            
        Returns:
            List of tracked objects
        """
        # Detect objects
        detections = self.object_detector.detect(frame)
        
        # Filter by minimum size
        min_size = self.config['object_detection']['min_object_size']
        detections = self.object_detector.filter_by_size(detections, min_size)
        
        # Update tracker
        tracked_objects = self.object_tracker.update(detections)
        
        # Check for left-behind objects
        left_behind = self.object_tracker.get_left_behind_objects()
        
        # Send alerts for new left-behind objects
        for obj in left_behind:
            if not obj.alert_sent:
                self._send_left_behind_alert(obj, camera_id, frame)
                obj.alert_sent = True
        
        return tracked_objects
    
    def process_frame_for_threats(
        self,
        frame,
        camera_id: str
    ) -> Dict:
        """
        Process frame for threat detection
        
        Args:
            frame: Input frame
            camera_id: Camera identifier
            
        Returns:
            Threat detection result
        """
        # Detect threats
        result = self.threat_detector.detect(frame)
        
        # Send alert if threat detected
        if result['is_threat']:
            self._send_threat_alert(result, camera_id, frame)
        
        return result
    
    def _send_left_behind_alert(
        self,
        obj,
        camera_id: str,
        frame
    ):
        """Send alert for left-behind object"""
        camera_info = self.cameras[camera_id]
        
        # Save snapshot
        snapshot_dir = Path(self.config['storage']['snapshots_path'])
        snapshot_dir.mkdir(parents=True, exist_ok=True)
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        snapshot_path = snapshot_dir / f"{camera_id}_leftbehind_{timestamp}.jpg"

        # Draw bounding box and save
        annotated = self.object_detector.visualize_detections(frame, [obj.get_info()])
        cv2.imwrite(str(snapshot_path), annotated)

        # Prepare notification
        recipients = {
            'email': self.config['notifications']['left_behind_objects']['recipients'].get('email', []),
            'telegram': self.config['notifications']['left_behind_objects']['recipients'].get('telegram', []),
            'sms': self.config['notifications']['left_behind_objects']['recipients'].get('sms', [])
        }

        self.alert_system.send_left_behind_alert(
            object_info=obj.get_info(),
            camera_info=camera_info,
            recipients=recipients,
            image_path=str(snapshot_path),
            cooldown_minutes=self.config['notifications']['left_behind_objects']['cooldown_minutes']
        )

    def _send_threat_alert(
        self,
        threat_result: Dict,
        camera_id: str,
        frame
    ):
        """Send alert for detected threat"""
        camera_info = self.cameras[camera_id]

        # Save snapshot
        snapshot_dir = Path(self.config['storage']['snapshots_path'])
        snapshot_dir.mkdir(parents=True, exist_ok=True)

        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        snapshot_path = snapshot_dir / f"{camera_id}_threat_{timestamp}.jpg"

        # Annotate and save
        annotated = self.threat_detector.visualize_result(frame, threat_result)
        cv2.imwrite(str(snapshot_path), annotated)

        # Prepare notification
        recipients = {
            'email': self.config['notifications']['threats']['recipients'].get('email', []),
            'telegram': self.config['notifications']['threats']['recipients'].get('telegram', []),
            'sms': self.config['notifications']['threats']['recipients'].get('sms', [])
        }

        self.alert_system.send_threat_alert(
            threat_info=threat_result,
            camera_info=camera_info,
            recipients=recipients,
            image_path=str(snapshot_path),
            cooldown_minutes=self.config['notifications']['threats']['cooldown_minutes']
        )

    def process_camera(self, camera_id: str, source=0):
        """
        Process video stream from a camera

        Args:
            camera_id: Camera identifier
            source: Video source (0 for webcam, path for video file, URL for stream)
        """
        logger.info(f"Starting processing for camera {camera_id}")

        # Open video source
        cap = cv2.VideoCapture(source)

        if not cap.isOpened():
            logger.error(f"Failed to open video source: {source}")
            return

        try:
            while True:
                ret, frame = cap.read()
                if not ret:
                    logger.warning("Failed to read frame")
                    break

                self.frame_count += 1

                # Skip frames for performance
                if self.frame_count % self.frame_skip != 0:
                    continue

                # Process for left-behind objects
                tracked_objects = self.process_frame_for_objects(frame, camera_id)

                # Process for threats
                threat_result = self.process_frame_for_threats(frame, camera_id)

                # Visualize results
                display_frame = frame.copy()

                # Draw tracked objects
                for obj in tracked_objects:
                    x1, y1, x2, y2 = map(int, obj.bbox)
                    color = (0, 0, 255) if obj.is_left_behind else (0, 255, 0)
                    cv2.rectangle(display_frame, (x1, y1), (x2, y2), color, 2)

                    label = f"ID:{obj.track_id} {obj.class_name}"
                    if obj.is_left_behind:
                        label += " [LEFT BEHIND]"

                    cv2.putText(display_frame, label, (x1, y1-10),
                               cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)

                # Draw threat status
                if threat_result['is_threat']:
                    cv2.putText(display_frame, f"THREAT: {threat_result['threat_type']}",
                               (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

                # Display
                cv2.imshow(f"Camera {camera_id}", display_frame)

                # Exit on 'q' key
                if cv2.waitKey(1) & 0xFF == ord('q'):
                    break

        except KeyboardInterrupt:
            logger.info("Processing interrupted by user")
        finally:
            cap.release()
            cv2.destroyAllWindows()
            logger.info(f"Stopped processing camera {camera_id}")

    def run(self):
        """Run the system for all configured cameras"""
        logger.info("Starting School Security System...")

        # For now, process first camera
        # In production, use multiprocessing for multiple cameras
        if self.cameras:
            first_camera = list(self.cameras.values())[0]
            camera_id = first_camera['id']

            # Determine source
            if 'stream_url' in first_camera:
                source = first_camera['stream_url']
            elif 'ip' in first_camera:
                source = f"http://{first_camera['ip']}/stream"
            else:
                source = 0  # Default to webcam

            self.process_camera(camera_id, source)
        else:
            logger.error("No cameras configured!")


def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(
        description="Video-Based Left Behind Object and Threat Detection System"
    )
    parser.add_argument(
        '--config',
        type=str,
        default='config/config.yaml',
        help='Path to configuration file'
    )
    parser.add_argument(
        '--camera',
        type=str,
        help='Specific camera ID to process'
    )
    parser.add_argument(
        '--source',
        type=str,
        help='Video source (file path, URL, or camera index)'
    )

    args = parser.parse_args()

    # Create necessary directories
    Path('logs').mkdir(exist_ok=True)
    Path('data/snapshots').mkdir(parents=True, exist_ok=True)
    Path('data/videos').mkdir(parents=True, exist_ok=True)

    # Initialize and run system
    system = SchoolSecuritySystem(args.config)

    if args.camera and args.source:
        system.process_camera(args.camera, args.source)
    else:
        system.run()


if __name__ == "__main__":
    main()


