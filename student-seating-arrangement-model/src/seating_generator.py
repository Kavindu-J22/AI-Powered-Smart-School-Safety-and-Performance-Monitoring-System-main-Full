"""
Seating Arrangement Generator

This module implements the core algorithm for generating optimal seating arrangements
based on student performance marks. The algorithm pairs high-performing students with
lower-performing students to encourage peer learning and support.

Algorithm Overview:
1. Sort students by their average marks
2. Divide into high performers and low performers
3. Pair them alternately (zigzag pattern)
4. Assign seats row by row

Author: School Management System
Version: 1.0.0
"""

from typing import List, Dict, Any, Optional
import logging
from .utils import (
    validate_student_data,
    calculate_performance_category,
    format_seat_number,
    logger
)

class SeatingArrangementGenerator:
    """
    Generates optimal seating arrangements based on student performance marks
    """
    
    def __init__(self, seats_per_row: int = 5, total_rows: int = 6):
        """
        Initialize the seating arrangement generator
        
        Args:
            seats_per_row: Number of seats per row in classroom
            total_rows: Total number of rows in classroom
        """
        self.seats_per_row = seats_per_row
        self.total_rows = total_rows
        self.total_capacity = seats_per_row * total_rows
        
        logger.info(f"Seating generator initialized: {seats_per_row} seats/row x {total_rows} rows = {self.total_capacity} total seats")
    
    def generate_arrangement(self, students: List[Dict[str, Any]], 
                           grade: str, 
                           section: str) -> Dict[str, Any]:
        """
        Generate seating arrangement for a class
        
        Args:
            students: List of student dictionaries with keys:
                     - student_id: Unique student identifier
                     - name: Student name
                     - average_marks: Average marks from recent term
                     - grade: Grade level (e.g., 11)
                     - section: Class section (e.g., 'A')
            grade: Grade level (e.g., '11')
            section: Class section (e.g., 'A')
            
        Returns:
            dict: Seating arrangement with student assignments
            
        Raises:
            ValueError: If student data is invalid
        """
        # Validate input
        if not validate_student_data(students):
            raise ValueError("Invalid student data provided")
        
        if len(students) > self.total_capacity:
            logger.warning(f"Number of students ({len(students)}) exceeds classroom capacity ({self.total_capacity})")
        
        # Sort students by marks
        sorted_students = self._sort_students_by_performance(students)
        
        # Generate seating using high-low pairing strategy
        seating_map = self._generate_high_low_pairing(sorted_students)
        
        # Create result
        result = {
            'grade': grade,
            'section': section,
            'total_students': len(students),
            'seating_capacity': self.total_capacity,
            'seats_per_row': self.seats_per_row,
            'total_rows': self.total_rows,
            'arrangement': seating_map,
            'strategy': 'high_low_pairing',
            'description': 'High-performing students are seated next to lower-performing students to encourage peer learning'
        }
        
        logger.info(f"Generated seating arrangement for Grade {grade}-{section} with {len(students)} students")
        
        return result
    
    def _sort_students_by_performance(self, students: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Sort students by their average marks in descending order
        
        Args:
            students: List of student dictionaries
            
        Returns:
            list: Sorted list of students
        """
        return sorted(students, key=lambda x: x['average_marks'], reverse=True)
    
    def _generate_high_low_pairing(self, sorted_students: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Generate seating arrangement using high-low pairing strategy
        
        This method pairs high performers with low performers in a zigzag pattern:
        - First student (highest marks) sits in first seat
        - Last student (lowest marks) sits in second seat
        - Second student sits in third seat
        - Second-to-last student sits in fourth seat
        - And so on...
        
        Args:
            sorted_students: Students sorted by marks (highest to lowest)
            
        Returns:
            list: List of seat assignments
        """
        seating_map = []
        n = len(sorted_students)
        
        # Use two pointers technique for pairing
        left = 0   # Points to high performers
        right = n - 1  # Points to low performers
        seat_num = 1
        
        # Zigzag pairing
        while left <= right:
            # Add high performer
            if left <= right:
                seating_map.append(self._create_seat_assignment(
                    sorted_students[left], 
                    seat_num,
                    'high' if left < n // 3 else 'medium'
                ))
                seat_num += 1
                left += 1
            
            # Add low performer
            if left <= right:
                seating_map.append(self._create_seat_assignment(
                    sorted_students[right], 
                    seat_num,
                    'low' if right > 2 * n // 3 else 'medium'
                ))
                seat_num += 1
                right -= 1
        
        return seating_map
    
    def _create_seat_assignment(self, student: Dict[str, Any], 
                               seat_number: int, 
                               performance_level: str) -> Dict[str, Any]:
        """
        Create a seat assignment entry
        
        Args:
            student: Student dictionary
            seat_number: Assigned seat number
            performance_level: Performance category ('high', 'medium', 'low')
            
        Returns:
            dict: Seat assignment details
        """
        # Calculate row and column from seat number
        row = (seat_number - 1) // self.seats_per_row + 1
        col = (seat_number - 1) % self.seats_per_row + 1
        
        return {
            'seat_number': seat_number,
            'seat_label': f"S{seat_number}",
            'row': row,
            'column': col,
            'student_id': student['student_id'],
            'student_name': student['name'],
            'average_marks': student['average_marks'],
            'performance_level': performance_level,
            'grade': student.get('grade', ''),
            'section': student.get('section', '')
        }
    
    def get_student_seat(self, arrangement: Dict[str, Any], student_id: str) -> Optional[Dict[str, Any]]:
        """
        Get seat assignment for a specific student
        
        Args:
            arrangement: Complete seating arrangement
            student_id: Student ID to search for
            
        Returns:
            dict: Seat assignment or None if not found
        """
        for seat in arrangement.get('arrangement', []):
            if str(seat['student_id']) == str(student_id):
                return seat
        
        return None
    
    def visualize_arrangement(self, arrangement: Dict[str, Any]) -> str:
        """
        Create a text-based visualization of the seating arrangement
        
        Args:
            arrangement: Seating arrangement dictionary
            
        Returns:
            str: Text visualization of the classroom
        """
        seats = arrangement.get('arrangement', [])
        rows = self.total_rows
        cols = self.seats_per_row
        
        # Create 2D grid
        grid = [[None for _ in range(cols)] for _ in range(rows)]
        
        # Fill grid with seat assignments
        for seat in seats:
            row_idx = seat['row'] - 1
            col_idx = seat['column'] - 1
            if 0 <= row_idx < rows and 0 <= col_idx < cols:
                grid[row_idx][col_idx] = seat
        
        # Generate visualization
        viz = []
        viz.append(f"\n{'=' * 80}")
        viz.append(f"Seating Arrangement: Grade {arrangement['grade']}-{arrangement['section']}")
        viz.append(f"{'=' * 80}\n")
        viz.append("FRONT OF CLASSROOM")
        viz.append("-" * 80)
        
        for row_idx, row in enumerate(grid, 1):
            row_display = f"Row {row_idx}: "
            for seat in row:
                if seat:
                    # Display seat number and performance indicator
                    perf_indicator = seat['performance_level'][0].upper()  # H, M, L
                    row_display += f"[S{seat['seat_number']:02d}-{perf_indicator}] "
                else:
                    row_display += "[Empty] "
            viz.append(row_display)
        
        viz.append("-" * 80)
        viz.append("BACK OF CLASSROOM\n")
        viz.append("Legend: H=High Performer, M=Medium Performer, L=Low Performer\n")
        
        return "\n".join(viz)
