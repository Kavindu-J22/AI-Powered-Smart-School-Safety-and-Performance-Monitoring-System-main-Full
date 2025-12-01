@extends('admin.layouts.app')

@section('title', 'Add Marks')

@section('css')
    @vite(['resources/css/admin/forms.css', 'resources/css/admin/common-forms.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">{{ __('school.add_student_marks') }}</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-dark mb-0 btn-back-auto"
                                        href="{{ route('admin.management.marks.index') }}">
                                        <i
                                            class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>{{ __('common.back') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.management.marks.store') }}" method="POST" id="marksForm">
                                @csrf

                                <!-- Student Selection -->
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-primary">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">person</i>
                                            {{ __('school.select_student') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-input type="select" name="student_id"
                                                    title="{{ __('school.select_student') }}" :isRequired="true"
                                                    :value="old('student_id', request('student_id'))" placeholder="-- {{ __('school.select_student') }} --"
                                                    :options="$students
                                                        ->mapWithKeys(
                                                            fn($s) => [
                                                                $s->student_id =>
                                                                    $s->student_code .
                                                                    ' - ' .
                                                                    $s->full_name .
                                                                    ' (Grade ' .
                                                                    $s->grade_level .
                                                                    ')',
                                                            ],
                                                        )
                                                        ->toArray()" attr="id=student_id" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Details (shown after selection) -->
                                <div id="studentDetails" style="display: none;" class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-success">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">info</i>
                                            {{ __('school.student_details') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('school.student_id') }}</label>
                                                <div class="fw-bold" id="display_student_code"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.name') }}</label>
                                                <div class="fw-bold" id="display_full_name"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.grade') }}</label>
                                                <div class="fw-bold" id="display_grade_level"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.class') }}</label>
                                                <div class="fw-bold" id="display_class_name"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Details -->
                                <div class="row mb-4" id="markDetailsSection" style="display: none;">
                                    <div class="col-md-6">
                                        <x-input type="select" name="academic_year"
                                            title="{{ __('school.academic_year') }}" :isRequired="true" :value="old('academic_year', $currentAcademicYear)"
                                            :options="array_combine($academicYears, $academicYears)" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-input type="select" name="term" title="{{ __('common.term') }}"
                                            :isRequired="true" :value="old('term')"
                                            placeholder="-- {{ __('common.select_term') }} --" :options="$terms" />
                                    </div>
                                </div>

                                <!-- Subjects and Marks Entry -->
                                <div class="card mb-4 shadow-sm" id="subjectsMarksSection" style="display: none;">
                                    <div class="card-header bg-gradient-info">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">assignment</i>
                                            {{ __('school.subject_marks') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">{{ __('school.enter_marks_for_all_subjects') }}
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="subjectsTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>{{ __('common.subject') }}</th>
                                                        <th>{{ __('school.marks_obtained') }}</th>
                                                        <th>{{ __('school.total_marks') }}</th>
                                                        <th>{{ __('common.percentage') }}</th>
                                                        <th>{{ __('common.remarks') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="subjectsTableBody">
                                                    <!-- Subjects will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="material-symbols-rounded me-1">save</i>{{ __('common.save') }}
                                        </button>
                                        <a href="{{ route('admin.management.marks.index') }}" class="btn btn-secondary">
                                            <i class="material-symbols-rounded me-1">cancel</i>{{ __('common.cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // When student is selected
            $('#student_id').on('change', function() {
                const studentId = $(this).val();

                if (studentId) {
                    // Fetch student details and all subjects for the grade
                    $.ajax({
                        url: '{{ route('admin.management.marks.student.details') }}',
                        method: 'GET',
                        data: {
                            student_id: studentId
                        },
                        success: function(response) {
                            // Show student details
                            $('#display_student_code').text(response.student_code);
                            $('#display_full_name').text(response.full_name);
                            $('#display_grade_level').text('Grade ' + response.grade_level);
                            $('#display_class_name').text(response.class_name);
                            $('#studentDetails').slideDown();

                            // Populate subjects table
                            const tableBody = $('#subjectsTableBody');
                            tableBody.empty();

                            response.subjects.forEach(function(subject, index) {
                                const row = `
                                    <tr>
                                        <td>
                                            <strong>${subject.subject_code}</strong><br>
                                            <small class="text-muted">${subject.subject_name}</small>
                                            <input type="hidden" name="marks[${index}][subject_id]" value="${subject.id}">
                                        </td>
                                        <td>
                                            <input type="number" name="marks[${index}][marks_obtained]"
                                                   class="form-control marks-obtained"
                                                   step="0.01" min="0" placeholder="0.00"
                                                   data-row="${index}">
                                        </td>
                                        <td>
                                            <input type="number" name="marks[${index}][total_marks]"
                                                   class="form-control total-marks"
                                                   step="0.01" min="0" placeholder="100.00" value="100.00"
                                                   data-row="${index}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control percentage-display"
                                                   readonly disabled data-row="${index}">
                                        </td>
                                        <td>
                                            <textarea name="marks[${index}][remarks]" class="form-control"
                                                      rows="1" maxlength="500" placeholder="Optional remarks"></textarea>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });

                            // Show academic details and subjects marks section
                            $('#markDetailsSection, #subjectsMarksSection').slideDown();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', xhr, status, error);
                            alert('{{ __('common.error_fetching_student_details') }}');
                        }
                    });
                } else {
                    // Hide all sections
                    $('#studentDetails, #markDetailsSection, #subjectsMarksSection').slideUp();
                    $('#subjectsTableBody').empty();
                }
            });

            // Calculate percentage when marks or total marks change
            $(document).on('input', '.marks-obtained, .total-marks', function() {
                const row = $(this).data('row');
                const marks = parseFloat($(`.marks-obtained[data-row="${row}"]`).val()) || 0;
                const totalMarks = parseFloat($(`.total-marks[data-row="${row}"]`).val()) || 0;
                const percentageField = $(`.percentage-display[data-row="${row}"]`);

                if (totalMarks > 0) {
                    const percentage = (marks / totalMarks) * 100;
                    percentageField.val(percentage.toFixed(2) + '%');
                } else {
                    percentageField.val('');
                }
            });

            // Trigger student details load if student_id is pre-selected
            @if (request('student_id'))
                $('#student_id').trigger('change');
            @endif
        });
    </script>
@endpush

@push('styles')
    <style>
        .required::after {
            content: " *";
            color: red;
        }

        .material-icons-outlined {
            vertical-align: middle;
        }
    </style>
@endpush
