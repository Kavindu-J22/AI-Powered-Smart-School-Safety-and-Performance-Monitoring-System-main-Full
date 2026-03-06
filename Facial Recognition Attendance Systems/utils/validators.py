"""
Validators
===========
Input validation utilities.
"""

import re
from typing import Optional, Tuple
import numpy as np


class Validators:
    """Input validation utilities."""
    
    @staticmethod
    def validate_student_id(student_id: str) -> Tuple[bool, str]:
        """
        Validate student ID format.
        
        Args:
            student_id: Student ID to validate
            
        Returns:
            (is_valid, error_message)
        """
        if not student_id:
            return False, "Student ID is required"
        
        if len(student_id) < 2 or len(student_id) > 50:
            return False, "Student ID must be 2-50 characters"
        
        # Allow alphanumeric, dashes, underscores
        if not re.match(r'^[a-zA-Z0-9_-]+$', student_id):
            return False, "Student ID can only contain letters, numbers, dashes, and underscores"
        
        return True, ""
    
    @staticmethod
    def validate_student_name(name: str) -> Tuple[bool, str]:
        """
        Validate student name.
        
        Args:
            name: Student name to validate
            
        Returns:
            (is_valid, error_message)
        """
        if not name:
            return False, "Student name is required"
        
        if len(name) < 2 or len(name) > 200:
            return False, "Student name must be 2-200 characters"
        
        return True, ""
    
    @staticmethod
    def validate_email(email: str) -> Tuple[bool, str]:
        """
        Validate email format.
        
        Args:
            email: Email to validate
            
        Returns:
            (is_valid, error_message)
        """
        if not email:
            return True, ""  # Email is optional
        
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        
        if not re.match(pattern, email):
            return False, "Invalid email format"
        
        return True, ""
    
    @staticmethod
    def validate_date(date_str: str) -> Tuple[bool, str]:
        """
        Validate date format (YYYY-MM-DD).
        
        Args:
            date_str: Date string to validate
            
        Returns:
            (is_valid, error_message)
        """
        if not date_str:
            return False, "Date is required"
        
        pattern = r'^\d{4}-\d{2}-\d{2}$'
        
        if not re.match(pattern, date_str):
            return False, "Date must be in YYYY-MM-DD format"
        
        # Validate actual date
        try:
            from datetime import datetime
            datetime.strptime(date_str, '%Y-%m-%d')
        except ValueError:
            return False, "Invalid date"
        
        return True, ""
    
    @staticmethod
    def validate_image(
        image: np.ndarray,
        min_size: int = 50,
        max_size: int = 5000
    ) -> Tuple[bool, str]:
        """
        Validate image.
        
        Args:
            image: Image array to validate
            min_size: Minimum dimension size
            max_size: Maximum dimension size
            
        Returns:
            (is_valid, error_message)
        """
        if image is None:
            return False, "Image is required"
        
        if not isinstance(image, np.ndarray):
            return False, "Invalid image format"
        
        if len(image.shape) < 2:
            return False, "Invalid image dimensions"
        
        h, w = image.shape[:2]
        
        if h < min_size or w < min_size:
            return False, f"Image too small (minimum {min_size}x{min_size})"
        
        if h > max_size or w > max_size:
            return False, f"Image too large (maximum {max_size}x{max_size})"
        
        return True, ""
    
    @staticmethod
    def validate_capture_count(count: int) -> Tuple[bool, str]:
        """
        Validate capture count.
        
        Args:
            count: Number of images to capture
            
        Returns:
            (is_valid, error_message)
        """
        if not isinstance(count, int):
            return False, "Capture count must be an integer"
        
        if count < 5:
            return False, "Minimum capture count is 5"
        
        if count > 100:
            return False, "Maximum capture count is 100"
        
        return True, ""
    
    @staticmethod
    def validate_confidence(confidence: float) -> Tuple[bool, str]:
        """
        Validate confidence score.
        
        Args:
            confidence: Confidence score to validate
            
        Returns:
            (is_valid, error_message)
        """
        if not isinstance(confidence, (int, float)):
            return False, "Confidence must be a number"
        
        if confidence < 0 or confidence > 1:
            return False, "Confidence must be between 0 and 1"
        
        return True, ""
    
    @staticmethod
    def sanitize_string(text: str) -> str:
        """
        Sanitize string input.
        
        Args:
            text: String to sanitize
            
        Returns:
            Sanitized string
        """
        if not text:
            return ""
        
        # Remove leading/trailing whitespace
        text = text.strip()
        
        # Remove potentially dangerous characters
        text = re.sub(r'[<>"\']', '', text)
        
        return text
