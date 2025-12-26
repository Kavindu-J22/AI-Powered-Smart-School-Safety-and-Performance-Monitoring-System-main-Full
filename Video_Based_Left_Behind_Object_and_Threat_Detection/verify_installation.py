#!/usr/bin/env python3
"""
Verify that all required dependencies are installed correctly
"""

import sys

def check_import(module_name, package_name=None):
    """Try to import a module and report status"""
    if package_name is None:
        package_name = module_name
    
    try:
        module = __import__(module_name)
        version = getattr(module, '__version__', 'unknown')
        print(f"✓ {package_name:20s} - Version: {version}")
        return True
    except ImportError as e:
        print(f"✗ {package_name:20s} - FAILED: {str(e)}")
        return False

def main():
    print("=" * 60)
    print("Dependency Verification")
    print("=" * 60)
    print()
    
    all_ok = True
    
    # Core dependencies
    print("Core Dependencies:")
    all_ok &= check_import('numpy', 'NumPy')
    all_ok &= check_import('cv2', 'OpenCV')
    all_ok &= check_import('torch', 'PyTorch')
    all_ok &= check_import('torchvision', 'TorchVision')
    print()
    
    # YOLO
    print("Object Detection:")
    try:
        from ultralytics import YOLO
        import ultralytics
        print(f"✓ {'Ultralytics YOLO':20s} - Version: {ultralytics.__version__}")
    except ImportError as e:
        print(f"✗ {'Ultralytics YOLO':20s} - FAILED: {str(e)}")
        all_ok = False
    print()
    
    # Tracking
    print("Object Tracking:")
    all_ok &= check_import('scipy', 'SciPy')
    all_ok &= check_import('filterpy', 'FilterPy')
    print()
    
    # Web Framework
    print("Web Framework:")
    all_ok &= check_import('flask', 'Flask')
    all_ok &= check_import('flask_cors', 'Flask-CORS')
    print()
    
    # Utilities
    print("Utilities:")
    all_ok &= check_import('yaml', 'PyYAML')
    all_ok &= check_import('PIL', 'Pillow')
    print()
    
    # NumPy version check
    print("NumPy Version Check:")
    import numpy as np
    numpy_version = tuple(map(int, np.__version__.split('.')[:2]))
    if numpy_version[0] >= 2:
        print(f"⚠ WARNING: NumPy {np.__version__} detected (2.x)")
        print("  This may cause compatibility issues with PyTorch/Ultralytics")
        print("  Recommended: NumPy 1.26.x")
        all_ok = False
    else:
        print(f"✓ NumPy {np.__version__} (1.x) - Compatible")
    print()
    
    # Summary
    print("=" * 60)
    if all_ok:
        print("✓ All dependencies are installed correctly!")
        print("  You can now run: python app.py")
    else:
        print("✗ Some dependencies are missing or incompatible")
        print("  Please run: fix_dependencies.bat")
        print("  Or manually install: pip install -r requirements.txt")
    print("=" * 60)
    
    return 0 if all_ok else 1

if __name__ == '__main__':
    sys.exit(main())

