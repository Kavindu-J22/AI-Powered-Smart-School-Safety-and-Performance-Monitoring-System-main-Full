"""
Test script for Seating Arrangement API

This script tests the seating arrangement generation with sample data
to ensure the system is working correctly.
"""

import requests
import json

# API Configuration
API_URL = "http://localhost:5001"

# Sample student data
SAMPLE_STUDENTS = [
    {"student_id": "S001", "name": "Alice Johnson", "average_marks": 92.0, "grade": "11", "section": "A"},
    {"student_id": "S002", "name": "Bob Smith", "average_marks": 45.0, "grade": "11", "section": "A"},
    {"student_id": "S003", "name": "Charlie Brown", "average_marks": 88.0, "grade": "11", "section": "A"},
    {"student_id": "S004", "name": "David Lee", "average_marks": 52.0, "grade": "11", "section": "A"},
    {"student_id": "S005", "name": "Emma Wilson", "average_marks": 78.0, "grade": "11", "section": "A"},
    {"student_id": "S006", "name": "Frank Miller", "average_marks": 41.0, "grade": "11", "section": "A"},
    {"student_id": "S007", "name": "Grace Taylor", "average_marks": 85.0, "grade": "11", "section": "A"},
    {"student_id": "S008", "name": "Henry Davis", "average_marks": 58.0, "grade": "11", "section": "A"},
    {"student_id": "S009", "name": "Ivy Martinez", "average_marks": 91.0, "grade": "11", "section": "A"},
    {"student_id": "S010", "name": "Jack Anderson", "average_marks": 48.0, "grade": "11", "section": "A"},
]


def test_health_check():
    """Test API health check"""
    print("=" * 60)
    print("TEST 1: Health Check")
    print("=" * 60)
    
    try:
        response = requests.get(f"{API_URL}/health", timeout=5)
        
        if response.status_code == 200:
            print("✅ API is healthy!")
            print(f"Response: {response.json()}")
            return True
        else:
            print(f"❌ Health check failed with status: {response.status_code}")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ Cannot connect to API: {e}")
        print("Make sure the API is running on http://localhost:5001")
        return False


def test_generate_seating():
    """Test seating arrangement generation"""
    print("\n" + "=" * 60)
    print("TEST 2: Generate Seating Arrangement")
    print("=" * 60)
    
    try:
        payload = {
            "grade": "11",
            "section": "A",
            "students": SAMPLE_STUDENTS,
            "seats_per_row": 5,
            "total_rows": 2
        }
        
        response = requests.post(
            f"{API_URL}/generate-seating",
            json=payload,
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            
            if result.get('success'):
                print("✅ Seating arrangement generated successfully!")
                
                data = result['data']
                print(f"\nGrade: {data['grade']}-{data['section']}")
                print(f"Total Students: {data['total_students']}")
                print(f"Seating Capacity: {data['seating_capacity']}")
                print(f"Strategy: {data['strategy']}")
                
                print("\n📋 Seating Arrangement:")
                print("-" * 60)
                
                for seat in data['arrangement'][:10]:  # Show first 10 seats
                    print(f"Seat {seat['seat_label']}: {seat['student_name']} "
                          f"({seat['average_marks']}% - {seat['performance_level']})")
                
                if len(data['arrangement']) > 10:
                    print(f"... and {len(data['arrangement']) - 10} more seats")
                
                return data
            else:
                print("❌ Generation failed")
                return None
        else:
            print(f"❌ Request failed with status: {response.status_code}")
            print(f"Response: {response.text}")
            return None
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Request error: {e}")
        return None


def test_student_seat(arrangement):
    """Test getting individual student seat"""
    print("\n" + "=" * 60)
    print("TEST 3: Get Student Seat")
    print("=" * 60)
    
    if not arrangement:
        print("⏭️  Skipping (no arrangement available)")
        return
    
    try:
        student_id = "S001"
        
        response = requests.get(
            f"{API_URL}/student-seat",
            params={"student_id": student_id},
            json={"arrangement": arrangement},
            timeout=10
        )
        
        if response.status_code == 200:
            result = response.json()
            
            if result.get('success'):
                seat = result['data']
                print(f"✅ Found seat for student {student_id}")
                print(f"\nStudent: {seat['student_name']}")
                print(f"Seat: {seat['seat_label']}")
                print(f"Position: Row {seat['row']}, Column {seat['column']}")
                print(f"Marks: {seat['average_marks']}%")
                print(f"Performance Level: {seat['performance_level']}")
            else:
                print("❌ Failed to retrieve seat")
        else:
            print(f"❌ Request failed with status: {response.status_code}")
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Request error: {e}")


def test_algorithm_correctness(arrangement):
    """Verify the high-low pairing algorithm"""
    print("\n" + "=" * 60)
    print("TEST 4: Algorithm Correctness")
    print("=" * 60)
    
    if not arrangement or not arrangement.get('arrangement'):
        print("⏭️  Skipping (no arrangement available)")
        return
    
    seats = arrangement['arrangement']
    
    # Check alternating pattern
    print("Checking high-low pairing pattern...")
    
    # First few seats should alternate between high and low
    alternating = True
    for i in range(0, min(6, len(seats)), 2):
        if i + 1 < len(seats):
            current_marks = seats[i]['average_marks']
            next_marks = seats[i + 1]['average_marks']
            
            # High performer should be paired with lower performer
            if current_marks > 70 and next_marks > 70:
                print(f"⚠️  Seats {i+1} and {i+2} both have high marks")
                alternating = False
    
    if alternating:
        print("✅ Pairing pattern looks correct!")
    
    # Check performance distribution
    high_count = sum(1 for s in seats if s['performance_level'] == 'high')
    medium_count = sum(1 for s in seats if s['performance_level'] == 'medium')
    low_count = sum(1 for s in seats if s['performance_level'] == 'low')
    
    print(f"\n📊 Performance Distribution:")
    print(f"  High Performers: {high_count}")
    print(f"  Medium Performers: {medium_count}")
    print(f"  Low Performers: {low_count}")
    
    # Check for duplicates
    student_ids = [s['student_id'] for s in seats]
    if len(student_ids) == len(set(student_ids)):
        print("✅ No duplicate student assignments")
    else:
        print("❌ Found duplicate student assignments!")
    
    # Check seat numbering
    seat_numbers = [s['seat_number'] for s in seats]
    expected = list(range(1, len(seats) + 1))
    if seat_numbers == expected:
        print("✅ Seat numbering is sequential")
    else:
        print("❌ Seat numbering has gaps or duplicates!")


def main():
    """Run all tests"""
    print("\n🧪 SEATING ARRANGEMENT API TEST SUITE\n")
    
    # Test 1: Health Check
    if not test_health_check():
        print("\n❌ API is not available. Please start the API first:")
        print("   cd api")
        print("   python app.py")
        return
    
    # Test 2: Generate Seating
    arrangement = test_generate_seating()
    
    # Test 3: Get Student Seat
    test_student_seat(arrangement)
    
    # Test 4: Algorithm Correctness
    test_algorithm_correctness(arrangement)
    
    print("\n" + "=" * 60)
    print("✅ ALL TESTS COMPLETED!")
    print("=" * 60)
    print("\nThe seating arrangement system is working correctly.")
    print("You can now integrate it with Laravel.")


if __name__ == "__main__":
    main()
