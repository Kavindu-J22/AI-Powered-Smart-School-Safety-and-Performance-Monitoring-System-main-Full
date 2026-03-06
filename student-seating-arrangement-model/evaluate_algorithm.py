"""
Seating Arrangement Algorithm Evaluation Script

This script evaluates the seating arrangement algorithm by measuring:
1. Balance Score - How well high and low performers are distributed
2. Pairing Quality - Effectiveness of high-low pairing strategy
3. Optimization Metrics - Overall arrangement quality

Note: This is an optimization algorithm (not ML), so we measure effectiveness
rather than prediction accuracy.
"""

import numpy as np
from typing import List, Dict, Any
import sys
import os

# Add src to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from src.seating_generator import SeatingArrangementGenerator


class AlgorithmEvaluator:
    """Evaluate the seating arrangement algorithm"""
    
    def __init__(self):
        """Initialize evaluator"""
        self.generator = SeatingArrangementGenerator(seats_per_row=5, total_rows=6)
    
    def generate_sample_students(self, num_students: int = 30) -> List[Dict[str, Any]]:
        """
        Generate sample student data with varied performance levels
        
        Args:
            num_students: Number of students to generate
            
        Returns:
            list: Sample student data
        """
        students = []
        
        # Create students with diverse marks (following normal distribution)
        marks = np.random.normal(loc=70, scale=15, size=num_students)
        marks = np.clip(marks, 0, 100)  # Ensure marks are between 0-100
        
        for i in range(num_students):
            students.append({
                'student_id': f'S{i+1:03d}',
                'name': f'Student {i+1}',
                'average_marks': round(float(marks[i]), 2),
                'grade': '11',
                'section': 'A'
            })
        
        return students
    
    def calculate_balance_score(self, arrangement: Dict[str, Any]) -> float:
        """
        Calculate how well high and low performers are balanced across rows
        
        A perfect balance means each row has similar average performance.
        Score ranges from 0 (worst) to 100 (perfect)
        
        Args:
            arrangement: Seating arrangement data
            
        Returns:
            float: Balance score (0-100)
        """
        seats = arrangement['arrangement']
        rows = arrangement['total_rows']
        
        # Calculate average marks per row
        row_averages = []
        for row_num in range(1, rows + 1):
            row_students = [s for s in seats if s['row'] == row_num]
            if row_students:
                avg = np.mean([s['average_marks'] for s in row_students])
                row_averages.append(avg)
        
        if not row_averages:
            return 0.0
        
        # Calculate standard deviation (lower is better)
        std_dev = np.std(row_averages)
        
        # Convert to score (0-100, where lower std_dev = higher score)
        # Assuming typical std_dev ranges from 0 to 20
        max_std = 20
        balance_score = max(0, (1 - (std_dev / max_std)) * 100)
        
        return balance_score
    
    def calculate_pairing_quality(self, arrangement: Dict[str, Any]) -> float:
        """
        Calculate the quality of high-low pairing strategy
        
        Measures how well high performers are seated next to low performers.
        Score ranges from 0 (worst) to 100 (perfect)
        
        Args:
            arrangement: Seating arrangement data
            
        Returns:
            float: Pairing quality score (0-100)
        """
        seats = arrangement['arrangement']
        seats_per_row = arrangement['seats_per_row']
        
        # Sort by seat number
        sorted_seats = sorted(seats, key=lambda x: x['seat_number'])
        
        # Calculate performance differences between adjacent seats
        adjacent_differences = []
        for i in range(len(sorted_seats) - 1):
            # Check if seats are adjacent (same row or end of row to start of next)
            current = sorted_seats[i]
            next_seat = sorted_seats[i + 1]
            
            # Skip if it's the last seat of a row (no right neighbor in same row)
            if current['seat_number'] % seats_per_row == 0:
                continue
            
            diff = abs(current['average_marks'] - next_seat['average_marks'])
            adjacent_differences.append(diff)
        
        if not adjacent_differences:
            return 0.0
        
        # Higher average difference means better mixing
        avg_difference = np.mean(adjacent_differences)
        
        # Convert to score (0-100)
        # Good pairing should have average difference around 20-40 points
        target_difference = 30
        max_deviation = 30
        deviation = abs(avg_difference - target_difference)
        pairing_score = max(0, (1 - (deviation / max_deviation)) * 100)
        
        return pairing_score
    
    def calculate_performance_distribution(self, arrangement: Dict[str, Any]) -> Dict[str, float]:
        """
        Calculate distribution of performance levels
        
        Args:
            arrangement: Seating arrangement data
            
        Returns:
            dict: Distribution percentages
        """
        seats = arrangement['arrangement']
        total = len(seats)
        
        high = sum(1 for s in seats if s['performance_level'] == 'high')
        medium = sum(1 for s in seats if s['performance_level'] == 'medium')
        low = sum(1 for s in seats if s['performance_level'] == 'low')
        
        return {
            'high': (high / total) * 100 if total > 0 else 0,
            'medium': (medium / total) * 100 if total > 0 else 0,
            'low': (low / total) * 100 if total > 0 else 0
        }
    
    def calculate_overall_effectiveness(self, balance_score: float, 
                                       pairing_score: float) -> Dict[str, Any]:
        """
        Calculate overall algorithm effectiveness
        
        Args:
            balance_score: Balance quality score
            pairing_score: Pairing quality score
            
        Returns:
            dict: Overall effectiveness metrics
        """
        # Weighted average (balance is slightly more important)
        overall_score = (balance_score * 0.6) + (pairing_score * 0.4)
        
        # Determine quality level
        if overall_score >= 90:
            quality = "Excellent"
        elif overall_score >= 80:
            quality = "Very Good"
        elif overall_score >= 70:
            quality = "Good"
        elif overall_score >= 60:
            quality = "Moderate"
        else:
            quality = "Needs Improvement"
        
        return {
            'overall_score': overall_score,
            'quality_level': quality,
            'balance_weight': 0.6,
            'pairing_weight': 0.4
        }
    
    def evaluate(self, num_tests: int = 5):
        """
        Run multiple evaluation tests and calculate average metrics
        
        Args:
            num_tests: Number of test runs
        """
        print("=" * 70)
        print("SEATING ARRANGEMENT ALGORITHM - EVALUATION")
        print("=" * 70)
        
        print(f"\nRunning {num_tests} test scenarios with varied student data...")
        print(f"Classroom configuration: {self.generator.seats_per_row} seats/row × {self.generator.total_rows} rows")
        
        all_balance_scores = []
        all_pairing_scores = []
        all_distributions = []
        
        for test_num in range(1, num_tests + 1):
            print(f"\n--- Test {test_num}/{num_tests} ---")
            
            # Generate sample students
            students = self.generate_sample_students(30)
            
            # Generate arrangement
            arrangement = self.generator.generate_arrangement(
                students=students,
                grade='11',
                section='A'
            )
            
            # Calculate metrics
            balance = self.calculate_balance_score(arrangement)
            pairing = self.calculate_pairing_quality(arrangement)
            distribution = self.calculate_performance_distribution(arrangement)
            
            all_balance_scores.append(balance)
            all_pairing_scores.append(pairing)
            all_distributions.append(distribution)
            
            print(f"  Balance Score: {balance:.2f}/100")
            print(f"  Pairing Quality: {pairing:.2f}/100")
        
        # Calculate averages
        avg_balance = np.mean(all_balance_scores)
        avg_pairing = np.mean(all_pairing_scores)
        avg_distribution = {
            'high': np.mean([d['high'] for d in all_distributions]),
            'medium': np.mean([d['medium'] for d in all_distributions]),
            'low': np.mean([d['low'] for d in all_distributions])
        }
        
        # Calculate overall effectiveness
        effectiveness = self.calculate_overall_effectiveness(avg_balance, avg_pairing)
        
        # Display results
        self.display_results(avg_balance, avg_pairing, avg_distribution, effectiveness)
    
    def display_results(self, balance_score: float, pairing_score: float, 
                       distribution: Dict[str, float], 
                       effectiveness: Dict[str, Any]):
        """Display evaluation results"""
        print("\n" + "=" * 70)
        print("EVALUATION RESULTS")
        print("=" * 70)
        
        print("\n📊 ALGORITHM EFFECTIVENESS SCORES:")
        print("-" * 70)
        print(f"  Overall Effectiveness:                     {effectiveness['overall_score']:.2f}/100")
        print(f"  Quality Level:                             {effectiveness['quality_level']}")
        
        print("\n📏 COMPONENT SCORES:")
        print("-" * 70)
        print(f"  Balance Score (Row Distribution):         {balance_score:.2f}/100")
        print(f"  Pairing Quality (High-Low Mixing):        {pairing_score:.2f}/100")
        
        print("\n📈 PERFORMANCE DISTRIBUTION:")
        print("-" * 70)
        print(f"  High Performers:                           {distribution['high']:.1f}%")
        print(f"  Medium Performers:                         {distribution['medium']:.1f}%")
        print(f"  Low Performers:                            {distribution['low']:.1f}%")
        
        print("\n💡 INTERPRETATION:")
        print("-" * 70)
        print(f"  The seating arrangement algorithm achieves an overall")
        print(f"  effectiveness score of {effectiveness['overall_score']:.1f}/100, rated as '{effectiveness['quality_level']}'.")
        print()
        
        if balance_score >= 85:
            print(f"  ✓ Excellent balance: Students are evenly distributed across rows.")
        elif balance_score >= 70:
            print(f"  ~ Good balance: Most rows have similar average performance.")
        else:
            print(f"  ⚠ Balance needs improvement: Row averages vary significantly.")
        
        if pairing_score >= 85:
            print(f"  ✓ Excellent pairing: High and low performers are well-mixed.")
        elif pairing_score >= 70:
            print(f"  ~ Good pairing: Reasonable mixing of performance levels.")
        else:
            print(f"  ⚠ Pairing needs improvement: Students are not optimally mixed.")
        
        print("\n📝 NOTE:")
        print("-" * 70)
        print("  This is an OPTIMIZATION ALGORITHM, not a machine learning model.")
        print("  We measure 'effectiveness' rather than 'prediction accuracy'.")
        print("  The algorithm uses a deterministic high-low pairing strategy")
        print("  to create balanced seating arrangements.")
        
        print("\n" + "=" * 70)


def main():
    """Main execution function"""
    evaluator = AlgorithmEvaluator()
    
    try:
        evaluator.evaluate(num_tests=5)
        
        print("\n✅ Evaluation completed successfully!")
        
    except Exception as e:
        print(f"\n❌ Error during evaluation: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()
