@extends('admin.layouts.app')

@section('title', 'Edit Lesson')

@push('styles')
<style>
    .form-page-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .form-page-header h4 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
    }

    .form-page-header p {
        opacity: .85;
        margin: 4px 0 0;
        font-size: .9rem;
    }

    .form-section-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(245, 87, 108, .08);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .form-section-header {
        background: linear-gradient(90deg, #fff0f3, #fce4ec);
        padding: 14px 24px;
        border-bottom: 1px solid #fce4ec;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-header .section-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f093fb, #f5576c);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .form-section-header .section-icon .material-symbols-outlined {
        font-size: 18px;
        color: #fff;
    }

    .form-section-header h6 {
        margin: 0;
        font-size: .9rem;
        font-weight: 700;
        color: #3d0f1a;
    }

    .form-section-body {
        padding: 22px 24px;
    }

    .enhanced-label {
        font-size: .82rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .enhanced-label .material-symbols-outlined {
        font-size: 16px;
        color: #f5576c;
    }

    .enhanced-input {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff8f9;
        font-size: .88rem;
        transition: all .2s;
    }

    .enhanced-input:focus {
        border-color: #f5576c;
        box-shadow: 0 0 0 3px rgba(245, 87, 108, .10);
        background: #fff;
    }

    .ai-hint {
        background: linear-gradient(135deg, #fce4ec, #f3e5f5);
        border-left: 4px solid #f5576c;
        border-radius: 0 10px 10px 0;
        padding: 10px 16px;
        margin-top: 8px;
        font-size: .82rem;
        color: #880e4f;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-hint .material-symbols-outlined {
        font-size: 18px;
        color: #f5576c;
    }

    .diff-option {
        display: flex;
        gap: 10px;
    }

    .diff-card {
        flex: 1;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        font-size: .82rem;
        font-weight: 600;
    }

    .diff-card.beginner-card {
        color: #2d9f6c;
    }

    .diff-card.intermediate-card {
        color: #d08a00;
    }

    .diff-card.advanced-card {
        color: #c0392b;
    }

    .diff-card.selected-beginner {
        border-color: #2d9f6c;
        background: #e6f9f0;
    }

    .diff-card.selected-intermediate {
        border-color: #d08a00;
        background: #fff7e6;
    }

    .diff-card.selected-advanced {
        border-color: #c0392b;
        background: #fde8e8;
    }

    .diff-card .material-symbols-outlined {
        font-size: 24px;
        display: block;
        margin-bottom: 4px;
    }

    .status-select-wrap select {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff8f9;
        font-size: .88rem;
    }

    .submit-bar {
        background: #fff;
        border-radius: 14px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 16px rgba(245, 87, 108, .08);
    }

    .btn-update {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 28px;
        font-weight: 700;
        font-size: .9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: opacity .2s;
    }

    .btn-update:hover {
        opacity: .9;
        color: #fff;
    }

    .btn-cancel-form {
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        font-size: .9rem;
    }

    .edit-meta-badge {
        background: rgba(255, 255, 255, .2);
        border-radius: 50px;
        padding: 4px 14px;
        font-size: .8rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="form-page-header">
        <div>
            <h4><span class="material-symbols-outlined" style="vertical-align:middle;margin-right:8px;">edit</span>Edit Lesson</h4>
            <p>Update lesson details — changes will reflect in AI homework generation</p>
            <div style="margin-top:10px;">
                <span class="edit-meta-badge"><span class="material-symbols-outlined" style="font-size:14px;">title</span>{{ Str::limit($lesson->title, 40) }}</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.management.lessons.show', $lesson->lesson_id) }}" class="btn btn-light btn-sm d-flex align-items-center gap-1" style="border-radius:10px;font-weight:600;">
                <span class="material-symbols-outlined" style="font-size:17px;">visibility</span> View
            </a>
            <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-1" style="border-radius:10px;font-weight:600;">
                <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.management.lessons.update', $lesson->lesson_id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Section 1: Classification & Status --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">category</span></div>
                <div>
                    <h6>Classification & Status</h6><small class="text-muted" style="font-size:.78rem;">Subject, grade and publication status</small>
                </div>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">subject</span>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control enhanced-input" required>
                            <option value="">— Select Subject —</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}" {{ $lesson->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">school</span>Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-control enhanced-input" required>
                            @for($i = 6; $i <= 11; $i++)
                                <option value="{{ $i }}" {{ $lesson->grade_level == $i ? 'selected' : '' }}>Grade {{ $i }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">toggle_on</span>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control enhanced-input" required>
                            <option value="draft" {{ $lesson->status === 'draft'     ? 'selected' : '' }}>◷ Draft</option>
                            <option value="published" {{ $lesson->status === 'published' ? 'selected' : '' }}>✓ Published</option>
                            <option value="archived" {{ $lesson->status === 'archived'  ? 'selected' : '' }}>⊘ Archived</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">schedule</span>Duration (min)</label>
                        <input type="number" name="duration_minutes" class="form-control enhanced-input" value="{{ $lesson->duration_minutes ?? 60 }}" min="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Lesson Details --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">edit_note</span></div>
                <div>
                    <h6>Lesson Details</h6><small class="text-muted" style="font-size:.78rem;">Title, unit and full lesson content</small>
                </div>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">folder_open</span>Unit / Chapter <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control enhanced-input" value="{{ $lesson->unit }}" required>
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">title</span>Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control enhanced-input" value="{{ $lesson->title }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="enhanced-label"><span class="material-symbols-outlined">article</span>Lesson Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control enhanced-input" rows="7" required>{{ $lesson->content }}</textarea>
                    <div class="ai-hint">
                        <span class="material-symbols-outlined">auto_awesome</span>
                        <span>The AI homework engine re-uses this content — keep it accurate and detailed for best question quality.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Topics & Outcomes --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">checklist</span></div>
                <div>
                    <h6>Topics & Learning Outcomes</h6><small class="text-muted" style="font-size:.78rem;">Key topics and what students will learn</small>
                </div>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">tag</span>Topics <small class="fw-normal text-muted">(comma-separated)</small></label>
                        <input type="text" name="topics" class="form-control enhanced-input"
                            value="{{ is_array($lesson->topics) ? implode(', ', $lesson->topics) : $lesson->topics }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">emoji_events</span>Learning Outcomes <small class="fw-normal text-muted">(comma-separated)</small></label>
                        <input type="text" name="learning_outcomes" class="form-control enhanced-input"
                            value="{{ is_array($lesson->learning_outcomes) ? implode(', ', $lesson->learning_outcomes) : $lesson->learning_outcomes }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Difficulty --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">signal_cellular_alt</span></div>
                <div>
                    <h6>Difficulty Level</h6><small class="text-muted" style="font-size:.78rem;">Select the appropriate difficulty for students</small>
                </div>
            </div>
            <div class="form-section-body">
                <input type="hidden" name="difficulty" id="difficultyInput" value="{{ $lesson->difficulty ?? 'beginner' }}" required>
                <div class="diff-option">
                    <div class="diff-card beginner-card {{ ($lesson->difficulty ?? 'beginner') === 'beginner' ? 'selected-beginner' : '' }}" onclick="selectDiff('beginner',this)">
                        <span class="material-symbols-outlined">sentiment_satisfied</span>Beginner
                    </div>
                    <div class="diff-card intermediate-card {{ ($lesson->difficulty ?? '') === 'intermediate' ? 'selected-intermediate' : '' }}" onclick="selectDiff('intermediate',this)">
                        <span class="material-symbols-outlined">sentiment_neutral</span>Intermediate
                    </div>
                    <div class="diff-card advanced-card {{ ($lesson->difficulty ?? '') === 'advanced' ? 'selected-advanced' : '' }}" onclick="selectDiff('advanced',this)">
                        <span class="material-symbols-outlined">sentiment_very_dissatisfied</span>Advanced
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Bar --}}
        <div class="submit-bar">
            <button type="submit" class="btn-update">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span> Update Lesson
            </button>
            <a href="{{ route('admin.management.lessons.show', $lesson->lesson_id) }}" class="btn btn-outline-secondary btn-cancel-form">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function selectDiff(value, el) {
        document.getElementById('difficultyInput').value = value;
        document.querySelectorAll('.diff-card').forEach(c => {
            c.classList.remove('selected-beginner', 'selected-intermediate', 'selected-advanced');
        });
        el.classList.add('selected-' + value);
    }
</script>
@endpush
@endsection