"""
Flask API for Seating Arrangement Generation
Provides REST API endpoints for the Laravel application

Endpoints:
- POST /generate-seating: Generate seating arrangement for a grade/section
- GET /student-seat: Get seat assignment for a specific student
- GET /health: Health check

Author: School Management System
Version: 1.0.0
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import sys
import os
import logging

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from src.seating_generator import SeatingArrangementGenerator
from src.utils import validate_student_data, calculate_average_marks, logger
from config.config import API_HOST, API_PORT, API_DEBUG

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel integration

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)

# Initialize seating generator
seating_generator = None

def get_generator():
    """Get or initialize seating generator instance"""
    global seating_generator
    if seating_generator is None:
        seating_generator = SeatingArrangementGenerator()
    return seating_generator


@app.route('/health', methods=['GET'])
def health_check():
    """
    Health check endpoint
    
    Returns:
        JSON response with service status
    """
    return jsonify({
        'status': 'healthy',
        'service': 'Seating Arrangement API',
        'version': '1.0.0'
    }), 200


@app.route('/generate-seating', methods=['POST'])
def generate_seating():
    """
    Generate seating arrangement for a class
    
    Request body (JSON):
    {
        "grade": "11",
        "section": "A",
        "students": [
            {
                "student_id": "S001",
                "name": "John Doe",
                "average_marks": 85.5,
                "grade": "11",
                "section": "A"
            },
            ...
        ],
        "seats_per_row": 5,  # Optional, defaults to 5
        "total_rows": 6      # Optional, defaults to 6
    }
    
    Returns:
        JSON response with seating arrangement
    """
    try:
        data = request.get_json()
        
        # Validate required fields
        if not data:
            return jsonify({
                'error': 'No data provided',
                'message': 'Request body must contain JSON data'
            }), 400
        
        grade = data.get('grade')
        section = data.get('section')
        students = data.get('students', [])
        
        if not grade or not section:
            return jsonify({
                'error': 'Missing required fields',
                'message': 'grade and section are required'
            }), 400
        
        if not students:
            return jsonify({
                'error': 'No students provided',
                'message': 'students array cannot be empty'
            }), 400
        
        # Optional classroom configuration
        seats_per_row = data.get('seats_per_row', 5)
        total_rows = data.get('total_rows', 6)
        
        # Initialize generator with custom configuration if provided
        generator = SeatingArrangementGenerator(
            seats_per_row=seats_per_row,
            total_rows=total_rows
        )
        
        # Generate seating arrangement
        arrangement = generator.generate_arrangement(students, grade, section)
        
        logger.info(f"Successfully generated seating for Grade {grade}-{section} with {len(students)} students")
        
        return jsonify({
            'success': True,
            'data': arrangement
        }), 200
        
    except ValueError as e:
        logger.error(f"Validation error: {str(e)}")
        return jsonify({
            'error': 'Validation error',
            'message': str(e)
        }), 400
        
    except Exception as e:
        logger.error(f"Error generating seating arrangement: {str(e)}", exc_info=True)
        return jsonify({
            'error': 'Internal server error',
            'message': 'Failed to generate seating arrangement'
        }), 500


@app.route('/student-seat', methods=['GET'])
def get_student_seat():
    """
    Get seat assignment for a specific student
    
    Query parameters:
    - student_id: Student ID to search for
    
    Request body (JSON) - seating arrangement from previous generation:
    {
        "arrangement": { ... }  # Full arrangement object from /generate-seating
    }
    
    Returns:
        JSON response with seat assignment
    """
    try:
        student_id = request.args.get('student_id')
        
        if not student_id:
            return jsonify({
                'error': 'Missing student_id parameter',
                'message': 'student_id query parameter is required'
            }), 400
        
        data = request.get_json()
        if not data or 'arrangement' not in data:
            return jsonify({
                'error': 'Missing arrangement data',
                'message': 'Request body must contain arrangement data'
            }), 400
        
        arrangement = data['arrangement']
        generator = get_generator()
        
        # Find student seat
        seat = generator.get_student_seat(arrangement, student_id)
        
        if not seat:
            return jsonify({
                'error': 'Student not found',
                'message': f'No seat assignment found for student ID: {student_id}'
            }), 404
        
        return jsonify({
            'success': True,
            'data': seat
        }), 200
        
    except Exception as e:
        logger.error(f"Error retrieving student seat: {str(e)}", exc_info=True)
        return jsonify({
            'error': 'Internal server error',
            'message': 'Failed to retrieve seat assignment'
        }), 500


@app.route('/visualize', methods=['POST'])
def visualize_arrangement():
    """
    Get text-based visualization of seating arrangement
    
    Request body (JSON):
    {
        "arrangement": { ... }  # Full arrangement object from /generate-seating
    }
    
    Returns:
        JSON response with text visualization
    """
    try:
        data = request.get_json()
        
        if not data or 'arrangement' not in data:
            return jsonify({
                'error': 'Missing arrangement data',
                'message': 'Request body must contain arrangement data'
            }), 400
        
        arrangement = data['arrangement']
        generator = get_generator()
        
        # Generate visualization
        visualization = generator.visualize_arrangement(arrangement)
        
        return jsonify({
            'success': True,
            'visualization': visualization
        }), 200
        
    except Exception as e:
        logger.error(f"Error generating visualization: {str(e)}", exc_info=True)
        return jsonify({
            'error': 'Internal server error',
            'message': 'Failed to generate visualization'
        }), 500


@app.errorhandler(404)
def not_found(error):
    """Handle 404 errors"""
    return jsonify({
        'error': 'Not found',
        'message': 'The requested endpoint does not exist'
    }), 404


@app.errorhandler(500)
def internal_error(error):
    """Handle 500 errors"""
    logger.error(f"Internal server error: {str(error)}", exc_info=True)
    return jsonify({
        'error': 'Internal server error',
        'message': 'An unexpected error occurred'
    }), 500


if __name__ == '__main__':
    logger.info(f"Starting Seating Arrangement API on {API_HOST}:{API_PORT}")
    logger.info(f"Debug mode: {API_DEBUG}")
    
    app.run(
        host=API_HOST,
        port=API_PORT,
        debug=API_DEBUG
    )
