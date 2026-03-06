"""
Flask API for Student Performance Prediction
Provides REST API endpoints for the Laravel application

IMPROVED: Now returns 95% confidence intervals with predictions

Endpoints:
- POST /predict: Predict student performance with confidence intervals
- GET /health: Health check
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import sys
import os

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from src.predictor import StudentPerformancePredictor
from config.config import API_HOST, API_PORT, API_DEBUG

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel integration

# Initialize predictor
predictor = None

def get_predictor():
    """Get or initialize predictor instance"""
    global predictor
    if predictor is None:
        predictor = StudentPerformancePredictor()
    return predictor


@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Student Performance Prediction API',
        'version': '2.0.0',  # Updated version for improved model
        'model': 'RandomForestRegressor',
        'features': [
            'One-Hot Encoding for subjects',
            '95% Confidence Intervals',
            'Feature Engineering (risk_index, etc.)',
            '5-Fold Cross-Validated'
        ]
    }), 200


@app.route('/predict', methods=['POST'])
def predict_performance():
    """
    Predict student performance for all subjects
    
    IMPROVED: Now returns 95% confidence intervals
    
    Request body:
    {
        "student_id": 123,
        "age": 15,
        "grade": 10,
        "subjects": [
            {
                "subject_name": "Mathematics",
                "attendance": 85.5,
                "marks": 78.0
            }
        ]
    }
    
    Response (IMPROVED with confidence_interval):
    {
        "student_id": 123,
        "predictions": [
            {
                "subject": "Mathematics",
                "current_performance": 78.0,
                "predicted_performance": 82.5,
                "confidence_interval": {
                    "lower_bound": 74.2,
                    "upper_bound": 90.8,
                    "confidence_level": 0.95
                },
                "prediction_trend": "improving",
                "confidence": 0.89
            }
        ]
    }
    """
    try:
        # Get request data
        data = request.get_json()
        
        # Validate required fields
        if not data:
            return jsonify({
                'error': 'No data provided',
                'message': 'Request body must contain JSON data'
            }), 400
        
        if 'subjects' not in data or not data['subjects']:
            return jsonify({
                'error': 'Missing subjects',
                'message': 'At least one subject must be provided'
            }), 400
        
        # Set defaults for optional fields
        student_data = {
            'student_id': data.get('student_id'),
            'age': data.get('age', 15),
            'grade': data.get('grade', 10),
            'subjects': data.get('subjects', [])
        }
        
        # Validate subjects format
        for subject in student_data['subjects']:
            if 'subject_name' not in subject:
                return jsonify({
                    'error': 'Invalid subject format',
                    'message': 'Each subject must have subject_name'
                }), 400
            
            # Set defaults for missing attendance/marks
            subject['attendance'] = subject.get('attendance', 0)
            subject['marks'] = subject.get('marks', 0)
        
        # Get predictor and make predictions
        pred = get_predictor()
        predictions = pred.predict(student_data)
        
        # Format response
        response = {
            'student_id': student_data['student_id'],
            'age': student_data['age'],
            'grade': student_data['grade'],
            'predictions': predictions,
            'total_subjects': len(predictions)
        }
        
        return jsonify(response), 200
        
    except Exception as e:
        return jsonify({
            'error': 'Prediction failed',
            'message': str(e)
        }), 500


@app.route('/predict/batch', methods=['POST'])
def predict_batch():
    """
    Predict performance for multiple students
    
    Request body:
    {
        "students": [
            {
                "student_id": 123,
                "age": 15,
                "grade": 10,
                "subjects": [...]
            },
            ...
        ]
    }
    """
    try:
        data = request.get_json()
        
        if not data or 'students' not in data:
            return jsonify({
                'error': 'Invalid request',
                'message': 'Request must contain students array'
            }), 400
        
        students = data.get('students', [])
        pred = get_predictor()
        
        results = []
        for student_data in students:
            try:
                predictions = pred.predict(student_data)
                results.append({
                    'student_id': student_data.get('student_id'),
                    'predictions': predictions,
                    'status': 'success'
                })
            except Exception as e:
                results.append({
                    'student_id': student_data.get('student_id'),
                    'error': str(e),
                    'status': 'failed'
                })
        
        return jsonify({
            'total_students': len(students),
            'results': results
        }), 200
        
    except Exception as e:
        return jsonify({
            'error': 'Batch prediction failed',
            'message': str(e)
        }), 500


@app.errorhandler(404)
def not_found(error):
    """Handle 404 errors"""
    return jsonify({
        'error': 'Endpoint not found',
        'message': 'The requested endpoint does not exist'
    }), 404


@app.errorhandler(500)
def internal_error(error):
    """Handle 500 errors"""
    return jsonify({
        'error': 'Internal server error',
        'message': 'An unexpected error occurred'
    }), 500


if __name__ == '__main__':
    print("=" * 60)
    print("STUDENT PERFORMANCE PREDICTION API v2.0")
    print("Model: RandomForestRegressor (improved accuracy)")
    print("=" * 60)
    print(f"Starting API server on {API_HOST}:{API_PORT}")
    print(f"Health check: http://localhost:{API_PORT}/health")
    print(f"Prediction endpoint: http://localhost:{API_PORT}/predict")
    print("\nNew Features:")
    print("  - 95% Confidence Intervals")
    print("  - One-Hot Encoded Subjects")
    print("  - Feature Engineering")
    print("=" * 60)
    
    app.run(host=API_HOST, port=API_PORT, debug=API_DEBUG)
