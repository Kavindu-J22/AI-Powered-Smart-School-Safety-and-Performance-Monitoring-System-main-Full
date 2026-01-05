# Homework Management ML Service - Dataset Documentation

## Overview

This document explains how the datasets for the Homework Management ML Service are structured, where they are located, and how to generate them in JSON format.

---

## 📁 Dataset Location

The datasets are stored in JSON/JSONL format in the following directory structure:

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/
├── datasets/
│   ├── raw/
│   │   └── srilanka_syllabus/
│   │       ├── lessons/
│   │       │   ├── science/
│   │       │   │   ├── grade_6/
│   │       │   │   │   └── lessons.jsonl
│   │       │   │   ├── grade_7/
│   │       │   │   │   └── lessons.jsonl
│   │       │   │   └── ... (grades 8-11)
│   │       │   ├── history/
│   │       │   ├── english/
│   │       │   └── health_science/
│   │       └── questions/
│   │           ├── science/
│   │           │   ├── grade_6/
│   │           │   │   └── questions.jsonl
│   │           │   ├── grade_7/
│   │           │   │   └── questions.jsonl
│   │           │   └── ... (grades 8-11)
│   │           ├── history/
│   │           ├── english/
│   │           └── health_science/
│   └── srilanka_dataset_report.json
```

---

## 📊 Dataset Statistics

Based on the current dataset:

- **Total Lessons**: 156
- **Total Questions**: 1,560
- **Subjects**: 4 (Science, History, English, Health Science)
- **Grades**: 6 (Grades 6-11)
- **Question Types**: MCQ, SHORT_ANSWER, DESCRIPTIVE

### Breakdown by Subject:
- **Science**: 48 lessons, 480 questions
- **History**: 36 lessons, 360 questions
- **English**: 36 lessons, 360 questions
- **Health Science**: 36 lessons, 360 questions

---

## 📝 Data Format

### 1. JSONL Format (JSON Lines)

The datasets use **JSONL format** where each line is a valid JSON object. This format is:
- Easy to stream and process
- Memory efficient for large datasets
- Simple to append new data

### 2. Lesson Format

Each lesson is stored as a JSON object with the following structure:

```json
{
  "subject": "science",
  "grade": 6,
  "unit": "Measurements and Units",
  "title": "Measurements and Units - Grade 6",
  "topics": ["Length", "Mass", "Time", "Temperature", "SI Units"],
  "content": "# Measurements and Units - Grade 6\n\n## Learning Objectives...",
  "difficulty": "beginner",
  "duration_minutes": 60,
  "learning_outcomes": [
    "Understand Length",
    "Apply knowledge of Mass",
    "Analyze concepts related to Measurements and Units"
  ]
}
```

**Fields Explanation:**
- `subject`: Subject name (science, history, english, health_science)
- `grade`: Grade level (6-11)
- `unit`: Unit/chapter name
- `title`: Lesson title
- `topics`: Array of topics covered in the lesson
- `content`: Full lesson content in Markdown format
- `difficulty`: Difficulty level (beginner, intermediate, advanced)
- `duration_minutes`: Expected lesson duration
- `learning_outcomes`: Array of learning objectives

### 3. Question Format

Each question is stored as a JSON object. There are three types:

#### A. Multiple Choice Question (MCQ)

```json
{
  "question_type": "MCQ",
  "question_text": "What is the correct usage of Length?",
  "options": [
    "Option A related to Length",
    "Option B related to Length",
    "Option C related to Length",
    "Option D related to Length"
  ],
  "correct_answer": "A",
  "explanation": "This is correct because Length functions in this way according to Measurements and Units principles.",
  "difficulty": "beginner",
  "bloom_level": "remember",
  "marks": 1,
  "subject": "science",
  "grade": 6,
  "unit": "Measurements and Units",
  "topic": "Length"
}
```

#### B. Short Answer Question

```json
{
  "question_type": "SHORT_ANSWER",
  "question_text": "Explain the concept of Mass in the context of Measurements and Units.",
  "expected_answer": "A comprehensive explanation of Mass including its key aspects.",
  "key_points": [
    "Definition of Mass",
    "Relationship to Measurements and Units",
    "Practical application"
  ],
  "difficulty": "beginner",
  "bloom_level": "understand",
  "marks": 3,
  "subject": "science",
  "grade": 6,
  "unit": "Measurements and Units",
  "topic": "Mass"
}
```

#### C. Descriptive Question

```json
{
  "question_type": "DESCRIPTIVE",
  "question_text": "Analyze the role of Temperature in Measurements and Units and provide examples.",
  "expected_answer": "A detailed analysis of Temperature with examples and critical evaluation.",
  "key_points": [
    "Theoretical understanding of Temperature",
    "Practical applications in Measurements and Units",
    "Critical analysis and evaluation",
    "Real-world examples"
  ],
  "difficulty": "beginner",
  "bloom_level": "analyze",
  "marks": 5,
  "subject": "science",
  "grade": 6,
  "unit": "Measurements and Units",
  "topic": "Temperature"
}
```

---

## 🔧 How to Generate Datasets

### Method 1: Using the Dataset Generation Script

We provide a Python script to generate datasets in JSON format from curriculum data.

**Location**: `scripts/generate_dataset_json.py`

**Usage:**

```bash
# Navigate to the project directory
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING

# Run the generation script
python scripts/generate_dataset_json.py
```

**What the script does:**
1. Takes curriculum data (subjects, units, topics)
2. Generates lessons in JSONL format
3. Generates questions (MCQ, SHORT_ANSWER, DESCRIPTIVE) for each topic
4. Saves files in the proper directory structure
5. Creates a summary report

### Method 2: Custom Dataset Generation

You can customize the script to generate datasets for your own curriculum:

```python
from scripts.generate_dataset_json import DatasetGenerator

# Define your curriculum data
my_curriculum = {
    'science': {
        'Physics': ['Motion', 'Force', 'Energy'],
        'Chemistry': ['Atoms', 'Molecules', 'Reactions'],
        'Biology': ['Cells', 'Genetics', 'Evolution']
    },
    'mathematics': {
        'Algebra': ['Equations', 'Functions', 'Graphs'],
        'Geometry': ['Shapes', 'Angles', 'Area']
    }
}

# Generate dataset
generator = DatasetGenerator(output_dir="datasets/raw/my_curriculum")
generator.generate_complete_dataset(my_curriculum)
```

---

## 🔄 Converting Existing Data to JSON

If you have data in other formats (CSV, Excel, Text), here's how to convert it:

### From CSV to JSON

```python
import csv
import json

def csv_to_jsonl(csv_file, jsonl_file):
    """Convert CSV file to JSONL format"""
    with open(csv_file, 'r', encoding='utf-8') as f_in:
        reader = csv.DictReader(f_in)

        with open(jsonl_file, 'w', encoding='utf-8') as f_out:
            for row in reader:
                # Convert CSV row to JSON object
                json_obj = {
                    "question_type": row.get('type', 'MCQ'),
                    "question_text": row.get('question', ''),
                    "options": [
                        row.get('option_a', ''),
                        row.get('option_b', ''),
                        row.get('option_c', ''),
                        row.get('option_d', '')
                    ],
                    "correct_answer": row.get('answer', 'A'),
                    "subject": row.get('subject', ''),
                    "grade": int(row.get('grade', 6)),
                    "marks": int(row.get('marks', 1))
                }
                f_out.write(json.dumps(json_obj, ensure_ascii=False) + '\n')

# Usage
csv_to_jsonl('questions.csv', 'questions.jsonl')
```

### From Excel to JSON

```python
import pandas as pd
import json

def excel_to_jsonl(excel_file, jsonl_file, sheet_name='Sheet1'):
    """Convert Excel file to JSONL format"""
    # Read Excel file
    df = pd.read_excel(excel_file, sheet_name=sheet_name)

    # Convert to JSONL
    with open(jsonl_file, 'w', encoding='utf-8') as f:
        for _, row in df.iterrows():
            json_obj = {
                "question_type": row.get('Type', 'MCQ'),
                "question_text": row.get('Question', ''),
                "subject": row.get('Subject', ''),
                "grade": int(row.get('Grade', 6)),
                "marks": int(row.get('Marks', 1))
            }
            f.write(json.dumps(json_obj, ensure_ascii=False) + '\n')

# Usage
excel_to_jsonl('questions.xlsx', 'questions.jsonl')
```

### From Text Files to JSON

```python
import json

def text_to_json_lessons(text_file, json_file):
    """Convert structured text file to JSON lessons"""
    lessons = []

    with open(text_file, 'r', encoding='utf-8') as f:
        content = f.read()
        # Parse your text format here
        # This is an example structure

        lesson = {
            "subject": "science",
            "grade": 6,
            "unit": "Physics",
            "title": "Introduction to Physics",
            "topics": ["Motion", "Force", "Energy"],
            "content": content,
            "difficulty": "beginner",
            "duration_minutes": 60
        }
        lessons.append(lesson)

    # Save as JSON
    with open(json_file, 'w', encoding='utf-8') as f:
        json.dump(lessons, f, indent=2, ensure_ascii=False)

# Usage
text_to_json_lessons('lesson.txt', 'lesson.json')
```

---

## 📖 Reading and Using the Datasets

### Reading JSONL Files

```python
import json

def read_jsonl(file_path):
    """Read JSONL file and return list of objects"""
    data = []
    with open(file_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                data.append(json.loads(line))
    return data

# Usage
lessons = read_jsonl('datasets/raw/srilanka_syllabus/lessons/science/grade_6/lessons.jsonl')
questions = read_jsonl('datasets/raw/srilanka_syllabus/questions/science/grade_6/questions.jsonl')

print(f"Loaded {len(lessons)} lessons")
print(f"Loaded {len(questions)} questions")
```

### Reading JSON Files

```python
import json

def read_json(file_path):
    """Read JSON file"""
    with open(file_path, 'r', encoding='utf-8') as f:
        return json.load(f)

# Usage
report = read_json('datasets/srilanka_dataset_report.json')
print(f"Total lessons: {report['statistics']['total_lessons']}")
```

---

## 🎯 Dataset Sources

The datasets were generated from the following sources:

1. **Sri Lankan National Curriculum** (English Medium)
   - Official curriculum guidelines for grades 6-11
   - Subject syllabi for Science, History, English, and Health Science

2. **Educational Standards**
   - Bloom's Taxonomy for question difficulty levels
   - International educational best practices

3. **Question Templates**
   - Based on common examination patterns
   - Aligned with Sri Lankan educational assessment methods

---

## 🔍 Dataset Quality Assurance

The datasets include:

✅ **Structured Format**: Consistent JSON schema across all files
✅ **Metadata**: Complete information about subject, grade, difficulty
✅ **Question Types**: Multiple formats (MCQ, Short Answer, Descriptive)
✅ **Bloom's Taxonomy**: Questions mapped to cognitive levels
✅ **Curriculum Alignment**: Based on official Sri Lankan curriculum

---

## 💡 Tips for Working with the Datasets

1. **Use JSONL for Large Datasets**: JSONL allows streaming and is memory-efficient
2. **Validate JSON**: Always validate JSON structure before processing
3. **Backup Original Data**: Keep backups before converting or modifying
4. **Use UTF-8 Encoding**: Ensures proper handling of special characters
5. **Version Control**: Track changes to datasets using git

---

## 🛠️ Tools and Libraries

Recommended Python libraries for working with JSON datasets:

```bash
pip install pandas      # For Excel/CSV conversion
pip install openpyxl    # For Excel file support
pip install jsonschema  # For JSON validation
```


