# Student Seating Arrangement Model - Technical Documentation

Complete technical documentation for the Seating Arrangement API.

---

## 📋 Overview

This is a **Rule-Based Algorithm System** that generates optimal classroom seating arrangements based on student performance data.

**Purpose:** Create balanced seating arrangements pairing high-performing students with lower-performing students to facilitate peer learning.

---

## 🛠️ Technology Stack

### Programming Language

- **Python 3.8+** (Tested on Python 3.13.7)
- No machine learning training required

### Core Libraries

#### Data Processing

- **pandas 2.0.0+** - Data manipulation and analysis
- **numpy 1.24.0+** - Numerical operations and array handling

#### API Framework

- **Flask 3.0.0** - Web framework for REST API
- **flask-cors** - Cross-Origin Resource Sharing support

#### Utilities

- **json** - Data interchange (built-in)
- **logging** - Application logging (built-in)
- **typing** - Type hints (built-in)

### Development Tools

- **Virtual Environment (venv)** - Dependency isolation
- **pip** - Package management

---

## 🏗️ Architecture

### Design Pattern: **MVC-Inspired** (Model-View-Controller)

```
┌─────────────────────────────────────────────────────────┐
│                   API Layer (Flask)                      │
│                   [Controller/View]                      │
│  - Receives seating requests                             │
│  - Validates input data                                  │
│  - Returns JSON seat assignments                         │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│                Business Logic Layer                      │
│                [Service/Generator]                       │
│  - Implement seating algorithm                           │
│  - Apply high-low pairing strategy                       │
│  - Map students to classroom grid                        │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│                    Utility Layer                         │
│                   [Helper/Utils]                         │
│  - Data transformation                                   │
│  - Validation helpers                                    │
│  - Response formatting                                   │
└─────────────────────────────────────────────────────────┘
```

### Component Breakdown

**1. API Layer (`api/app.py`)**

- Role: HTTP interface
- Responsibilities:
  - Handle API requests
  - Input validation
  - Response formatting
  - Error handling
- Pattern: REST API Controller

**2. Business Logic (`src/seating_generator.py`)**

- Role: Seating algorithm implementation
- Responsibilities:
  - Sort students by performance
  - Apply pairing strategy
  - Generate seat assignments
  - Map to classroom layout
- Pattern: Strategy Pattern

**3. Utility Layer (`src/utils.py`)**

- Role: Helper functions
- Responsibilities:
  - Data validation
  - Format conversions
  - Logging utilities
- Pattern: Utility/Helper Pattern

**4. Configuration (`config/config.py`)**

- Role: Centralized settings
- Responsibilities:
  - API configuration
  - Classroom layout settings
  - Default parameters
- Pattern: Configuration Object

---

## 🎯 Algorithm: High-Low Pairing Strategy

### Algorithm Type: **Rule-Based** (NOT Machine Learning)

**No training required!** This uses a deterministic algorithm based on educational best practices.

### Strategy Overview

**Educational Principle:** Pairing high-performing students with lower-performing students promotes peer learning and balanced classroom dynamics.

### Algorithm Steps

```
Input: List of students with performance scores
Output: Seat assignments in classroom grid

Step 1: SORT students by average performance (descending)
   [95, 88, 82, 75, 70, 65, 60, 55]

Step 2: SPLIT into two groups
   High performers: [95, 88, 82, 75]
   Low performers:  [70, 65, 60, 55]

Step 3: PAIR high with low (zigzag pattern)
   Pair 1: 95 ↔ 70
   Pair 2: 88 ↔ 65
   Pair 3: 82 ↔ 60
   Pair 4: 75 ↔ 55

Step 4: ARRANGE in classroom grid (serpentine/zigzag)
   Row 1: [95, 88] → Left to Right
   Row 2: [65, 70] ← Right to Left (reversed)
   Row 3: [82, 75] → Left to Right
   Row 4: [60, 55] ← Right to Left (reversed)

Step 5: GENERATE seat labels and coordinates
   {row: 1, column: 1, student_id: 101, seat_label: "A1"}
   {row: 1, column: 2, student_id: 102, seat_label: "A2"}
   ...
```

### Visual Representation

**Classroom Layout (4 rows × 2 columns):**

```
┌────────────────────────────────┐
│         WHITEBOARD             │
└────────────────────────────────┘

Row 1:  [High-95]  [High-88]     →
Row 2:  [Low-65]   [Low-70]      ← (reversed)
Row 3:  [High-82]  [High-75]     →
Row 4:  [Low-60]   [Low-55]      ← (reversed)
```

### Algorithm Pseudocode

```python
def generate_seating_arrangement(students, rows, columns):
    """
    Generate seating arrangement using high-low pairing

    Args:
        students: List of student objects with performance scores
        rows: Number of rows in classroom
        columns: Number of columns per row

    Returns:
        List of seat assignments with coordinates
    """

    # Step 1: Calculate average performance for each student
    for student in students:
        student.avg_score = calculate_average(student.marks)

    # Step 2: Sort students by performance (descending)
    sorted_students = sort(students, key=avg_score, reverse=True)

    # Step 3: Split into high and low performers
    mid_point = len(sorted_students) // 2
    high_performers = sorted_students[:mid_point]
    low_performers = sorted_students[mid_point:]

    # Step 4: Create paired list (zigzag pattern)
    paired_list = []
    for i in range(max(len(high_performers), len(low_performers))):
        if i < len(high_performers):
            paired_list.append(high_performers[i])
        if i < len(low_performers):
            paired_list.append(low_performers[i])

    # Step 5: Arrange in serpentine pattern
    seat_assignments = []
    student_index = 0

    for row in range(rows):
        if row % 2 == 0:  # Even rows: left to right
            for col in range(columns):
                if student_index < len(paired_list):
                    seat = create_seat_assignment(
                        row=row + 1,
                        column=col + 1,
                        student=paired_list[student_index]
                    )
                    seat_assignments.append(seat)
                    student_index += 1
        else:  # Odd rows: right to left
            for col in range(columns - 1, -1, -1):
                if student_index < len(paired_list):
                    seat = create_seat_assignment(
                        row=row + 1,
                        column=col + 1,
                        student=paired_list[student_index]
                    )
                    seat_assignments.append(seat)
                    student_index += 1

    return seat_assignments
```

---

## 📂 File Structure

```
student-seating-arrangement-model/
│
├── api/
│   └── app.py                      # Flask API server
│
├── config/
│   └── config.py                   # Configuration settings
│
├── src/
│   ├── seating_generator.py        # Seating algorithm implementation
│   └── utils.py                    # Utility functions
│
├── dataset/                         # Sample data (optional)
│   └── sample_students.csv
│
├── docs/                            # Documentation
│   ├── DOCUMENTATION.md            # This file
│   └── SETUP.md                    # Setup guide
│
├── venv/                            # Virtual environment
│
├── requirements.txt                 # Python dependencies
├── setup.sh                         # Automated setup script
├── start_api.sh                     # Start API script
├── test_system.py                   # System tests
├── README.md                        # Quick reference
└── SETUP.md                         # Setup instructions
```

---

## 🔄 How It Works - Complete Flow

### Request Flow (Real-Time Operation)

```
┌────────────────────────────────────────┐
│  HTTP Request (JSON)                   │
│  POST /generate-seating                │
│  {                                     │
│    "grade": "13-A",                    │
│    "rows": 5,                          │
│    "columns": 4,                       │
│    "students": [                       │
│      {                                 │
│        "id": 1,                        │
│        "name": "John",                 │
│        "marks": {                      │
│          "math": 85,                   │
│          "science": 90                 │
│        }                               │
│      },                                │
│      ...                               │
│    ]                                   │
│  }                                     │
└──────┬─────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│  API Endpoint (Flask)                  │
│  - Validate request format             │
│  - Extract student data                │
│  - Extract classroom parameters        │
│  - Pass to generator                   │
└──────┬─────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│  Seating Generator                     │
│  Step 1: Calculate averages            │
│  Step 2: Sort by performance           │
│  Step 3: Split high/low groups         │
│  Step 4: Create pairs                  │
│  Step 5: Map to grid (serpentine)      │
│  Step 6: Generate seat labels          │
└──────┬─────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────┐
│  HTTP Response (JSON)                  │
│  {                                     │
│    "success": true,                    │
│    "arrangement_id": "SA-2026-001",    │
│    "seats": [                          │
│      {                                 │
│        "row": 1,                       │
│        "column": 1,                    │
│        "student_id": 1,                │
│        "seat_label": "A1"              │
│      },                                │
│      {                                 │
│        "row": 1,                       │
│        "column": 2,                    │
│        "student_id": 5,                │
│        "seat_label": "A2"              │
│      },                                │
│      ...                               │
│    ],                                  │
│    "total_students": 20,               │
│    "total_seats": 20                   │
│  }                                     │
└────────────────────────────────────────┘
```

**Key Points:**

- **No training phase** - algorithm is rule-based
- **Instant response** - calculations happen in milliseconds
- **Stateless** - each request is independent
- **Deterministic** - same input produces same output

---

## 🔌 API Documentation

### Base URL

```
http://localhost:5001
```

### Endpoints

#### 1. Health Check

```http
GET /health
```

**Response:**

```json
{
  "service": "Seating Arrangement API",
  "status": "healthy",
  "version": "1.0.0"
}
```

#### 2. Generate Seating Arrangement

```http
POST /generate-seating
Content-Type: application/json
```

**Request Body:**

```json
{
  "grade": "13-A",
  "rows": 5,
  "columns": 4,
  "students": [
    {
      "id": 1,
      "name": "John Doe",
      "marks": {
        "mathematics": 85,
        "science": 90,
        "english": 78
      }
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "marks": {
        "mathematics": 92,
        "science": 88,
        "english": 95
      }
    }
  ]
}
```

**Response (Success):**

```json
{
  "success": true,
  "arrangement_id": "SA-2026-001",
  "grade": "13-A",
  "seats": [
    {
      "row": 1,
      "column": 1,
      "student_id": 2,
      "seat_label": "A1"
    },
    {
      "row": 1,
      "column": 2,
      "student_id": 1,
      "seat_label": "A2"
    }
  ],
  "total_students": 2,
  "total_seats": 20,
  "rows": 5,
  "columns": 4,
  "generated_at": "2026-01-03T10:30:00Z"
}
```

**Response (Error):**

```json
{
  "success": false,
  "error": "ValidationError",
  "message": "Invalid input: students array is required"
}
```

### API Implementation Details

**Framework:** Flask 3.0.0

**Key Features:**

- **CORS Enabled** - Cross-origin requests allowed
- **JSON-based** - All communication in JSON format
- **Input Validation** - Required fields checked
- **Error Handling** - Comprehensive error responses
- **Logging** - All requests logged
- **Fast Processing** - Typical response time <100ms

**Port:** 5001 (configurable in `config/config.py`)

---

## 🎓 Key Methods and Functions

### Core Classes

#### 1. `SeatingGenerator` (src/seating_generator.py)

**Purpose:** Generate seating arrangements

**Key Methods:**

```python
class SeatingGenerator:
    """
    Generate classroom seating arrangements using high-low pairing
    """

    def __init__(self):
        """Initialize generator"""
        pass

    def generate(self, students, rows, columns):
        """
        Generate seating arrangement

        Args:
            students (list): Student data with marks
            rows (int): Number of rows in classroom
            columns (int): Number of columns

        Returns:
            list: Seat assignments with coordinates
        """
        # Calculate averages
        students_with_avg = self._calculate_averages(students)

        # Sort by performance
        sorted_students = self._sort_by_performance(students_with_avg)

        # Create pairing
        paired_students = self._create_pairs(sorted_students)

        # Map to grid
        seat_assignments = self._map_to_grid(
            paired_students, rows, columns
        )

        return seat_assignments

    def _calculate_averages(self, students):
        """Calculate average marks for each student"""
        for student in students:
            marks = student.get('marks', {})
            if marks:
                student['average'] = sum(marks.values()) / len(marks)
            else:
                student['average'] = 0
        return students

    def _sort_by_performance(self, students):
        """Sort students by average performance (descending)"""
        return sorted(
            students,
            key=lambda x: x.get('average', 0),
            reverse=True
        )

    def _create_pairs(self, sorted_students):
        """Create high-low pairs"""
        mid = len(sorted_students) // 2
        high = sorted_students[:mid]
        low = sorted_students[mid:]

        paired = []
        for i in range(max(len(high), len(low))):
            if i < len(high):
                paired.append(high[i])
            if i < len(low):
                paired.append(low[i])

        return paired

    def _map_to_grid(self, students, rows, columns):
        """Map students to classroom grid (serpentine pattern)"""
        seats = []
        student_idx = 0

        for row in range(rows):
            if row % 2 == 0:  # Even rows: left to right
                cols = range(columns)
            else:  # Odd rows: right to left
                cols = range(columns - 1, -1, -1)

            for col in cols:
                if student_idx < len(students):
                    seat = {
                        'row': row + 1,
                        'column': col + 1,
                        'student_id': students[student_idx]['id'],
                        'seat_label': self._generate_label(row + 1, col + 1)
                    }
                    seats.append(seat)
                    student_idx += 1

        return seats

    def _generate_label(self, row, column):
        """Generate seat label (e.g., A1, B2)"""
        row_letter = chr(64 + row)  # A, B, C, ...
        return f"{row_letter}{column}"
```

#### 2. `Utils` (src/utils.py)

**Purpose:** Helper functions

**Key Functions:**

```python
def validate_students(students):
    """
    Validate student data format

    Args:
        students (list): Student records

    Returns:
        bool: True if valid

    Raises:
        ValueError: If validation fails
    """
    if not isinstance(students, list):
        raise ValueError("Students must be a list")

    for student in students:
        if 'id' not in student:
            raise ValueError("Each student must have an id")
        if 'marks' not in student:
            raise ValueError("Each student must have marks")

    return True

def validate_classroom(rows, columns):
    """
    Validate classroom dimensions

    Args:
        rows (int): Number of rows
        columns (int): Number of columns

    Returns:
        bool: True if valid

    Raises:
        ValueError: If validation fails
    """
    if rows < 1 or columns < 1:
        raise ValueError("Rows and columns must be positive")

    if rows > 20 or columns > 20:
        raise ValueError("Classroom too large (max 20×20)")

    return True

def format_response(success, data=None, error=None):
    """
    Format API response

    Args:
        success (bool): Success status
        data (dict): Response data
        error (str): Error message

    Returns:
        dict: Formatted response
    """
    response = {
        'success': success
    }

    if success and data:
        response.update(data)
    elif not success and error:
        response['error'] = error

    return response
```

---

## 🔧 Configuration

### config/config.py

```python
# API Configuration
API_HOST = '0.0.0.0'
API_PORT = 5001
DEBUG = False

# Classroom Configuration
DEFAULT_ROWS = 5
DEFAULT_COLUMNS = 4
MAX_ROWS = 20
MAX_COLUMNS = 20

# Seating Strategy
STRATEGY = 'high_low_pairing'  # Options: 'high_low_pairing', 'random', 'performance_based'
PAIRING_MODE = 'zigzag'        # Options: 'zigzag', 'linear'

# Logging Configuration
LOG_LEVEL = 'INFO'
LOG_FILE = '/tmp/seating_api.log'
```

---

## 🚀 Performance Characteristics

### Speed

- **Average Response Time:** 50-100ms
- **Algorithm Complexity:** O(n log n) due to sorting
- **Bottleneck:** Sorting students by performance

### Scalability

- **Handles up to 400 students** (20×20 classroom)
- **No memory-intensive operations**
- **Stateless design** - easy to scale horizontally

### Limitations

1. **Fixed Strategy** - Currently only high-low pairing
2. **No Student Preferences** - Doesn't consider friendships or conflicts
3. **Static Arrangement** - Doesn't adapt over time
4. **No Special Needs** - Doesn't account for accessibility requirements

### Future Improvements

- Add multiple seating strategies
- Consider student preferences/conflicts
- Account for special needs
- Dynamic rearrangement based on performance changes
- A/B testing of different arrangements

---

## 🔍 Algorithm Variations

### Current: High-Low Pairing (Zigzag)

**Pros:**

- Balances peer learning opportunities
- Simple to implement
- Fast execution
- Predictable results

**Cons:**

- May not account for personality matches
- Ignores student preferences
- Static approach

### Alternative Strategies (Future)

**1. Random Seating**

```python
def random_seating(students, rows, columns):
    shuffled = random.shuffle(students)
    return map_to_grid(shuffled, rows, columns)
```

**2. Performance Clusters**

```python
def cluster_seating(students, rows, columns):
    # Group similar performers together
    sorted_students = sort_by_performance(students)
    return map_to_grid(sorted_students, rows, columns)
```

**3. Social Network Based**

```python
def social_seating(students, friendships, rows, columns):
    # Optimize based on friendship graph
    # (requires graph algorithm)
    pass
```

---

## 📊 Data Format

### Input Format

**Student Object:**

```json
{
  "id": 1,
  "name": "John Doe",
  "marks": {
    "mathematics": 85,
    "science": 90,
    "english": 78,
    "history": 82
  }
}
```

**Classroom Request:**

```json
{
  "grade": "13-A",
  "rows": 5,
  "columns": 4,
  "students": [...]
}
```

### Output Format

**Seat Assignment:**

```json
{
  "row": 1,
  "column": 1,
  "student_id": 1,
  "seat_label": "A1"
}
```

**Complete Response:**

```json
{
  "success": true,
  "arrangement_id": "SA-2026-001",
  "seats": [...],
  "total_students": 20,
  "total_seats": 20
}
```

---

## 🧪 Testing

### Test Cases

**1. Basic Functionality**

- Generate seating for 2 students
- Generate seating for 20 students
- Empty student list
- Single student

**2. Edge Cases**

- More students than seats
- Fewer students than seats
- Students with no marks
- Invalid classroom dimensions

**3. Algorithm Correctness**

- Verify high-low pairing
- Verify serpentine pattern
- Verify seat labels
- Verify no duplicate assignments

### Test Script

**Location:** `test_system.py`

**Run:**

```bash
python test_system.py
```

---

## 🌟 Summary

**What This Model Does:**

- Generates classroom seating arrangements
- Uses high-low pairing strategy for peer learning
- Provides REST API for integration
- No machine learning - rule-based algorithm

**Key Technologies:**

- Python + Flask (API)
- pandas + NumPy (Data processing)
- Rule-based algorithm (No ML)

**Architecture:**

- MVC-inspired layered design
- Strategy pattern for algorithms
- RESTful API interface

**Algorithm:**

- High-low pairing
- Serpentine grid mapping
- O(n log n) complexity

---

**Last Updated:** January 3, 2026
