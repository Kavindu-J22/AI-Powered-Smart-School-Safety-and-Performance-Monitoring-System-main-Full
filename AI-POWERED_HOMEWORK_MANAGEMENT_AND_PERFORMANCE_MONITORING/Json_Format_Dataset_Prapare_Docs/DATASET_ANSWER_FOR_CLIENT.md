# Answer to Client: Dataset Location, Format, and Conversion

## 📍 Question 1: Where Are the Datasets Located?

### Answer:

The datasets for the Homework Management ML Service are located in:

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/datasets/raw/srilanka_syllabus/
```

**Complete Directory Structure**:
```
datasets/
├── raw/
│   └── srilanka_syllabus/
│       ├── lessons/
│       │   ├── science/
│       │   │   ├── grade_6/lessons.jsonl
│       │   │   ├── grade_7/lessons.jsonl
│       │   │   ├── grade_8/lessons.jsonl
│       │   │   ├── grade_9/lessons.jsonl
│       │   │   ├── grade_10/lessons.jsonl
│       │   │   └── grade_11/lessons.jsonl
│       │   ├── history/
│       │   │   └── (same grade structure)
│       │   ├── english/
│       │   │   └── (same grade structure)
│       │   └── health_science/
│       │       └── (same grade structure)
│       └── questions/
│           ├── science/
│           │   └── (same grade structure)
│           ├── history/
│           ├── english/
│           └── health_science/
└── srilanka_dataset_report.json  ← Summary report with statistics
```

**Dataset Statistics**:
- **Total Files**: 48 lesson files + 48 question files = 96 files
- **Total Lessons**: 156 lessons
- **Total Questions**: 1,560 questions
- **Subjects**: 4 (Science, History, English, Health Science)
- **Grades**: 6 (Grades 6-11)

---

## 📄 Question 2: What Format Are the Datasets In?

### Answer:

The datasets are in **JSONL (JSON Lines)** format.

### What is JSONL?

**JSONL** is a text format where:
- Each line is a complete, valid JSON object
- No array wrapper (unlike traditional JSON)
- One record per line
- Easy to stream and process

**Example JSONL File** (`questions.jsonl`):
```jsonl
{"question_type": "MCQ", "question_text": "What is force?", "subject": "science", "grade": 6}
{"question_type": "SHORT_ANSWER", "question_text": "Explain energy", "subject": "science", "grade": 6}
{"question_type": "DESCRIPTIVE", "question_text": "Analyze motion", "subject": "science", "grade": 6}
```

**Comparison with Traditional JSON**:

Traditional JSON (Array):
```json
[
  {"question_type": "MCQ", "question_text": "What is force?"},
  {"question_type": "SHORT_ANSWER", "question_text": "Explain energy"}
]
```

JSONL (Line-by-line):
```jsonl
{"question_type": "MCQ", "question_text": "What is force?"}
{"question_type": "SHORT_ANSWER", "question_text": "Explain energy"}
```

### Why JSONL?

✅ **Memory Efficient**: Process line-by-line without loading entire file
✅ **Streaming**: Read and process data incrementally
✅ **Append-Friendly**: Easy to add new records
✅ **ML Ready**: Perfect for machine learning pipelines
✅ **Simple**: Each line is independent

---

## 🔨 Question 3: How Were These Datasets Created?

### Answer:

The datasets were generated using a **Python script** that converts curriculum data into JSON format.

### Creation Process:

**Step 1: Define Curriculum Structure**
```python
curriculum_data = {
    'science': {
        'Measurements and Units': ['Length', 'Mass', 'Time', 'Temperature'],
        'Force and Motion': ['Types of Forces', 'Friction', 'Simple Machines'],
        'Energy': ['Forms of Energy', 'Energy Transformation', 'Heat']
    },
    'history': {
        'Ancient Civilizations': ['Indus Valley', 'Mesopotamia', 'Egypt'],
        'Sri Lankan History': ['Ancient Kingdoms', 'Colonial Period']
    }
    # ... more subjects
}
```

**Step 2: Run Generation Script**
```bash
python scripts/generate_dataset_json.py
```

**Step 3: Script Generates**:
- ✅ Lesson files with full content
- ✅ Question files (MCQ, Short Answer, Descriptive)
- ✅ Proper directory structure
- ✅ Summary report

### Data Sources:

1. **Sri Lankan National Curriculum** (Grades 6-11)
2. **Educational Standards** (Bloom's Taxonomy)
3. **Question Templates** (Examination patterns)

---

## 🔄 Question 4: How to Convert Data to JSON Format?

### Answer:

We provide **two scripts** to convert your data to JSON format:

### Script 1: `generate_dataset_json.py`

**Purpose**: Generate complete datasets from curriculum structure

**Usage**:
```bash
python scripts/generate_dataset_json.py
```

**What it does**:
1. Takes curriculum data (subjects → units → topics)
2. Generates lesson content in Markdown format
3. Creates questions for each topic
4. Saves everything in JSONL format
5. Creates summary report

**Example**:
```python
from scripts.generate_dataset_json import DatasetGenerator

# Your curriculum
curriculum = {
    'mathematics': {
        'Algebra': ['Variables', 'Equations', 'Functions'],
        'Geometry': ['Shapes', 'Angles', 'Area']
    }
}

# Generate
generator = DatasetGenerator()
generator.generate_complete_dataset(curriculum)
```

---

### Script 2: `convert_to_json.py`

**Purpose**: Convert existing data (CSV, Text, JSON) to JSONL format

**Supported Conversions**:
- CSV → JSONL
- Text → JSON
- JSON → JSONL
- JSONL → JSON

**Usage Examples**:

#### Convert CSV to JSONL (Most Common)
```bash
python scripts/convert_to_json.py \
    --mode csv-to-jsonl \
    --input your_questions.csv \
    --output questions.jsonl
```

**CSV Format Required**:
```csv
question,type,subject,grade,unit,topic,option_a,option_b,option_c,option_d,answer,marks
"What is force?",MCQ,science,6,Physics,Force,Push,Pull,Both,None,C,1
"Explain energy",SHORT_ANSWER,science,6,Physics,Energy,,,,,A,3
```

#### Convert Text to JSON Lesson
```bash
python scripts/convert_to_json.py \
    --mode text-to-json \
    --input lesson.txt \
    --output lesson.json \
    --subject science \
    --grade 6 \
    --unit "Force and Motion"
```

#### Convert JSON Array to JSONL
```bash
python scripts/convert_to_json.py \
    --mode json-to-jsonl \
    --input questions_array.json \
    --output questions.jsonl
```

---

## 📦 What We Provide to You

### 1. Complete Datasets
- ✅ 156 lessons in JSONL format
- ✅ 1,560 questions in JSONL format
- ✅ Organized by subject and grade
- ✅ Summary report with statistics

### 2. Generation Script
- ✅ `scripts/generate_dataset_json.py`
- ✅ Generates datasets from curriculum structure
- ✅ Fully customizable

### 3. Conversion Script
- ✅ `scripts/convert_to_json.py`
- ✅ Converts CSV, Text, JSON to JSONL
- ✅ Command-line interface

### 4. Sample Template
- ✅ `scripts/sample_questions_template.csv`
- ✅ Ready-to-use CSV template
- ✅ Just replace with your data

### 5. Documentation
- ✅ `CLIENT_DATASET_GUIDE.md` - Complete guide
- ✅ `DATASET_DOCUMENTATION.md` - Technical details
- ✅ `QUICK_START_DATASET.md` - Quick reference
- ✅ `scripts/README.md` - Script documentation

---

## 🎯 Quick Start for Your Team

### Option A: Use Existing Datasets (Easiest)
1. Navigate to `datasets/raw/srilanka_syllabus/`
2. Review the JSONL files
3. Use them directly in your ML service

### Option B: Convert Your CSV Data
1. Prepare CSV file with your questions
2. Use sample template: `scripts/sample_questions_template.csv`
3. Run: `python scripts/convert_to_json.py --mode csv-to-jsonl --input your_file.csv --output output.jsonl`
4. Done!

### Option C: Generate from Curriculum
1. Edit `scripts/generate_dataset_json.py`
2. Define your curriculum structure
3. Run the script
4. Complete dataset generated automatically

---

## 📞 Summary

**Your Questions Answered**:

1. ✅ **Where?** → `datasets/raw/srilanka_syllabus/`
2. ✅ **Format?** → JSONL (JSON Lines) - one JSON object per line
3. ✅ **How created?** → Python script (`generate_dataset_json.py`)
4. ✅ **How to convert?** → Use `convert_to_json.py` script

**What You Get**:
- Complete datasets (156 lessons, 1,560 questions)
- Generation script (create new datasets)
- Conversion script (convert your data)
- Sample templates (CSV template)
- Full documentation (4 guide documents)

**Next Steps**:
1. Review existing datasets
2. Read `CLIENT_DATASET_GUIDE.md` for detailed instructions
3. Prepare your data in CSV format (if needed)
4. Use conversion script to create JSON files

---

**Need More Help?**
- Read: `CLIENT_DATASET_GUIDE.md` (comprehensive guide)
- Check: `QUICK_START_DATASET.md` (quick reference)
- Review: `scripts/README.md` (script usage)

