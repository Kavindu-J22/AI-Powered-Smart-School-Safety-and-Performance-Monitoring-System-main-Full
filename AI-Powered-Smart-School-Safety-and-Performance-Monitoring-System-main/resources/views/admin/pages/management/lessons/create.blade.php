@extends('admin.layouts.app')

@section('title', 'Add Lesson')

@push('styles')
<style>
    .form-page-header {
        background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
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
        box-shadow: 0 2px 16px rgba(24, 90, 157, .08);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .form-section-header {
        background: linear-gradient(90deg, #f0f4ff, #e8f5e9);
        padding: 14px 24px;
        border-bottom: 1px solid #e8eaf6;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-header .section-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: linear-gradient(135deg, #185a9d, #43cea2);
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
        color: #1a2a4a;
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
        color: #185a9d;
    }

    .enhanced-input,
    .form-control,
    .form-select,
    select.form-control,
    textarea.form-control {
        border-radius: 10px;
        border: 1.5px solid #cbd5e0 !important;
        background: #fff;
        font-size: .88rem;
        transition: all .2s;
    }

    .enhanced-input:focus,
    .form-control:focus,
    .form-select:focus,
    select.form-control:focus,
    textarea.form-control:focus {
        border-color: #185a9d !important;
        box-shadow: 0 0 0 3px rgba(24, 90, 157, .10);
        background: #fff;
    }

    .ai-hint {
        background: linear-gradient(135deg, #e8f5e9, #e3f2fd);
        border-left: 4px solid #43cea2;
        border-radius: 0 10px 10px 0;
        padding: 10px 16px;
        margin-top: 8px;
        font-size: .82rem;
        color: #2e7d32;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-hint .material-symbols-outlined {
        font-size: 18px;
        color: #43a047;
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

    .diff-card input[type=radio] {
        display: none;
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

    .submit-bar {
        background: #fff;
        border-radius: 14px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 16px rgba(24, 90, 157, .08);
    }

    .btn-save {
        background: linear-gradient(135deg, #185a9d, #43cea2);
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

    .btn-save:hover {
        opacity: .9;
        color: #fff;
    }

    .btn-cancel-form {
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        font-size: .9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="form-page-header">
        <div>
            <h4><span class="material-symbols-outlined" style="vertical-align:middle;margin-right:8px;">add_circle</span>Add New Lesson</h4>
            <p>Fill in the details below to create a new curriculum lesson</p>
        </div>
        <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-1" style="border-radius:10px;font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span> Back to Lessons
        </a>
    </div>

    <form action="{{ route('admin.management.lessons.store') }}" method="POST">
        @csrf

        {{-- Section 1: Classification --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">category</span></div>
                <div>
                    <h6>Classification</h6><small class="text-muted" style="font-size:.78rem;">Subject, grade level and assigned teacher</small>
                </div>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">subject</span>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control enhanced-input" required>
                            <option value="">— Select Subject —</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<span class="text-danger" style="font-size:.8rem;">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">school</span>Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-control enhanced-input" required>
                            @for($i = 6; $i <= 11; $i++)
                                <option value="{{ $i }}">Grade {{ $i }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">person</span>Teacher <span class="text-danger">*</span></label>
                        <select name="teacher_id" class="form-control enhanced-input" required>
                            <option value="">— Select Teacher —</option>
                            @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->teacher_id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                            @endforeach
                        </select>
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
                        <input type="text" name="unit" class="form-control enhanced-input" placeholder="e.g., Photosynthesis" required>
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">title</span>Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control enhanced-input" placeholder="e.g., Introduction to Photosynthesis" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="enhanced-label"><span class="material-symbols-outlined">article</span>Lesson Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control enhanced-input" rows="7"
                        placeholder="Enter the full lesson content or summary here. The AI will use this to generate homework questions." required></textarea>
                    <div class="ai-hint">
                        <span class="material-symbols-outlined">auto_awesome</span>
                        <span>The AI homework engine uses this content to auto-generate relevant questions — write it clearly and thoroughly.</span>
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
                            placeholder="e.g., photosynthesis, chlorophyll, sunlight, plants">
                        <small class="text-muted" style="font-size:.78rem;">Key topics covered in this lesson</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">emoji_events</span>Learning Outcomes <small class="fw-normal text-muted">(comma-separated)</small></label>
                        <input type="text" name="learning_outcomes" class="form-control enhanced-input"
                            placeholder="e.g., Understand photosynthesis, Identify chlorophyll function">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Settings --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="section-icon"><span class="material-symbols-outlined">tune</span></div>
                <div>
                    <h6>Lesson Settings</h6><small class="text-muted" style="font-size:.78rem;">Difficulty level and estimated duration</small>
                </div>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">signal_cellular_alt</span>Difficulty Level <span class="text-danger">*</span></label>
                        <select name="difficulty" class="form-control enhanced-input" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="enhanced-label"><span class="material-symbols-outlined">schedule</span>Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control enhanced-input" value="60" min="1" style="max-width:160px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Bar --}}
        <div class="submit-bar">
            <button type="submit" class="btn-save">
                <span class="material-symbols-outlined" style="font-size:18px;">save</span> Save Lesson
            </button>
            <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-cancel-form">Cancel</a>
        </div>
    </form>
</div>


@endsection