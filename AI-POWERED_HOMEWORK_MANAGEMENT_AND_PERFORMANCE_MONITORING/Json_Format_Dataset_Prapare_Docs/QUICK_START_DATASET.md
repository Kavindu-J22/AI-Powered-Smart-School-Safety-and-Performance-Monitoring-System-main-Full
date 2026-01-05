# Quick Start: Dataset Generation and Conversion

## 🚀 For Your Client - Simple 3-Step Guide

### Step 1: Find the Datasets

**Location**: `datasets/raw/srilanka_syllabus/`

```
datasets/
├── raw/
│   └── srilanka_syllabus/
│       ├── lessons/          ← 156 lesson files
│       │   ├── science/
│       │   ├── history/
│       │   ├── english/
│       │   └── health_science/
│       └── questions/        ← 1,560 question files
│           ├── science/
│           ├── history/
│           ├── english/
│           └── health_science/
└── srilanka_dataset_report.json  ← Summary
```

**Format**: JSONL (JSON Lines) - one JSON object per line

---

### Step 2: Understand the Format

**Example Question (JSONL format)**:
```jsonl
{"question_type": "MCQ", "question_text": "What is force?", "options": ["Push or pull", "Temperature", "Color", "Sound"], "correct_answer": "A", "marks": 1, "subject": "science", "grade": 6}
```

**Example Lesson (JSONL format)**:
```jsonl
{"subject": "science", "grade": 6, "unit": "Force and Motion", "title": "Force and Motion - Grade 6", "topics": ["Types of Forces", "Friction"], "difficulty": "beginner"}
```

---

### Step 3: Convert Your Data to JSON

#### Option A: Use CSV (Easiest)

1. **Create CSV file** with your questions:
   ```csv
   question,type,subject,grade,option_a,option_b,option_c,option_d,answer,marks
   "What is force?",MCQ,science,6,Push,Pull,Both,None,C,1
   ```

2. **Run conversion**:
   ```bash
   python scripts/convert_to_json.py --mode csv-to-jsonl --input your_file.csv --output output.jsonl
   ```

3. **Done!** Your data is now in JSON format.

#### Option B: Generate from Curriculum

1. **Edit the script** `scripts/generate_dataset_json.py`:
   ```python
   curriculum_data = {
       'science': {
           'Physics': ['Motion', 'Force', 'Energy'],
           'Chemistry': ['Atoms', 'Molecules']
       }
   }
   ```

2. **Run the script**:
   ```bash
   python scripts/generate_dataset_json.py
   ```

3. **Done!** Complete dataset generated.

---

## 📊 Dataset Statistics

Current dataset includes:
- **156 lessons** across 4 subjects
- **1,560 questions** (MCQ, Short Answer, Descriptive)
- **6 grade levels** (Grades 6-11)
- **4 subjects** (Science, History, English, Health Science)

---

## 🔧 Provided Tools

### 1. Generation Script
**File**: `scripts/generate_dataset_json.py`
- Generates complete datasets from curriculum structure
- Creates lessons and questions automatically
- Outputs JSONL format

### 2. Conversion Script
**File**: `scripts/convert_to_json.py`
- Converts CSV → JSONL
- Converts Text → JSON
- Converts JSON ↔ JSONL

### 3. Sample Template
**File**: `scripts/sample_questions_template.csv`
- Ready-to-use CSV template
- Just replace with your data

---

## 📖 Documentation Files

1. **CLIENT_DATASET_GUIDE.md** - Complete guide for clients
2. **DATASET_DOCUMENTATION.md** - Technical documentation
3. **scripts/README.md** - Script usage guide

---

## ❓ Common Questions

**Q: Why JSON format?**
A: JSON is the standard format for ML services. It's machine-readable, efficient, and universal.

**Q: What's the difference between JSON and JSONL?**
A: JSONL has one JSON object per line (no array wrapper). It's more efficient for large datasets.

**Q: Can I edit JSON files manually?**
A: Yes! Use any text editor. Just maintain the structure and use UTF-8 encoding.

**Q: How were the original datasets created?**
A: Using the `generate_dataset_json.py` script with Sri Lankan curriculum data.

---

## 🎯 Quick Commands

```bash
# Generate new dataset
python scripts/generate_dataset_json.py

# Convert CSV to JSONL
python scripts/convert_to_json.py --mode csv-to-jsonl --input data.csv --output data.jsonl

# Convert text to JSON lesson
python scripts/convert_to_json.py --mode text-to-json --input lesson.txt --output lesson.json --subject science --grade 6 --unit Physics

# View dataset report
cat datasets/srilanka_dataset_report.json
```

---

## ✅ Checklist for Your Client

- [ ] Located the datasets in `datasets/raw/srilanka_syllabus/`
- [ ] Reviewed sample JSONL files
- [ ] Understood the JSON structure
- [ ] Prepared data in CSV format (if converting)
- [ ] Ran the conversion script
- [ ] Validated the output
- [ ] Read the full documentation

---

## 📞 Next Steps

1. **Review existing datasets** to understand the structure
2. **Prepare your data** in CSV format (easiest)
3. **Use conversion script** to create JSON files
4. **Validate output** using JSON validators
5. **Integrate** with the ML service

---

**Need Help?** Check the detailed guides:
- `CLIENT_DATASET_GUIDE.md` - Complete client guide
- `DATASET_DOCUMENTATION.md` - Technical details
- `scripts/README.md` - Script documentation

---

**Last Updated**: 2026-01-05

