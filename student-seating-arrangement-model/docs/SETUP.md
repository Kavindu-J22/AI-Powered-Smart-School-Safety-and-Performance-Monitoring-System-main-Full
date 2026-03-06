# Student Seating Arrangement Model - Setup Guide

Step-by-step guide to run the Seating Arrangement API from scratch or daily use.

---

## 📋 Table of Contents

1. [Run from Scratch (New PC)](#run-from-scratch-new-pc)
2. [Run After Already Setup](#run-after-already-setup)
3. [Testing the Model](#testing-the-model)
4. [Demo Without Laravel](#demo-without-laravel)
5. [Troubleshooting](#troubleshooting)

---

## 🆕 Run from Scratch (New PC)

Complete setup guide for a fresh installation.

### Prerequisites

**Check Python Version:**

```bash
python3 --version
# Should be Python 3.8 or higher
```

**Check pip:**

```bash
pip3 --version
```

**If Python not installed:**

- macOS: `brew install python3`
- Ubuntu: `sudo apt install python3 python3-pip`
- Windows: Download from python.org

### Step 1: Navigate to Model Directory

```bash
cd student-seating-arrangement-model
```

### Step 2: Run Automated Setup

```bash
# Make setup script executable
chmod +x setup.sh

# Run setup (this will take 1-2 minutes)
./setup.sh
```

**What Happens During Setup:**

1. ✅ Creates virtual environment (`venv/`)
2. ✅ Installs all dependencies
3. ✅ Creates necessary directories
4. ✅ Runs system tests
5. ✅ Verifies seating algorithm

**Expected Output:**

```
============================================================
Student Seating Arrangement Model - Setup
============================================================

Step 1: Creating virtual environment...
✓ Virtual environment created

Step 2: Installing dependencies...
✓ Dependencies installed successfully

Step 3: Creating directories...
✓ Directories created

Step 4: Running tests...
✓ Seating algorithm test passed
✓ API endpoint test passed
✓ All tests passed

============================================================
✅ Setup Complete!
============================================================

Next steps:
  1. Start API: cd api && python app.py
  2. Test API: python test_system.py
```

**Note:** Unlike the performance prediction model, this doesn't require training since it's a rule-based algorithm!

### Step 3: Verify Installation

```bash
# Check virtual environment
ls -la venv/

# Check dependencies
source venv/bin/activate
pip list | grep -E 'Flask|pandas|numpy'
```

### Step 4: Start the API

```bash
# Activate virtual environment
source venv/bin/activate

# Start API server
cd api
python app.py
```

**Expected Output:**

```
 * Serving Flask app 'app'
 * Debug mode: off
WARNING: This is a development server.
 * Running on http://0.0.0.0:5001
Press CTRL+C to quit
```

### Step 5: Test API (In New Terminal)

```bash
# Test health endpoint
curl http://localhost:5001/health

# Expected response:
# {
#   "service": "Seating Arrangement API",
#   "status": "healthy",
#   "version": "1.0.0"
# }
```

**✅ Setup Complete!** API is running on port 5001.

---

## 🔄 Run After Already Setup

If you ran the setup yesterday and just want to start working again.

### Quick Start (API Only)

```bash
# Navigate to model directory
cd student-seating-arrangement-model

# Activate virtual environment
source venv/bin/activate

# Start API
cd api
python app.py
```

**That's it!** API runs on http://localhost:5001

### Using the Convenience Script

```bash
# From project root
cd student-seating-arrangement-model

# Make script executable (first time only)
chmod +x start_api.sh

# Start API
./start_api.sh
```

### Background Mode

If you want API to run in background:

```bash
cd student-seating-arrangement-model
source venv/bin/activate
cd api
nohup python app.py > /tmp/seating_api.log 2>&1 &

# Get the process ID
echo $!
```

**Stop Background API:**

```bash
# Find process
lsof -ti:5001

# Kill process
lsof -ti:5001 | xargs kill -9
```

### Check if Already Running

```bash
# Check if port 5001 is in use
lsof -ti:5001

# If it returns a number, API is already running
# If empty, API is not running
```

---

## 🧪 Testing the Model

Multiple ways to test your seating arrangement algorithm.

### Test 1: Health Check

**Check API is responsive:**

```bash
curl http://localhost:5001/health
```

**Expected:**

```json
{
  "service": "Seating Arrangement API",
  "status": "healthy",
  "version": "1.0.0"
}
```

### Test 2: Run Test Script

**Comprehensive system test:**

```bash
cd student-seating-arrangement-model
source venv/bin/activate
python test_system.py
```

**What It Tests:**

- ✅ API is running
- ✅ Health endpoint responds
- ✅ Seating generation works
- ✅ High-low pairing is correct
- ✅ Seat labels are valid
- ✅ No duplicate assignments
- ✅ Response format

**Expected Output:**

```
============================================================
Seating Arrangement Model - System Test
============================================================

Test 1: Testing API health...
✓ API is healthy

Test 2: Testing seating generation...
Request:
{
  "grade": "13-A",
  "rows": 3,
  "columns": 2,
  "students": [
    {"id": 1, "name": "Alice", "marks": {"math": 95, "science": 92}},
    {"id": 2, "name": "Bob", "marks": {"math": 75, "science": 78}},
    {"id": 3, "name": "Charlie", "marks": {"math": 88, "science": 85}},
    {"id": 4, "name": "David", "marks": {"math": 65, "science": 70}}
  ]
}

Response:
{
  "success": true,
  "seats": [
    {"row": 1, "column": 1, "student_id": 1, "seat_label": "A1"},
    {"row": 1, "column": 2, "student_id": 3, "seat_label": "A2"},
    {"row": 2, "column": 2, "student_id": 4, "seat_label": "B2"},
    {"row": 2, "column": 1, "student_id": 2, "seat_label": "B1"}
  ]
}
✓ Seating generation successful

Test 3: Verifying high-low pairing...
✓ High performers paired with low performers

Test 4: Checking serpentine pattern...
✓ Zigzag pattern correct

Test 5: Validating seat labels...
✓ All seat labels valid

============================================================
✅ All Tests Passed!
============================================================
```

### Test 3: Manual API Testing

**Test with curl:**

```bash
curl -X POST http://localhost:5001/generate-seating \
  -H "Content-Type: application/json" \
  -d '{
    "grade": "13-A",
    "rows": 3,
    "columns": 2,
    "students": [
      {
        "id": 1,
        "name": "Alice",
        "marks": {
          "mathematics": 95,
          "science": 92
        }
      },
      {
        "id": 2,
        "name": "Bob",
        "marks": {
          "mathematics": 75,
          "science": 78
        }
      },
      {
        "id": 3,
        "name": "Charlie",
        "marks": {
          "mathematics": 88,
          "science": 85
        }
      },
      {
        "id": 4,
        "name": "David",
        "marks": {
          "mathematics": 65,
          "science": 70
        }
      }
    ]
  }'
```

**Test with Postman:**

1. Open Postman
2. Set method to POST
3. URL: `http://localhost:5001/generate-seating`
4. Headers: `Content-Type: application/json`
5. Body (raw JSON): See curl example above

### Test 4: Python Script Testing

**Create test_manual.py:**

```python
import requests
import json

# API endpoint
url = "http://localhost:5001/generate-seating"

# Test data
data = {
    "grade": "13-A",
    "rows": 3,
    "columns": 2,
    "students": [
        {
            "id": 1,
            "name": "Alice (Top Performer)",
            "marks": {
                "mathematics": 95,
                "science": 92,
                "english": 90
            }
        },
        {
            "id": 2,
            "name": "Bob (Low Performer)",
            "marks": {
                "mathematics": 60,
                "science": 65,
                "english": 62
            }
        },
        {
            "id": 3,
            "name": "Charlie (Good)",
            "marks": {
                "mathematics": 85,
                "science": 82,
                "english": 88
            }
        },
        {
            "id": 4,
            "name": "David (Needs Support)",
            "marks": {
                "mathematics": 55,
                "science": 58,
                "english": 60
            }
        }
    ]
}

# Make request
response = requests.post(url, json=data)

# Print results
print("Status Code:", response.status_code)
print("\nResponse:")
result = response.json()
print(json.dumps(result, indent=2))

# Visualize seating arrangement
if result.get('success'):
    print("\n" + "=" * 60)
    print("CLASSROOM LAYOUT")
    print("=" * 60)

    seats = result['seats']
    rows = result['rows']
    cols = result['columns']

    # Create grid
    grid = [[None for _ in range(cols)] for _ in range(rows)]

    for seat in seats:
        grid[seat['row'] - 1][seat['column'] - 1] = seat

    # Print grid
    print("\n[WHITEBOARD]")
    print()
    for i, row in enumerate(grid):
        row_label = chr(65 + i)  # A, B, C
        print(f"Row {row_label}:", end=" ")
        for seat in row:
            if seat:
                student = next(s for s in data['students'] if s['id'] == seat['student_id'])
                print(f"[{seat['seat_label']}: {student['name'].split()[0]}]", end=" ")
            else:
                print("[Empty]", end=" ")
        print()
```

**Run:**

```bash
python test_manual.py
```

---

## 🎯 Demo Without Laravel

Show the seating algorithm working with sample data independently.

### Method 1: Interactive Python Demo

**Create demo.py:**

```python
#!/usr/bin/env python3
"""
Interactive Seating Arrangement Demo
Demonstrates the algorithm without Laravel dashboard
"""

import sys
import os
sys.path.append(os.path.join(os.path.dirname(__file__), 'src'))

from seating_generator import SeatingGenerator

def print_classroom(seats, rows, cols, students_dict):
    """Visualize classroom layout"""
    print("\n" + "=" * 80)
    print("CLASSROOM LAYOUT".center(80))
    print("=" * 80)
    print()
    print("[WHITEBOARD]".center(80))
    print()

    # Create grid
    grid = [[None for _ in range(cols)] for _ in range(rows)]

    for seat in seats:
        grid[seat['row'] - 1][seat['column'] - 1] = seat

    # Print grid with student names
    for i, row in enumerate(grid):
        row_label = chr(65 + i)  # A, B, C...
        print(f"Row {row_label}: ", end="")

        for j, seat in enumerate(row):
            if seat:
                student = students_dict[seat['student_id']]
                name = student['name'].split()[0]  # First name only
                avg = student.get('average', 0)
                print(f"[{seat['seat_label']}: {name} ({avg:.0f}%)]", end=" ")
            else:
                print("[Empty]", end=" ")
        print()
    print()

def main():
    print("=" * 80)
    print("Student Seating Arrangement - Live Demo")
    print("High-Low Pairing Strategy")
    print("=" * 80)
    print()

    # Sample students
    students = [
        {
            "id": 1,
            "name": "Alice Johnson (Excellent)",
            "marks": {
                "mathematics": 95,
                "science": 92,
                "english": 90,
                "history": 93
            }
        },
        {
            "id": 2,
            "name": "Bob Smith (Needs Support)",
            "marks": {
                "mathematics": 60,
                "science": 65,
                "english": 62,
                "history": 58
            }
        },
        {
            "id": 3,
            "name": "Charlie Brown (Very Good)",
            "marks": {
                "mathematics": 88,
                "science": 85,
                "english": 87,
                "history": 86
            }
        },
        {
            "id": 4,
            "name": "David Wilson (Struggling)",
            "marks": {
                "mathematics": 55,
                "science": 58,
                "english": 60,
                "history": 57
            }
        },
        {
            "id": 5,
            "name": "Emma Davis (Good)",
            "marks": {
                "mathematics": 82,
                "science": 80,
                "english": 85,
                "history": 83
            }
        },
        {
            "id": 6,
            "name": "Frank Miller (Below Average)",
            "marks": {
                "mathematics": 68,
                "science": 70,
                "english": 65,
                "history": 67
            }
        }
    ]

    # Display student data
    print("Students to be seated:")
    print("-" * 80)
    for student in students:
        marks = student['marks']
        avg = sum(marks.values()) / len(marks)
        student['average'] = avg

        if avg >= 85:
            level = "🌟 Excellent"
        elif avg >= 75:
            level = "✅ Good"
        elif avg >= 60:
            level = "⚠️  Average"
        else:
            level = "🚨 Needs Support"

        print(f"{student['name']}")
        print(f"   Average: {avg:.1f}% - {level}")
        print(f"   Subjects: {', '.join(marks.keys())}")
        print()

    # Create students dictionary for lookup
    students_dict = {s['id']: s for s in students}

    # Classroom configuration
    rows = 3
    cols = 2
    print(f"Classroom: {rows} rows × {cols} columns = {rows * cols} seats")
    print(f"Students: {len(students)}")
    print()

    # Generate seating
    print("Generating seating arrangement using High-Low Pairing Strategy...")
    print()

    generator = SeatingGenerator()
    seats = generator.generate(students, rows, cols)

    # Display results
    print("✅ Seating arrangement generated successfully!")
    print()

    # Explain the algorithm
    print("=" * 80)
    print("ALGORITHM EXPLANATION")
    print("=" * 80)
    print()
    print("Step 1: Sort students by average performance")
    sorted_students = sorted(students, key=lambda x: x['average'], reverse=True)
    for i, s in enumerate(sorted_students):
        print(f"  {i+1}. {s['name'].split()[0]} - {s['average']:.1f}%")
    print()

    print("Step 2: Split into high and low performers")
    mid = len(sorted_students) // 2
    high = sorted_students[:mid]
    low = sorted_students[mid:]

    print(f"  High Performers: {', '.join([s['name'].split()[0] for s in high])}")
    print(f"  Low Performers: {', '.join([s['name'].split()[0] for s in low])}")
    print()

    print("Step 3: Pair high with low (zigzag)")
    print("  This promotes peer learning!")
    print()

    print("Step 4: Arrange in serpentine pattern")
    print("  Row 1: Left → Right")
    print("  Row 2: Right ← Left")
    print("  Row 3: Left → Right")
    print()

    # Show final layout
    print_classroom(seats, rows, cols, students_dict)

    # Analysis
    print("=" * 80)
    print("PAIRING ANALYSIS")
    print("=" * 80)
    print()

    for i in range(0, len(seats), 2):
        if i + 1 < len(seats):
            seat1 = seats[i]
            seat2 = seats[i + 1]

            student1 = students_dict[seat1['student_id']]
            student2 = students_dict[seat2['student_id']]

            print(f"Pair {i//2 + 1}:")
            print(f"  {student1['name'].split()[0]} ({student1['average']:.0f}%) ↔ "
                  f"{student2['name'].split()[0]} ({student2['average']:.0f}%)")

            diff = abs(student1['average'] - student2['average'])
            print(f"  Performance difference: {diff:.0f}%")

            if diff > 20:
                print("  ✅ Good pairing - significant difference for peer learning")
            elif diff > 10:
                print("  ✅ Moderate pairing - some learning opportunity")
            else:
                print("  ➡️  Similar levels - good for collaboration")
            print()

    print("=" * 80)
    print("Demo Complete!")
    print("=" * 80)

if __name__ == "__main__":
    main()
```

**Run Demo:**

```bash
cd student-seating-arrangement-model
source venv/bin/activate
python demo.py
```

**Expected Output:**

```
================================================================================
Student Seating Arrangement - Live Demo
High-Low Pairing Strategy
================================================================================

Students to be seated:
--------------------------------------------------------------------------------
Alice Johnson (Excellent)
   Average: 92.5% - 🌟 Excellent
   Subjects: mathematics, science, english, history

Bob Smith (Needs Support)
   Average: 61.3% - ⚠️  Average
   Subjects: mathematics, science, english, history

...

Classroom: 3 rows × 2 columns = 6 seats
Students: 6

Generating seating arrangement using High-Low Pairing Strategy...

✅ Seating arrangement generated successfully!

================================================================================
ALGORITHM EXPLANATION
================================================================================

Step 1: Sort students by average performance
  1. Alice - 92.5%
  2. Charlie - 86.5%
  3. Emma - 82.5%
  4. Frank - 67.5%
  5. Bob - 61.3%
  6. David - 57.5%

Step 2: Split into high and low performers
  High Performers: Alice, Charlie, Emma
  Low Performers: Frank, Bob, David

Step 3: Pair high with low (zigzag)
  This promotes peer learning!

Step 4: Arrange in serpentine pattern
  Row 1: Left → Right
  Row 2: Right ← Left
  Row 3: Left → Right

================================================================================
                              CLASSROOM LAYOUT
================================================================================

                            [WHITEBOARD]

Row A: [A1: Alice (92%)] [A2: Charlie (87%)]
Row B: [B2: Bob (61%)] [B1: Frank (68%)]
Row C: [C1: Emma (83%)] [C2: David (58%)]

================================================================================
                            PAIRING ANALYSIS
================================================================================

Pair 1:
  Alice (92%) ↔ Charlie (87%)
  Performance difference: 6%
  ➡️  Similar levels - good for collaboration

Pair 2:
  Bob (61%) ↔ Frank (68%)
  Performance difference: 7%
  ➡️  Similar levels - good for collaboration

Pair 3:
  Emma (83%) ↔ David (58%)
  Performance difference: 25%
  ✅ Good pairing - significant difference for peer learning

================================================================================
Demo Complete!
================================================================================
```

### Method 2: Web Interface Demo

**Create simple_web_demo.html:**

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seating Arrangement Demo</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
      }
      .container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }
      h1 {
        color: #333;
        text-align: center;
      }
      .controls {
        margin: 20px 0;
      }
      label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
      }
      input,
      select {
        padding: 8px;
        margin-right: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
      }
      button {
        padding: 12px 24px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 10px 5px;
      }
      button:hover {
        background: #0056b3;
      }
      .classroom {
        margin: 30px 0;
        padding: 20px;
        background: #e7f3ff;
        border-radius: 10px;
        display: none;
      }
      .classroom.show {
        display: block;
      }
      .whiteboard {
        background: #333;
        color: white;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
        border-radius: 5px;
      }
      .row {
        display: flex;
        justify-content: center;
        margin: 10px 0;
      }
      .seat {
        width: 150px;
        margin: 5px;
        padding: 15px;
        background: white;
        border: 2px solid #007bff;
        border-radius: 5px;
        text-align: center;
      }
      .seat .label {
        font-weight: bold;
        color: #007bff;
        font-size: 14px;
      }
      .seat .name {
        font-size: 16px;
        margin: 5px 0;
      }
      .seat .score {
        font-size: 12px;
        color: #666;
      }
      .student-list {
        margin: 20px 0;
      }
      .student-item {
        padding: 10px;
        margin: 5px 0;
        background: #f9f9f9;
        border-left: 4px solid #007bff;
        border-radius: 3px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>🪑 Classroom Seating Arrangement Generator</h1>

      <div class="controls">
        <h3>Classroom Configuration</h3>
        <label
          >Rows: <input type="number" id="rows" value="3" min="1" max="10"
        /></label>
        <label
          >Columns:
          <input type="number" id="columns" value="2" min="1" max="10"
        /></label>

        <h3>Sample Students</h3>
        <div id="studentList" class="student-list">
          <!-- Students will be added dynamically -->
        </div>

        <button onclick="addStudent()">➕ Add Student</button>
        <button onclick="generateSeating()">🪑 Generate Seating</button>
        <button onclick="clearAll()">🗑️ Clear</button>
      </div>

      <div class="classroom" id="classroom">
        <div class="whiteboard">WHITEBOARD</div>
        <div id="seatingGrid"></div>
      </div>
    </div>

    <script>
      let students = [
        { id: 1, name: "Alice", marks: { math: 95, science: 92 } },
        { id: 2, name: "Bob", marks: { math: 60, science: 65 } },
        { id: 3, name: "Charlie", marks: { math: 88, science: 85 } },
        { id: 4, name: "David", marks: { math: 55, science: 58 } },
      ];

      function renderStudentList() {
        const list = document.getElementById("studentList");
        list.innerHTML = "";

        students.forEach((student, index) => {
          const avg =
            Object.values(student.marks).reduce((a, b) => a + b) /
            Object.values(student.marks).length;
          const item = document.createElement("div");
          item.className = "student-item";
          item.innerHTML = `
                    <strong>${student.name}</strong> - Average: ${avg.toFixed(
            1
          )}%
                    <button onclick="removeStudent(${index})" style="float: right; padding: 5px 10px;">Remove</button>
                `;
          list.appendChild(item);
        });
      }

      function addStudent() {
        const name = prompt("Student name:");
        if (!name) return;

        const math = parseInt(prompt("Math marks (0-100):"));
        const science = parseInt(prompt("Science marks (0-100):"));

        students.push({
          id: students.length + 1,
          name: name,
          marks: { math: math, science: science },
        });

        renderStudentList();
      }

      function removeStudent(index) {
        students.splice(index, 1);
        renderStudentList();
      }

      function clearAll() {
        document.getElementById("classroom").classList.remove("show");
      }

      async function generateSeating() {
        const rows = parseInt(document.getElementById("rows").value);
        const columns = parseInt(document.getElementById("columns").value);

        const data = {
          grade: "Demo",
          rows: rows,
          columns: columns,
          students: students,
        };

        try {
          const response = await fetch(
            "http://localhost:5001/generate-seating",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify(data),
            }
          );

          const result = await response.json();

          if (result.success) {
            displaySeating(result.seats, rows, columns);
          } else {
            alert("Error: " + result.error);
          }
        } catch (error) {
          alert(
            "Error connecting to API. Make sure API is running on port 5001."
          );
          console.error(error);
        }
      }

      function displaySeating(seats, rows, columns) {
        const grid = document.getElementById("seatingGrid");
        grid.innerHTML = "";

        // Create grid structure
        const seatGrid = Array.from({ length: rows }, () =>
          Array(columns).fill(null)
        );

        seats.forEach((seat) => {
          seatGrid[seat.row - 1][seat.column - 1] = seat;
        });

        // Render grid
        seatGrid.forEach((row) => {
          const rowDiv = document.createElement("div");
          rowDiv.className = "row";

          row.forEach((seat) => {
            const seatDiv = document.createElement("div");
            seatDiv.className = "seat";

            if (seat) {
              const student = students.find((s) => s.id === seat.student_id);
              const avg =
                Object.values(student.marks).reduce((a, b) => a + b) /
                Object.values(student.marks).length;

              seatDiv.innerHTML = `
                            <div class="label">${seat.seat_label}</div>
                            <div class="name">${student.name}</div>
                            <div class="score">Avg: ${avg.toFixed(1)}%</div>
                        `;
            } else {
              seatDiv.innerHTML = '<div class="label">Empty</div>';
              seatDiv.style.opacity = "0.3";
            }

            rowDiv.appendChild(seatDiv);
          });

          grid.appendChild(rowDiv);
        });

        document.getElementById("classroom").classList.add("show");
      }

      // Initialize
      renderStudentList();
    </script>
  </body>
</html>
```

**Run:**

1. Make sure API is running on port 5001
2. Open `simple_web_demo.html` in a web browser
3. Adjust classroom size, add/remove students
4. Click "Generate Seating"
5. See the visual classroom layout!

---

## 🐛 Troubleshooting

### Problem 1: Virtual Environment Not Found

**Error:** `venv/bin/activate: No such file or directory`

**Solution:**

```bash
# Run setup first
./setup.sh

# Or create manually
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### Problem 2: Port Already in Use

**Error:** `Address already in use: Port 5001`

**Solution:**

```bash
# Find process using port
lsof -ti:5001

# Kill the process
lsof -ti:5001 | xargs kill -9

# Start API again
python api/app.py
```

### Problem 3: Module Not Found

**Error:** `ModuleNotFoundError: No module named 'flask'`

**Solution:**

```bash
# Make sure virtual environment is activated
source venv/bin/activate

# Reinstall dependencies
pip install -r requirements.txt
```

### Problem 4: API Not Responding

**Check if running:**

```bash
curl http://localhost:5001/health
```

**Check logs:**

```bash
# If running in background
tail -f /tmp/seating_api.log
```

**Restart:**

```bash
# Stop
lsof -ti:5001 | xargs kill -9

# Start
cd api && python app.py
```

### Problem 5: Wrong Python Version

**Error:** `Python 3.6 or higher required`

**Solution:**

```bash
# Check version
python3 --version

# Use specific version
python3.11 -m venv venv
```

### Problem 6: Permission Denied

**Error:** `Permission denied: ./setup.sh`

**Solution:**

```bash
chmod +x setup.sh
chmod +x start_api.sh
```

### Problem 7: Invalid Input Error

**Error:** `ValidationError: students array is required`

**Solution:**
Check your API request format. Each student must have:

- `id` (unique identifier)
- `name` (student name)
- `marks` (dictionary of subject marks)

---

## 🔍 Verification Checklist

Before considering setup complete, verify:

- [ ] Virtual environment created (`venv/` folder exists)
- [ ] Dependencies installed (`pip list` shows flask, pandas, numpy)
- [ ] API starts without errors
- [ ] Health endpoint responds: `curl http://localhost:5001/health`
- [ ] Seating generation works: run `test_system.py`
- [ ] No errors in logs

---

## 📞 Quick Reference

```bash
# Setup from scratch
./setup.sh

# Start API (manual)
source venv/bin/activate && cd api && python app.py

# Start API (script)
./start_api.sh

# Stop API
lsof -ti:5001 | xargs kill -9

# Test API
python test_system.py

# Check if running
lsof -ti:5001

# View logs
tail -f /tmp/seating_api.log
```

---

**Last Updated:** January 3, 2026
