# Student Seating Arrangement - Examples & Conditions

This document explains the seating arrangement system with real examples and the conditions that determine seat assignments.

---

## 📊 Understanding Seating Arrangements

### What Gets Generated?

For each classroom, the system generates:

1. **Seat Assignments** (row, column, student mapping)
2. **Seat Labels** (A1, B2, C3, etc.)
3. **High-Low Pairing** (balanced peer learning)
4. **Serpentine Pattern** (zigzag arrangement)

---

## 🎯 Example 1: Small Classroom (Grade 13-A)

### Input Data

**Grade:** 13-A  
**Classroom:** 3 rows × 2 columns = 6 seats  
**Students:** 6

| Student ID | Name          | Math | Science | English | Average |
| ---------- | ------------- | ---- | ------- | ------- | ------- |
| 1          | Alice Johnson | 95   | 92      | 90      | 92.3%   |
| 2          | Bob Smith     | 60   | 65      | 62      | 62.3%   |
| 3          | Charlie Brown | 88   | 85      | 87      | 86.7%   |
| 4          | David Wilson  | 55   | 58      | 60      | 57.7%   |
| 5          | Emma Davis    | 82   | 80      | 85      | 82.3%   |
| 6          | Frank Miller  | 68   | 70      | 65      | 67.7%   |

### API Request

```json
{
  "grade": "13-A",
  "rows": 3,
  "columns": 2,
  "students": [
    {
      "id": 1,
      "name": "Alice Johnson",
      "marks": {
        "mathematics": 95,
        "science": 92,
        "english": 90
      }
    },
    {
      "id": 2,
      "name": "Bob Smith",
      "marks": {
        "mathematics": 60,
        "science": 65,
        "english": 62
      }
    },
    {
      "id": 3,
      "name": "Charlie Brown",
      "marks": {
        "mathematics": 88,
        "science": 85,
        "english": 87
      }
    },
    {
      "id": 4,
      "name": "David Wilson",
      "marks": {
        "mathematics": 55,
        "science": 58,
        "english": 60
      }
    },
    {
      "id": 5,
      "name": "Emma Davis",
      "marks": {
        "mathematics": 82,
        "science": 80,
        "english": 85
      }
    },
    {
      "id": 6,
      "name": "Frank Miller",
      "marks": {
        "mathematics": 68,
        "science": 70,
        "english": 65
      }
    }
  ]
}
```

### Processing Steps

#### Step 1: Calculate Average Performance

```
Alice:   (95 + 92 + 90) / 3 = 92.3%  🌟 Excellent
Charlie: (88 + 85 + 87) / 3 = 86.7%  🌟 Excellent
Emma:    (82 + 80 + 85) / 3 = 82.3%  ✅ Good
Frank:   (68 + 70 + 65) / 3 = 67.7%  ⚠️ Average
Bob:     (60 + 65 + 62) / 3 = 62.3%  ⚠️ Average
David:   (55 + 58 + 60) / 3 = 57.7%  🚨 Needs Support
```

#### Step 2: Sort by Performance (Descending)

```
1. Alice   - 92.3%  (Highest)
2. Charlie - 86.7%
3. Emma    - 82.3%
4. Frank   - 67.7%
5. Bob     - 62.3%
6. David   - 57.7%  (Lowest)
```

#### Step 3: Split into High and Low Performers

**High Performers (Top 50%):**

- Alice (92.3%)
- Charlie (86.7%)
- Emma (82.3%)

**Low Performers (Bottom 50%):**

- Frank (67.7%)
- Bob (62.3%)
- David (57.7%)

#### Step 4: Create Zigzag Pairing

**Paired List:**

1. Alice (High)
2. Frank (Low)
3. Charlie (High)
4. Bob (Low)
5. Emma (High)
6. David (Low)

#### Step 5: Map to Serpentine Grid

**Classroom Layout:**

```
[WHITEBOARD]

Row A:  [A1: Alice (92%)]    [A2: Frank (68%)]     → Left to Right

Row B:  [B2: Bob (62%)]      [B1: Charlie (87%)]   ← Right to Left (Reversed)

Row C:  [C1: Emma (82%)]     [C2: David (58%)]     → Left to Right
```

### API Response

```json
{
  "success": true,
  "arrangement_id": "SA-2026-001",
  "grade": "13-A",
  "generated_at": "2026-01-03T10:30:00Z",
  "seats": [
    {
      "row": 1,
      "column": 1,
      "student_id": 1,
      "seat_label": "A1"
    },
    {
      "row": 1,
      "column": 2,
      "student_id": 6,
      "seat_label": "A2"
    },
    {
      "row": 2,
      "column": 1,
      "student_id": 3,
      "seat_label": "B1"
    },
    {
      "row": 2,
      "column": 2,
      "student_id": 2,
      "seat_label": "B2"
    },
    {
      "row": 3,
      "column": 1,
      "student_id": 5,
      "seat_label": "C1"
    },
    {
      "row": 3,
      "column": 2,
      "student_id": 4,
      "seat_label": "C2"
    }
  ],
  "total_students": 6,
  "total_seats": 6,
  "rows": 3,
  "columns": 2
}
```

### Visual Representation

```
================================================================================
                            CLASSROOM SEATING ARRANGEMENT
                                   Grade 13-A
================================================================================

                              [ WHITEBOARD ]

--------------------------------------------------------------------------------
Row A:
    Seat A1: Alice Johnson (92.3% - Excellent)
    Seat A2: Frank Miller (67.7% - Average)

    → Direction: Left to Right
    🔄 Pairing: High performer (Alice) with Average performer (Frank)
    📊 Gap: 24.6% difference - Good for peer learning

--------------------------------------------------------------------------------
Row B:
    Seat B1: Charlie Brown (86.7% - Excellent)
    Seat B2: Bob Smith (62.3% - Average)

    → Direction: Right to Left (Serpentine)
    🔄 Pairing: High performer (Charlie) with Average performer (Bob)
    📊 Gap: 24.4% difference - Good for peer learning

--------------------------------------------------------------------------------
Row C:
    Seat C1: Emma Davis (82.3% - Good)
    Seat C2: David Wilson (57.7% - Needs Support)

    → Direction: Left to Right
    🔄 Pairing: Good performer (Emma) with Low performer (David)
    📊 Gap: 24.6% difference - Excellent for peer support

================================================================================
```

---

## 📋 Seating Logic Conditions

### When to Generate New Arrangement

#### Condition 1: No Existing Arrangement

```
IF no arrangement exists for this grade
THEN generate new arrangement
```

**Example:** First time creating seating for Grade 13-A

---

#### Condition 2: Marks Changed

```
IF any student's marks updated AFTER last arrangement generation
THEN show "⚠️ Marks Changed - Regeneration Recommended" badge
```

**Example:**

```
Last Generated: 2026-01-01 10:00 AM
Student Mark Updated: 2026-01-02 03:00 PM

Result: ⚠️ Warning badge appears
Action: Teacher should regenerate seating
```

---

#### Condition 3: New Students Added

```
IF number of students > number of assigned seats
THEN show "⚠️ New Students - Regeneration Required" badge
```

**Example:**

```
Original: 6 students assigned
Current: 8 students in grade
Result: 2 unassigned students
Action: Must regenerate to include new students
```

---

#### Condition 4: Students Removed

```
IF assigned student_id no longer exists in grade
THEN show "⚠️ Students Removed - Update Required" badge
```

**Example:**

```
Original: Student ID 5 assigned to seat C1
Current: Student ID 5 transferred/graduated
Result: Empty seat in arrangement
Action: Regenerate to redistribute seats
```

---

### High-Low Pairing Conditions

#### Pairing Strategy Rules

**Rule 1: Calculate Averages**

```python
for student in students:
    marks = student['marks']
    student['average'] = sum(marks.values()) / len(marks)
```

**Rule 2: Sort Descending**

```python
sorted_students = sorted(
    students,
    key=lambda x: x['average'],
    reverse=True  # Highest to lowest
)
```

**Rule 3: Split in Half**

```python
mid_point = len(sorted_students) // 2
high_performers = sorted_students[:mid_point]
low_performers = sorted_students[mid_point:]
```

**Rule 4: Zigzag Pairing**

```python
paired = []
for i in range(max(len(high_performers), len(low_performers))):
    if i < len(high_performers):
        paired.append(high_performers[i])
    if i < len(low_performers):
        paired.append(low_performers[i])
```

---

### Serpentine Pattern Conditions

#### Row Direction Rules

```python
for row_index in range(rows):
    if row_index % 2 == 0:
        # Even rows (0, 2, 4...): Left to Right
        direction = "left_to_right"
        columns_order = range(columns)
    else:
        # Odd rows (1, 3, 5...): Right to Left
        direction = "right_to_left"
        columns_order = range(columns - 1, -1, -1)
```

**Visual Example:**

```
Row 0 (Even):  → [S1] [S2] [S3] [S4]
Row 1 (Odd):   ← [S8] [S7] [S6] [S5]
Row 2 (Even):  → [S9] [S10] [S11] [S12]
Row 3 (Odd):   ← [S16] [S15] [S14] [S13]
```

---

## 🎯 Example 2: Larger Classroom

### Input: Grade 11-B (20 Students, 5×4 Layout)

**Classroom:** 5 rows × 4 columns = 20 seats

**Top 5 Students:**

1. Sarah (94%)
2. Michael (91%)
3. Jessica (89%)
4. Ryan (87%)
5. Amanda (85%)

**Bottom 5 Students:** 16. Kevin (62%) 17. Laura (60%) 18. Mark (58%) 19. Nina (55%) 20. Oscar (52%)

### Generated Seating

```
[WHITEBOARD]

Row A: [Sarah-94%]  [Michael-91%]  [Jessica-89%]  [Ryan-87%]      → LTR
Row B: [Laura-60%]  [Mark-58%]     [Nina-55%]     [Kevin-62%]     ← RTL
Row C: [Amanda-85%] [Student-83%]  [Student-80%]  [Student-78%]   → LTR
Row D: [Student-70%][Student-68%]  [Student-66%]  [Student-65%]   ← RTL
Row E: [Student-75%][Student-73%]  [Student-71%]  [Oscar-52%]     → LTR
```

**Pairing Analysis:**

| Seat | Student | Performance | Adjacent To     | Gap |
| ---- | ------- | ----------- | --------------- | --- |
| A1   | Sarah   | 94% (High)  | -               | -   |
| B1   | Laura   | 60% (Low)   | Sarah (above)   | 34% |
| A2   | Michael | 91% (High)  | Sarah (left)    | 3%  |
| B2   | Mark    | 58% (Low)   | Michael (above) | 33% |

**Result:** High performers surrounded by lower performers for maximum peer learning opportunity.

---

## 💬 Dashboard Messages & Conditions

### Message 1: "✅ Current Arrangement"

**Condition:**

- Arrangement exists
- No marks updated since generation
- All students assigned
- No new students added

**Display:**

```
Last Generated: 3 days ago
Status: ✅ Current and valid
Students Assigned: 20/20
Action: No regeneration needed
```

---

### Message 2: "⚠️ Marks Changed - Regeneration Recommended"

**Condition:**

- Arrangement exists
- One or more students' marks updated AFTER generation date
- Marks changed > 5%

**Display:**

```
Last Generated: 2026-01-01 10:00 AM
Marks Last Updated: 2026-01-02 03:00 PM

⚠️ Warning: Student performance has changed
Recommendation: Regenerate seating for optimal pairing

Changed Students:
- John Doe: 65% → 78% (+13%)
- Jane Smith: 82% → 70% (-12%)

Action: Click "Generate New Arrangement"
```

---

### Message 3: "🚨 New Students - Regeneration Required"

**Condition:**

- Arrangement exists
- Student count > assigned seat count
- New students have no seat assignment

**Display:**

```
Current Arrangement: 18 seats assigned
Total Students: 20

🚨 Error: 2 students without seats
Unassigned Students:
- New Student A (Added: 2026-01-02)
- New Student B (Added: 2026-01-03)

Action: Regenerate to include all students
```

---

### Message 4: "ℹ️ Empty Seats Available"

**Condition:**

- Total seats > number of students
- Some seats remain empty

**Display:**

```
Classroom Capacity: 20 seats
Students Assigned: 18

ℹ️ Info: 2 empty seats
Empty Seats: D4, E4

Note: Arrangement optimized for current student count
```

---

## 📊 Complete Example: API Testing

### Test Case 1: Perfect Scenario

**Request:**

```bash
curl -X POST http://localhost:5001/generate-seating \
  -H "Content-Type: application/json" \
  -d '{
    "grade": "13-A",
    "rows": 3,
    "columns": 2,
    "students": [
      {"id": 1, "name": "Alice", "marks": {"math": 95, "sci": 92}},
      {"id": 2, "name": "Bob", "marks": {"math": 60, "sci": 65}},
      {"id": 3, "name": "Charlie", "marks": {"math": 88, "sci": 85}},
      {"id": 4, "name": "David", "marks": {"math": 55, "sci": 58}}
    ]
  }'
```

**Response:**

```json
{
  "success": true,
  "arrangement_id": "SA-2026-001",
  "seats": [
    { "row": 1, "column": 1, "student_id": 1, "seat_label": "A1" },
    { "row": 1, "column": 2, "student_id": 3, "seat_label": "A2" },
    { "row": 2, "column": 2, "student_id": 4, "seat_label": "B2" },
    { "row": 2, "column": 1, "student_id": 2, "seat_label": "B1" }
  ],
  "message": "Seating arrangement generated successfully"
}
```

**Visual:**

```
[WHITEBOARD]

A1: Alice (93.5%)    A2: Charlie (86.5%)   → LTR
B1: Bob (62.5%)      B2: David (56.5%)     ← RTL

Pairing:
- Row A: Two high performers together
- Row B: Two low performers together
- Vertical: A1-B1 (Alice-Bob): 31% gap ✅
- Vertical: A2-B2 (Charlie-David): 30% gap ✅
```

---

### Test Case 2: Uneven Numbers

**Request:**

```json
{
  "grade": "11-C",
  "rows": 3,
  "columns": 2,
  "students": [
    { "id": 1, "name": "Student A", "marks": { "math": 90 } },
    { "id": 2, "name": "Student B", "marks": { "math": 85 } },
    { "id": 3, "name": "Student C", "marks": { "math": 70 } },
    { "id": 4, "name": "Student D", "marks": { "math": 65 } },
    { "id": 5, "name": "Student E", "marks": { "math": 60 } }
  ]
}
```

**Result:** 5 students, 6 seats (3×2)

```
A1: Student A (90%)  A2: Student B (85%)   → LTR
B1: Student C (70%)  B2: Student D (65%)   ← RTL
C1: Student E (60%)  C2: [Empty]           → LTR
```

**Condition:** Last seat empty because odd number of students

---

## 🎓 Summary of Conditions

### Seating Generation Triggers

| Trigger            | Condition                                   | Action Required        |
| ------------------ | ------------------------------------------- | ---------------------- |
| **First Time**     | No arrangement exists                       | Generate new           |
| **Marks Updated**  | Student marks changed after last generation | Regenerate recommended |
| **New Students**   | More students than assigned seats           | Regenerate required    |
| **Students Left**  | Assigned students no longer in grade        | Regenerate required    |
| **Manual Request** | Teacher clicks "Generate New"               | Regenerate             |

### Pairing Outcomes

| Scenario                            | Result  | Benefit                 |
| ----------------------------------- | ------- | ----------------------- |
| High (90%) next to Low (55%)        | 35% gap | Excellent peer learning |
| High (85%) next to Average (70%)    | 15% gap | Good collaboration      |
| Average (70%) next to Average (68%) | 2% gap  | Balanced teamwork       |

### Warning Badges

| Badge                    | Condition           | Color  |
| ------------------------ | ------------------- | ------ |
| ✅ Current               | Up to date          | Green  |
| ⚠️ Marks Changed         | Performance updated | Yellow |
| 🚨 Regeneration Required | Structure mismatch  | Red    |
| ℹ️ Empty Seats           | Seats > Students    | Blue   |

---

**Last Updated:** January 3, 2026
