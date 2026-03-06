"""
Utility functions for seating arrangement system
"""

from typing import Dict, List, Any
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def validate_student_data(students: List[Dict[str, Any]]) -> bool:
    """
    Validate student data format
    
    Args:
        students: List of student dictionaries
        
    Returns:
        bool: True if valid, False otherwise
    """
    if not students or not isinstance(students, list):
        logger.error("Students data must be a non-empty list")
        return False
    
    required_fields = ['student_id', 'name', 'average_marks']
    
    for student in students:
        if not isinstance(student, dict):
            logger.error(f"Invalid student data format: {student}")
            return False
            
        for field in required_fields:
            if field not in student:
                logger.error(f"Missing required field '{field}' in student data")
                return False
                
        # Validate marks range
        if not (0 <= student['average_marks'] <= 100):
            logger.error(f"Invalid marks for student {student['student_id']}: {student['average_marks']}")
            return False
    
    return True


def calculate_average_marks(marks: List[float]) -> float:
    """
    Calculate average marks from a list of marks
    
    Args:
        marks: List of mark values
        
    Returns:
        float: Average marks
    """
    if not marks:
        return 0.0
    
    valid_marks = [m for m in marks if m is not None and 0 <= m <= 100]
    
    if not valid_marks:
        return 0.0
    
    return round(sum(valid_marks) / len(valid_marks), 2)


def format_seat_number(row: int, col: int, prefix: str = 'S') -> str:
    """
    Format seat number based on row and column
    
    Args:
        row: Row number (0-indexed)
        col: Column number (0-indexed)
        prefix: Prefix for seat number
        
    Returns:
        str: Formatted seat number (e.g., 'S1', 'S2')
    """
    # Calculate seat number as row * seats_per_row + col + 1
    # This creates a linear numbering system
    seat_num = row * 10 + col + 1  # Assuming max 10 seats per row
    return f"{prefix}{seat_num}"


def group_students_by_grade(students: List[Dict[str, Any]]) -> Dict[str, List[Dict[str, Any]]]:
    """
    Group students by their grade level
    
    Args:
        students: List of all students
        
    Returns:
        dict: Dictionary with grade as key and list of students as value
    """
    grouped = {}
    
    for student in students:
        grade = student.get('grade', 'Unknown')
        if grade not in grouped:
            grouped[grade] = []
        grouped[grade].append(student)
    
    return grouped


def calculate_performance_category(marks: float) -> str:
    """
    Categorize student performance based on marks
    
    Args:
        marks: Average marks
        
    Returns:
        str: Performance category ('high', 'medium', 'low')
    """
    if marks >= 75:
        return 'high'
    elif marks >= 50:
        return 'medium'
    else:
        return 'low'
