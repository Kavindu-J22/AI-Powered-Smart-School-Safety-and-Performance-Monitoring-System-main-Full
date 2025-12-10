# AI-Powered Homework Management and Performance Monitoring System

## 📋 Executive Summary

This component introduces an intelligent system that automates the generation, delivery, and evaluation of student homework using Natural Language Processing (NLP) and Small Language Models (SLMs). The system reduces teacher workload, ensures consistency in homework assignments, and improves student performance monitoring through real-time analytics and reporting.

---

## 🎯 Key Objectives

| Objective | Description |
|-----------|-------------|
| **Automate Homework Creation** | Generate questions directly from lesson content using AI |
| **Multi-Subject Support** | Science, History, English, and Health Science |
| **Automatic Grading** | Instant MCQ grading + NLP-assisted subjective answer evaluation |
| **Performance Analytics** | Real-time dashboards with graphs, charts, and heat maps |
| **Monthly Reports** | Comprehensive progress reports for teachers and parents |

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        PRESENTATION LAYER                                │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐         │
│  │  Admin Portal   │  │ Teacher Portal  │  │ Student Portal  │         │
│  │  (Laravel/Blade)│  │  (Laravel/Blade)│  │ (Laravel/Blade) │         │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘         │
└───────────┼─────────────────────┼─────────────────────┼─────────────────┘
            │                     │                     │
            ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        APPLICATION LAYER                                 │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                    Laravel Backend (PHP 8.x)                     │   │
│  │  • HomeworkController      • PerformanceController               │   │
│  │  • SubmissionController    • MonthlyReportController             │   │
│  │  • HomeworkAIService (API Integration)                           │   │
│  └─────────────────────────────┬───────────────────────────────────┘   │
└─────────────────────────────────┼───────────────────────────────────────┘
                                  │ REST API
                                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        AI/ML LAYER (Flask API)                           │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐         │
│  │  NLP Processor  │  │Question Generator│  │Answer Evaluator │         │
│  │  • Keywords     │  │  • MCQ          │  │  • Auto-grading │         │
│  │  • Similarity   │  │  • Short Answer │  │  • Feedback     │         │
│  │  • Topics       │  │  • Descriptive  │  │  • Scoring      │         │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘         │
└─────────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                                        │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                      MySQL Database                              │   │
│  │  • lessons              • homework_submissions                   │   │
│  │  • homework             • student_performance                    │   │
│  │  • monthly_reports      • (existing school tables)               │   │
│  └─────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 System Workflow

### Complete Homework Lifecycle

```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   LESSON     │───▶│  QUESTION    │───▶│  HOMEWORK    │───▶│    ASSIGN    │
│   INPUT      │    │  GENERATION  │    │  CREATION    │    │  TO STUDENTS │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
  Teacher uploads    AI generates:        System creates      Students receive
  lesson summary     • 2 MCQs             structured          homework via
  with key topics    • 2 Short Answer     homework with       LMS dashboard
                     • 1 Descriptive      due dates
                                                                   │
┌──────────────┐    ┌──────────────┐    ┌──────────────┐           │
│   MONTHLY    │◀───│  PERFORMANCE │◀───│    AUTO      │◀──────────┘
│   REPORTS    │    │   TRACKING   │    │   GRADING    │
└──────────────┘    └──────────────┘    └──────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
  PDF reports to     Analytics on:       Instant MCQ grading
  parents/teachers   • Subject trends    NLP evaluation for
                     • Weak areas        subjective answers
                     • Class rankings
```

---

## ✨ Implemented Features

### 1. Lesson Management
- **Input**: Teachers upload lesson content with topics, keywords, and learning outcomes
- **Processing**: NLP extracts key concepts automatically
- **Storage**: Lessons linked to subjects, grades, and teachers

### 2. AI Question Generation
| Question Type | Count per Assignment | Marks | Evaluation Method |
|---------------|---------------------|-------|-------------------|
| MCQ | 2 | 1 each | Instant auto-grading |
| Short Answer | 2 | 3 each | NLP semantic similarity |
| Descriptive | 1 | 5 | Comprehensive NLP analysis |

### 3. Homework Assignment
- **Automatic Scheduling**: 2 homework assignments per subject per week
- **Random Question Selection**: Ensures variety across assignments
- **Due Date Management**: Configurable due dates with late submission tracking

### 4. Auto-Grading System
- **MCQ**: 100% automated with instant feedback
- **Short Answer**: Semantic similarity + keyword matching (60%/40% weight)
- **Descriptive**: Multi-criteria evaluation:
  - Semantic similarity (40%)
  - Key points coverage (30%)
  - Length adequacy (15%)
  - Coherence (15%)

### 5. Performance Analytics
- Individual student dashboards
- Class-level analytics
- Subject-wise performance breakdown
- Trend analysis (improving/stable/declining)
- Weak areas identification

### 6. Monthly Reports
- Overall performance summary
- Subject-wise grades and trends
- Strengths and areas for improvement
- Personalized recommendations
- Homework completion statistics

---

## 📊 Database Schema

### New Tables Created

```sql
-- 1. Lessons Table
lessons (
    lesson_id, subject_id, teacher_id, grade_level,
    unit, title, content, topics (JSON), keywords (JSON),
    learning_outcomes (JSON), difficulty, status
)

-- 2. Homework Table
homework (
    homework_id, lesson_id, subject_id, class_id,
    assigned_by, grade_level, title, description,
    questions (JSON), total_marks, assigned_date,
    due_date, status, week_number, academic_year
)

-- 3. Homework Submissions Table
homework_submissions (
    submission_id, homework_id, student_id,
    answers (JSON), evaluation_results (JSON),
    marks_obtained, percentage, grade, feedback,
    started_at, submitted_at, graded_at, status, is_late
)

-- 4. Student Performance Table
student_performance (
    performance_id, student_id, subject_id, grade_level,
    academic_year, month, total_homework_assigned,
    homework_completed, average_score, grade, trend,
    strong_areas (JSON), weak_areas (JSON), recommendations (JSON)
)

-- 5. Monthly Reports Table
monthly_reports (
    report_id, student_id, grade_level, academic_year,
    month, year, overall_average, overall_grade,
    class_rank, subject_performance (JSON),
    strengths (JSON), recommendations (JSON), status
)
```

---

## 🚀 How to Use the System

### Step 1: Adding a Lesson (Teacher)

**Option A: Via Admin Dashboard**
1. Navigate to: `Admin → Management → Lessons`
2. Click "Add New Lesson"
3. Fill in the form:
   - **Subject**: Select from Science, History, English, Health Science
   - **Grade Level**: 6-11
   - **Unit**: e.g., "Photosynthesis"
   - **Title**: e.g., "Introduction to Photosynthesis"
   - **Content**: Full lesson text/summary
   - **Topics**: Key topics covered (comma-separated)
   - **Learning Outcomes**: What students will learn
4. Click "Save Lesson"

**Option B: Via API (for bulk import)**
```bash
POST /api/lessons/parse
Content-Type: application/json

{
    "subject": "science",
    "grade": 6,
    "unit": "Living Things",
    "title": "Introduction to Cells",
    "content": "Cells are the basic units of life...",
    "topics": ["cells", "cell membrane", "nucleus"],
    "difficulty": "beginner"
}
```

---

### Step 2: Creating Homework (Teacher)

1. Navigate to: `Admin → Homework → Dashboard`
2. Click "Create Homework"
3. Fill in details:
   - **Title**: "Week 1 - Science Assignment"
   - **Subject**: Science
   - **Grade**: 6
   - **Class**: Select target class
   - **Due Date**: Select date
4. **AI Question Generation**:
   - Select a lesson from dropdown
   - Set question counts (MCQ: 2, Short: 2, Descriptive: 1)
   - Click "Generate" button
   - AI will create questions automatically
5. Review generated questions
6. Click "Create Homework"

---

### Step 3: Weekly Auto-Scheduling

The system can automatically schedule 2 homework assignments per week:

1. Navigate to: `Admin → Homework → Dashboard`
2. Click "Schedule Weekly Homework"
3. Select:
   - Subject
   - Class
   - Lesson (source content)
4. Click "Schedule"
5. System creates 2 assignments:
   - Assignment 1: Due in 3 days
   - Assignment 2: Due in 6 days

---

### Step 4: Student Homework Submission

**For Students:**
1. Login to student portal
2. Navigate to: `My Homework`
3. See list of pending assignments
4. Click on an assignment to start
5. Answer each question:
   - **MCQ**: Select A, B, C, or D
   - **Short Answer**: Type 2-3 sentences
   - **Descriptive**: Type detailed answer (100+ words)
6. Click "Submit Homework"
7. See instant results and feedback

**API for Submission:**
```bash
POST /api/homework/submissions/{submission_id}/submit
Content-Type: application/json

{
    "answers": [
        {"question_idx": 0, "answer": "A"},
        {"question_idx": 1, "answer": "B"},
        {"question_idx": 2, "answer": "Photosynthesis is the process..."},
        {"question_idx": 3, "answer": "Plants use chlorophyll to..."},
        {"question_idx": 4, "answer": "Detailed explanation of..."}
    ]
}
```

---

### Step 5: Viewing Auto-Grading Results

**Immediate Feedback to Students:**
- MCQ: Correct/Incorrect with explanation
- Short Answer: Score + feedback on missing points
- Descriptive: Detailed breakdown:
  - Semantic similarity score
  - Key points coverage
  - Improvement suggestions

**For Teachers:**
1. Navigate to: `Admin → Homework → [Assignment] → Submissions`
2. View all student submissions
3. See:
   - Marks obtained
   - Percentage
   - Grade
   - Individual question results
4. Option to override AI grades if needed

---

### Step 6: Performance Analytics

**Student Performance Dashboard:**
1. Navigate to: `Admin → Performance → Dashboard`
2. View:
   - Top performers
   - Students needing attention
   - Overall class statistics

**Individual Student View:**
1. Click on a student
2. See:
   - Subject-wise performance
   - Trend graphs
   - Recent submissions
   - Strong/weak areas

**Class Performance:**
1. Select a class
2. View:
   - Class average
   - Subject breakdown
   - Performance distribution (A, B, C, D, F)

---

### Step 7: Monthly Report Generation

**Generate Reports:**
1. Navigate to: `Admin → Monthly Reports`
2. Click "Generate Reports"
3. Select:
   - Class
   - Month/Year
4. Click "Generate"
5. System creates reports for all students

**Send to Parents:**

1. Select generated reports
2. Click "Send to Parents"
3. Parents receive email/notification with:
   - PDF report attachment
   - Summary of performance
   - Recommendations

---

## 🔌 API Endpoints Reference

### Lesson APIs
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/lessons/parse` | POST | Parse lesson and extract keywords |
| `/api/lessons/generate-questions` | POST | Generate questions from lesson |
| `/api/lessons/extract-keywords` | POST | Extract keywords from text |
| `/api/lessons/subjects` | GET | Get supported subjects |

### Homework APIs
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/homework/create` | POST | Create new homework |
| `/api/homework/schedule-weekly` | POST | Schedule 2 weekly assignments |
| `/api/homework/assign` | POST | Assign homework to students |
| `/api/homework/submit` | POST | Submit student answers |

### Evaluation APIs
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/evaluation/evaluate` | POST | Evaluate full submission |
| `/api/evaluation/evaluate-single` | POST | Evaluate single answer |
| `/api/evaluation/batch-evaluate` | POST | Batch evaluate submissions |

### Performance APIs
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/performance/student/{id}` | GET | Get student performance |
| `/api/performance/class/{id}` | GET | Get class performance |
| `/api/performance/analytics/trends` | POST | Get performance trends |
| `/api/performance/analytics/heatmap` | POST | Get heatmap data |

### Report APIs
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/reports/monthly/student/{id}` | GET | Get student monthly report |
| `/api/reports/monthly/class/{id}` | GET | Get class monthly report |
| `/api/reports/send-to-parents` | POST | Send reports to parents |

---

## 📈 Model Evaluation Results

The AI models were trained and evaluated using the Sri Lankan curriculum dataset:

### Dataset Statistics
| Metric | Value |
|--------|-------|
| Total Lessons | 156 |
| Total Questions | 1,560 |
| Subjects | 4 (Science, History, English, Health Science) |
| Grades | 6-11 |

### Model Performance
| Model | Metric | Score |
|-------|--------|-------|
| Question Generation | Validity Rate | 100% |
| MCQ Auto-Grading | Accuracy | 100% |
| Keyword Extraction | F1 Score | 20.49% |
| **Overall System** | **Average Score** | **73.5%** |

### Unit Test Results
- Total Tests: 13
- Passed: 13 ✅
- Failed: 0

---

## 🛠️ Technical Stack

| Component | Technology |
|-----------|------------|
| Frontend | Laravel Blade + Vite + Bootstrap |
| Backend | Laravel 10.x (PHP 8.x) |
| AI/ML API | Flask (Python 3.9+) |
| NLP | Sentence Transformers, NLTK |
| Question Gen | Google Flan-T5 (fallback: templates) |
| Database | MySQL 8.x |
| Caching | Laravel Cache |

---

## 📁 File Structure

```
AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING/
├── api/                          # Flask API
│   └── routes/
│       ├── lesson_routes.py      # Lesson processing
│       ├── homework_routes.py    # Homework management
│       ├── evaluation_routes.py  # Answer grading
│       ├── performance_routes.py # Analytics
│       └── report_routes.py      # Report generation
├── models/                       # AI Models
│   ├── nlp_processor.py          # NLP for text processing
│   ├── question_generator.py     # AI question generation
│   └── answer_evaluator.py       # Auto-grading system
├── training/                     # Model training
│   ├── data_loader.py            # Dataset loader
│   ├── train_models.py           # Training script
│   └── evaluate_models.py        # Evaluation script
├── datasets/                     # Sri Lankan curriculum
│   └── raw/srilanka_syllabus/
│       ├── lessons/              # Lesson content
│       └── questions/            # Question bank
├── tests/                        # Unit tests
│   └── test_models.py
├── config/                       # Configuration
│   └── config.py
├── app.py                        # Flask entry point
├── run_training.py               # Training runner
└── requirements.txt              # Python dependencies
```

---

## 🔐 Access Control

| Role | Permissions |
|------|------------|
| **Admin** | Full access to all features |
| **Teacher** | Create lessons, homework, view submissions, generate reports |
| **Parent** | View child's homework, performance, reports |
| **Student** | Submit homework, view own grades and feedback |

---

## 📝 Grading Scale

| Percentage | Grade | Description |
|------------|-------|-------------|
| 90-100% | A+ | Excellent |
| 85-89% | A | Very Good |
| 80-84% | A- | Good |
| 75-79% | B+ | Above Average |
| 70-74% | B | Average |
| 65-69% | B- | Below Average |
| 60-64% | C+ | Satisfactory |
| 55-59% | C | Pass |
| 50-54% | C- | Borderline |
| 40-49% | D | Needs Improvement |
| 0-39% | F | Fail |

---

## 🚀 Quick Start Commands

```bash
# 1. Start Laravel Backend
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve

# 2. Start Vite Frontend (new terminal)
npm install
npm run dev

# 3. Start AI API (new terminal)
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
pip install -r requirements.txt
python app.py

# 4. Run Training & Evaluation
python run_training.py

# 5. Run Unit Tests
python -m pytest tests/ -v
```

---

## 📞 Support

For technical issues or questions about the AI Homework Management System, please contact the development team.

---

**Document Version**: 1.0
**Last Updated**: November 2024
**Author**: AI Development Team

