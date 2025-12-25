@extends('admin.layouts.app')

@section('title', 'Create Homework')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Create AI-Powered Homework</h6>
                        <a href="{{ route('admin.management.homework.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="material-symbols-outlined">arrow_back</i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.management.homework.store') }}" method="POST" id="homeworkForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group input-group-outline mb-3">
                                    <label class="form-label">Homework Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-static mb-3">
                                    <label class="form-label">Subject</label>
                                    <select name="subject_id" class="form-control" id="subjectSelect" required>
                                        <option value="">Select Subject</option>
                                        @foreach($subjects ?? [] as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group input-group-static mb-3">
                                    <label class="form-label">Grade Level</label>
                                    <select name="grade_level" class="form-control" required>
                                        @for($i = 6; $i <= 11; $i++)
                                            <option value="{{ $i }}">Grade {{ $i }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-static mb-3">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-control">
                                        <option value="">Select Class (Optional)</option>
                                        @foreach($classes ?? [] as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-outline mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="input-group input-group-static mb-3">
                                    <label class="form-label">Select Lesson (for AI Question Generation)</label>
                                    <select name="lesson_id" class="form-control" id="lessonSelect">
                                        <option value="">Select a Lesson</option>
                                        @foreach($lessons ?? [] as $lesson)
                                        <option value="{{ $lesson->lesson_id }}"
                                            data-subject="{{ $lesson->subject_id }}"
                                            data-topics="{{ json_encode($lesson->topics) }}">
                                            {{ $lesson->title }} ({{ $lesson->subject->subject_name ?? '' }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- AI Question Generation -->
                        <div class="card bg-gradient-light mb-4">
                            <div class="card-header pb-0">
                                <h6><i class="material-symbols-outlined me-2">auto_awesome</i>AI Question Generation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>MCQ Questions</label>
                                        <input type="number" id="numMcq" class="form-control" value="2" min="0" max="10">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Short Answer</label>
                                        <input type="number" id="numShort" class="form-control" value="2" min="0" max="10">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Descriptive</label>
                                        <input type="number" id="numDescriptive" class="form-control" value="1" min="0" max="5">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary w-100" id="generateQuestionsBtn">
                                            <i class="material-symbols-outlined">psychology</i> Generate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Questions Container -->
                        <div id="questionsContainer" class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Questions <span id="questionsCount">(0)</span></h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addManualQuestionBtn">
                                    <i class="material-symbols-outlined">add</i> Add Question Manually
                                </button>
                            </div>
                            <div id="questionsList"></div>
                        </div>
                        <input type="hidden" name="questions" id="questionsInput">

                        <!-- Manual Question Modal -->
                        <div class="modal fade" id="addQuestionModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add Question</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Question Type</label>
                                            <select id="modalQuestionType" class="form-control">
                                                <option value="MCQ">MCQ (1 mark)</option>
                                                <option value="SHORT_ANSWER">Short Answer (3 marks)</option>
                                                <option value="DESCRIPTIVE">Descriptive (5 marks)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Question Text</label>
                                            <textarea id="modalQuestionText" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div id="mcqOptionsContainer">
                                            <label class="form-label">Options (for MCQ)</label>
                                            <input type="text" id="optionA" class="form-control mb-2" placeholder="Option A">
                                            <input type="text" id="optionB" class="form-control mb-2" placeholder="Option B">
                                            <input type="text" id="optionC" class="form-control mb-2" placeholder="Option C">
                                            <input type="text" id="optionD" class="form-control mb-2" placeholder="Option D">
                                            <div class="mb-3">
                                                <label class="form-label">Correct Answer</label>
                                                <select id="correctAnswer" class="form-control">
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="answerKeyContainer" style="display:none;">
                                            <label class="form-label">Model Answer</label>
                                            <textarea id="modelAnswer" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="saveQuestionBtn">Add Question</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($errors->has('questions'))
                        <div class="alert alert-danger">
                            {{ $errors->first('questions') }}
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="material-symbols-outlined">save</i> Create Homework
                                </button>
                            </div>
                        </div>
                    </form>
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
            btn.innerHTML = '<i class="material-symbols-outlined">psychology</i> Generate';
        }
    });

    function renderQuestions() {
        const container = document.getElementById('questionsList');
        if (questions.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No questions added yet. Generate questions from a lesson or add manually.</div>';
        } else {
            container.innerHTML = questions.map((q, i) => {
                let answerSection = '';

                // Render answer section based on question type
                if (q.question_type === 'MCQ') {
                    // MCQ: Show options with correct answer marked
                    if (q.options) {
                        answerSection = '<ul class="mb-0 mt-2">' +
                            q.options.map((o, j) => {
                                const optionLetter = String.fromCharCode(65 + j);
                                const isCorrect = q.correct_answer === optionLetter;
                                return `<li>${optionLetter}. ${o} ${isCorrect ? '<span class="badge bg-success">✓ Correct</span>' : ''}</li>`;
                            }).join('') +
                            '</ul>';
                    }
                    // Show explanation if available
                    if (q.explanation) {
                        answerSection += `<div class="mt-2"><small class="text-muted"><strong>Explanation:</strong> ${q.explanation}</small></div>`;
                    }
                } else if (q.question_type === 'SHORT_ANSWER' || q.question_type === 'DESCRIPTIVE') {
                    // SHORT_ANSWER and DESCRIPTIVE: Show expected answer and key points
                    if (q.expected_answer || q.answer_key) {
                        const answer = q.expected_answer || q.answer_key;
                        answerSection = `<div class="mt-2 p-2 bg-light rounded">
                            <strong class="text-success">Expected Answer:</strong>
                            <p class="mb-0 mt-1 text-sm">${answer}</p>
                        </div>`;
                    }
                    // Show key points if available
                    if (q.key_points && Array.isArray(q.key_points) && q.key_points.length > 0) {
                        answerSection += `<div class="mt-2">
                            <strong class="text-info">Key Points:</strong>
                            <ul class="mb-0 mt-1">
                                ${q.key_points.map(point => `<li class="text-sm">${point}</li>`).join('')}
                            </ul>
                        </div>`;
                    }
                }

                return `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="mb-2">
                                    <span class="badge bg-${q.question_type === 'MCQ' ? 'info' : q.question_type === 'SHORT_ANSWER' ? 'warning' : 'success'}">${q.question_type}</span>
                                    <span class="badge bg-secondary">${q.marks} marks</span>
                                    ${q.difficulty ? `<span class="badge bg-dark">${q.difficulty}</span>` : ''}
                                </div>
                                <p class="mt-2 mb-2"><strong>Q${i+1}:</strong> ${q.question_text}</p>
                                ${answerSection}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeQuestion(${i})">
                                <i class="material-symbols-outlined">delete</i>
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }
        document.getElementById('questionsCount').textContent = `(${questions.length})`;
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