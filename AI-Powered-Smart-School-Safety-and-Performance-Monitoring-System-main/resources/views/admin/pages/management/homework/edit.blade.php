@extends('admin.layouts.app')

@section('title', 'Edit Homework')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Edit Homework</h6>
                        <a href="{{ route('admin.management.homework.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="material-symbols-outlined">arrow_back</i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.management.homework.update', $homework->homework_id) }}" method="POST" id="homeworkForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Homework Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $homework->title }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-control" required>
                                    @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}" {{ $homework->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->subject_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Grade Level</label>
                                <select name="grade_level" class="form-control" required>
                                    @for($i = 6; $i <= 11; $i++)
                                    <option value="{{ $i }}" {{ $homework->grade_level == $i ? 'selected' : '' }}>Grade {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Class</label>
                                <select name="class_id" class="form-control">
                                    <option value="">All Classes</option>
                                    @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}" {{ $homework->class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" 
                                    value="{{ $homework->due_date ? $homework->due_date->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $homework->description }}</textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="active" {{ $homework->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="closed" {{ $homework->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="draft" {{ $homework->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Questions Section -->
                        <h6 class="mb-3">Questions ({{ count($homework->questions ?? []) }})</h6>
                        
                        <div id="questionsContainer">
                            @forelse($homework->questions ?? [] as $index => $question)
                            <div class="card mb-3 border question-card" data-index="{{ $index }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-{{ $question['question_type'] === 'MCQ' ? 'info' : ($question['question_type'] === 'SHORT_ANSWER' ? 'warning' : 'success') }}">
                                                {{ $question['question_type'] }}
                                            </span>
                                            <span class="badge bg-secondary">{{ $question['marks'] }} marks</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion({{ $index }})">
                                            <i class="material-symbols-outlined" style="font-size: 16px;">delete</i>
                                        </button>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Question Text</label>
                                        <textarea name="questions[{{ $index }}][question_text]" class="form-control" rows="2">{{ $question['question_text'] }}</textarea>
                                        <input type="hidden" name="questions[{{ $index }}][question_type]" value="{{ $question['question_type'] }}">
                                        <input type="hidden" name="questions[{{ $index }}][marks]" value="{{ $question['marks'] }}">
                                    </div>
                                    @if($question['question_type'] === 'MCQ' && isset($question['options']))
                                    <div class="row">
                                        @foreach($question['options'] as $optIndex => $option)
                                        <div class="col-md-6 mb-2">
                                            <div class="input-group">
                                                <span class="input-group-text">{{ chr(65 + $optIndex) }}</span>
                                                <input type="text" name="questions[{{ $index }}][options][]" class="form-control" value="{{ $option }}">
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Correct Answer</label>
                                        <select name="questions[{{ $index }}][correct_answer]" class="form-control" style="width: 100px;">
                                            @foreach(['A', 'B', 'C', 'D'] as $opt)
                                            <option value="{{ $opt }}" {{ ($question['correct_answer'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="alert alert-warning">No questions in this homework.</div>
                            @endforelse
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="material-symbols-outlined">save</i> Update Homework
                                </button>
                                <a href="{{ route('admin.management.homework.show', $homework->homework_id) }}" class="btn btn-outline-secondary btn-lg">
                                    Cancel
                                </a>
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
function removeQuestion(index) {
    document.querySelector(`.question-card[data-index="${index}"]`).remove();
}
</script>
@endpush
@endsection

