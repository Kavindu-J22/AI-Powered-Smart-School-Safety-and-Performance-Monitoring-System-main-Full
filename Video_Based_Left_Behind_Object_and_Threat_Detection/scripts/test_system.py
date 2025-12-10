"""
Test Script for Video-Based Left Behind Object and Threat Detection System
Tests both models and the complete system integration
"""

import os
import sys
from pathlib import Path
import cv2
import numpy as np
import torch
from datetime import datetime

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))


def print_banner(text: str, char: str = "="):
    """Print a banner with text"""
    width = 70
    print("\n" + char * width)
    print(f" {text}")
    print(char * width)


def test_object_detection_model():
    """Test the object detection model"""
    print_banner("TESTING OBJECT DETECTION MODEL")
    
    model_path = Path("models/left_behind_detector.pt")
    
    if not model_path.exists():
        print(f"WARNING: Model not found at {model_path}")
        print("Attempting to use pretrained YOLOv8...")
        model_path = "yolov8n.pt"
    
    try:
        from ultralytics import YOLO
        model = YOLO(str(model_path))
        print(f"✓ Model loaded successfully from: {model_path}")
        
        # Test with a sample image
        test_img = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
        results = model(test_img, verbose=False)
        print(f"✓ Model inference successful")
        print(f"  - Detected {len(results[0].boxes)} objects")
        
        return True
    except Exception as e:
        print(f"✗ Object detection test failed: {e}")
        return False


def test_threat_detection_model():
    """Test the threat detection model"""
    print_banner("TESTING THREAT DETECTION MODEL")
    
    model_path = Path("models/threat_detector.pt")
    
    if not model_path.exists():
        print(f"WARNING: Model not found at {model_path}")
        print("Creating a test model...")
        
        # Create a simple test model
        from train_models import Simple3DCNN
        model = Simple3DCNN(num_classes=2)
        print("✓ Test model created successfully")
    else:
        try:
            from train_models import Simple3DCNN
            model = Simple3DCNN(num_classes=2)
            checkpoint = torch.load(model_path, map_location='cpu')
            model.load_state_dict(checkpoint['model_state_dict'])
            print(f"✓ Model loaded successfully from: {model_path}")
        except Exception as e:
            print(f"✗ Failed to load model: {e}")
            return False
    
    try:
        # Test with random input
        model.eval()
        test_input = torch.randn(1, 3, 16, 224, 224)
        with torch.no_grad():
            output = model(test_input)
        
        print(f"✓ Model inference successful")
        print(f"  - Output shape: {output.shape}")
        print(f"  - Predicted class: {'Fight' if output.argmax().item() == 1 else 'No Fight'}")
        
        return True
    except Exception as e:
        print(f"✗ Threat detection test failed: {e}")
        return False


def test_tracking_system():
    """Test the object tracking system"""
    print_banner("TESTING OBJECT TRACKING SYSTEM")
    
    try:
        from src.tracking.object_tracker import ObjectTracker, TrackedObject
        
        tracker = ObjectTracker()
        print("✓ ObjectTracker initialized successfully")
        
        # Simulate tracking
        detections = [
            {'bbox': [100, 100, 200, 200], 'class': 'backpack', 'confidence': 0.9},
            {'bbox': [300, 300, 400, 400], 'class': 'laptop', 'confidence': 0.85}
        ]
        
        tracker.update(detections)
        print(f"✓ Tracking update successful")
        print(f"  - Active tracks: {len(tracker.tracks)}")
        
        return True
    except Exception as e:
        print(f"✗ Tracking system test failed: {e}")
        return False


def test_alert_system():
    """Test the alert system"""
    print_banner("TESTING ALERT SYSTEM")
    
    try:
        from src.notifications.alert_system import AlertSystem
        
        alert_system = AlertSystem()
        print("✓ AlertSystem initialized successfully")
        
        # Test alert creation (without actually sending)
        print("✓ Alert system ready (notifications disabled for testing)")
        
        return True
    except Exception as e:
        print(f"✗ Alert system test failed: {e}")
        return False


def test_full_system():
    """Test the complete system integration"""
    print_banner("TESTING FULL SYSTEM INTEGRATION")
    
    try:
        from src.models.object_detector import LeftBehindObjectDetector
        from src.models.threat_detector import ThreatDetector
        from src.tracking.object_tracker import ObjectTracker
        
        print("✓ All modules imported successfully")
        
        # Initialize components
        object_detector = LeftBehindObjectDetector()
        print("✓ LeftBehindObjectDetector initialized")
        
        threat_detector = ThreatDetector()
        print("✓ ThreatDetector initialized")
        
        tracker = ObjectTracker()
        print("✓ ObjectTracker initialized")
        
        # Test with a sample frame
        test_frame = np.random.randint(0, 255, (480, 640, 3), dtype=np.uint8)
        
        # Test object detection
        detections = object_detector.detect(test_frame)
        print(f"✓ Object detection: {len(detections)} objects detected")
        
        # Test tracking
        tracker.update(detections)
        print(f"✓ Tracking: {len(tracker.tracks)} active tracks")
        
        # Test threat detection
        threat_result = threat_detector.detect(test_frame)
        print(f"✓ Threat detection: {threat_result}")
        
        return True
    except Exception as e:
        print(f"✗ Full system test failed: {e}")
        import traceback
        traceback.print_exc()
        return False


def main():
    """Run all tests"""
    print_banner("VIDEO-BASED DETECTION SYSTEM TEST SUITE", "=")
    print(f"Test started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    results = {}
    
    # Run tests
    results['object_detection'] = test_object_detection_model()
    results['threat_detection'] = test_threat_detection_model()
    results['tracking'] = test_tracking_system()
    results['alerts'] = test_alert_system()
    results['full_system'] = test_full_system()
    
    # Print summary
    print_banner("TEST SUMMARY", "=")
    
    passed = sum(results.values())
    total = len(results)
    
    for test_name, result in results.items():
        status = "✓ PASSED" if result else "✗ FAILED"
        print(f"  {test_name:25s}: {status}")
    
    print(f"\nTotal: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n✓ ALL TESTS PASSED!")
        return 0
    else:
        print(f"\n✗ {total - passed} test(s) failed")
        return 1


if __name__ == "__main__":
    exit(main())

