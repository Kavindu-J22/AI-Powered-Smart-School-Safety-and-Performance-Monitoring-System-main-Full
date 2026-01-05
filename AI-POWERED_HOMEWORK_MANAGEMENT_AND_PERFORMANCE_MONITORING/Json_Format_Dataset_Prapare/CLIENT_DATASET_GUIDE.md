# Client Guide: Understanding and Creating Datasets for Homework Management ML Service

## 📋 Table of Contents
1. [Overview](#overview)
2. [Where to Find the Datasets](#where-to-find-the-datasets)
3. [Understanding the Dataset Format](#understanding-the-dataset-format)
4. [How the Datasets Were Created](#how-the-datasets-were-created)
5. [Converting Your Data to JSON](#converting-your-data-to-json)
6. [Step-by-Step Conversion Guide](#step-by-step-conversion-guide)
7. [Provided Scripts and Tools](#provided-scripts-and-tools)
8. [FAQ](#faq)

---

## Overview

The Homework Management ML Service uses educational datasets in **JSON format** (specifically JSONL - JSON Lines format) to:
- Generate intelligent homework questions
- Evaluate student answers
- Track performance metrics
- Provide personalized recommendations

This guide explains:
- ✅ Where the datasets are located
- ✅ How they are structured
- ✅ How they were originally created
- ✅ How to convert your own data to JSON format
- ✅ Scripts provided for easy conversion

---

## Where to Find the Datasets

### Dataset Location

All datasets are stored in the following directory:

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/
└── datasets/
    ├── raw/
    │   └── srilanka_syllabus/
    │       ├── lessons/          ← Lesson content files
    │       │   ├── science/
    │       │   ├── history/
    │       │   ├── english/
    │       │   └── health_science/
    │       └── questions/        ← Question bank files
    │           ├── science/
    │           ├── history/
    │           ├── english/
    │           └── health_science/
    └── srilanka_dataset_report.json  ← Summary report
```

### Current Dataset Statistics

- **Total Lessons**: 156
- **Total Questions**: 1,560
- **Subjects**: Science, History, English, Health Science
- **Grades**: 6, 7, 8, 9, 10, 11
- **Question Types**: MCQ, Short Answer, Descriptive

---

## Understanding the Dataset Format

### Why JSON/JSONL Format?

The datasets use **JSONL (JSON Lines)** format because:

1. **Efficient Processing**: Each line is a separate JSON object, making it easy to process large files
2. **Streaming Support**: Can read and process data line-by-line without loading entire file
3. **Append-Friendly**: Easy to add new data without rewriting the entire file
4. **Machine Learning Ready**: Perfect format for ML model training
5. **Human Readable**: Still readable and editable by humans

### JSONL vs JSON

**JSONL Format** (Used in this system):
```jsonl
{"question_type": "MCQ", "question_text": "What is force?", "subject": "science"}
{"question_type": "MCQ", "question_text": "What is energy?", "subject": "science"}
```

**JSON Format** (Traditional):
```json
[
  {"question_type": "MCQ", "question_text": "What is force?", "subject": "science"},
  {"question_type": "MCQ", "question_text": "What is energy?", "subject": "science"}
]
```

### Sample Lesson Structure

Here's what a lesson looks like in JSON format:

```json
{
  "subject": "science",
  "grade": 6,
  "unit": "Force and Motion",
  "title": "Force and Motion - Grade 6",
  "topics": ["Types of Forces", "Friction", "Simple Machines", "Motion"],
  "content": "# Force and Motion - Grade 6\n\n## Learning Objectives...",
  "difficulty": "beginner",
  "duration_minutes": 60,
  "learning_outcomes": [
    "Understand Types of Forces",
    "Apply knowledge of Friction",
    "Analyze concepts related to Force and Motion"
  ]
}
```

### Sample Question Structure

Here's what a question looks like in JSON format:

**MCQ Question**:
```json
{
  "question_type": "MCQ",
  "question_text": "What is the SI unit of length?",
  "options": ["Meter", "Kilogram", "Second", "Kelvin"],
  "correct_answer": "A",
  "explanation": "The meter is the standard SI unit for measuring length.",
  "difficulty": "beginner",
  "bloom_level": "remember",
  "marks": 1,
  "subject": "science",
  "grade": 6,
  "unit": "Measurements and Units",
  "topic": "Length"
}
```

---

## How the Datasets Were Created

### Original Data Sources

The datasets were created from:

1. **Sri Lankan National Curriculum**
   - Official curriculum guidelines for grades 6-11
   - Subject syllabi (Science, History, English, Health Science)

2. **Educational Standards**
   - Bloom's Taxonomy for cognitive levels
   - International best practices in education

3. **Question Templates**
   - Common examination patterns
   - Sri Lankan assessment methods

### Creation Process

The datasets were generated using a Python script that:

1. **Defined Curriculum Structure**
   - Subjects → Units → Topics hierarchy
   - Grade-appropriate difficulty levels

2. **Generated Lessons**
   - Created lesson content in Markdown format
   - Included learning objectives and outcomes
   - Added Sri Lankan context

3. **Generated Questions**
   - Created MCQ, Short Answer, and Descriptive questions
   - Mapped to Bloom's Taxonomy levels
   - Assigned appropriate marks and difficulty

4. **Saved in JSONL Format**
   - One JSON object per line
   - Organized by subject and grade
   - UTF-8 encoding for special characters

---

## Converting Your Data to JSON

### What You Need

To convert your existing data to JSON format, you need:

1. **Your Data** in one of these formats:
   - CSV (Excel spreadsheet saved as CSV)
   - Excel file (.xlsx, .xls)
   - Text files
   - Existing JSON files

2. **Python** installed on your computer
   - Download from: https://www.python.org/downloads/
   - Version 3.7 or higher

3. **Conversion Scripts** (provided in this package)
   - `generate_dataset_json.py` - Generate new datasets
   - `convert_to_json.py` - Convert existing data

### Supported Conversion Paths

```
CSV File          →  JSONL File
Excel File        →  JSONL File (via CSV)
Text File         →  JSON Lesson File
JSON Array        →  JSONL File
JSONL File        →  JSON Array
```

---

## Step-by-Step Conversion Guide

### Option 1: Convert CSV to JSONL (Recommended)

**Step 1**: Prepare your CSV file

Create a CSV file with these columns:
- question, type, subject, grade, unit, topic
- option_a, option_b, option_c, option_d (for MCQ)
- answer, marks, difficulty, explanation

**Example CSV** (see `scripts/sample_questions_template.csv`):
```csv
question,type,subject,grade,unit,topic,option_a,option_b,option_c,option_d,answer,marks
"What is force?",MCQ,science,6,Physics,Force,Push or pull,Temperature,Color,Sound,A,1
```

**Step 2**: Run the conversion script

```bash
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
python scripts/convert_to_json.py --mode csv-to-jsonl --input your_questions.csv --output questions.jsonl
```

**Step 3**: Verify the output

Open `questions.jsonl` and check that each line is a valid JSON object.

---

### Option 2: Generate Dataset from Curriculum Structure

**Step 1**: Define your curriculum in Python

```python
from scripts.generate_dataset_json import DatasetGenerator

# Define your curriculum structure
my_curriculum = {
    'science': {
        'Physics': ['Motion', 'Force', 'Energy', 'Gravity'],
        'Chemistry': ['Atoms', 'Molecules', 'Reactions', 'Elements'],
        'Biology': ['Cells', 'Genetics', 'Evolution', 'Ecology']
    },
    'mathematics': {
        'Algebra': ['Variables', 'Equations', 'Functions', 'Graphs'],
        'Geometry': ['Shapes', 'Angles', 'Area', 'Volume']
    }
}
```

**Step 2**: Generate the dataset

```python
# Create generator
generator = DatasetGenerator(output_dir="datasets/raw/my_curriculum")

# Generate complete dataset
generator.generate_complete_dataset(my_curriculum)
```

**Step 3**: Check the output

The script will create:
- Lesson files in `datasets/raw/my_curriculum/lessons/`
- Question files in `datasets/raw/my_curriculum/questions/`
- Summary report in `datasets/dataset_report.json`

---

### Option 3: Convert Excel to JSON

**Step 1**: Save Excel as CSV

1. Open your Excel file
2. Click "File" → "Save As"
3. Choose "CSV (Comma delimited) (*.csv)"
4. Save the file

**Step 2**: Follow Option 1 (CSV to JSONL)

Use the CSV conversion method described above.

---

### Option 4: Convert Text Files to JSON Lessons

**Step 1**: Prepare your text file

Create a text file with lesson content:

```text
# Force and Motion

## Topics
- Types of Forces
- Friction
- Simple Machines
- Motion

## Content
Force is a push or pull that can change the motion of an object...
```

**Step 2**: Run the conversion

```bash
python scripts/convert_to_json.py --mode text-to-json \
    --input lesson.txt \
    --output lesson.json \
    --subject science \
    --grade 6 \
    --unit "Force and Motion"
```

---

## Provided Scripts and Tools

### 1. Dataset Generation Script

**File**: `scripts/generate_dataset_json.py`

**Purpose**: Generate complete educational datasets from curriculum structure

**Usage**:
```bash
python scripts/generate_dataset_json.py
```

**Features**:
- Generates lessons with full Markdown content
- Creates MCQ, Short Answer, and Descriptive questions
- Supports multiple subjects and grades
- Produces JSONL files
- Creates summary reports

---

### 2. Data Conversion Script

**File**: `scripts/convert_to_json.py`

**Purpose**: Convert various formats to JSON/JSONL

**Usage**:
```bash
# CSV to JSONL
python scripts/convert_to_json.py --mode csv-to-jsonl --input data.csv --output data.jsonl

# Text to JSON
python scripts/convert_to_json.py --mode text-to-json --input lesson.txt --output lesson.json --subject science --grade 6 --unit Physics

# JSON to JSONL
python scripts/convert_to_json.py --mode json-to-jsonl --input data.json --output data.jsonl

# JSONL to JSON
python scripts/convert_to_json.py --mode jsonl-to-json --input data.jsonl --output data.json
```

---

### 3. Sample CSV Template

**File**: `scripts/sample_questions_template.csv`

**Purpose**: Template for creating your own question CSV files

**How to use**:
1. Open the template in Excel or any spreadsheet software
2. Replace the sample data with your own questions
3. Save as CSV
4. Convert using the conversion script

---

## FAQ

### Q1: Why use JSON format instead of Excel or CSV?

**Answer**: JSON format offers several advantages:
- **Machine Learning Ready**: ML models can directly process JSON
- **Structured Data**: Supports nested data (like options in MCQ)
- **Efficient**: JSONL format is memory-efficient for large datasets
- **Universal**: Works across all programming languages and platforms
- **Version Control**: Easy to track changes in git

### Q2: What is the difference between JSON and JSONL?

**Answer**:
- **JSON**: Traditional format with array of objects `[{...}, {...}]`
- **JSONL**: One JSON object per line, no array wrapper
- **JSONL is better** for large datasets and streaming processing

### Q3: Can I edit JSON files manually?

**Answer**: Yes! JSON files are text files. You can:
- Open with any text editor (Notepad, VS Code, etc.)
- Edit the content
- Save with UTF-8 encoding
- Validate using online JSON validators

### Q4: How do I validate my JSON files?

**Answer**: Use online validators:
- https://jsonlint.com/
- https://jsonformatter.org/
- Or use Python:
  ```python
  import json
  with open('file.json', 'r') as f:
      data = json.load(f)  # Will raise error if invalid
  ```

### Q5: What if my data has special characters (සිංහල, தமிழ்)?

**Answer**:
- Always save files with **UTF-8 encoding**
- The provided scripts handle UTF-8 automatically
- JSON supports all Unicode characters

### Q6: Can I add more subjects or grades?

**Answer**: Yes! Simply:
1. Create new folders in the dataset structure
2. Generate or convert data for new subjects/grades
3. Update the curriculum structure in the generation script

### Q7: How do I know if my conversion was successful?

**Answer**: Check for:
- ✅ Output file is created
- ✅ File size is reasonable (not empty)
- ✅ Each line in JSONL is valid JSON
- ✅ Required fields are present
- ✅ No error messages during conversion

### Q8: Where can I get help?

**Answer**:
1. Read the documentation: `DATASET_DOCUMENTATION.md`
2. Check script README: `scripts/README.md`
3. Review sample files in `datasets/raw/srilanka_syllabus/`
4. Examine the sample CSV template

---

## Quick Reference Commands

### Generate New Dataset
```bash
python scripts/generate_dataset_json.py
```

### Convert CSV to JSONL
```bash
python scripts/convert_to_json.py --mode csv-to-jsonl --input questions.csv --output questions.jsonl
```

### Convert Text to JSON
```bash
python scripts/convert_to_json.py --mode text-to-json --input lesson.txt --output lesson.json --subject science --grade 6 --unit Physics
```

### Validate JSON File (Python)
```python
import json
with open('file.jsonl', 'r', encoding='utf-8') as f:
    for line in f:
        json.loads(line)  # Will error if invalid
print("✓ Valid JSON!")
```

---

## Summary

**Key Points**:
1. ✅ Datasets are in `datasets/raw/srilanka_syllabus/` directory
2. ✅ Format is JSONL (one JSON object per line)
3. ✅ Created from Sri Lankan curriculum using Python scripts
4. ✅ You can convert CSV, Excel, or Text to JSON using provided scripts
5. ✅ Sample templates and documentation are provided
6. ✅ Scripts handle all the technical details automatically



