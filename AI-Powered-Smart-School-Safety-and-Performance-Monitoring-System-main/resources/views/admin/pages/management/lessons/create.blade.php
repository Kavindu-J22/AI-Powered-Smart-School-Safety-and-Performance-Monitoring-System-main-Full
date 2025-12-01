@extends('admin.layouts.app')

@section('title', 'Add Lesson')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Add New Lesson</h6>
                        <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="material-symbols-outlined">arrow_back</i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.management.lessons.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-control" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects ?? [] as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select name="grade_level" class="form-control" required>
                                    @for($i = 6; $i <= 11; $i++)
                                    <option value="{{ $i }}">Grade {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Teacher <span class="text-danger">*</span></label>
                                <select name="teacher_id" class="form-control" required>
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->teacher_id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit/Chapter <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control" placeholder="e.g., Photosynthesis" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Introduction to Photosynthesis" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Lesson Content <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control" rows="6" 
                                    placeholder="Enter the full lesson content or summary here. This will be used by AI to generate questions." required></textarea>
                                <small class="text-muted">The AI will use this content to generate homework questions.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Topics (comma-separated)</label>
                                <input type="text" name="topics" class="form-control" 
                                    placeholder="e.g., photosynthesis, chlorophyll, sunlight, plants">
                                <small class="text-muted">Key topics covered in this lesson</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Learning Outcomes (comma-separated)</label>
                                <input type="text" name="learning_outcomes" class="form-control" 
                                    placeholder="e.g., Understand photosynthesis, Identify chlorophyll function">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Difficulty Level <span class="text-danger">*</span></label>
                                <select name="difficulty" class="form-control" required>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="60" min="1">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="material-symbols-outlined">save</i> Save Lesson
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

