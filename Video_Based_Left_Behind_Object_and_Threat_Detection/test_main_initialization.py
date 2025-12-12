"""
Test Main Application Initialization
Tests that main.py can initialize all components correctly
"""

import sys
import logging
from pathlib import Path

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

def print_banner(text, char="="):
    """Print a banner"""
    width = 70
    print("\n" + char * width)
    print(f" {text}")
    print(char * width)

def test_imports():
    """Test that all imports work"""
    print_banner("1. TESTING IMPORTS")
    
    try:
        import cv2
        print(f"✓ OpenCV version: {cv2.__version__}")
    except Exception as e:
        print(f"✗ OpenCV import failed: {e}")
        return False
    
    try:
        import yaml
        print(f"✓ PyYAML imported")
    except Exception as e:
        print(f"✗ PyYAML import failed: {e}")
        return False
    
    try:
        from src.models.object_detector import LeftBehindObjectDetector
        print(f"✓ LeftBehindObjectDetector imported")
    except Exception as e:
        print(f"✗ LeftBehindObjectDetector import failed: {e}")
        return False
    
    try:
        from src.models.threat_detector import ThreatDetector
        print(f"✓ ThreatDetector imported")
    except Exception as e:
        print(f"✗ ThreatDetector import failed: {e}")
        return False
    
    try:
        from src.tracking.object_tracker import ObjectTracker
        print(f"✓ ObjectTracker imported")
    except Exception as e:
        print(f"✗ ObjectTracker import failed: {e}")
        return False
    
    try:
        from src.notifications.alert_system import AlertSystem
        print(f"✓ AlertSystem imported")
    except Exception as e:
        print(f"✗ AlertSystem import failed: {e}")
        return False
    
    return True

def test_system_initialization():
    """Test system initialization"""
    print_banner("2. TESTING SYSTEM INITIALIZATION")
    
    try:
        # Create necessary directories
        Path('logs').mkdir(exist_ok=True)
        Path('data/snapshots').mkdir(parents=True, exist_ok=True)
        Path('data/videos').mkdir(parents=True, exist_ok=True)
        print("✓ Directories created")
        
        # Import main system
        from main import SchoolSecuritySystem
        print("✓ SchoolSecuritySystem imported")
        
        # Initialize system
        system = SchoolSecuritySystem("config/config.yaml")
        print("✓ System initialized successfully")
        
        # Check components
        if hasattr(system, 'object_detector'):
            print(f"✓ Object detector loaded")
        else:
            print(f"✗ Object detector not found")
            return False
        
        if hasattr(system, 'threat_detector'):
            print(f"✓ Threat detector loaded")
        else:
            print(f"✗ Threat detector not found")
            return False
        
        if hasattr(system, 'tracker'):
            print(f"✓ Tracker initialized")
        else:
            print(f"✗ Tracker not found")
            return False
        
        if hasattr(system, 'alert_system'):
            print(f"✓ Alert system initialized")
        else:
            print(f"✗ Alert system not found")
            return False
        
        return True
        
    except Exception as e:
        print(f"✗ System initialization failed: {e}")
        import traceback
        traceback.print_exc()
        return False

def test_model_files():
    """Test that model files exist"""
    print_banner("3. TESTING MODEL FILES")
    
    model_files = {
        "Object Detection Model": "models/left_behind_detector.pt",
        "Threat Detection Model": "models/threat_detector.pt"
    }
    
    all_exist = True
    for name, path in model_files.items():
        if Path(path).exists():
            size_mb = Path(path).stat().st_size / (1024 * 1024)
            print(f"✓ {name}: {path} ({size_mb:.2f} MB)")
        else:
            print(f"✗ {name} NOT FOUND: {path}")
            all_exist = False
    
    return all_exist

def main():
    print_banner("MAIN APPLICATION INITIALIZATION TEST", "=")
    
    results = {}
    
    # Test 1: Imports
    results['imports'] = test_imports()
    
    # Test 2: Model files
    results['model_files'] = test_model_files()
    
    # Test 3: System initialization
    results['initialization'] = test_system_initialization()
    
    # Summary
    print_banner("TEST SUMMARY", "=")
    
    passed = sum(results.values())
    total = len(results)
    
    for test_name, result in results.items():
        status = "✓ PASSED" if result else "✗ FAILED"
        print(f"  {test_name:20s}: {status}")
    
    print(f"\nTotal: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n✅ ALL TESTS PASSED!")
        print("\nThe main application is ready to run.")
        print("\nTo run with a video file:")
        print("  python main.py --camera CAM_001 --source path/to/video.mp4")
        print("\nTo run with a webcam:")
        print("  python main.py --camera CAM_001 --source 0")
        return 0
    else:
        print(f"\n✗ {total - passed} test(s) failed")
        print("Please fix the issues above before running main.py")
        return 1

if __name__ == "__main__":
    exit(main())

