# 📚 DOCUMENT 1: AI-Powered Homework Management System
### AI-Powered Smart School Safety and Performance Monitoring System
---

## 1. EXECUTIVE SUMMARY

The AI-Powered Homework Management System is an intelligent sub-module of the broader Smart School Safety and Performance Monitoring System, built using **Laravel (PHP)** as the web framework and a **Python Flask microservice** for NLP/ML-powered question generation and answer evaluation. The system automates the creation of homework assignments from lesson content, schedules recurring weekly assignments per subject, and automatically grades student submissions using semantic NLP analysis.

**Key Capabilities:**
- AI-generated questions (MCQ, Short Answer, Descriptive) from lesson content
- Weekly homework scheduling (2 assignments/subject/week)
- Automated answer evaluation with NLP semantic similarity
- Bloom's Taxonomy-aligned question difficulty
- Fallback local generation when AI service is offline
- Parent and student notification on assignment creation

---

## 2. TECHNOLOGY STACK

| Layer | Technology |
|---|---|
| Web Framework | Laravel 11 (PHP) |
| AI/ML Backend | Python 3.12 + Flask |
| NLP Model | Google FLAN-T5-Base (HuggingFace Transformers) |
| Semantic Similarity | sentence-transformers / spaCy |
| Database | MySQL (via Laravel Eloquent ORM) |
| Question Generation | Template-based + T5 model fallback |
| Answer Evaluation | Cosine similarity, keyword coverage scoring |
| API Communication | Laravel HTTP Client → Flask REST API |

---

## 3. SYSTEM ARCHITECTURE

```
┌────────────────────────────────────────────────────────────┐
│                  Laravel Web Application                    │
│  ┌──────────────────┐    ┌──────────────────────────────┐  │
│  │ HomeworkController│    │HomeworkSubmissionController  │  │
│  │  - create()       │    │  - submit()                  │  │
│  │  - generateQ()    │    │  - autoGrade()               │  │
│  │  - scheduleWeekly │    │  - viewResults()             │  │
│  └────────┬─────────┘    └──────────┬───────────────────┘  │
│           │ HTTP Request             │ HTTP Request          │
└───────────┼──────────────────────────┼──────────────────────┘
            ▼                          ▼
┌──────────────────────────────────────────────────────────┐
│              Python Flask Microservice                     │
│  ┌─────────────────┐  ┌──────────────────────────────┐   │
│  │ QuestionGenerator│  │     AnswerEvaluator          │   │
│  │  - FLAN-T5-Base  │  │  - MCQ instant grade         │   │
│  │  - NLP templates │  │  - NLP semantic similarity   │   │
│  │  - MCQ/SA/DESC   │  │  - Keyword coverage          │   │
│  └─────────────────┘  └──────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐  │
│  │              NLPProcessor (spaCy/transformers)       │  │
│  │  - calculate_similarity()  - extract_keywords()     │  │
│  └─────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
            │
            ▼
     MySQL Database
  (homework, homework_submissions, lessons, students, subjects)
```

---

## 4. IMPLEMENTATION DETAILS

### 4.1 Core Files and Their Roles

| File | Role |
|---|---|
| `HomeworkController.php` | Web routes: list, create, generate questions, schedule weekly |
| `HomeworkAIService.php` | Laravel service class connecting to Flask API with 300s timeout |
| `HomeworkSubmissionController.php` | Student submission, auto-grading via API |
| `Homework.php` (Model) | Eloquent model: questions (JSON), due_date, week_number, status |
| `HomeworkSubmission.php` | Submission model: answers, ai_score, teacher_score, feedback |
| `question_generator.py` | NLP question generator (MCQ, Short, Descriptive) |
| `answer_evaluator.py` | Automated grading engine |
| `nlp_processor.py` | Core NLP: similarity, keyword extraction, summarization |
| `homework_routes.py` | Flask routes: /generate-questions, /schedule-weekly, /evaluate |

### 4.2 Question Generation Pipeline

```
Lesson Content (title, topics, content, learning_outcomes, keywords)
        │
        ▼
  NLPProcessor.extract_keywords()
        │
        ▼
  QuestionGenerator.generate_questions(lesson_data, num_mcq, num_short, num_desc)
        │
        ├──► _generate_mcq()
        │       - Template selection (anti-repeat used_templates list)
        │       - _extract_correct_option() from learning_outcomes or content
        │       - _generate_distractors() (cross-topic + negation strategies)
        │       - Shuffle options → random correct answer position (A-D)
        │       - Bloom level: "remember"
        │
        ├──► _generate_short_answer()
        │       - Template selection
        │       - _extract_expected_answer() → relevant sentences + outcomes
        │       - key_points list for grading rubric
        │       - Bloom level: "understand"  | marks: 3
        │
        └──► _generate_descriptive()
                - Comprehensive expected_answer from all outcomes
                - 5+ key_points including Sri Lankan context
                - Bloom level: "analyze"  | marks: 5
```

### 4.3 Weekly Scheduling Logic

```
scheduleWeekly(subject_id, class_id, lesson_id)
        │
        ▼
   HomeworkAIService → Flask /api/lessons/schedule-weekly
        │
        ├─ Assignment 1: Due Wednesday (today + 3 days)
        └─ Assignment 2: Due Friday (today + 5 days)
        │
        ▼
   Homework::create() × 2 per week
   Fields: title, description, questions(JSON), total_marks,
           assigned_date, due_date, week_number, academic_year
        │
        ▼
   Notifications sent to students/parents
```

### 4.4 Answer Evaluation Engine

```
Student submits answers
        │
        ▼
AnswerEvaluator.evaluate_answer(question, student_answer)
        │
        ├── MCQ:
        │    compare student_answer == correct_answer (instant)
        │    → marks: 1 | feedback: Correct / Incorrect
        │
        ├── SHORT_ANSWER:
        │    similarity = cosine_similarity(student_answer, expected_answer)
        │    keyword_coverage = matched_keywords / total_key_points
        │    combined_score = 0.6 × similarity + 0.4 × keyword_coverage
        │    → marks: 0-3 | threshold: 0.60
        │
        └── DESCRIPTIVE:
             similarity = cosine_similarity(student_answer, expected_answer)
             key_point_matches = count(key_points_found_in_answer)
             combined_score = 0.5 × similarity + 0.5 × key_points_coverage
             → marks: 0-5 | threshold: 0.50
```

### 4.5 Fallback Mechanism

When the Flask AI service is unavailable:
- `HomeworkAIService` catches the connection exception
- Laravel logs a warning: *"AI service unavailable, using local generation"*
- `generateLocalAssignments()` in `HomeworkController` creates basic template assignments
- System remains operational without AI service dependency

---

## 5. DATABASE SCHEMA

```sql
-- homeworks table
CREATE TABLE homeworks (
  id               BIGINT AUTO_INCREMENT PRIMARY KEY,
  subject_id       BIGINT,
  class_id         BIGINT,
  lesson_id        BIGINT,
  assigned_by      BIGINT,          -- teacher_id
  grade_level      INT,
  title            VARCHAR(255),
  description      TEXT,
  questions        JSON,            -- Array of question objects
  total_marks      INT,
  assigned_date    DATE,
  due_date         DATE,
  status           ENUM('active','completed','cancelled'),
  week_number      INT,
  academic_year    VARCHAR(9),      -- e.g., "2024/2025"
  created_at       TIMESTAMP,
  updated_at       TIMESTAMP
);

-- homework_submissions table
CREATE TABLE homework_submissions (
  id               BIGINT AUTO_INCREMENT PRIMARY KEY,
  homework_id      BIGINT,
  student_id       BIGINT,
  answers          JSON,            -- Student's answers per question
  ai_score         DECIMAL(5,2),   -- Auto-graded score
  teacher_score    DECIMAL(5,2),   -- Manual override
  feedback         TEXT,
  submitted_at     TIMESTAMP,
  status           ENUM('submitted','graded','reviewed')
);
```

---

## 6. API ENDPOINTS (Flask Microservice)

| Endpoint | Method | Description |
|---|---|---|
| `/api/lessons/generate-questions` | POST | Generate MCQ/Short/Descriptive from lesson |
| `/api/lessons/schedule-weekly` | POST | Generate 2 weekly assignments |
| `/api/homework/evaluate` | POST | Auto-grade a student submission |
| `/api/homework/create` | POST | Create a new homework record |
| `/api/status` | GET | Health check of AI service |

---

## 7. SYSTEM FLOW DIAGRAM

```
TEACHER                LARAVEL APP              FLASK AI SERVICE         DATABASE
   │                        │                         │                      │
   ├─ Opens Lesson ─────────►│                         │                      │
   │                        ├─ GET /lessons/{id} ─────────────────────────────►│
   │                        │◄─ Lesson Data ──────────────────────────────────┤
   │                        │                         │                      │
   ├─ Click "Generate Q" ───►│                         │                      │
   │                        ├─ POST /generate-questions►│                      │
   │                        │                         ├─ NLP Processing      │
   │                        │                         ├─ T5 Model / Template │
   │                        │◄─ Questions JSON ────────┤                      │
   │◄─ Preview Questions ───┤                         │                      │
   │                        │                         │                      │
   ├─ Confirm & Save ────────►│                         │                      │
   │                        ├─ Homework::create() ─────────────────────────────►│
   │                        │◄─ Saved ────────────────────────────────────────┤
   │                        │                         │                      │
STUDENT                     │                         │                      │
   ├─ Views Homework ────────►│                         │                      │
   ├─ Submits Answers ───────►│                         │                      │
   │                        ├─ POST /evaluate ─────────►│                      │
   │                        │                         ├─ MCQ: instant grade  │
   │                        │                         ├─ NLP: similarity     │
   │                        │◄─ Scores + Feedback ─────┤                      │
   │                        ├─ Submission::save() ─────────────────────────────►│
   │◄─ Results Shown ────────┤                         │                      │
```

---

## 8. RESEARCH PANEL — QUESTION & ANSWER (Q&A)

### 🔬 Important Q&A for Research / Viva / Panel Presentation

---

**Q1. What is the primary purpose of the Homework Management System in this project?**

> **A:** The primary purpose is to automate the entire homework lifecycle — from AI-powered question generation based on lesson content, to weekly scheduling, to automated grading of student submissions — reducing teacher workload while providing consistent, curriculum-aligned assessments. It uses NLP models (Google FLAN-T5) and semantic similarity to generate questions aligned with Bloom's Taxonomy levels.

---

**Q2. What NLP model is used for question generation, and why was it chosen?**

> **A:** **Google FLAN-T5-Base** (HuggingFace Transformers) is used. It was chosen because it is a lightweight yet capable instruction-tuned language model that can perform question generation, summarization, and answer extraction from instructional text without fine-tuning on domain-specific data. It has a template-based fallback for offline operation.

---

**Q3. How does the system prevent generating repetitive or identical questions?**

> **A:** The system maintains a `used_templates` list per generation session. Each time a template is selected (e.g., "What is the primary function of {topic}?"), it is added to the used list. The next question picks from the remaining unused templates. When all templates are exhausted, the list is cleared and recycled, guaranteeing variation.

---

**Q4. How are MCQ distractors (wrong options) generated?**

> **A:** Two strategies are used: (1) **Cross-topic distractors** — using the correct-option phrasing of *other* topics in the lesson as plausible but incorrect answers. (2) **Negation/misconception statements** — subject-specific false statements (e.g., for science: "X has no significant role in Y", for history: "X developed long after the period of Y"). Options are shuffled randomly so the correct answer is NOT always option A.

---

**Q5. What is Bloom's Taxonomy alignment and how is it implemented?**

> **A:** Bloom's Taxonomy classifies learning objectives into levels: Remember, Understand, Analyze, Evaluate, Create. The system maps: MCQ → "remember" level (recall facts), Short Answer → "understand" level (explain/describe), Descriptive → "analyze" level (evaluate/examine critically). This ensures assessments test progressively deeper understanding.

---

**Q6. How does the automated grading system evaluate short answer questions?**

> **A:** Using **cosine semantic similarity** between the student's answer and the expected answer (derived from lesson learning outcomes and content sentences). Additionally, it checks **keyword coverage** — how many key_points from the rubric appear in the student's response. The combined score formula is: `0.6 × similarity + 0.4 × keyword_coverage`, with a 0.60 threshold for the SHORT_ANSWER type.

---

**Q7. What happens if the Flask AI microservice is unavailable?**

> **A:** Laravel's `HomeworkAIService` has a try-catch around all HTTP calls. If the service is unreachable (connection timeout, server down), it catches the exception, logs a warning, and falls back to `generateLocalAssignments()` in `HomeworkController` — which generates basic template-based assignments. This ensures the system remains functional without the AI backend.

---

**Q8. How does the weekly homework scheduling work?**

> **A:** The `scheduleWeekly()` method takes subject, class, and lesson IDs. It calls the Flask `/schedule-weekly` endpoint which generates two distinct assignments (varying question mix). Assignment 1 is due Wednesday (today +3 days) and Assignment 2 is due Friday (today +5 days). Both are saved with `week_number` (ISO week of year) and `academic_year` (e.g., 2024/2025) for tracking.

---

**Q9. How is the academic year automatically determined?**

> **A:** `Homework::getCurrentAcademicYear()` checks the current month. If the month is January–August, the academic year is the previous-to-current year pair (e.g., 2024/2025). If September–December, it shifts forward. This aligns with Sri Lankan school calendar conventions.

---

**Q10. What security measures are in place for the homework API?**

> **A:** The Laravel `HomeworkController` uses route middleware (auth, role-based permissions via Spatie Laravel Permission). Teachers can only create/manage homework for their assigned subjects. Students can only view and submit to their class assignments. API endpoints to the Flask service are internal (not publicly exposed) and protected by Laravel's authentication layer.

---

**Q11. What database structure stores generated questions?**

> **A:** Questions are stored in a **JSON column** in the `homeworks` table. Each question object contains: `question_type`, `question_text`, `options` (MCQ), `correct_answer`, `expected_answer` (subjective), `key_points`, `marks`, `difficulty`, `bloom_level`, `subject`, `grade`, `unit`, `topic`. This flexible JSON structure supports all three question types in a single column.

---

**Q12. How does the system handle class imbalance in answer quality evaluation?**

> **A:** The evaluation thresholds differ per question type: SHORT_ANSWER uses 0.60 similarity threshold (higher bar — answers expected to be concise and precise), DESCRIPTIVE uses 0.50 (more lenient — student elaboration is valued). Partial marks are awarded proportionally rather than all-or-nothing, rewarding partial understanding.

---

**Q13. How does the HomeworkAIService handle the cold-start latency problem with ML models?**

> **A:** A **300-second (5-minute) timeout** is configured via `config('services.homework_ai.generate_timeout', 300)`. This is explicitly designed to handle the case where the HuggingFace model (FLAN-T5) is being downloaded on first use or loaded from disk cold — which can take 2-4 minutes — without triggering a premature timeout error.

---

**Q14. What is the role of the NLPProcessor in the system?**

> **A:** `NLPProcessor` is the shared NLP utility class that provides: `calculate_similarity()` (cosine similarity between two texts using sentence embeddings), `extract_keywords()` (identifies important terms from content), and `summarize_content()`. Both `QuestionGenerator` and `AnswerEvaluator` depend on it, making it the central NLP engine of the homework microservice.

---

**Q15. How are student performance reports generated from homework data?**

> **A:** The system aggregates all `homework_submissions` for a student across subjects and weeks. It calculates: average score per subject, completion rate (submitted vs. total assigned), improvement trend (comparing week-over-week scores), and weak topic identification (questions consistently answered incorrectly). This feeds into the broader **PerformanceController** and **MonthlyReportController** for parent/teacher dashboards.

---

**Q16. Can the system support multiple languages? (Relevance to Sri Lanka)**

> **A:** The current implementation supports English. The system architecture includes a `lang/si` folder in the Laravel application for Sinhala translations of UI elements. The question template system can be extended with Sinhala templates by adding a language-specific template set in `_load_question_templates()`. The Sri Lankan curriculum context is explicitly encoded in descriptive question templates (e.g., "Provide examples from Sri Lankan context").

---

**Q17. What is the API timeout strategy and why does it matter?**

> **A:** Two timeouts are configured: (1) A **general timeout** (default 30s) for status checks and simple operations. (2) A **generate_timeout** (300s/5 minutes) for question generation calls that may trigger ML model loading. Without these properly set timeouts, the Laravel app would throw a connection timeout exception during the first ML model cold-start, making the feature appear broken even though the backend is working correctly.

---

**Q18. How does the system ensure question difficulty is appropriate for the grade level?**

> **A:** The `lesson_data` passed to the question generator includes `grade` (e.g., grade 6-13) and `difficulty` (beginner/intermediate/advanced). Templates and expected_answer complexity are adjusted: for lower grades, simpler language is used and key_points are fewer. For higher grades, descriptive questions include critical analysis and comparison requirements. The `bloom_level` field also reflects this progression.

---

*End of Document 1*

