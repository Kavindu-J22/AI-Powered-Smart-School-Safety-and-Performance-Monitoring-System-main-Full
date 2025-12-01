@extends('admin.layouts.app')

@section('title', pageTitle())

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
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">
                                        <i class="material-symbols-rounded me-2">subject</i>
                                        {{ isset($subject) ? 'Edit Subject' : 'Create Subject' }}
                                    </h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-secondary mb-0" href="{{ route('admin.management.subjects.index') }}">
                                        <i class="material-symbols-rounded text-sm me-1">arrow_back</i>
                                        Back to Subjects
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.management.subjects.enroll') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @if (isset($subject))
                                    <input type="hidden" name="id" value="{{ $subject->id }}">
                                @endif

                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">info</i>
                                        Basic Information
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="subject_code" title="Subject Code" :isRequired="true"
                                                :value="old('subject_code', $subject->subject_code ?? '')" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="subject_name" title="Subject Name" :isRequired="true"
                                                :value="old('subject_name', $subject->subject_name ?? '')" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="grade_level" type="select" title="Grade Level"
                                                placeholder="Select Grade Level" :options="collect(range(1, 12))
                                                    ->mapWithKeys(fn($i) => ['Grade ' . $i => 'Grade ' . $i])
                                                    ->put('All Grades', 'All Grades')
                                                    ->toArray()" :value="old('grade_level', $subject->grade_level ?? '')" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="type" type="select" title="Subject Type"
                                                placeholder="Select Type" :options="[
                                                    'Core' => 'Core',
                                                    'Elective' => 'Elective',
                                                    'Optional' => 'Optional',
                                                    'Extra-curricular' => 'Extra-curricular',
                                                ]" :value="old('type', $subject->type ?? '')" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Details Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">school</i>
                                        Academic Details
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="credits" type="number" title="Credits"
                                                placeholder="Subject credits" attr="min='1' max='10'" :value="old('credits', $subject->credits ?? '')" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="status" type="select" title="Status" :options="['1' => 'Active', '0' => 'Inactive']"
                                                :value="old('status', $subject->status ?? '1')" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <x-input name="description" type="textarea" title="Description"
                                                placeholder="Subject description, curriculum details, learning objectives..."
                                                attr="rows='4'" :value="old('description', $subject->description ?? '')" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <a href="{{ route('admin.management.subjects.index') }}"
                                            class="btn btn-secondary me-2">
                                            <i class="material-symbols-rounded me-1">cancel</i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="material-symbols-rounded me-1">
                                                {{ isset($subject) ? 'update' : 'add' }}
                                            </i>
                                            {{ isset($subject) ? 'Update Subject' : 'Create Subject' }}
                                        </button>
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
