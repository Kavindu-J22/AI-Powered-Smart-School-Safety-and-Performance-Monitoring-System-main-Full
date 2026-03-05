@extends('admin.layouts.app')

@section('title', 'Edit Homework')

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
        content: 'edit';
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
        border: 1.5px solid #e2e8f0;
        font-size: .88rem;
        padding: 9px 14px;
        transition: border-color .2s;
    }

    .hw-field .form-control:focus,
    .hw-field select:focus,
    .hw-field textarea:focus {
        border-color: #f5576c;
        box-shadow: 0 0 0 3px rgba(245, 87, 108, .1);
    }

    .q-edit-card {
        background: #fff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .q-edit-header {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #f0f2fb;
    }

    .q-edit-body {
        padding: 16px;
    }

    .q-type-pill {
        border-radius: 50px;
        padding: 3px 12px;
        font-size: .72rem;
        font-weight: 700;
    }

    .input-grp-letter {
        background: #f0f2fb;
        border: 1.5px solid #e2e8f0;
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 8px 12px;
        font-weight: 700;
        color: #4a5568;
    }

    .input-grp-letter+.form-control {
        border-radius: 0 10px 10px 0;
        border: 1.5px solid #e2e8f0;
    }

    .btn-hw-update {
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
    }

    .btn-hw-update:hover {
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
                <h2><span class="material-symbols-outlined" style="font-size:1.4rem;vertical-align:middle;">edit</span> Edit Homework</h2>
                <p>{{ $homework->title }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.management.homework.show', $homework->homework_id) }}" class="btn-back">
                    <span class="material-symbols-outlined" style="font-size:17px;">visibility</span> View
                </a>
                <a href="{{ route('admin.management.homework.dashboard') }}" class="btn-back">
                    <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span> Dashboard
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.management.homework.update', $homework->homework_id) }}" method="POST" id="homeworkForm">
        @csrf
        @method('PUT')

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
                        <input type="text" name="title" class="form-control" value="{{ $homework->title }}" required>
                    </div>
                    <div class="col-md-6 hw-field mb-3">
                        <label>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}" {{ $homework->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 hw-field mb-3">
                        <label>Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-control" required>
                            @for($i = 6; $i <= 11; $i++)
                                <option value="{{ $i }}" {{ $homework->grade_level == $i ? 'selected' : '' }}>Grade {{ $i }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-3 hw-field mb-3">
                        <label>Class</label>
                        <select name="class_id" class="form-control">
                            <option value="">All Classes</option>
                            @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ $homework->class_id == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 hw-field mb-3">
                        <label>Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ $homework->due_date ? $homework->due_date->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="col-md-3 hw-field mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ $homework->status === 'active'  ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ $homework->status === 'closed'  ? 'selected' : '' }}>Closed</option>
                            <option value="draft" {{ $homework->status === 'draft'   ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-12 hw-field">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $homework->description }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Questions --}}
        <div class="section-card">
            <div class="sc-head">
                <div class="sc-icon" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                    <span class="material-symbols-outlined">quiz</span>
                </div>
                <h6>Questions <span style="background:linear-gradient(135deg,#f093fb,#f5576c);color:#fff;border-radius:50px;padding:2px 10px;font-size:.75rem;font-weight:700;">{{ count($homework->questions ?? []) }}</span></h6>
            </div>
            <div class="sc-body">
                <div id="questionsContainer">
                    @forelse($homework->questions ?? [] as $index => $question)
                    @php
                    $typeColor = $question['question_type'] === 'MCQ' ? '#3b82f6' : ($question['question_type'] === 'SHORT_ANSWER' ? '#f59e0b' : '#10b981');
                    $typeIcon = $question['question_type'] === 'MCQ' ? 'radio_button_checked' : ($question['question_type'] === 'SHORT_ANSWER' ? 'short_text' : 'article');
                    @endphp
                    <div class="q-edit-card question-card" data-index="{{ $index }}">
                        <div class="q-edit-header">
                            <div style="width:30px;height:30px;border-radius:9px;background:{{ $typeColor }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:16px;color:{{ $typeColor }};">{{ $typeIcon }}</span>
                            </div>
                            <span style="background:{{ $typeColor }}22;color:{{ $typeColor }};" class="q-type-pill">{{ str_replace('_', ' ', $question['question_type']) }}</span>
                            <span style="background:#e2e8f0;color:#4a5568;" class="q-type-pill">{{ $question['marks'] }} marks</span>
                            <span style="font-size:.78rem;color:#a0aec0;">Q{{ $index + 1 }}</span>
                            <button type="button" class="ms-auto" onclick="removeQuestion({{ $index }})"
                                style="background:#fff0f5;border:none;border-radius:8px;padding:5px 8px;color:#f5576c;cursor:pointer;">
                                <span class="material-symbols-outlined" style="font-size:16px;display:block;">delete</span>
                            </button>
                        </div>
                        <div class="q-edit-body">
                            <div class="hw-field mb-3">
                                <label>Question Text</label>
                                <textarea name="questions[{{ $index }}][question_text]" class="form-control" rows="2">{{ $question['question_text'] }}</textarea>
                                <input type="hidden" name="questions[{{ $index }}][question_type]" value="{{ $question['question_type'] }}">
                                <input type="hidden" name="questions[{{ $index }}][marks]" value="{{ $question['marks'] }}">
                            </div>
                            @if($question['question_type'] === 'MCQ' && isset($question['options']))
                            <div class="hw-field mb-3">
                                <label>Options</label>
                                <div class="row g-2">
                                    @foreach($question['options'] as $optIndex => $option)
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <span class="input-grp-letter">{{ chr(65 + $optIndex) }}</span>
                                            <input type="text" name="questions[{{ $index }}][options][]" class="form-control" value="{{ $option }}" style="border-radius:0 10px 10px 0;border:1.5px solid #e2e8f0;border-left:none;">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="hw-field mb-3">
                                <label>Correct Answer</label>
                                <select name="questions[{{ $index }}][correct_answer]" class="form-control" style="width:120px;">
                                    @foreach(['A','B','C','D'] as $opt)
                                    <option value="{{ $opt }}" {{ ($question['correct_answer'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(isset($question['explanation']))
                            <div class="hw-field">
                                <label>Explanation</label>
                                <textarea name="questions[{{ $index }}][explanation]" class="form-control" rows="2">{{ $question['explanation'] }}</textarea>
                            </div>
                            @endif
                            @elseif(in_array($question['question_type'], ['SHORT_ANSWER', 'DESCRIPTIVE']))
                            <div class="hw-field mb-3">
                                <label>Expected Answer / Model Answer</label>
                                <textarea name="questions[{{ $index }}][expected_answer]" class="form-control" rows="3">{{ $question['expected_answer'] ?? $question['answer_key'] ?? '' }}</textarea>
                            </div>
                            @if(isset($question['key_points']) && is_array($question['key_points']))
                            <div class="hw-field">
                                <label>Key Points <small style="font-weight:400;text-transform:none;letter-spacing:0;">(one per line)</small></label>
                                <textarea name="questions[{{ $index }}][key_points]" class="form-control" rows="3">{{ implode("\n", $question['key_points']) }}</textarea>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-warning" style="border-radius:12px;">No questions in this homework.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mb-4">
            <button type="submit" class="btn-hw-update">
                <span class="material-symbols-outlined" style="font-size:19px;">save</span> Update Homework
            </button>
            <a href="{{ route('admin.management.homework.show', $homework->homework_id) }}" class="btn-back">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function removeQuestion(index) {
        document.querySelector(`.question-card[data-index="${index}"]`).remove();
    }
</script>
@endpush
@endsection