@extends('admin.layouts.app')

@section('title', 'Create Homework')

@push('styles')
<style>
    .hw-hero {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 18px;
        padding: 24px 32px;
        color: #fff;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .hw-hero::after {
        content: 'edit_note';
        font-family: 'Material Symbols Outlined';
        font-size: 130px;
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        opacity: .08;
        line-height: 1;
    }

    .hw-hero h2 {
        font-size: 1.45rem;
        font-weight: 800;
        margin-bottom: 3px;
    }

    .hw-hero p {
        font-size: .85rem;
        opacity: .85;
        margin: 0;
    }

    .section-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(245, 87, 108, .07);
        margin-bottom: 22px;
        overflow: hidden;
    }

    .section-card .sc-head {
        padding: 14px 20px;
        border-bottom: 1px solid #f0f2fb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-card .sc-head .sc-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .section-card .sc-head .sc-icon .material-symbols-outlined {
        font-size: 18px;
        color: #fff;
    }

    .section-card .sc-head h6 {
        margin: 0;
        font-size: .9rem;
        font-weight: 700;
        color: #1a2550;
    }

    .section-card .sc-body {
        padding: 20px;
    }

    .hw-field label {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #718096;
        margin-bottom: 6px;
        display: block;
    }

    .hw-field .form-control,
    .hw-field select,
    .hw-field textarea {
        border-radius: 10px;
        border: 1.5px solid #cbd5e0 !important;
        font-size: .88rem;
        padding: 9px 14px;
        transition: border-color .2s;
    }

    .hw-field .form-control:focus,
    .hw-field select:focus,
    .hw-field textarea:focus {
        border-color: #f5576c !important;
        box-shadow: 0 0 0 3px rgba(245, 87, 108, .1);
    }

    .ai-panel {
        background: linear-gradient(135deg, #f8f4ff 0%, #fff0f5 100%);
        border-radius: 14px;
        padding: 18px;
        border: 1.5px solid #f093fb33;
    }

    .ai-panel-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9f7aea;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .q-count-badge {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: #fff;
        border-radius: 50px;
        padding: 2px 10px;
        font-size: .75rem;
        font-weight: 700;
    }

    .q-card {
        background: #fff;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        padding: 16px;
        margin-bottom: 12px;
        transition: border-color .2s;
    }

    .q-card:hover {
        border-color: #f5576c55;
    }

    .q-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: .72rem;
        font-weight: 700;
    }

    .btn-hw-submit {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 32px;
        font-weight: 700;
        font-size: .95rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: opacity .2s;
    }

    .btn-hw-submit:hover {
        opacity: .9;
        color: #fff;
    }

    .btn-back {
        background: #f8f9fa;
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: .82rem;
        color: #4a5568;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-back:hover {
        background: #e9ecef;
        color: #2d3748;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Hero --}}
    <div class="hw-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2><span class="material-symbols-outlined" style="font-size:1.4rem;vertical-align:middle;">edit_note</span> Create Homework</h2>
                <p>Fill in the details and use AI to generate questions from lesson content</p>
            </div>
            <a href="{{ route('admin.management.homework.dashboard') }}" class="btn-back">
                <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span> Back to Dashboard
            </a>
        </div>
    </div>

    <form action="{{ route('admin.management.homework.store') }}" method="POST" id="homeworkForm">
        @csrf

        {{-- Basic Info --}}
        <div class="section-card">
            <div class="sc-head">
                <div class="sc-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                    <span class="material-symbols-outlined">info</span>
                </div>
                <h6>Basic Information</h6>
            </div>
            <div class="sc-body">
                <div class="row">
                    <div class="col-md-6 hw-field mb-3">
                        <label>Homework Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 3 – Algebra Basics" required>
                    </div>
                    <div class="col-md-6 hw-field mb-3">
                        <label>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control" id="subjectSelect" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 hw-field mb-3">
                        <label>Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-control" required>
                            @for($i = 6; $i <= 11; $i++)
                                <option value="{{ $i }}">Grade {{ $i }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-4 hw-field mb-3">
                        <label>Class</label>
                        <select name="class_id" class="form-control">
                            <option value="">All Classes (Optional)</option>
                            @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 hw-field mb-3">
                        <label>Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="col-md-12 hw-field">
                        <label>Source Lesson <span style="font-size:.72rem;color:#a0aec0;">(used for AI generation)</span></label>
                        <select name="lesson_id" class="form-control" id="lessonSelect">
                            <option value="">Select a Lesson</option>
                            @foreach($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->lesson_id }}" data-subject="{{ $lesson->subject_id }}" data-topics="{{ json_encode($lesson->topics) }}">
                                {{ $lesson->title }} ({{ $lesson->subject->subject_name ?? '' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Generation Panel --}}
        <div class="section-card">
            <div class="sc-head">
                <div class="sc-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <span class="material-symbols-outlined">psychology</span>
                </div>
                <h6>AI Question Generation</h6>
                <span style="font-size:.72rem;color:#a0aec0;margin-left:auto;">Powered by ML model</span>
            </div>
            <div class="sc-body">
                <div class="ai-panel">
                    <div class="ai-panel-title">
                        <span class="material-symbols-outlined" style="font-size:15px;">auto_awesome</span> Choose question mix
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-3 hw-field mb-3 mb-md-0">
                            <label>MCQ Questions</label>
                            <input type="number" id="numMcq" class="form-control" value="2" min="0" max="10">
                        </div>
                        <div class="col-md-3 hw-field mb-3 mb-md-0">
                            <label>Short Answer</label>
                            <input type="number" id="numShort" class="form-control" value="2" min="0" max="10">
                        </div>
                        <div class="col-md-3 hw-field mb-3 mb-md-0">
                            <label>Descriptive</label>
                            <input type="number" id="numDescriptive" class="form-control" value="1" min="0" max="5">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn w-100 text-white fw-700" id="generateQuestionsBtn"
                                style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:10px;padding:10px;">
                                <span class="material-symbols-outlined" style="font-size:17px;vertical-align:middle;">psychology</span> Generate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Questions List --}}
        <div class="section-card">
            <div class="sc-head">
                <div class="sc-icon" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                    <span class="material-symbols-outlined">quiz</span>
                </div>
                <h6>Questions <span id="questionsCount" class="q-count-badge ms-1">0</span></h6>
                <button type="button" class="btn btn-sm ms-auto" id="addManualQuestionBtn"
                    style="border-radius:8px;border:1.5px solid #f5576c;color:#f5576c;font-weight:700;font-size:.78rem;">
                    <span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;">add</span> Add Manually
                </button>
            </div>
            <div class="sc-body" id="questionsContainer">
                <div id="questionsList"></div>
            </div>
        </div>

        <input type="hidden" name="questions" id="questionsInput">

        @if($errors->has('questions'))
        <div class="alert alert-danger" style="border-radius:12px;">{{ $errors->first('questions') }}</div>
        @endif

        <div class="d-flex gap-3 mb-4">
            <button type="submit" class="btn-hw-submit" id="submitBtn">
                <span class="material-symbols-outlined" style="font-size:19px;">save</span> Create Homework
            </button>
            <a href="{{ route('admin.management.homework.dashboard') }}" class="btn-back">Cancel</a>
        </div>
    </form>

    {{-- Manual Question Modal --}}
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0" style="background:linear-gradient(135deg,#43cea2,#185a9d);color:#fff;padding:18px 24px;">
                    <h5 class="modal-title fw-bold mb-0">
                        <span class="material-symbols-outlined me-2" style="vertical-align:middle;">quiz</span>Add Question
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="hw-field mb-3">
                        <label>Question Type</label>
                        <select id="modalQuestionType" class="form-control">
                            <option value="MCQ">MCQ (1 mark)</option>
                            <option value="SHORT_ANSWER">Short Answer (3 marks)</option>
                            <option value="DESCRIPTIVE">Descriptive (5 marks)</option>
                        </select>
                    </div>
                    <div class="hw-field mb-3">
                        <label>Question Text</label>
                        <textarea id="modalQuestionText" class="form-control" rows="3" placeholder="Enter your question here…"></textarea>
                    </div>
                    <div id="mcqOptionsContainer">
                        <div class="hw-field mb-2"><label>MCQ Options</label>
                            <input type="text" id="optionA" class="form-control mb-2" placeholder="Option A">
                            <input type="text" id="optionB" class="form-control mb-2" placeholder="Option B">
                            <input type="text" id="optionC" class="form-control mb-2" placeholder="Option C">
                            <input type="text" id="optionD" class="form-control mb-2" placeholder="Option D">
                        </div>
                        <div class="hw-field mb-3">
                            <label>Correct Answer</label>
                            <select id="correctAnswer" class="form-control">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    <div id="answerKeyContainer" style="display:none;" class="hw-field">
                        <label>Model Answer</label>
                        <textarea id="modelAnswer" class="form-control" rows="2" placeholder="Enter the expected answer…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="button" class="btn text-white" id="saveQuestionBtn"
                        style="background:linear-gradient(135deg,#43cea2,#185a9d);border-radius:10px;">Add Question</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    let questions = [];
    let questionModal;

    document.addEventListener('DOMContentLoaded', function() {
        questionModal = new bootstrap.Modal(document.getElementById('addQuestionModal'));

        // Toggle MCQ options based on question type
        document.getElementById('modalQuestionType').addEventListener('change', function() {
            const isMCQ = this.value === 'MCQ';
            document.getElementById('mcqOptionsContainer').style.display = isMCQ ? 'block' : 'none';
            document.getElementById('answerKeyContainer').style.display = isMCQ ? 'none' : 'block';
        });
    });

    document.getElementById('addManualQuestionBtn').addEventListener('click', function() {
        // Reset form
        document.getElementById('modalQuestionType').value = 'MCQ';
        document.getElementById('modalQuestionText').value = '';
        document.getElementById('optionA').value = '';
        document.getElementById('optionB').value = '';
        document.getElementById('optionC').value = '';
        document.getElementById('optionD').value = '';
        document.getElementById('correctAnswer').value = 'A';
        document.getElementById('modelAnswer').value = '';
        document.getElementById('mcqOptionsContainer').style.display = 'block';
        document.getElementById('answerKeyContainer').style.display = 'none';
        questionModal.show();
    });

    document.getElementById('saveQuestionBtn').addEventListener('click', function() {
        const type = document.getElementById('modalQuestionType').value;
        const text = document.getElementById('modalQuestionText').value.trim();

        if (!text) {
            alert('Please enter question text');
            return;
        }

        const marks = type === 'MCQ' ? 1 : (type === 'SHORT_ANSWER' ? 3 : 5);
        const question = {
            question_type: type,
            question_text: text,
            marks: marks
        };

        if (type === 'MCQ') {
            question.options = [
                document.getElementById('optionA').value.trim(),
                document.getElementById('optionB').value.trim(),
                document.getElementById('optionC').value.trim(),
                document.getElementById('optionD').value.trim()
            ];
            question.correct_answer = document.getElementById('correctAnswer').value;
        } else {
            // For SHORT_ANSWER and DESCRIPTIVE, use expected_answer (consistent with AI generation)
            const modelAnswer = document.getElementById('modelAnswer').value.trim();
            question.expected_answer = modelAnswer;
            question.answer_key = modelAnswer; // Keep for backward compatibility
        }

        questions.push(question);
        renderQuestions();
        questionModal.hide();
    });

    document.getElementById('generateQuestionsBtn').addEventListener('click', async function() {
        const lessonId = document.getElementById('lessonSelect').value;
        if (!lessonId) {
            alert('Please select a lesson first');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

        try {
            const response = await fetch('{{ route("admin.management.homework.generate-questions") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    lesson_id: lessonId,
                    num_mcq: parseInt(document.getElementById('numMcq').value),
                    num_short: parseInt(document.getElementById('numShort').value),
                    num_descriptive: parseInt(document.getElementById('numDescriptive').value)
                })
            });

            const data = await response.json();
            if (data.success) {
                questions = data.questions;
                renderQuestions();
            } else {
                alert('Error: ' + (data.error || 'Failed to generate questions'));
            }
        } catch (error) {
            alert('Error generating questions: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:17px;vertical-align:middle;">psychology</span> Generate';
        }
    });

    function renderQuestions() {
        const container = document.getElementById('questionsList');
        const typeColors = {
            MCQ: '#3b82f6',
            SHORT_ANSWER: '#f59e0b',
            DESCRIPTIVE: '#10b981'
        };
        const typeIcons = {
            MCQ: 'radio_button_checked',
            SHORT_ANSWER: 'short_text',
            DESCRIPTIVE: 'article'
        };
        if (questions.length === 0) {
            container.innerHTML = `<div style="text-align:center;padding:28px 0;color:#a0aec0;">
                <span class="material-symbols-outlined" style="font-size:40px;display:block;margin-bottom:8px;">quiz</span>
                No questions yet — generate with AI or add manually.
            </div>`;
        } else {
            container.innerHTML = questions.map((q, i) => {
                const color = typeColors[q.question_type] || '#718096';
                const icon = typeIcons[q.question_type] || 'help';
                let answerSection = '';
                if (q.question_type === 'MCQ' && q.options) {
                    answerSection = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px;">' +
                        q.options.map((o, j) => {
                            const letter = String.fromCharCode(65 + j);
                            const correct = q.correct_answer === letter;
                            return `<div style="background:${correct ? '#ecfdf5' : '#f8f9fa'};border:1.5px solid ${correct ? '#10b981' : '#e2e8f0'};border-radius:8px;padding:7px 10px;font-size:.82rem;">
                                <strong>${letter}.</strong> ${o} ${correct ? '<span style="color:#10b981;font-size:.72rem;font-weight:700;">✓</span>' : ''}
                            </div>`;
                        }).join('') + '</div>';
                    if (q.explanation) answerSection += `<p style="font-size:.78rem;color:#718096;margin-top:8px;margin-bottom:0;"><strong>Explanation:</strong> ${q.explanation}</p>`;
                } else if (q.expected_answer || q.answer_key) {
                    answerSection = `<div style="background:#f0fdf4;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:.82rem;">
                        <strong style="color:#059669;">Expected:</strong> ${q.expected_answer || q.answer_key}</div>`;
                    if (q.key_points && q.key_points.length)
                        answerSection += '<ul style="font-size:.8rem;color:#4a5568;margin-top:6px;padding-left:18px;">' + q.key_points.map(p => `<li>${p}</li>`).join('') + '</ul>';
                }
                return `<div class="q-card">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:34px;height:34px;border-radius:10px;background:${color}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-outlined" style="font-size:18px;color:${color};">${icon}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <span style="background:${color}22;color:${color};border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:700;">${q.question_type.replace('_', ' ')}</span>
                                <span style="background:#e2e8f0;color:#4a5568;border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:700;">${q.marks} mark${q.marks > 1 ? 's' : ''}</span>
                                ${q.difficulty ? `<span style="background:#1a252555;color:#1a2550;border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:700;">${q.difficulty}</span>` : ''}
                                <span style="font-size:.75rem;color:#a0aec0;margin-left:auto;">Q${i+1}</span>
                            </div>
                            <p style="font-size:.88rem;color:#2d3748;margin:0;">${q.question_text}</p>
                            ${answerSection}
                        </div>
                        <button type="button" onclick="removeQuestion(${i})" style="background:#fff0f5;border:none;border-radius:8px;padding:6px;color:#f5576c;cursor:pointer;" title="Remove">
                            <span class="material-symbols-outlined" style="font-size:17px;display:block;">delete</span>
                        </button>
                    </div>
                </div>`;
            }).join('');
        }
        document.getElementById('questionsCount').textContent = questions.length;
        document.getElementById('questionsInput').value = JSON.stringify(questions);
    }

    function removeQuestion(index) {
        questions.splice(index, 1);
        renderQuestions();
    }

    // Validate before submit
    document.getElementById('homeworkForm').addEventListener('submit', function(e) {
        if (questions.length === 0) {
            e.preventDefault();
            alert('Please add at least one question before creating homework.');
            return false;
        }
        document.getElementById('questionsInput').value = JSON.stringify(questions);
    });

    // Initialize
    renderQuestions();
</script>
@endpush
@endsection