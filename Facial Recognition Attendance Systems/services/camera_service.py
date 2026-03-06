"""
Camera Service
===============
Handles camera operations and video streaming.
"""

import cv2
import numpy as np
from typing import Optional, Generator, Callable, Tuple
from threading import Thread, Event, Lock
import time
import logging
from queue import Queue, Empty
from collections import deque

logger = logging.getLogger(__name__)


class CameraService:
    """
    Service for camera operations and video streaming.
    
    Features:
    - Multiple camera support
    - Threaded frame capture
    - Frame skipping for performance
    - MJPEG streaming
    """
    
    def __init__(
        self,
        camera_index: int = 0,
        width: int = 1280,
        height: int = 720,
        fps: int = 30,
        buffer_size: int = 2
    ):
        """
        Initialize camera service.
        
        Args:
            camera_index: Camera device index
            width: Frame width
            height: Frame height
            fps: Target FPS
            buffer_size: Frame buffer size
        """
        self.camera_index = camera_index
        self.width = width
        self.height = height
        self.fps = fps
        self.buffer_size = buffer_size
        
        self._cap = None
        self._frame_buffer = deque(maxlen=buffer_size)
        self._lock = Lock()
        self._running = Event()
        self._capture_thread = None
        
        # Stats
        self._fps_counter = deque(maxlen=30)
        self._last_frame_time = time.time()
    
    def start(self) -> bool:
        """
        Start camera capture.
        
        Returns:
            True if successful
        """
        if self._running.is_set():
            return True
        
        try:
            self._cap = cv2.VideoCapture(self.camera_index)
            
            if not self._cap.isOpened():
                logger.error(f"Failed to open camera {self.camera_index}")
                return False
            
            # Set camera properties
            self._cap.set(cv2.CAP_PROP_FRAME_WIDTH, self.width)
            self._cap.set(cv2.CAP_PROP_FRAME_HEIGHT, self.height)
            self._cap.set(cv2.CAP_PROP_FPS, self.fps)
            self._cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)  # Minimize latency
            
            # Start capture thread
            self._running.set()
            self._capture_thread = Thread(target=self._capture_loop, daemon=True)
            self._capture_thread.start()
            
            logger.info(f"Camera {self.camera_index} started")
            return True
            
        except Exception as e:
            logger.error(f"Camera start error: {e}")
            return False
    
    def stop(self):
        """Stop camera capture."""
        self._running.clear()
        
        if self._capture_thread:
            self._capture_thread.join(timeout=2)
        
        if self._cap:
            self._cap.release()
            self._cap = None
        
        self._frame_buffer.clear()
        logger.info("Camera stopped")
    
    def _capture_loop(self):
        """Background capture loop."""
        while self._running.is_set():
            if self._cap is None:
                break
            
            ret, frame = self._cap.read()
            
            if not ret:
                logger.warning("Failed to read frame")
                time.sleep(0.01)
                continue
            
            # Update buffer
            with self._lock:
                self._frame_buffer.append(frame)
            
            # Update FPS counter
            current_time = time.time()
            self._fps_counter.append(current_time - self._last_frame_time)
            self._last_frame_time = current_time
            
            # Small sleep to prevent CPU hogging
            time.sleep(0.001)
    
    def get_frame(self) -> Optional[np.ndarray]:
        """
        Get the latest frame.
        
        Returns:
            Latest frame or None
        """
        with self._lock:
            if self._frame_buffer:
                return self._frame_buffer[-1].copy()
        return None
    
    def read(self) -> Tuple[bool, Optional[np.ndarray]]:
        """
        Read a frame (cv2.VideoCapture compatible interface).
        
        Returns:
            (success, frame)
        """
        frame = self.get_frame()
        return (frame is not None, frame)
    
    def get_fps(self) -> float:
        """Get current FPS."""
        if len(self._fps_counter) < 2:
            return 0.0
        avg_interval = sum(self._fps_counter) / len(self._fps_counter)
        return 1.0 / avg_interval if avg_interval > 0 else 0.0
    
    def is_opened(self) -> bool:
        """Check if camera is opened."""
        return self._running.is_set() and self._cap is not None
    
    def generate_frames(
        self,
        processor: Callable[[np.ndarray], np.ndarray] = None,
        quality: int = 80
    ) -> Generator[bytes, None, None]:
        """
        Generate MJPEG frames for streaming.
        
        Args:
            processor: Optional function to process frames
            quality: JPEG quality (1-100)
            
        Yields:
            MJPEG frame bytes
        """
        encode_params = [cv2.IMWRITE_JPEG_QUALITY, quality]
        
        while self._running.is_set():
            frame = self.get_frame()
            
            if frame is None:
                time.sleep(0.01)
                continue
            
            # Apply processor if provided
            if processor:
                try:
                    frame = processor(frame)
                except Exception as e:
                    logger.error(f"Frame processor error: {e}")
            
            # Encode to JPEG
            _, buffer = cv2.imencode('.jpg', frame, encode_params)
            frame_bytes = buffer.tobytes()
            
            yield (
                b'--frame\r\n'
                b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n'
            )
            
            # Rate limiting
            time.sleep(1.0 / self.fps)
    
    def capture_image(self, filename: str = None) -> Tuple[bool, Optional[str]]:
        """
        Capture a single image.
        
        Args:
            filename: Optional filename to save
            
        Returns:
            (success, filename or None)
        """
        frame = self.get_frame()
        
        if frame is None:
            return False, None
        
        if filename:
            cv2.imwrite(filename, frame)
            return True, filename
        
        # Generate filename
        timestamp = time.strftime("%Y%m%d_%H%M%S")
        filename = f"capture_{timestamp}.jpg"
        cv2.imwrite(filename, frame)
        
        return True, filename
    
    def get_info(self) -> dict:
        """Get camera information."""
        info = {
            'index': self.camera_index,
            'is_opened': self.is_opened(),
            'target_resolution': f'{self.width}x{self.height}',
            'target_fps': self.fps,
            'current_fps': round(self.get_fps(), 1)
        }
        
        if self._cap:
            info['actual_width'] = int(self._cap.get(cv2.CAP_PROP_FRAME_WIDTH))
            info['actual_height'] = int(self._cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
            info['actual_fps'] = self._cap.get(cv2.CAP_PROP_FPS)
        
        return info


class MultiCameraService:
    """
    Manages multiple cameras for attendance at different locations.
    """
    
    def __init__(self):
        self._cameras: dict[str, CameraService] = {}
    
    def add_camera(
        self,
        camera_id: str,
        camera_index: int,
        **kwargs
    ) -> bool:
        """Add a camera."""
        if camera_id in self._cameras:
            return False
        
        camera = CameraService(camera_index=camera_index, **kwargs)
        self._cameras[camera_id] = camera
        return True
    
    def start_camera(self, camera_id: str) -> bool:
        """Start a specific camera."""
        camera = self._cameras.get(camera_id)
        if camera:
            return camera.start()
        return False
    
    def stop_camera(self, camera_id: str):
        """Stop a specific camera."""
        camera = self._cameras.get(camera_id)
        if camera:
            camera.stop()
    
    def start_all(self):
        """Start all cameras."""
        for camera in self._cameras.values():
            camera.start()
    
    def stop_all(self):
        """Stop all cameras."""
        for camera in self._cameras.values():
            camera.stop()
    
    def get_frame(self, camera_id: str) -> Optional[np.ndarray]:
        """Get frame from specific camera."""
        camera = self._cameras.get(camera_id)
        if camera:
            return camera.get_frame()
        return None
    
    def get_camera(self, camera_id: str) -> Optional[CameraService]:
        """Get camera instance."""
        return self._cameras.get(camera_id)
    
    def list_cameras(self) -> list:
        """List all cameras."""
        return [
            {'id': cam_id, **cam.get_info()}
            for cam_id, cam in self._cameras.items()
        ]
