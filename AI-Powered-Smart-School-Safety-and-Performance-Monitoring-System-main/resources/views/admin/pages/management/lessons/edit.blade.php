@extends('admin.layouts.app')

@section('title', 'Edit Lesson')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Edit Lesson</h6>
                        <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="material-symbols-outlined">arrow_back</i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.management.lessons.update', $lesson->lesson_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-control" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}" {{ $lesson->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->subject_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select name="grade_level" class="form-control" required>
                                    @for($i = 6; $i <= 11; $i++)
                                    <option value="{{ $i }}" {{ $lesson->grade_level == $i ? 'selected' : '' }}>Grade {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="draft" {{ $lesson->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ $lesson->status === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ $lesson->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit/Chapter <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control" value="{{ $lesson->unit }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Lesson Content <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control" rows="6" required>{{ $lesson->content }}</textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Topics (comma-separated)</label>
                                <input type="text" name="topics" class="form-control" 
                                    value="{{ is_array($lesson->topics) ? implode(', ', $lesson->topics) : $lesson->topics }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Learning Outcomes (comma-separated)</label>
                                <input type="text" name="learning_outcomes" class="form-control" 
                                    value="{{ is_array($lesson->learning_outcomes) ? implode(', ', $lesson->learning_outcomes) : $lesson->learning_outcomes }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Difficulty Level <span class="text-danger">*</span></label>
                                <select name="difficulty" class="form-control" required>
                                    <option value="beginner" {{ $lesson->difficulty === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ $lesson->difficulty === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ $lesson->difficulty === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control" 
                                    value="{{ $lesson->duration_minutes ?? 60 }}" min="1">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="material-symbols-outlined">save</i> Update Lesson
                                </button>
                                <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-lg">
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
@endsection

