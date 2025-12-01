@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/forms.css'])
<style>
    .question-card {
        border-left: 4px solid #6c757d;
        transition: border-color 0.3s;
    }

    .question-card.answered {
        border-left-color: #198754;
    }

    .question-card.mcq {
        border-left-color: #0d6efd;
    }

    .question-card.short {
        border-left-color: #ffc107;
    }

    .question-card.descriptive {
        border-left-color: #6f42c1;
    }

    .question-type-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .mcq-option {
        cursor: pointer;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }

    .mcq-option:hover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }

    .mcq-option.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .mcq-option input {
        display: none;
    }

    .timer-display {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .progress-indicator {
        position: sticky;
        top: 80px;
        z-index: 100;
    }

    .question-nav-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        border-radius: 50%;
        margin: 2px;
    }

    .question-nav-btn.answered {
        background-color: #198754;
        color: white;
    }

    .question-nav-btn.current {
        border: 2px solid #0d6efd;
    }
</style>
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-dark">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="text-white mb-1">{{ $homework->title }}</h4>
                                <p class="text-white opacity-8 mb-0">
                                    <span class="badge bg-light text-dark me-2">{{ $homework->subject->subject_name ?? 'N/A' }}</span>
                                    <span>{{ count($questions) }} Questions</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $homework->total_marks }} Marks</span>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p class="text-white mb-0">
                                    <i class="material-symbols-rounded align-middle">event</i>
                                    Due: {{ $homework->due_date->format('M d, Y') }}
                                </p>
                                @if($homework->due_date->isPast())
                                <span class="badge bg-danger">Overdue - Late Submission</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Questions -->
            <div class="col-lg-9">
                <form id="homeworkForm">
                    @csrf
                    @foreach($questions as $index => $question)
                    <div class="card question-card mb-4 {{ strtolower($question['question_type'] ?? 'mcq') }}" id="question-{{ $index }}">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge question-type-badge bg-{{ $question['question_type'] === 'MCQ' ? 'primary' : ($question['question_type'] === 'SHORT_ANSWER' ? 'warning' : 'info') }}">
                                        {{ $question['question_type'] ?? 'MCQ' }}
                                    </span>
                                    <span class="text-muted ms-2">Question {{ $index + 1 }} of {{ count($questions) }}</span>
                                </div>
                                <span class="badge bg-secondary">{{ $question['marks'] ?? 1 }} marks</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-4">{{ $question['question'] ?? $question['question_text'] ?? 'Question' }}</h6>

                            @php
                            $questionType = $question['question_type'] ?? $question['type'] ?? 'MCQ';
                            $options = $question['options'] ?? [];
                            @endphp

                            @if($questionType === 'MCQ')
                            <!-- MCQ Options -->
                            <div class="mcq-options">
                                @foreach(['A', 'B', 'C', 'D'] as $optIdx => $optKey)
                                @php
                                // Handle both keyed arrays ['A' => 'text'] and indexed arrays [0 => 'text']
                                $optionText = $options[$optKey] ?? $options[$optIdx] ?? null;
                                @endphp
                                @if($optionText)
                                <label class="mcq-option d-flex align-items-center" data-question="{{ $index }}" data-answer="{{ $optKey }}">
                                    <input type="radio" name="answers[{{ $index }}]" value="{{ $optKey }}">
                                    <span class="badge bg-light text-dark me-3">{{ $optKey }}</span>
                                    <span>{{ $optionText }}</span>
                                </label>
                                @endif
                                @endforeach
                            </div>
                            @elseif($questionType === 'SHORT_ANSWER')
                            <!-- Short Answer -->
                            <textarea class="form-control answer-input" name="answers[{{ $index }}]" rows="3"
                                placeholder="Write your answer here (2-3 sentences)..." data-question="{{ $index }}"></textarea>
                            <small class="text-muted">Suggested length: 2-3 sentences</small>
                            @else
                            <!-- Descriptive -->
                            <textarea class="form-control answer-input" name="answers[{{ $index }}]" rows="6"
                                placeholder="Write your detailed answer here..." data-question="{{ $index }}"></textarea>
                            <small class="text-muted">Suggested length: 100+ words for full marks</small>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    <!-- Submit Button -->
                    <div class="card">
                        <div class="card-body text-center py-4">
                            <p class="text-muted mb-3">Make sure you've answered all questions before submitting.</p>
                            <button type="button" class="btn btn-outline-secondary me-2" id="saveProgressBtn">
                                <i class="material-symbols-rounded me-1">save</i>Save Progress
                            </button>
                            <button type="button" class="btn btn-primary btn-lg" id="submitHomeworkBtn">
                                <i class="material-symbols-rounded me-1">send</i>Submit Homework
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card progress-indicator">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Progress</h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
                        </div>
                        <p class="text-sm text-center mb-3">
                            <span id="answeredCount">0</span> / {{ count($questions) }} answered
                        </p>
                        <hr>
                        <p class="text-sm font-weight-bold mb-2">Quick Navigation</p>
                        <div class="d-flex flex-wrap justify-content-center">
                            @foreach($questions as $index => $q)
                            <button type="button" class="btn btn-outline-secondary question-nav-btn" data-target="question-{{ $index }}">
                                {{ $index + 1 }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Confirm Submit Modal -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit your homework?</p>
                <div class="alert alert-warning">
                    <i class="material-symbols-rounded me-2">warning</i>
                    <strong>Warning:</strong> You cannot modify your answers after submission.
                </div>
                <p class="mb-0">
                    <strong>Answered:</strong> <span id="modalAnsweredCount">0</span> / {{ count($questions) }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Submit Now</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    const submissionId = {{ $submission->submission_id }};
    const totalQuestions = {{ count($questions) }};
    const submitUrl = "{{ route('student.homework.submit', $submission->submission_id) }}";
    const saveUrl = "{{ route('student.homework.save-progress', $submission->submission_id) }}";
    const csrfToken = "{{ csrf_token() }}";
</script>
@vite('resources/js/student/homework-attempt.js')
@endsection