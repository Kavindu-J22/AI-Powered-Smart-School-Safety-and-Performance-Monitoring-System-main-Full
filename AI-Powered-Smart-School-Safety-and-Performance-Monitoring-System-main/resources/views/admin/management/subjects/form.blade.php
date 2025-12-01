@extends('admin.layouts.app')

@section('title', isset($subject) ? 'Edit Subject' : 'Add New Subject')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">{{ isset($subject) ? 'edit' : 'add' }}</i>
                            {{ isset($subject) ? 'Edit Subject' : 'Add New Subject' }}
                        </h3>
                        <a href="{{ route('admin.management.subjects.index') }}" class="btn btn-secondary">
                            <i class="material-icons-outlined me-1">arrow_back</i>
                            Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.management.subjects.enroll') }}">
                            @csrf
                            @if (isset($subject))
                                <input type="hidden" name="id" value="{{ $subject->id }}">
                            @endif

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">info</i>
                                                Basic Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Subject Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                                    name="name" value="{{ old('name', $subject->name ?? '') }}"
                                                    placeholder="e.g., Mathematics, English Literature, Physics" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="subject_code" class="form-label">Subject Code</label>
                                                <input type="text"
                                                    class="form-control @error('subject_code') is-invalid @enderror"
                                                    id="subject_code" name="subject_code"
                                                    value="{{ old('subject_code', $subject->subject_code ?? '') }}"
                                                    placeholder="e.g., MATH01, ENG01, PHY01"
                                                    style="text-transform: uppercase;">
                                                @error('subject_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('category') is-invalid @enderror"
                                                    id="category" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="core"
                                                        {{ old('category', $subject->category ?? '') == 'core' ? 'selected' : '' }}>
                                                        Core Subject</option>
                                                    <option value="elective"
                                                        {{ old('category', $subject->category ?? '') == 'elective' ? 'selected' : '' }}>
                                                        Elective Subject</option>
                                                    <option value="practical"
                                                        {{ old('category', $subject->category ?? '') == 'practical' ? 'selected' : '' }}>
                                                        Practical Subject</option>
                                                    <option value="language"
                                                        {{ old('category', $subject->category ?? '') == 'language' ? 'selected' : '' }}>
                                                        Language Subject</option>
                                                    <option value="extracurricular"
                                                        {{ old('category', $subject->category ?? '') == 'extracurricular' ? 'selected' : '' }}>
                                                        Extracurricular</option>
                                                </select>
                                                @error('category')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="credit_hours" class="form-label">Credit Hours</label>
                                                <input type="number"
                                                    class="form-control @error('credit_hours') is-invalid @enderror"
                                                    id="credit_hours" name="credit_hours" min="1" max="10"
                                                    value="{{ old('credit_hours', $subject->credit_hours ?? 3) }}"
                                                    placeholder="Number of weekly hours">
                                                @error('credit_hours')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                    rows="3" placeholder="Brief description of the subject">{{ old('description', $subject->description ?? '') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grade Levels & Requirements -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">grade</i>
                                                Grade Levels & Requirements
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Available for Grade Levels <span
                                                        class="text-danger">*</span></label>
                                                <div class="row">
                                                    @for ($grade = 1; $grade <= 13; $grade++)
                                                        <div class="col-md-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="grade_{{ $grade }}" name="grade_levels[]"
                                                                    value="{{ $grade }}"
                                                                    {{ in_array($grade, old('grade_levels', isset($subject) && $subject->grade_levels ? json_decode($subject->grade_levels, true) : [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="grade_{{ $grade }}">
                                                                    Grade {{ $grade }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                                @error('grade_levels')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="prerequisites" class="form-label">Prerequisites</label>
                                                <textarea class="form-control @error('prerequisites') is-invalid @enderror" id="prerequisites" name="prerequisites"
                                                    rows="2" placeholder="List any prerequisite subjects or requirements">{{ old('prerequisites', $subject->prerequisites ?? '') }}</textarea>
                                                @error('prerequisites')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="textbook" class="form-label">Recommended Textbook</label>
                                                <input type="text"
                                                    class="form-control @error('textbook') is-invalid @enderror"
                                                    id="textbook" name="textbook"
                                                    value="{{ old('textbook', $subject->textbook ?? '') }}"
                                                    placeholder="e.g., Oxford Mathematics Grade 10">
                                                @error('textbook')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="syllabus_url" class="form-label">Syllabus URL</label>
                                                <input type="url"
                                                    class="form-control @error('syllabus_url') is-invalid @enderror"
                                                    id="syllabus_url" name="syllabus_url"
                                                    value="{{ old('syllabus_url', $subject->syllabus_url ?? '') }}"
                                                    placeholder="https://example.com/syllabus.pdf">
                                                @error('syllabus_url')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assessment & Settings -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">assessment</i>
                                                Assessment & Settings
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="max_marks" class="form-label">Maximum Marks</label>
                                                        <input type="number"
                                                            class="form-control @error('max_marks') is-invalid @enderror"
                                                            id="max_marks" name="max_marks" min="50"
                                                            max="200"
                                                            value="{{ old('max_marks', $subject->max_marks ?? 100) }}"
                                                            placeholder="e.g., 100">
                                                        @error('max_marks')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="pass_marks" class="form-label">Pass Marks</label>
                                                        <input type="number"
                                                            class="form-control @error('pass_marks') is-invalid @enderror"
                                                            id="pass_marks" name="pass_marks" min="20"
                                                            max="100"
                                                            value="{{ old('pass_marks', $subject->pass_marks ?? 40) }}"
                                                            placeholder="e.g., 40">
                                                        @error('pass_marks')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">Status</label>
                                                        <select class="form-select @error('status') is-invalid @enderror"
                                                            id="status" name="status">
                                                            <option value="active"
                                                                {{ old('status', $subject->status ?? 'active') == 'active' ? 'selected' : '' }}>
                                                                Active</option>
                                                            <option value="inactive"
                                                                {{ old('status', $subject->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                                                Inactive</option>
                                                            <option value="archived"
                                                                {{ old('status', $subject->status ?? '') == 'archived' ? 'selected' : '' }}>
                                                                Archived</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="has_practical" name="has_practical" value="1"
                                                            {{ old('has_practical', $subject->has_practical ?? false) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="has_practical">
                                                            Has Practical Component
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="is_optional"
                                                            name="is_optional" value="1"
                                                            {{ old('is_optional', $subject->is_optional ?? false) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_optional">
                                                            Optional Subject
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-end">
                                            <a href="{{ route('admin.management.subjects.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="material-icons-outlined me-1">cancel</i>
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="material-icons-outlined me-1">save</i>
                                                {{ isset($subject) ? 'Update Subject' : 'Create Subject' }}
                                            </button>
                                        </div>
                                    </div>
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
            $(document).ready(function() {
                // Auto-generate subject code from name
                $('#name').on('blur', function() {
                    const name = $(this).val().trim();
                    const currentCode = $('#subject_code').val();

                    if (name && !currentCode) {
                        const words = name.split(' ');
                        let code = '';

                        if (words.length === 1) {
                            code = words[0].substring(0, 3).toUpperCase() + '01';
                        } else {
                            code = words.map(word => word.charAt(0)).join('').toUpperCase() + '01';
                        }

                        $('#subject_code').val(code);
                    }
                });

                // Validate pass marks against max marks
                $('#max_marks, #pass_marks').on('input', function() {
                    const maxMarks = parseInt($('#max_marks').val()) || 0;
                    const passMarks = parseInt($('#pass_marks').val()) || 0;

                    if (passMarks > maxMarks) {
                        $('#pass_marks').addClass('is-invalid');
                        $('#pass_marks').next('.invalid-feedback').text(
                            'Pass marks cannot be greater than maximum marks');
                    } else {
                        $('#pass_marks').removeClass('is-invalid');
                    }
                });

                // Grade level selection helpers
                $('#select-all-grades').on('click', function(e) {
                    e.preventDefault();
                    $('input[name="grade_levels[]"]').prop('checked', true);
                });

                $('#select-primary').on('click', function(e) {
                    e.preventDefault();
                    $('input[name="grade_levels[]"]').prop('checked', false);
                    for (let i = 1; i <= 5; i++) {
                        $(`#grade_${i}`).prop('checked', true);
                    }
                });

                $('#select-secondary').on('click', function(e) {
                    e.preventDefault();
                    $('input[name="grade_levels[]"]').prop('checked', false);
                    for (let i = 6; i <= 11; i++) {
                        $(`#grade_${i}`).prop('checked', true);
                    }
                });

                $('#select-advanced').on('click', function(e) {
                    e.preventDefault();
                    $('input[name="grade_levels[]"]').prop('checked', false);
                    for (let i = 12; i <= 13; i++) {
                        $(`#grade_${i}`).prop('checked', true);
                    }
                });

                // Form validation before submit
                $('form').on('submit', function(e) {
                    let isValid = true;

                    // Check required fields
                    $(this).find('[required]').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    // Check grade levels selection
                    if ($('input[name="grade_levels[]"]:checked').length === 0) {
                        notificationManager.error('Validation Error', 'Please select at least one grade level');
                        isValid = false;
                    }

                    // Validate marks
                    const maxMarks = parseInt($('#max_marks').val()) || 0;
                    const passMarks = parseInt($('#pass_marks').val()) || 0;

                    if (passMarks > maxMarks) {
                        $('#pass_marks').addClass('is-invalid');
                        notificationManager.error('Validation Error',
                            'Pass marks cannot be greater than maximum marks');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        notificationManager.error('Validation Error',
                            'Please fill in all required fields correctly');
                    }
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .material-icons-outlined {
                font-size: 18px;
                vertical-align: middle;
            }

            .card-header .card-title {
                font-weight: 600;
            }

            .form-label {
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .form-check-input:checked {
                background-color: #0d6efd;
                border-color: #0d6efd;
            }

            .form-check-label {
                margin-left: 0.5rem;
            }

            #subject_code {
                font-family: 'Courier New', monospace;
            }

            .grade-selection-helpers {
                margin-bottom: 1rem;
            }

            .grade-selection-helpers .btn {
                margin-right: 0.5rem;
                margin-bottom: 0.5rem;
            }
        </style>

        <!-- Add grade selection helper buttons -->
        <div class="grade-selection-helpers mb-3" style="display: none;">
            <small class="text-muted d-block mb-2">Quick Selection:</small>
            <button type="button" class="btn btn-sm btn-outline-primary" id="select-primary">Primary (1-5)</button>
            <button type="button" class="btn btn-sm btn-outline-info" id="select-secondary">Secondary (6-11)</button>
            <button type="button" class="btn btn-sm btn-outline-success" id="select-advanced">Advanced (12-13)</button>
            <button type="button" class="btn btn-sm btn-outline-warning" id="select-all-grades">All Grades</button>
        </div>

        <script>
            // Show grade selection helpers when the grade levels section is focused
            $(document).ready(function() {
                $('input[name="grade_levels[]"]').on('focus', function() {
                    $('.grade-selection-helpers').show();
                });
            });
        </script>
    @endpush
@endsection
