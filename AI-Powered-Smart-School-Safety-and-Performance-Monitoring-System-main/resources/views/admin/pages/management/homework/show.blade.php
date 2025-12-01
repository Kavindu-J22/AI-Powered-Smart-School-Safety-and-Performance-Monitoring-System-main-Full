@extends('admin.layouts.app')

@section('title', 'View Homework')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>{{ $homework->title }}</h6>
                        <div>
                            <a href="{{ route('admin.management.homework.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="material-symbols-outlined">arrow_back</i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Homework Info -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong>Subject:</strong><br>
                            <span class="badge bg-info">{{ $homework->subject->subject_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Grade:</strong><br>
                            Grade {{ $homework->grade_level }}
                        </div>
                        <div class="col-md-3">
                            <strong>Due Date:</strong><br>
                            {{ $homework->due_date ? $homework->due_date->format('M d, Y') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge bg-{{ $homework->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($homework->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong>Total Marks:</strong><br>
                            {{ $homework->total_marks }}
                        </div>
                        <div class="col-md-3">
                            <strong>Class:</strong><br>
                            {{ $homework->schoolClass->class_name ?? 'All Classes' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Assigned By:</strong><br>
                            {{ $homework->assignedBy->first_name ?? '' }} {{ $homework->assignedBy->last_name ?? '' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Assigned Date:</strong><br>
                            {{ $homework->assigned_date ? $homework->assigned_date->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>

                    @if($homework->description)
                    <div class="row mb-4">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p>{{ $homework->description }}</p>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <!-- Questions -->
                    <h6 class="mb-3">Questions ({{ count($homework->questions ?? []) }})</h6>
                    @forelse($homework->questions ?? [] as $index => $question)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-{{ $question['question_type'] === 'MCQ' ? 'info' : ($question['question_type'] === 'SHORT_ANSWER' ? 'warning' : 'success') }}">
                                        {{ $question['question_type'] }}
                                    </span>
                                    <span class="badge bg-secondary">{{ $question['marks'] }} marks</span>
                                </div>
                            </div>
                            <p class="mt-2 mb-2"><strong>Q{{ $index + 1 }}:</strong> {{ $question['question_text'] }}</p>
                            
                            @if(isset($question['options']) && is_array($question['options']))
                            <ul class="mb-0">
                                @foreach($question['options'] as $optIndex => $option)
                                <li>
                                    {{ chr(65 + $optIndex) }}. {{ $option }}
                                    @if(isset($question['correct_answer']) && $question['correct_answer'] === chr(65 + $optIndex))
                                    <span class="badge bg-success">✓ Correct</span>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                            @endif

                            @if(isset($question['answer_key']))
                            <p class="mt-2 mb-0 text-muted"><small><strong>Model Answer:</strong> {{ $question['answer_key'] }}</small></p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-warning">No questions found for this homework.</div>
                    @endforelse

                    <hr>

                    <!-- Submission Stats -->
                    <h6 class="mb-3">Submission Statistics</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-gradient-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $submissionStats['total'] ?? 0 }}</h3>
                                    <p class="mb-0">Total Assigned</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $submissionStats['submitted'] ?? 0 }}</h3>
                                    <p class="mb-0">Submitted</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $submissionStats['pending'] ?? 0 }}</h3>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($submissionStats['average_score'] ?? 0, 1) }}%</h3>
                                    <p class="mb-0">Average Score</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

