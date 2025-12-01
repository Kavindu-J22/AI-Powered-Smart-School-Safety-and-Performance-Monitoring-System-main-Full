@extends('admin.layouts.app')

@section('title', __('school.edit_marks'))

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
                                    <h6 class="mb-0">{{ __('school.edit_student_marks') }}</h6>
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
                            <form action="{{ route('admin.management.marks.update', $mark->mark_id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Student Details (Read-only) -->
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-info">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">person</i>
                                            {{ __('school.student_details') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('school.student_id') }}</label>
                                                <div class="fw-bold">{{ $mark->student->student_code }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.name') }}</label>
                                                <div class="fw-bold">{{ $mark->student->full_name }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.grade') }}</label>
                                                <div class="fw-bold">{{ __('common.grade') }} {{ $mark->grade_level }}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="text-muted small">{{ __('common.class') }}</label>
                                                <div class="fw-bold">
                                                    {{ $mark->student->schoolClass ? $mark->student->schoolClass->class_name : 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label class="text-muted small">{{ __('common.subject') }}</label>
                                                <div class="fw-bold">{{ $mark->subject->subject_name }}
                                                    ({{ $mark->subject->subject_code }})</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="text-muted small">{{ __('school.academic_year') }}</label>
                                                <div class="fw-bold">{{ $mark->academic_year }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="text-muted small">{{ __('common.term') }}</label>
                                                <div class="fw-bold">{{ __('common.term') }} {{ $mark->term }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Marks Entry -->
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-primary">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">assessment</i>
                                            {{ __('school.marks_entry') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-input type="number" name="marks"
                                                    title="{{ __('school.marks_obtained') }}" :isRequired="true"
                                                    :value="old('marks', $mark->marks)" placeholder="{{ __('school.enter_marks_obtained') }}"
                                                    attr="step=0.01 min=0" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input type="number" name="total_marks"
                                                    title="{{ __('school.total_marks') }}" :isRequired="true"
                                                    :value="old('total_marks', $mark->total_marks)" placeholder="{{ __('school.enter_total_marks') }}"
                                                    attr="step=0.01 min=0" />
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mt-2">
                                                    <small class="text-xs">{{ __('common.percentage') }}</small>
                                                    <div class="input-group input-group-outline my-1">
                                                        <input type="text" id="percentage_display" class="form-control"
                                                            value="{{ number_format($mark->percentage ?? 0, 2) }}%"
                                                            readonly disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Grade Display -->
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <div class="alert alert-secondary">
                                                    <strong>{{ __('school.current_grade') }}:</strong>
                                                    <span
                                                        class="badge
                                                        @if (in_array($mark->grade, ['A+', 'A', 'A-'])) bg-success
                                                        @elseif(in_array($mark->grade, ['B+', 'B', 'B-'])) bg-primary
                                                        @elseif(in_array($mark->grade, ['C+', 'C', 'C-'])) bg-warning
                                                        @else bg-danger @endif"
                                                        style="font-size: 1.2em;">
                                                        {{ $mark->grade }}
                                                    </span>
                                                    <span class="ms-3">
                                                        <strong>{{ __('common.percentage') }}:</strong>
                                                        {{ number_format($mark->percentage, 2) }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-warning">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">comment</i>
                                            {{ __('common.remarks') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <x-input type="textarea" name="remarks"
                                                    title="{{ __('common.remarks') }} ({{ __('common.optional') }})"
                                                    :isRequired="false" :value="old('remarks', $mark->remarks)"
                                                    placeholder="{{ __('common.enter_remarks') }}"
                                                    attr="rows=3 maxlength=500" />
                                                <small
                                                    class="form-text text-muted ms-2">{{ __('common.maximum_500_characters') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="material-symbols-rounded me-1">save</i>{{ __('common.update') }}
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
            // Calculate percentage on marks change
            function calculatePercentage() {
                const marks = parseFloat($('#marks').val()) || 0;
                const totalMarks = parseFloat($('#total_marks').val()) || 0;

                if (totalMarks > 0) {
                    const percentage = (marks / totalMarks) * 100;
                    $('#percentage_display').val(percentage.toFixed(2) + '%');
                } else {
                    $('#percentage_display').val('');
                }
            }

            $('#marks, #total_marks').on('input', calculatePercentage);

            // Calculate initial percentage
            calculatePercentage();
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
