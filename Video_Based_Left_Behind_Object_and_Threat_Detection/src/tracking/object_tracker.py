"""
Object Tracking System
Tracks detected objects over time to determine if they are left behind
"""

import numpy as np
from typing import List, Dict, Optional, Tuple
from datetime import datetime, timedelta
from collections import defaultdict
import logging

logger = logging.getLogger(__name__)


class TrackedObject:
    """Represents a tracked object with temporal information"""
    
    def __init__(
        self,
        track_id: int,
        bbox: List[float],
        class_id: int,
        class_name: str,
        confidence: float,
        timestamp: datetime
    ):
        self.track_id = track_id
        self.bbox = bbox
        self.class_id = class_id
        self.class_name = class_name
        self.confidence = confidence
        
        # Temporal information
        self.first_seen = timestamp
        self.last_seen = timestamp
        self.last_update = timestamp
        
        # Tracking state
        self.is_stationary = False
        self.stationary_since = None
        self.is_left_behind = False
        self.left_behind_since = None
        
        # Movement tracking
        self.position_history = [self._get_center(bbox)]
        self.max_history_length = 100
        
        # Alert state
        self.alert_sent = False
        
    def _get_center(self, bbox: List[float]) -> Tuple[float, float]:
        """Get center point of bounding box"""
        x1, y1, x2, y2 = bbox
        return ((x1 + x2) / 2, (y1 + y2) / 2)
    
    def update(
        self,
        bbox: List[float],
        confidence: float,
        timestamp: datetime
    ):
        """Update tracked object with new detection"""
        self.bbox = bbox
        self.confidence = confidence
        self.last_seen = timestamp
        self.last_update = timestamp
        
        # Update position history
        center = self._get_center(bbox)
        self.position_history.append(center)
        
        # Limit history length
        if len(self.position_history) > self.max_history_length:
            self.position_history.pop(0)
    
    def get_movement_distance(self, window: int = 10) -> float:
        """
        Calculate total movement distance over recent frames
        
        Args:
            window: Number of recent frames to consider
            
        Returns:
            Total distance moved
        """
        if len(self.position_history) < 2:
            return 0.0
        
        recent_positions = self.position_history[-window:]
        total_distance = 0.0
        
        for i in range(1, len(recent_positions)):
            x1, y1 = recent_positions[i-1]
            x2, y2 = recent_positions[i]
            distance = np.sqrt((x2 - x1)**2 + (y2 - y1)**2)
            total_distance += distance
        
        return total_distance
    
    def is_moving(self, threshold: float = 10.0, window: int = 10) -> bool:
        """
        Check if object is currently moving
        
        Args:
            threshold: Movement threshold in pixels
            window: Number of frames to check
            
        Returns:
            True if object is moving
        """
        distance = self.get_movement_distance(window)
        return distance > threshold
    
    def update_stationary_status(
        self,
        current_time: datetime,
        movement_threshold: float = 10.0
    ):
        """Update whether object is stationary"""
        is_moving = self.is_moving(threshold=movement_threshold)
        
        if not is_moving:
            if not self.is_stationary:
                # Just became stationary
                self.is_stationary = True
                self.stationary_since = current_time
                logger.debug(f"Object {self.track_id} ({self.class_name}) became stationary")
        else:
            # Object is moving
            if self.is_stationary:
                logger.debug(f"Object {self.track_id} ({self.class_name}) started moving again")
            self.is_stationary = False
            self.stationary_since = None
            self.is_left_behind = False
            self.left_behind_since = None
    
    def check_left_behind(
        self,
        current_time: datetime,
        threshold_minutes: int = 60
    ) -> bool:
        """
        Check if object has been left behind
        
        Args:
            current_time: Current timestamp
            threshold_minutes: Minutes of being stationary to consider left behind
            
        Returns:
            True if object is left behind
        """
        if not self.is_stationary or self.stationary_since is None:
            return False
        
        time_stationary = current_time - self.stationary_since
        threshold = timedelta(minutes=threshold_minutes)
        
        if time_stationary >= threshold:
            if not self.is_left_behind:
                self.is_left_behind = True
                self.left_behind_since = current_time
                logger.info(
                    f"Object {self.track_id} ({self.class_name}) "
                    f"detected as left behind after {threshold_minutes} minutes"
                )
            return True
        
        return False
    
    def get_info(self) -> Dict:
        """Get object information as dictionary"""
        return {
            'track_id': self.track_id,
            'class_name': self.class_name,
            'class_id': self.class_id,
            'bbox': self.bbox,
            'confidence': self.confidence,
            'first_seen': self.first_seen.isoformat(),
            'last_seen': self.last_seen.isoformat(),
            'is_stationary': self.is_stationary,
            'stationary_since': self.stationary_since.isoformat() if self.stationary_since else None,
            'is_left_behind': self.is_left_behind,
            'left_behind_since': self.left_behind_since.isoformat() if self.left_behind_since else None,
            'alert_sent': self.alert_sent
        }


class ObjectTracker:
    """
    Manages tracking of multiple objects using simple IoU-based tracking
    """

    def __init__(
        self,
        iou_threshold: float = 0.3,
        max_age: int = 30,
        min_hits: int = 3,
        movement_threshold: float = 10.0,
        left_behind_threshold_minutes: int = 60
    ):
        """
        Initialize object tracker

        Args:
            iou_threshold: Minimum IoU for matching detections to tracks
            max_age: Maximum frames to keep track alive without detections
            min_hits: Minimum detections before track is confirmed
            movement_threshold: Pixel threshold for movement detection
            left_behind_threshold_minutes: Minutes before object is considered left behind
        """
        self.iou_threshold = iou_threshold
        self.max_age = max_age
        self.min_hits = min_hits
        self.movement_threshold = movement_threshold
        self.left_behind_threshold_minutes = left_behind_threshold_minutes

        self.tracks: Dict[int, TrackedObject] = {}
        self.next_track_id = 1
        self.frame_count = 0

        # Track ages (frames since last detection)
        self.track_ages: Dict[int, int] = defaultdict(int)

        # Track hit counts (number of detections)
        self.track_hits: Dict[int, int] = defaultdict(int)

    def _calculate_iou(self, bbox1: List[float], bbox2: List[float]) -> float:
        """Calculate Intersection over Union between two bounding boxes"""
        x1_1, y1_1, x2_1, y2_1 = bbox1
        x1_2, y1_2, x2_2, y2_2 = bbox2

        # Calculate intersection
        x1_i = max(x1_1, x1_2)
        y1_i = max(y1_1, y1_2)
        x2_i = min(x2_1, x2_2)
        y2_i = min(y2_1, y2_2)

        if x2_i < x1_i or y2_i < y1_i:
            return 0.0

        intersection = (x2_i - x1_i) * (y2_i - y1_i)

        # Calculate union
        area1 = (x2_1 - x1_1) * (y2_1 - y1_1)
        area2 = (x2_2 - x1_2) * (y2_2 - y1_2)
        union = area1 + area2 - intersection

        if union == 0:
            return 0.0

        return intersection / union

    def update(
        self,
        detections: List[Dict],
        timestamp: Optional[datetime] = None
    ) -> List[TrackedObject]:
        """
        Update tracker with new detections

        Args:
            detections: List of detections from object detector
            timestamp: Current timestamp (defaults to now)

        Returns:
            List of active tracked objects
        """
        if timestamp is None:
            timestamp = datetime.now()

        self.frame_count += 1

        # Match detections to existing tracks
        matched_tracks = set()
        matched_detections = set()

        # Calculate IoU matrix
        for det_idx, detection in enumerate(detections):
            best_iou = 0.0
            best_track_id = None

            for track_id, track in self.tracks.items():
                # Only match same class
                if track.class_id != detection['class_id']:
                    continue

                iou = self._calculate_iou(detection['bbox'], track.bbox)

                if iou > best_iou and iou >= self.iou_threshold:
                    best_iou = iou
                    best_track_id = track_id

            # Update matched track
            if best_track_id is not None:
                self.tracks[best_track_id].update(
                    detection['bbox'],
                    detection['confidence'],
                    timestamp
                )
                matched_tracks.add(best_track_id)
                matched_detections.add(det_idx)

                # Reset age and increment hits
                self.track_ages[best_track_id] = 0
                self.track_hits[best_track_id] += 1

        # Create new tracks for unmatched detections
        for det_idx, detection in enumerate(detections):
            if det_idx not in matched_detections:
                track_id = self.next_track_id
                self.next_track_id += 1

                new_track = TrackedObject(
                    track_id=track_id,
                    bbox=detection['bbox'],
                    class_id=detection['class_id'],
                    class_name=detection['class_name'],
                    confidence=detection['confidence'],
                    timestamp=timestamp
                )

                self.tracks[track_id] = new_track
                self.track_ages[track_id] = 0
                self.track_hits[track_id] = 1

        # Increment age for unmatched tracks
        for track_id in list(self.tracks.keys()):
            if track_id not in matched_tracks:
                self.track_ages[track_id] += 1

        # Remove old tracks
        tracks_to_remove = []
        for track_id, age in self.track_ages.items():
            if age > self.max_age:
                tracks_to_remove.append(track_id)

        for track_id in tracks_to_remove:
            logger.debug(f"Removing track {track_id} (age: {self.track_ages[track_id]})")
            del self.tracks[track_id]
            del self.track_ages[track_id]
            del self.track_hits[track_id]

        # Update stationary status for all tracks
        for track in self.tracks.values():
            track.update_stationary_status(timestamp, self.movement_threshold)

        # Return confirmed tracks only
        confirmed_tracks = [
            track for track_id, track in self.tracks.items()
            if self.track_hits[track_id] >= self.min_hits
        ]

        return confirmed_tracks

    def get_left_behind_objects(
        self,
        current_time: Optional[datetime] = None
    ) -> List[TrackedObject]:
        """
        Get list of objects that have been left behind

        Args:
            current_time: Current timestamp (defaults to now)

        Returns:
            List of left-behind objects
        """
        if current_time is None:
            current_time = datetime.now()

        left_behind = []

        for track_id, track in self.tracks.items():
            # Only check confirmed tracks
            if self.track_hits[track_id] < self.min_hits:
                continue

            if track.check_left_behind(current_time, self.left_behind_threshold_minutes):
                left_behind.append(track)

        return left_behind

    def reset(self):
        """Reset tracker state"""
        self.tracks.clear()
        self.track_ages.clear()
        self.track_hits.clear()
        self.next_track_id = 1
        self.frame_count = 0


if __name__ == "__main__":
    # Example usage
    import cv2
    from src.models.object_detector import LeftBehindObjectDetector

    # Initialize detector and tracker
    detector = LeftBehindObjectDetector()
    tracker = ObjectTracker(left_behind_threshold_minutes=1)  # 1 minute for testing

    # Process video
    cap = cv2.VideoCapture("test_video.mp4")

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        # Detect objects
        detections = detector.detect(frame)

        # Update tracker
        tracked_objects = tracker.update(detections)

        # Check for left-behind objects
        left_behind = tracker.get_left_behind_objects()

        # Visualize
        for track in tracked_objects:
            x1, y1, x2, y2 = map(int, track.bbox)
            color = (0, 0, 255) if track.is_left_behind else (0, 255, 0)
            cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)

            label = f"ID:{track.track_id} {track.class_name}"
            if track.is_left_behind:
                label += " [LEFT BEHIND]"

            cv2.putText(frame, label, (x1, y1-10),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)

        cv2.imshow("Tracking", frame)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()


