# Dataset Generation and Conversion Scripts

This directory contains scripts for generating and converting educational datasets for the Homework Management ML Service.

## 📁 Available Scripts

### 1. `generate_dataset_json.py`
**Purpose**: Generate complete educational datasets in JSON/JSONL format from curriculum data.

**Features**:
- Generates lessons with full content in Markdown format
- Creates questions (MCQ, SHORT_ANSWER, DESCRIPTIVE) for each topic
- Supports multiple subjects, grades, and difficulty levels
- Produces structured JSONL files for efficient processing
- Creates summary reports

**Usage**:
```bash
# Basic usage - generates dataset with example curriculum
python generate_dataset_json.py

# The script will create:
# - datasets/raw/srilanka_syllabus/lessons/{subject}/grade_{X}/lessons.jsonl
# - datasets/raw/srilanka_syllabus/questions/{subject}/grade_{X}/questions.jsonl
# - datasets/dataset_report.json
```

**Customization**:
```python
from generate_dataset_json import DatasetGenerator

# Define your curriculum
my_curriculum = {
    'science': {
        'Physics': ['Motion', 'Force', 'Energy'],
        'Chemistry': ['Atoms', 'Molecules', 'Reactions']
    }
}

# Generate dataset
generator = DatasetGenerator(output_dir="datasets/raw/my_curriculum")
generator.generate_complete_dataset(my_curriculum)
```

---

### 2. `convert_to_json.py`
**Purpose**: Convert various data formats (CSV, Text, JSON) to JSON/JSONL format.

**Features**:
- CSV to JSONL conversion
- Text files to JSON lessons
- JSON array to JSONL conversion
- JSONL to JSON array conversion
- Automatic field mapping
- UTF-8 encoding support

**Usage**:

#### Convert CSV to JSONL
```bash
python convert_to_json.py --mode csv-to-jsonl \
    --input questions.csv \
    --output questions.jsonl
```

**Expected CSV Format**:
```csv
question,type,subject,grade,unit,topic,option_a,option_b,option_c,option_d,answer,marks
"What is force?",MCQ,science,6,Physics,Force,"A force","B force","C force","D force",A,1
```

#### Convert Text to JSON Lesson
```bash
python convert_to_json.py --mode text-to-json \
    --input lesson.txt \
    --output lesson.json \
    --subject science \
    --grade 6 \
    --unit "Physics Basics"
```

#### Convert JSON to JSONL
```bash
python convert_to_json.py --mode json-to-jsonl \
    --input questions_array.json \
    --output questions.jsonl
```

#### Convert JSONL to JSON
```bash
python convert_to_json.py --mode jsonl-to-json \
    --input questions.jsonl \
    --output questions_array.json
```

---

## 📊 Data Format Specifications

### JSONL Format (Recommended)
- One JSON object per line
- Memory efficient for large datasets
- Easy to stream and process
- Append-friendly

**Example**:
```jsonl
{"question_type": "MCQ", "question_text": "What is force?", "subject": "science"}
{"question_type": "SHORT_ANSWER", "question_text": "Explain energy", "subject": "science"}
```

### JSON Format
- Standard JSON array or object
- Human-readable with indentation
- Good for small datasets

**Example**:
```json
[
  {"question_type": "MCQ", "question_text": "What is force?"},
  {"question_type": "SHORT_ANSWER", "question_text": "Explain energy"}
]
```

---

## 🔧 Installation

No additional dependencies required beyond standard Python libraries:

```bash
# All scripts use only Python standard library
# No pip install needed!
```

For Excel support (optional):
```bash
pip install pandas openpyxl
```

---

## 📝 CSV Template for Questions

Create a CSV file with the following columns:

| Column | Description | Example |
|--------|-------------|---------|
| question | Question text | "What is photosynthesis?" |
| type | Question type | MCQ, SHORT_ANSWER, DESCRIPTIVE |
| subject | Subject name | science, history, english |
| grade | Grade level | 6, 7, 8, 9, 10, 11 |
| unit | Unit/chapter | "Plant Kingdom" |
| topic | Specific topic | "Photosynthesis" |
| option_a | MCQ option A | "Process of making food" |
| option_b | MCQ option B | "Process of breathing" |
| option_c | MCQ option C | "Process of growth" |
| option_d | MCQ option D | "Process of reproduction" |
| answer | Correct answer | A, B, C, or D |
| marks | Points value | 1, 3, 5 |
| difficulty | Difficulty level | beginner, intermediate, advanced |

---

## 🎯 Quick Start Examples

### Example 1: Generate Dataset from Scratch
```python
from generate_dataset_json import DatasetGenerator

curriculum = {
    'mathematics': {
        'Algebra': ['Variables', 'Equations', 'Functions'],
        'Geometry': ['Shapes', 'Angles', 'Area', 'Volume']
    }
}

generator = DatasetGenerator()
generator.generate_complete_dataset(curriculum)
```

### Example 2: Convert Existing CSV Data
```bash
# Prepare your CSV file with questions
# Then convert it:
python convert_to_json.py --mode csv-to-jsonl \
    --input my_questions.csv \
    --output datasets/raw/questions.jsonl
```

### Example 3: Programmatic Conversion
```python
from convert_to_json import DataConverter

converter = DataConverter()

# Convert CSV
converter.csv_to_jsonl('questions.csv', 'questions.jsonl')

# Convert text lesson
converter.text_to_json('lesson.txt', 'lesson.json', 
                       subject='science', grade=6, unit='Physics')
```

---

## 📖 Additional Resources

- **Main Documentation**: `../DATASET_DOCUMENTATION.md`
- **Dataset Location**: `../datasets/raw/srilanka_syllabus/`
- **Dataset Report**: `../datasets/srilanka_dataset_report.json`

---

## 🐛 Troubleshooting

**Issue**: CSV conversion fails
- **Solution**: Check CSV encoding (should be UTF-8)
- **Solution**: Verify column names match expected format

**Issue**: Generated files are empty
- **Solution**: Check input data structure
- **Solution**: Verify file paths are correct

**Issue**: Unicode characters not displaying
- **Solution**: Ensure UTF-8 encoding in all files
- **Solution**: Use `encoding='utf-8'` when opening files

---

## 💡 Best Practices

1. **Always backup original data** before conversion
2. **Use JSONL for large datasets** (>1000 records)
3. **Validate JSON structure** after conversion
4. **Use consistent naming conventions** for files
5. **Keep raw and processed data separate**

---

**Last Updated**: 2026-01-05

