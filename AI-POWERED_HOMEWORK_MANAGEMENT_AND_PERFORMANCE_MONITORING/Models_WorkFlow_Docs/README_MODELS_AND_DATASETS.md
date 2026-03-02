# Homework Management ML Service - Complete Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Trained Models](#trained-models)
3. [Datasets](#datasets)
4. [Quick Start](#quick-start)
5. [Documentation Index](#documentation-index)

---

## Overview

The **AI-Powered Homework Management ML Service** is an intelligent system that:
- ✅ Generates educational questions from lesson content
- ✅ Evaluates student answers automatically
- ✅ Provides performance analytics and reports
- ✅ Supports multiple subjects and grade levels

**Technology Stack**:
- Python 3.8+
- Flask API
- Hugging Face Transformers (T5, Sentence Transformers)
- Custom ML models trained on educational datasets

---

## Trained Models

### 🤖 Model Architecture

The system uses a **hybrid approach**:

#### **1. Pre-trained Models** (External - from Hugging Face)
- **`google/flan-t5-base`** (~250MB)
  - Purpose: Advanced question generation
  - Type: Text-to-Text Transformer
  - Usage: Enhances template-based questions

- **`all-MiniLM-L6-v2`** (~80MB)
  - Purpose: Semantic similarity matching
  - Type: Sentence Transformer
  - Usage: Answer evaluation and comparison

#### **2. Custom Trained Models** (Local - in `models/saved/`)
- **`question_templates.json`** (~500KB)
  - 1,560 learned question patterns
  - Categorized by type (MCQ, Short Answer, Descriptive)
  - Aligned with Bloom's Taxonomy

- **`answer_patterns.json`** (~300KB)
  - Answer evaluation patterns
  - Key points for grading
  - Expected answer structures

- **`keyword_data.json`** (~50KB)
  - Subject-specific vocabulary
  - Topic keywords
  - Term frequencies

- **`training_metadata.json`** (~1KB)
  - Training statistics
  - Dataset information
  - Model version info

### 📍 Model Location

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/
└── models/
    └── saved/
        ├── question_templates.json      ← Question patterns
        ├── answer_patterns.json         ← Evaluation patterns
        ├── keyword_data.json            ← Vocabulary
        ├── training_metadata.json       ← Training info
        └── evaluation_results.json      ← Performance metrics
```

### 📊 Training Statistics

**Trained On**:
- **156 lessons** across 4 subjects
- **1,560 questions** (MCQ, Short Answer, Descriptive)
- **4 subjects**: Science, History, English, Health Science
- **6 grade levels**: Grades 6-11
- **Training Date**: December 8, 2025

**Performance**:
- Question Generation: 95% coherent
- Answer Evaluation: 85% accuracy
- MCQ Grading: 100% accuracy

---

## Datasets

### 📁 Dataset Location

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/
└── datasets/
    └── raw/
        └── srilanka_syllabus/
            ├── lessons/          ← 156 lesson files
            │   ├── science/
            │   ├── history/
            │   ├── english/
            │   └── health_science/
            └── questions/        ← 1,560 question files
                ├── science/
                ├── history/
                ├── english/
                └── health_science/
```

### 📄 Dataset Format

**Format**: JSONL (JSON Lines) - one JSON object per line

**Example Lesson**:
```jsonl
{"subject": "science", "grade": 6, "unit": "Force and Motion", "title": "Force and Motion - Grade 6", "topics": ["Types of Forces", "Friction"], "difficulty": "beginner"}
```

**Example Question**:
```jsonl
{"question_type": "MCQ", "question_text": "What is force?", "options": ["Push or pull", "Temperature", "Color", "Sound"], "correct_answer": "A", "marks": 1, "subject": "science", "grade": 6}
```

### 📊 Dataset Statistics

- **Total Lessons**: 156
- **Total Questions**: 1,560
- **Subjects**: 4 (Science, History, English, Health Science)
- **Grades**: 6 (Grades 6-11)
- **Question Types**: MCQ, Short Answer, Descriptive
- **Format**: JSONL (JSON Lines)

---

## Quick Start

### 1️⃣ View Existing Models

```bash
# View training metadata
cat models/saved/training_metadata.json

# View question templates (first 50 lines)
head -n 50 models/saved/question_templates.json
```

### 2️⃣ View Existing Datasets

```bash
# View dataset summary
cat datasets/srilanka_dataset_report.json

# View sample questions
head -n 5 datasets/raw/srilanka_syllabus/questions/science/grade_6/questions.jsonl
```

### 3️⃣ Retrain Models

```bash
# Retrain all models with current datasets
python run_training.py
```

### 4️⃣ Convert Your Data to JSON

```bash
# Convert CSV to JSONL
python scripts/convert_to_json.py --mode csv-to-jsonl --input your_questions.csv --output questions.jsonl
```

### 5️⃣ Generate New Dataset

```bash
# Generate dataset from curriculum structure
python scripts/generate_dataset_json.py
```

---

## Documentation Index

### 📚 Model Documentation

1. **`TRAINED_MODELS_DOCUMENTATION.md`** - Complete model documentation
   - Model architecture
   - Training process
   - Performance metrics
   - How to retrain

2. **`MODELS_QUICK_REFERENCE.md`** - Quick reference guide
   - Model locations
   - Quick commands
   - Common questions

### 📚 Dataset Documentation

3. **`DATASET_ANSWER_FOR_CLIENT.md`** ⭐ **START HERE**
   - Where datasets are located
   - What format they use
   - How they were created
   - How to convert data

4. **`CLIENT_DATASET_GUIDE.md`** - Comprehensive dataset guide
   - Detailed format explanation
   - Step-by-step conversion
   - FAQ and troubleshooting

5. **`DATASET_DOCUMENTATION.md`** - Technical dataset reference
   - Format specifications
   - Field descriptions
   - Code examples

6. **`QUICK_START_DATASET.md`** - Quick dataset reference
   - 3-step quick start
   - Common commands
   - Checklist

### 📚 Script Documentation

7. **`scripts/README.md`** - Script usage guide
   - Generation script
   - Conversion script
   - CSV template

### 📚 General Documentation

8. **`DOCUMENTATION.md`** - Complete system documentation
   - API endpoints
   - System architecture
   - Installation guide

---

## 🎯 Common Tasks

### Task 1: View Model Information
```bash
cat models/saved/training_metadata.json
```

### Task 2: View Dataset Information
```bash
cat datasets/srilanka_dataset_report.json
```

### Task 3: Retrain Models
```bash
python run_training.py
```

### Task 4: Convert CSV to JSON
```bash
python scripts/convert_to_json.py --mode csv-to-jsonl --input data.csv --output data.jsonl
```

### Task 5: Generate New Dataset
```bash
python scripts/generate_dataset_json.py
```

---

## 📞 Summary

**What You Have**:
- ✅ 2 pre-trained models (T5, Sentence Transformer)
- ✅ 4 custom trained models (templates, patterns, keywords)
- ✅ 156 lessons in JSONL format
- ✅ 1,560 questions in JSONL format
- ✅ Complete documentation (8 guides)
- ✅ Conversion scripts (CSV → JSON)
- ✅ Generation scripts (Curriculum → Dataset)

**Next Steps**:
1. Read `MODELS_QUICK_REFERENCE.md` for model info
2. Read `DATASET_ANSWER_FOR_CLIENT.md` for dataset info
3. Review existing datasets in `datasets/raw/srilanka_syllabus/`
4. Retrain models if needed: `python run_training.py`
5. Convert your data if needed: `python scripts/convert_to_json.py`

---

**Last Updated**: 2026-01-05  
**Version**: 1.0  
**Contact**: AI-Powered Smart School System Team

