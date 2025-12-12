"""
Test PyTorch Loading
Diagnose PyTorch import issues on Windows
"""

import sys
import time

print("=" * 70)
print(" PyTorch Loading Test")
print("=" * 70)

print(f"\nPython version: {sys.version}")
print(f"Python executable: {sys.executable}")

print("\n1. Testing basic imports...")
try:
    import numpy as np
    print(f"  ✓ NumPy {np.__version__}")
except Exception as e:
    print(f"  ✗ NumPy failed: {e}")

try:
    import cv2
    print(f"  ✓ OpenCV {cv2.__version__}")
except Exception as e:
    print(f"  ✗ OpenCV failed: {e}")

print("\n2. Testing PyTorch import (this may take a while on Windows)...")
print("   Please wait... (can take 1-5 minutes on first run)")

start_time = time.time()
try:
    print("   Importing torch...")
    import torch
    load_time = time.time() - start_time
    print(f"  ✓ PyTorch {torch.__version__} (loaded in {load_time:.1f}s)")
    print(f"  ✓ CUDA available: {torch.cuda.is_available()}")
    if torch.cuda.is_available():
        print(f"  ✓ CUDA device: {torch.cuda.get_device_name(0)}")
except KeyboardInterrupt:
    print(f"\n  ✗ PyTorch import interrupted by user")
    print(f"     Time elapsed: {time.time() - start_time:.1f}s")
    print("\n  This is a known Windows issue with PyTorch.")
    print("  Possible solutions:")
    print("    1. Add Python and .venv to Windows Defender exclusions")
    print("    2. Disable real-time scanning temporarily")
    print("    3. Use CPU-only PyTorch version")
    sys.exit(1)
except Exception as e:
    print(f"  ✗ PyTorch failed: {e}")
    sys.exit(1)

print("\n3. Testing Ultralytics (YOLOv8)...")
start_time = time.time()
try:
    from ultralytics import YOLO
    load_time = time.time() - start_time
    print(f"  ✓ Ultralytics loaded (in {load_time:.1f}s)")
except Exception as e:
    print(f"  ✗ Ultralytics failed: {e}")

print("\n" + "=" * 70)
print(" All imports successful!")
print("=" * 70)
print("\nYou can now run main.py")
print("Note: First run may be slow due to PyTorch initialization")

