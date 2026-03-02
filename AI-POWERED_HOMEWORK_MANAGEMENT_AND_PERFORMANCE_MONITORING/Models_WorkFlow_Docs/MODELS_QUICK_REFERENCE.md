# Homework Management ML Models - Quick Reference

## 🎯 Quick Answer: What Are the Trained Models?

The system uses **2 types of models**:

### 1. **Pre-trained Models** (External - Downloaded from Hugging Face)
- ✅ `google/flan-t5-base` - Question generation (~250MB)
- ✅ `all-MiniLM-L6-v2` - Answer similarity (~80MB)

### 2. **Custom Trained Models** (Local - Saved in `models/saved/`)
- ✅ `question_templates.json` - 1,560 question patterns (~500KB)
- ✅ `answer_patterns.json` - Answer evaluation patterns (~300KB)
- ✅ `keyword_data.json` - Vocabulary and keywords (~50KB)
- ✅ `training_metadata.json` - Training info (~1KB)

**Total Size**: ~330 MB (external) + ~1 MB (local) = **~331 MB**

---

## 📍 Where Are the Models?

### Local Custom Models:
```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/models/saved/
├── question_templates.json      ← 1,560 question patterns
├── answer_patterns.json         ← Answer evaluation patterns
├── keyword_data.json            ← Vocabulary (thousands of terms)
├── training_metadata.json       ← Training statistics
└── evaluation_results.json      ← Performance metrics
```

### External Pre-trained Models:
- Downloaded to Hugging Face cache (usually `~/.cache/huggingface/`)
- Automatically downloaded on first use
- Shared across all projects using the same models

---

## 🔧 What Each Model Does

### **question_templates.json**
**Purpose**: Stores learned question patterns

**Example**:
```json
{
  "template": "What is the primary function of {topic}?",
  "difficulty": "beginner",
  "bloom_level": "remember"
}
```

**Usage**: Generates new questions by filling templates with different topics

---

### **answer_patterns.json**
**Purpose**: Stores expected answer patterns for evaluation

**Example**:
```json
{
  "expected_answer": "Photosynthesis is the process...",
  "key_points": ["light energy", "chemical energy", "chlorophyll"],
  "marks": 3
}
```

**Usage**: Evaluates student answers by matching key points

---

### **keyword_data.json**
**Purpose**: Subject-specific vocabulary and important terms

**Example**:
```json
{
  "vocabulary": {
    "force": 45,
    "energy": 38,
    "photosynthesis": 15
  },
  "topic_keywords": {
    "science": ["force", "motion", "energy"]
  }
}
```

**Usage**: Extracts important concepts from lesson content

---

### **google/flan-t5-base** (External)
**Purpose**: Advanced question generation using AI

**Type**: Text-to-Text Transformer (T5)

**Usage**: Enhances template-based questions with more natural language

---

### **all-MiniLM-L6-v2** (External)
**Purpose**: Convert text to numerical embeddings for similarity comparison

**Type**: Sentence Transformer

**Usage**: Compares student answers with expected answers semantically

---

## 📊 Training Statistics

**Trained On**:
- 156 lessons
- 1,560 questions
- 4 subjects (Science, History, English, Health Science)
- 6 grade levels (6-11)

**Training Date**: December 8, 2025

**Performance**:
- Question Generation: 95% coherent
- Answer Evaluation: 85% accuracy
- MCQ Grading: 100% accuracy

---

## 🚀 How to Retrain Models

### Quick Command:
```bash
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
python run_training.py
```

### What Happens:
1. ✅ Loads all lessons from `datasets/raw/srilanka_syllabus/`
2. ✅ Loads all questions from `datasets/raw/srilanka_syllabus/`
3. ✅ Extracts patterns and templates
4. ✅ Saves updated models to `models/saved/`
5. ✅ Generates new `training_metadata.json`

**Time**: ~30 seconds to 2 minutes (depending on dataset size)

---

## 🔍 How to View Model Contents

### View Training Metadata:
```bash
cat models/saved/training_metadata.json
```

### View Question Templates (first 50 lines):
```bash
head -n 50 models/saved/question_templates.json
```

### View Keyword Data:
```bash
cat models/saved/keyword_data.json
```

### Using Python:
```python
import json

# Load question templates
with open('models/saved/question_templates.json', 'r') as f:
    templates = json.load(f)
    
print(f"MCQ Templates: {len(templates['MCQ'])}")
print(f"Short Answer Templates: {len(templates['SHORT_ANSWER'])}")
print(f"Descriptive Templates: {len(templates['DESCRIPTIVE'])}")
```

---

## 💡 Key Concepts

### **Hybrid Approach**
The system combines:
- **Template-based** (fast, reliable, offline)
- **AI-enhanced** (natural, contextual, requires internet)

### **Fallback Mechanism**
If external models fail to load:
- System falls back to template-based generation
- Still fully functional
- No internet required

### **Lightweight Design**
- Only ~1 MB of custom trained data
- Fast loading and processing
- Suitable for production deployment

---

## 📖 Model Workflow

### Question Generation:
```
Lesson Content
    ↓
Extract Keywords (keyword_data.json)
    ↓
Select Template (question_templates.json)
    ↓
[Optional] Enhance with T5 (flan-t5-base)
    ↓
Generated Question
```

### Answer Evaluation:
```
Student Answer
    ↓
Load Expected Answer (answer_patterns.json)
    ↓
Convert to Embeddings (all-MiniLM-L6-v2)
    ↓
Calculate Similarity
    ↓
Match Key Points
    ↓
Assign Marks & Feedback
```

---

## ❓ Common Questions

**Q: Do I need to download models manually?**
A: No. External models download automatically on first use.

**Q: Can the system work offline?**
A: Yes, using template-based mode (without T5 enhancement).

**Q: How often should I retrain?**
A: Retrain when you add new lessons/questions to the dataset.

**Q: Are the models language-specific?**
A: Currently trained on English content. Can be retrained for other languages.

**Q: Can I use different pre-trained models?**
A: Yes, modify `config/config.py` to change model names.

---

## 🔗 Related Documentation

- **Full Documentation**: `TRAINED_MODELS_DOCUMENTATION.md`
- **Dataset Guide**: `CLIENT_DATASET_GUIDE.md`
- **API Documentation**: `DOCUMENTATION.md`
- **Training Script**: `training/train_models.py`

---

**Last Updated**: 2026-01-05  
**Model Version**: 1.0

