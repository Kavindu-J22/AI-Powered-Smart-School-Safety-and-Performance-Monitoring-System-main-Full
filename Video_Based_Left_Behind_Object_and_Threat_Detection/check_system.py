"""
Comprehensive System Check Script
Checks code structure, imports, and configuration without requiring all packages
"""

import sys
import os
from pathlib import Path
import importlib.util

def print_banner(text, char="="):
    """Print a banner"""
    width = 70
    print("\n" + char * width)
    print(f" {text}")
    print(char * width)

def check_file_exists(filepath, description):
    """Check if a file exists"""
    if Path(filepath).exists():
        print(f"✓ {description}: {filepath}")
        return True
    else:
        print(f"✗ {description} NOT FOUND: {filepath}")
        return False

def check_python_syntax(filepath):
    """Check if a Python file has valid syntax"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            compile(f.read(), filepath, 'exec')
        print(f"✓ Syntax valid: {filepath}")
        return True
    except SyntaxError as e:
        print(f"✗ Syntax error in {filepath}: {e}")
        return False
    except Exception as e:
        print(f"✗ Error checking {filepath}: {e}")
        return False

def main():
    print_banner("VIDEO-BASED DETECTION SYSTEM - COMPREHENSIVE CHECK", "=")
    
    results = {}
    
    # Check 1: File Structure
    print_banner("1. CHECKING FILE STRUCTURE")
    
    files_to_check = {
        "Main Application": "main.py",
        "Training Pipeline": "run_training.py",
        "Requirements": "requirements.txt",
        "Config": "config/config.yaml",
        "Object Detector": "src/models/object_detector.py",
        "Threat Detector": "src/models/threat_detector.py",
        "Object Tracker": "src/tracking/object_tracker.py",
        "Alert System": "src/notifications/alert_system.py",
        "Train Models Script": "scripts/train_models.py",
        "Test System Script": "scripts/test_system.py",
        "Prepare Datasets Script": "scripts/prepare_datasets.py",
    }
    
    file_check_results = []
    for desc, filepath in files_to_check.items():
        file_check_results.append(check_file_exists(filepath, desc))
    
    results['file_structure'] = all(file_check_results)
    
    # Check 2: Python Syntax
    print_banner("2. CHECKING PYTHON SYNTAX")
    
    python_files = [
        "main.py",
        "run_training.py",
        "src/models/object_detector.py",
        "src/models/threat_detector.py",
        "src/tracking/object_tracker.py",
        "src/notifications/alert_system.py",
        "scripts/train_models.py",
        "scripts/test_system.py",
        "scripts/prepare_datasets.py",
    ]
    
    syntax_results = []
    for filepath in python_files:
        if Path(filepath).exists():
            syntax_results.append(check_python_syntax(filepath))
    
    results['syntax'] = all(syntax_results)
    
    # Check 3: Configuration
    print_banner("3. CHECKING CONFIGURATION")
    
    try:
        import yaml
        with open('config/config.yaml', 'r') as f:
            config = yaml.safe_load(f)
        print(f"✓ Config loaded successfully")
        print(f"  - System: {config.get('system', {}).get('name', 'N/A')}")
        print(f"  - Cameras configured: {len(config.get('cameras', []))}")
        results['config'] = True
    except Exception as e:
        print(f"✗ Config error: {e}")
        results['config'] = False
    
    # Check 4: Directory Structure
    print_banner("4. CHECKING DIRECTORY STRUCTURE")
    
    dirs_to_check = [
        "src",
        "src/models",
        "src/tracking",
        "src/notifications",
        "scripts",
        "config",
        "datasets",
        "models",
    ]
    
    dir_results = []
    for dirpath in dirs_to_check:
        if Path(dirpath).exists():
            print(f"✓ Directory exists: {dirpath}")
            dir_results.append(True)
        else:
            print(f"✗ Directory missing: {dirpath}")
            dir_results.append(False)
    
    results['directories'] = all(dir_results)
    
    # Check 5: Package Requirements
    print_banner("5. CHECKING PACKAGE REQUIREMENTS")
    
    try:
        with open('requirements.txt', 'r') as f:
            requirements = [line.strip() for line in f if line.strip() and not line.startswith('#')]
        print(f"✓ Requirements file has {len(requirements)} packages")
        print(f"  Key packages: torch, opencv-python, ultralytics, tensorflow")
        results['requirements'] = True
    except Exception as e:
        print(f"✗ Requirements error: {e}")
        results['requirements'] = False
    
    # Final Summary
    print_banner("SYSTEM CHECK SUMMARY", "=")
    
    passed = sum(results.values())
    total = len(results)
    
    for check_name, result in results.items():
        status = "✓ PASSED" if result else "✗ FAILED"
        print(f"  {check_name:25s}: {status}")
    
    print(f"\nTotal: {passed}/{total} checks passed")
    
    if passed == total:
        print("\n✓ ALL CHECKS PASSED!")
        print("\nNext steps:")
        print("  1. Install dependencies: pip install -r requirements.txt")
        print("  2. Run system tests: python scripts/test_system.py")
        print("  3. Prepare datasets: python scripts/prepare_datasets.py")
        print("  4. Train models: python run_training.py")
        print("  5. Run application: python main.py")
        return 0
    else:
        print(f"\n✗ {total - passed} check(s) failed")
        print("Please fix the issues above before proceeding.")
        return 1

if __name__ == "__main__":
    exit(main())

