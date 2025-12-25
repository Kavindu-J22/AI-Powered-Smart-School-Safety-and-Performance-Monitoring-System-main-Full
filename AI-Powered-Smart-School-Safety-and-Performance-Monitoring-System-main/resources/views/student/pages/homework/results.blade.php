@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/forms.css'])
<style>
    .result-card {
        border-left: 4px solid #6c757d;
    }

    .result-card.correct {
        border-left-color: #198754;
        background-color: rgba(25, 135, 84, 0.05);
    }

    .result-card.partial {
        border-left-color: #ffc107;
        background-color: rgba(255, 193, 7, 0.05);
    }

    .result-card.incorrect {
        border-left-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.05);
    }

    .grade-display {
        font-size: 4rem;
        font-weight: bold;
    }

    .grade-A {
        color: #198754;
    }

    .grade-B {
        color: #0d6efd;
    }

    .grade-C {
        color: #ffc107;
    }

    .grade-D {
        color: #fd7e14;
    }

    .grade-F {
        color: #dc3545;
    }

    .feedback-box {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }

    .correct-answer-badge {
        background-color: #d1e7dd;
        color: #0f5132;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .your-answer-badge {
        background-color: #e2e3e5;
        color: #41464b;
        padding: 5px 10px;
        border-radius: 5px;
    }
</style>
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">
        <!-- Header with Grade -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-{{ $submission->percentage >= 75 ? 'success' : ($submission->percentage >= 50 ? 'warning' : 'danger') }}">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="text-white mb-1">{{ $homework->title }}</h4>
                                <p class="text-white opacity-8 mb-0">
                                    <span class="badge bg-light text-dark me-2">{{ $homework->subject->subject_name ?? 'N/A' }}</span>
                                    <span>Submitted {{ $submission->submitted_at->format('M d, Y h:i A') }}</span>
                                    @if($submission->is_late)
                                    <span class="badge bg-danger ms-2">Late Submission</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-center text-md-end">
                                <div class="grade-display text-white">{{ $submission->grade }}</div>
                                <h3 class="text-white mb-0">{{ number_format($submission->percentage, 1) }}%</h3>
                                <p class="text-white opacity-8 mb-0">{{ $submission->marks_obtained }}/{{ $homework->total_marks }} marks</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="material-symbols-rounded me-2">chat</i>Teacher Feedback</h6>
                        <div class="feedback-box">
                            <p class="mb-0">{{ $submission->feedback ?? 'No feedback provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-0">{{ collect($evaluationResults)->where('is_correct', true)->count() }}</h3>
                        <p class="text-sm mb-0">Correct Answers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-0">{{ collect($evaluationResults)->where('is_partial', true)->count() }}</h3>
                        <p class="text-sm mb-0">Partial Credit</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-0">{{ collect($evaluationResults)->where('is_correct', false)->where('is_partial', false)->count() }}</h3>
                        <p class="text-sm mb-0">Incorrect</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-0">{{ count($questions) }}</h3>
                        <p class="text-sm mb-0">Total Questions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Review -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6><i class="material-symbols-rounded me-2">quiz</i>Question Review</h6>
                    </div>
                    <div class="card-body">
                        @foreach($questions as $index => $question)
                        @php
                        $result = $evaluationResults[$index] ?? null;
                        $isCorrect = $result['is_correct'] ?? false;
                        $isPartial = $result['is_partial'] ?? false;
                        $studentAnswer = $answers[$index]['answer'] ?? ($answers[$index] ?? 'No answer provided');
                        $cardClass = $isCorrect ? 'correct' : ($isPartial ? 'partial' : 'incorrect');
                        $questionType = $question['question_type'] ?? $question['type'] ?? 'MCQ';
                        $questionText = $question['question'] ?? $question['question_text'] ?? 'Question';
                        $options = $question['options'] ?? [];
                        @endphp
                        <div class="card result-card {{ $cardClass }} mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-secondary me-2">Q{{ $index + 1 }}</span>
                                        <span class="badge bg-{{ $questionType === 'MCQ' ? 'primary' : 'info' }}">
                                            {{ $questionType }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $isCorrect ? 'success' : ($isPartial ? 'warning' : 'danger') }}">
                                            {{ $result['marks_awarded'] ?? 0 }}/{{ $question['marks'] ?? 1 }} marks
                                        </span>
                                    </div>
                                </div>

                                <h6 class="mb-3">{{ $questionText }}</h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-sm text-muted mb-1">Your Answer:</p>
                                        <div class="your-answer-badge">
                                            @if($questionType === 'MCQ')
                                            @php
                                            $answerKey = is_array($studentAnswer) ? ($studentAnswer['answer'] ?? '') : $studentAnswer;
                                            $optionText = $options[$answerKey] ?? $options[array_search($answerKey, ['A','B','C','D'])] ?? '';
                                            @endphp
                                            @if($optionText)
                                            <strong>{{ $answerKey }}:</strong> {{ $optionText }}
                                            @else
                                            {{ $answerKey ?: 'No answer provided' }}
                                            @endif
                                            @else
                                            {{ is_array($studentAnswer) ? ($studentAnswer['answer'] ?? 'No answer provided') : ($studentAnswer ?: 'No answer provided') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-sm text-muted mb-1">
                                            @if($questionType === 'MCQ')
                                            Correct Answer:
                                            @else
                                            Expected Answer:
                                            @endif
                                        </p>
                                        <div class="correct-answer-badge">
                                            @if($questionType === 'MCQ')
                                            @php
                                            $correct = $question['correct_answer'] ?? '';
                                            $correctOptionText = $options[$correct] ?? $options[array_search($correct, ['A','B','C','D'])] ?? '';
                                            @endphp
                                            <strong>{{ $correct }}:</strong> {{ $correctOptionText }}
                                            @else
                                            {{ $question['expected_answer'] ?? $question['correct_answer'] ?? $question['model_answer'] ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if(isset($result['feedback']) && $result['feedback'])
                                <div class="alert alert-info mb-2">
                                    <i class="material-symbols-rounded me-1">lightbulb</i>
                                    <strong>Feedback:</strong> {{ $result['feedback'] }}
                                </div>
                                @endif

                                @if($questionType === 'MCQ' && isset($result['explanation']) && $result['explanation'])
                                <div class="alert alert-secondary mb-2">
                                    <i class="material-symbols-rounded me-1">info</i>
                                    <strong>Explanation:</strong> {{ $result['explanation'] }}
                                </div>
                                @endif

                                @if(in_array($questionType, ['SHORT_ANSWER', 'DESCRIPTIVE']) && isset($question['key_points']) && is_array($question['key_points']))
                                <div class="alert alert-light mb-0">
                                    <i class="material-symbols-rounded me-1">checklist</i>
                                    <strong>Key Points to Cover:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($question['key_points'] as $point)
                                        <li>{{ $point }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="{{ route('student.homework.index') }}" class="btn btn-primary">
                    <i class="material-symbols-rounded me-1">arrow_back</i>Back to Homework List
                </a>
            </div>
        </div>
    </div>
</main>
@endsection