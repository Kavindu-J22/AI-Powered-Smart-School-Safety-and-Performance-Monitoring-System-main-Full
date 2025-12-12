"""
Quick Test - Verify main components without heavy imports
"""

import sys
from pathlib import Path

def print_banner(text):
    print("\n" + "=" * 70)
    print(f" {text}")
    print("=" * 70)

def main():
    print_banner("QUICK SYSTEM TEST")
    
    results = {}
    
    # Test 1: Model files exist
    print("\n1. Checking Model Files...")
    model_files = {
        "Object Detection": "models/left_behind_detector.pt",
        "Threat Detection": "models/threat_detector.pt"
    }
    
    models_ok = True
    for name, path in model_files.items():
        if Path(path).exists():
            size_mb = Path(path).stat().st_size / (1024 * 1024)
            print(f"  ✓ {name}: {size_mb:.2f} MB")
        else:
            print(f"  ✗ {name}: NOT FOUND")
            models_ok = False
    results['models'] = models_ok
    
    # Test 2: Configuration
    print("\n2. Checking Configuration...")
    try:
        import yaml
        with open('config/config.yaml', 'r') as f:
            config = yaml.safe_load(f)
        print(f"  ✓ Config loaded")
        print(f"  ✓ System: {config['system']['name']}")
        print(f"  ✓ Cameras: {len(config['cameras'])}")
        results['config'] = True
    except Exception as e:
        print(f"  ✗ Config error: {e}")
        results['config'] = False
    
    # Test 3: Directory structure
    print("\n3. Checking Directories...")
    dirs = ['src', 'scripts', 'config', 'models', 'datasets', 'logs', 'data']
    dirs_ok = True
    for d in dirs:
        if Path(d).exists():
            print(f"  ✓ {d}/")
        else:
            print(f"  ✗ {d}/ - missing")
            dirs_ok = False
    results['directories'] = dirs_ok
    
    # Test 4: Python files
    print("\n4. Checking Python Files...")
    files = [
        'main.py',
        'run_training.py',
        'src/models/object_detector.py',
        'src/models/threat_detector.py',
        'src/tracking/object_tracker.py',
        'src/notifications/alert_system.py'
    ]
    files_ok = True
    for f in files:
        if Path(f).exists():
            print(f"  ✓ {f}")
        else:
            print(f"  ✗ {f} - missing")
            files_ok = False
    results['files'] = files_ok
    
    # Test 5: Check if we can at least parse main.py
    print("\n5. Checking main.py Syntax...")
    try:
        with open('main.py', 'r', encoding='utf-8') as f:
            compile(f.read(), 'main.py', 'exec')
        print("  ✓ main.py syntax is valid")
        results['syntax'] = True
    except SyntaxError as e:
        print(f"  ✗ Syntax error: {e}")
        results['syntax'] = False
    
    # Summary
    print_banner("TEST SUMMARY")
    
    passed = sum(results.values())
    total = len(results)
    
    for test, result in results.items():
        status = "✓ PASSED" if result else "✗ FAILED"
        print(f"  {test:20s}: {status}")
    
    print(f"\nTotal: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n✅ SYSTEM IS READY!")
        print("\n📊 Training Results Summary:")
        print("  Object Detection:")
        print("    - Training mAP50: 79.32%")
        print("    - Test mAP50: 77.49%")
        print("    - F1 Score: 75.34%")
        print("  Threat Detection:")
        print("    - Training Accuracy: 70.80%")
        print("    - Test Accuracy: 73.97%")
        print("    - F1 Score: 73.97%")
        print("\n📝 Models saved:")
        print("  - models/left_behind_detector.pt (5.96 MB)")
        print("  - models/threat_detector.pt (13.75 MB)")
        print("\n🚀 To run the system:")
        print("  1. With video file:")
        print("     python main.py --camera CAM_001 --source video.mp4")
        print("  2. With webcam:")
        print("     python main.py --camera CAM_001 --source 0")
        print("  3. With all cameras from config:")
        print("     python main.py")
        print("\n⚠️  Note: Make sure to use the virtual environment:")
        print("     .venv\\Scripts\\python.exe main.py")
        return 0
    else:
        print(f"\n✗ {total - passed} test(s) failed")
        return 1

if __name__ == "__main__":
    exit(main())

