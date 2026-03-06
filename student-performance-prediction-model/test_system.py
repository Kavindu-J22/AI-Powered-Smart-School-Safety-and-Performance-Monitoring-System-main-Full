#!/usr/bin/env python3
"""
Quick Test Script
Verifies the prediction system is working correctly
"""

import sys
import os

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

def test_imports():
    """Test if all required modules can be imported"""
    print("Testing imports...")
    try:
        import pandas as pd
        import numpy as np
        import sklearn
        import joblib
        import flask
        print("✓ All required modules found")
        return True
    except ImportError as e:
        print(f"✗ Missing module: {e}")
        print("Run: pip install -r requirements.txt")
        return False

def test_config():
    """Test configuration file"""
    print("\nTesting configuration...")
    try:
        from config.config import DATASET_PATH, MODEL_PATH, API_PORT
        print(f"✓ Configuration loaded")
        print(f"  - Dataset: {DATASET_PATH}")
        print(f"  - Model: {MODEL_PATH}")
        print(f"  - API Port: {API_PORT}")
        return True
    except Exception as e:
        print(f"✗ Configuration error: {e}")
        return False

def test_data():
    """Test if dataset exists"""
    print("\nTesting dataset...")
    try:
        from config.config import DATASET_PATH
        if os.path.exists(DATASET_PATH):
            print(f"✓ Dataset found: {DATASET_PATH}")
            import pandas as pd
            df = pd.read_csv(DATASET_PATH)
            print(f"  - Records: {len(df)}")
            print(f"  - Columns: {len(df.columns)}")
            return True
        else:
            print(f"✗ Dataset not found: {DATASET_PATH}")
            return False
    except Exception as e:
        print(f"✗ Dataset error: {e}")
        return False

def test_models():
    """Test if trained models exist"""
    print("\nTesting trained models...")
    try:
        from config.config import MODEL_PATH, SCALER_PATH, MODELS_DIR
        
        model_exists = os.path.exists(MODEL_PATH)
        scaler_exists = os.path.exists(SCALER_PATH)
        
        if model_exists and scaler_exists:
            print("✓ All model files found")
            import joblib
            model = joblib.load(MODEL_PATH)
            scaler = joblib.load(SCALER_PATH)
            print(f"  - Model type: {type(model).__name__}")
            print(f"  - Scaler type: {type(scaler).__name__}")
            return True
        else:
            print("✗ Model files not found")
            print("  Run: python src/model_trainer.py")
            return False
    except Exception as e:
        print(f"✗ Model error: {e}")
        return False

def test_predictor():
    """Test prediction functionality"""
    print("\nTesting predictor...")
    try:
        from src.predictor import StudentPerformancePredictor
        
        predictor = StudentPerformancePredictor()
        
        # Test data
        test_student = {
            'student_id': 999,
            'age': 15,
            'grade': 10,
            'subjects': [
                {
                    'subject_name': 'Mathematics',
                    'attendance': 85.0,
                    'marks': 75.0
                }
            ]
        }
        
        predictions = predictor.predict(test_student)
        
        if predictions and len(predictions) > 0:
            print("✓ Predictor working")
            print(f"  - Test prediction: {predictions[0]['predicted_performance']:.2f}%")
            return True
        else:
            print("✗ Predictor returned empty results")
            return False
    except Exception as e:
        print(f"✗ Predictor error: {e}")
        return False

def test_api():
    """Test if API can be imported"""
    print("\nTesting API...")
    try:
        sys.path.append(os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'api'))
        from app import app
        print("✓ API module can be imported")
        print("  To start: cd api && python app.py")
        return True
    except Exception as e:
        print(f"✗ API error: {e}")
        return False

def main():
    """Run all tests"""
    print("=" * 60)
    print("STUDENT PERFORMANCE PREDICTION SYSTEM - TEST")
    print("=" * 60)
    
    tests = [
        ("Imports", test_imports),
        ("Configuration", test_config),
        ("Dataset", test_data),
        ("Models", test_models),
        ("Predictor", test_predictor),
        ("API", test_api)
    ]
    
    results = []
    for name, test_func in tests:
        result = test_func()
        results.append((name, result))
    
    print("\n" + "=" * 60)
    print("TEST RESULTS")
    print("=" * 60)
    
    for name, result in results:
        status = "✓ PASS" if result else "✗ FAIL"
        print(f"{name:.<40} {status}")
    
    passed = sum(1 for _, r in results if r)
    total = len(results)
    
    print("=" * 60)
    print(f"Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n✓ All tests passed! System is ready.")
        print("\nNext steps:")
        print("1. Start API: cd api && python app.py")
        print("2. Configure Laravel .env")
        print("3. Test integration")
    else:
        print("\n✗ Some tests failed. Please fix the issues above.")
        if not results[3][1]:  # Models test failed
            print("\nTip: Run setup to create models:")
            print("  python src/data_preprocessing.py")
            print("  python src/model_trainer.py")
    
    print("=" * 60)
    
    return 0 if passed == total else 1

if __name__ == "__main__":
    sys.exit(main())
