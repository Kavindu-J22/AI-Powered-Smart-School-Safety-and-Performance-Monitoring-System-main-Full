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
                <div class="card my-4">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">{{ pageTitle() }}</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a class="btn btn-outline-dark mb-0 btn-back-auto"
                                    href="{{ route('admin.management.students.index') }}">
                                    <i
                                        class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>{{ __('common.back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.management.students.enroll') }}" method="POST" id="studentForm"
                            enctype="multipart/form-data">
                            @csrf
                            @if ($id)
                            <input type="hidden" name="id" value="{{ $id }}">
                            @endif

                            <!-- Student Information -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-primary">
                                    <h6 class="mb-0 d-flex align-items-center text-white">
                                        <i class="material-symbols-rounded me-2 icon-size-sm">person</i>
                                        {{ __('school.student_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Profile Image Upload -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <div class="avatar avatar-xl position-relative mb-1">
                                                    @if (isset($student) && $student->photo_path)
                                                    <img id="profilePreview"
                                                        src="{{ asset('storage/' . $student->photo_path) }}"
                                                        alt="Student Photo"
                                                        class="w-100 h-100 border-radius-lg shadow-sm object-fit-cover"
                                                        style="border-radius: 50%;">
                                                    @else
                                                    <div id="profilePreview"
                                                        class="w-100 h-100 border-radius-lg shadow-sm bg-gradient-primary d-flex align-items-center justify-content-center"
                                                        style="border-radius: 50%;">
                                                        <i
                                                            class="material-symbols-rounded text-white text-lg">person</i>
                                                    </div>
                                                    @endif
                                                    <label for="profileImage"
                                                        class="btn btn-sm btn-icon-only bg-gradient-light position-absolute bottom-20 end-0 mb-n2 me-n2 cursor-pointer">
                                                        <i class="material-symbols-rounded text-xs">edit</i>
                                                    </label>
                                                    <input type="file" id="profileImage" name="profile_image"
                                                        accept="image/*" style="display: none;">
                                                </div>
                                                <small class="text-muted" style="margin-left: 8px">Click the edit icon
                                                    to upload a photo</small>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <!-- 4-column layout for first row -->
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <x-input name="student_code" title="{{ __('school.student_code') }}"
                                                        :isRequired="true"
                                                        attr="maxlength='50' readonly style='background-color: #f8f9fa; cursor: not-allowed;'"
                                                        :value="old(
                                                                'student_code',
                                                                $student->student_code ?? '',
                                                            )" />
                                                    @if (!$id)
                                                    <small
                                                        class="form-text text-muted">{{ __('common.auto_generated') }}</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="first_name" title="First Name" :isRequired="true"
                                                        attr="maxlength='50'" :value="old('first_name', $student->first_name ?? '')" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="middle_name" title="Middle Name"
                                                        attr="maxlength='50'" :value="old('middle_name', $student->middle_name ?? '')" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="last_name" title="Last Name" :isRequired="true"
                                                        attr="maxlength='50'" :value="old('last_name', $student->last_name ?? '')" />
                                                </div>
                                            </div>

                                            <!--  4-column layout for second row -->
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <x-input name="date_of_birth" type="date" title="Date of Birth"
                                                        :isRequired="true" :value="old(
                                                                'date_of_birth',
                                                                $student->date_of_birth ?? '',
                                                            )" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="gender" type="select" title="Gender"
                                                        :isRequired="true" placeholder="Select Gender" :options="[
                                                                'M' => 'Male',
                                                                'F' => 'Female',
                                                                'Other' => 'Other',
                                                            ]"
                                                        :value="old('gender', $student->gender ?? '')" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="nationality" title="Nationality"
                                                        attr="maxlength='50'" :value="old('nationality', $student->nationality ?? '')" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="religion" title="Religion" attr="maxlength='50'"
                                                        :value="old('religion', $student->religion ?? '')" />
                                                </div>
                                            </div>

                                            <!-- Updated: 4-column layout for third row  -->
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <x-input name="home_language" title="Home Language"
                                                        attr="maxlength='50'" :value="old(
                                                                'home_language',
                                                                $student->home_language ?? '',
                                                            )" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="mobile_phone" title="Mobile Phone"
                                                        attr="maxlength='15'" :value="old(
                                                                'mobile_phone',
                                                                $student->mobile_phone ?? '',
                                                            )" />
                                                </div>
                                                <div class="col-md-3">
                                                    <x-input name="email" type="email" title="Email Address"
                                                        attr="maxlength='100'" :value="old('email', $student->email ?? '')" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-info">
                                    <h6 class="mb-0 d-flex align-items-center text-white">
                                        <i class="material-symbols-rounded me-2 icon-size-sm">location_on</i>
                                        Address Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="address_line1" title="Address Line 1"
                                                attr="maxlength='255'" :value="old('address_line1', $student->address_line1 ?? '')" />
                                        </div>
                                        <div class="col-md-6">
                                            <x-input name="address_line2" title="Address Line 2"
                                                attr="maxlength='255'" :value="old('address_line2', $student->address_line2 ?? '')" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-input name="city" title="City" attr="maxlength='100'"
                                                :value="old('city', $student->city ?? '')" />
                                        </div>
                                        <div class="col-md-3">
                                            <x-input name="state" title="State/Province" attr="maxlength='100'"
                                                :value="old('state', $student->state ?? '')" />
                                        </div>
                                        <div class="col-md-3">
                                            <x-input name="postal_code" title="Postal Code" attr="maxlength='20'"
                                                :value="old('postal_code', $student->postal_code ?? '')" />
                                        </div>
                                        <div class="col-md-3">
                                            <x-input name="country" title="Country" attr="maxlength='100'"
                                                :value="old('country', $student->country ?? '')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-success">
                                    <h6 class="mb-0 d-flex align-items-center text-white">
                                        <i class="material-symbols-rounded me-2 icon-size-sm">school</i>
                                        Academic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-input name="grade_level" type="select" id="grade_level"
                                                title="Grade Level" :isRequired="true"
                                                placeholder="Select Grade Level" :options="$grades"
                                                :value="old('grade_level', $student->grade_level ?? '')" />
                                        </div>

                                        <div class="col-md-4">
                                            <x-input name="class_id" type="select" id="class_id" title="Class"
                                                :isRequired="true" placeholder="Select Grade First" :options="[]"
                                                :value="old('class_id', $student->class_id ?? '')" />
                                            <small class="text-muted">Classes will be filtered based on selected
                                                grade</small>
                                        </div>

                                        <div class="col-md-4">
                                            <x-input name="section" title="Section" attr="maxlength='10'"
                                                :value="old('section', $student->section ?? '')" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="enrollment_date" type="date" title="Enrollment Date"
                                                :isRequired="true" :value="old(
                                                        'enrollment_date',
                                                        $student->enrollment_date ?? date('Y-m-d'),
                                                    )" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="is_active" type="select" title="Active Status"
                                                :isRequired="true" :options="['1' => 'Yes', '0' => 'No']" :value="old('is_active', $student->is_active ?? '1')" />
                                        </div>
                                    </div>

                                    <!-- Subject Selection (Dynamic based on grade) -->
                                    <div class="row" id="subjectSelectionContainer" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="alert alert-info mb-3" id="subjectSelectionInfo">
                                                <i class="material-symbols-rounded me-2">info</i>
                                                <span id="educationLevelText"></span>
                                            </div>

                                            <!-- Primary Education (Grades 1-5) -->
                                            <div id="primarySubjects" style="display: none;">
                                                <h6 class="text-primary mb-3">Primary Education Subject Selection</h6>

                                                <!-- First Language (Required - Choose 1) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">First Language <span
                                                            class="text-danger">*</span> (Choose 1)</label>
                                                    <div id="firstLanguagePrimary" class="row g-2"></div>
                                                    <small class="text-danger d-none"
                                                        id="firstLanguagePrimaryError">Please select one first
                                                        language</small>
                                                </div>

                                                <!-- Religion (Required - Choose 1) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Religion <span
                                                            class="text-danger">*</span> (Choose 1)</label>
                                                    <div id="religionPrimary" class="row g-2"></div>
                                                    <small class="text-danger d-none" id="religionPrimaryError">Please
                                                        select one religion</small>
                                                </div>

                                                <!-- Aesthetic Studies (Required - Choose 1) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Aesthetic Studies <span
                                                            class="text-danger">*</span> (Choose 1)</label>
                                                    <div id="aestheticPrimary" class="row g-2"></div>
                                                    <small class="text-danger d-none"
                                                        id="aestheticPrimaryError">Please select one aesthetic
                                                        study</small>
                                                </div>

                                                <!-- Core Subjects (Auto-assigned) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Core Subjects
                                                        (Auto-assigned)</label>
                                                    <div id="corePrimary" class="alert alert-success">
                                                        <ul id="corePrimaryList" class="mb-0"></ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Secondary Education (Grades 6-11) -->
                                            <div id="secondarySubjects" style="display: none;">
                                                <h6 class="text-primary mb-3">Secondary Education Subject Selection
                                                </h6>

                                                <!-- First Language (Required - Choose 1) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">First Language <span
                                                            class="text-danger">*</span> (Choose 1)</label>
                                                    <div id="firstLanguageSecondary" class="row g-2"></div>
                                                    <small class="text-danger d-none"
                                                        id="firstLanguageSecondaryError">Please select one first
                                                        language</small>
                                                </div>

                                                <!-- Religion (Required - Choose 1) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Religion <span
                                                            class="text-danger">*</span> (Choose 1)</label>
                                                    <div id="religionSecondary" class="row g-2"></div>
                                                    <small class="text-danger d-none"
                                                        id="religionSecondaryError">Please select one religion</small>
                                                </div>

                                                <!-- Core Subjects (Auto-assigned) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Core Subjects
                                                        (Auto-assigned)</label>
                                                    <div id="coreSecondary" class="alert alert-success">
                                                        <ul id="coreSecondaryList" class="mb-0"></ul>
                                                    </div>
                                                </div>

                                                <!-- Elective Subjects (Choose 3) -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Elective Subjects <span
                                                            class="text-danger">*</span> (Choose exactly 3)</label>
                                                    <div id="electiveSecondary" class="row g-2"></div>
                                                    <small class="text-danger d-none"
                                                        id="electiveSecondaryError">Please select exactly 3 elective
                                                        subjects</small>
                                                    <small class="text-muted d-block mt-1">Selected: <span
                                                            id="electiveCount" class="fw-bold">0</span>/3</small>
                                                </div>
                                            </div>

                                            <!-- Advanced Level (Grades 12-13) -->
                                            <div id="advancedSubjects" style="display: none;">
                                                <h6 class="text-primary mb-3">Advanced Level Subject Selection</h6>

                                                <!-- Stream Selection -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Select Stream <span
                                                            class="text-danger">*</span></label>
                                                    <div class="row g-3" id="streamSelection">
                                                        <div class="col-md-3">
                                                            <div class="form-check border rounded p-3 stream-card"
                                                                data-stream="Arts">
                                                                <input class="form-check-input" type="radio"
                                                                    name="stream" id="streamArts" value="Arts">
                                                                <label class="form-check-label fw-bold"
                                                                    for="streamArts">
                                                                    <i class="material-symbols-rounded">palette</i>
                                                                    Arts Stream
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check border rounded p-3 stream-card"
                                                                data-stream="Commerce">
                                                                <input class="form-check-input" type="radio"
                                                                    name="stream" id="streamCommerce"
                                                                    value="Commerce">
                                                                <label class="form-check-label fw-bold"
                                                                    for="streamCommerce">
                                                                    <i
                                                                        class="material-symbols-rounded">business_center</i>
                                                                    Commerce Stream
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check border rounded p-3 stream-card"
                                                                data-stream="Science">
                                                                <input class="form-check-input" type="radio"
                                                                    name="stream" id="streamScience"
                                                                    value="Science">
                                                                <label class="form-check-label fw-bold"
                                                                    for="streamScience">
                                                                    <i class="material-symbols-rounded">science</i>
                                                                    Science Stream
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check border rounded p-3 stream-card"
                                                                data-stream="Technology">
                                                                <input class="form-check-input" type="radio"
                                                                    name="stream" id="streamTechnology"
                                                                    value="Technology">
                                                                <label class="form-check-label fw-bold"
                                                                    for="streamTechnology">
                                                                    <i class="material-symbols-rounded">computer</i>
                                                                    Technology Stream
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <small class="text-danger d-none" id="streamError">Please select a
                                                        stream</small>
                                                </div>

                                                <!-- Stream Subjects (Choose 3 after stream selection) -->
                                                <div class="mb-4" id="streamSubjectsContainer"
                                                    style="display: none;">
                                                    <label class="form-label fw-bold">Stream Subjects <span
                                                            class="text-danger">*</span> (Choose exactly 3)</label>
                                                    <div id="streamSubjects" class="row g-2"></div>
                                                    <small class="text-danger d-none" id="streamSubjectsError">Please
                                                        select exactly 3 subjects from your chosen stream</small>
                                                    <small class="text-muted d-block mt-1">Selected: <span
                                                            id="streamSubjectCount" class="fw-bold">0</span>/3</small>
                                                </div>
                                            </div>

                                            <!-- Hidden inputs to store selected subjects -->
                                            <input type="hidden" name="subject_ids" id="subject_ids">
                                            <input type="hidden" name="core_subject_ids" id="core_subject_ids">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User Account Information -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-warning">
                                    <h6 class="mb-0 d-flex align-items-center text-white">
                                        <i class="material-symbols-rounded me-2 icon-size-sm">account_circle</i>
                                        User Account Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if (!$id)
                                        <div class="col-md-6 password-field">
                                            <x-input name="password" type="password" title="Password"
                                                :isRequired="true" attr="minlength='8'"
                                                placeholder="Enter password (min 8 characters)" />
                                        </div>
                                        <div class="col-md-6 password-field">
                                            <x-input name="password_confirmation" type="password"
                                                title="Confirm Password" :isRequired="true"
                                                placeholder="Confirm your password" attr="minlength='8'" />
                                        </div>
                                        @endif
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="input-group input-group-outline mb-3">
                                                <select name="roles[]" class="form-control" multiple>
                                                    @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}"
                                                        {{ isset($student) && $student->user && $student->user->hasRole($role->name) ? 'selected' : ($role->name == 'student' ? 'selected' : '') }}>
                                                        {{ ucfirst($role->name) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple
                                                roles</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Parent Information -->
                            @if (!$id)
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i
                                                class="material-symbols-rounded me-2 icon-size-sm">family_restroom</i>
                                            Parent Information
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-light"
                                            id="addParentBtn" onclick="addParentForm()">
                                            <i class="material-symbols-rounded">add</i> Add Parent
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="parentContainer">
                                        <!-- Parent forms will be added here dynamically -->
                                    </div>
                                    <div class="text-center mt-3">
                                        <small class="text-muted">You can add multiple parents for this
                                            student</small>
                                    </div>
                                </div>
                            </div>
                            @else
                            <!-- Existing Parents for Edit Mode -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-gradient-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 d-flex align-items-center text-white">
                                            <i
                                                class="material-symbols-rounded me-2 icon-size-sm">family_restroom</i>
                                            Parent Information
                                        </h6>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-light"
                                                onclick="addParentForm()">
                                                <i class="material-symbols-rounded">add</i> Add New Parent
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-light"
                                                onclick="toggleParentSelector()">
                                                <i class="material-symbols-rounded">link</i> Link Existing Parent
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Existing Parent Details Display -->
                                    @if (isset($student) && $student->parents && $student->parents->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="text-primary mb-3 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2">people</i>
                                            Current Parents ({{ $student->parents->count() }})
                                        </h6>

                                        @foreach ($student->parents as $index => $parent)
                                        <div class="card border mb-3"
                                            id="existingParent{{ $parent->parent_id }}">
                                            <div class="card-header bg-light">
                                                <div
                                                    class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <span
                                                            class="badge bg-primary me-2">{{ $parent->parent_code }}</span>
                                                        {{ $parent->full_name }}
                                                        <small
                                                            class="text-muted">({{ ucfirst($parent->relationship_type) }})</small>
                                                    </h6>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="unlinkParent({{ $parent->parent_id }})">
                                                        <i class="material-symbols-rounded">link_off</i>
                                                        Unlink
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Name</small>
                                                        <p class="mb-0 fw-medium">{{ $parent->full_name }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small class="text-muted">Gender</small>
                                                        <p class="mb-0">
                                                            @if ($parent->gender == 'M')
                                                            Male
                                                            @elseif($parent->gender == 'F')
                                                            Female
                                                            @else
                                                            Other
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small class="text-muted">Birth Date</small>
                                                        <p class="mb-0">
                                                            {{ $parent->date_of_birth ? $parent->date_of_birth->format('M d, Y') : 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small class="text-muted">Relationship</small>
                                                        <p class="mb-0">
                                                            {{ ucfirst($parent->relationship_type) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Mobile Phone</small>
                                                        <p class="mb-0">
                                                            {{ $parent->mobile_phone ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-4">
                                                        <small class="text-muted">Email</small>
                                                        <p class="mb-0">{{ $parent->email ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Occupation</small>
                                                        <p class="mb-0">
                                                            {{ $parent->occupation ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">Workplace</small>
                                                        <p class="mb-0">
                                                            {{ $parent->workplace ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small class="text-muted">Emergency Contact</small>
                                                        <p class="mb-0">
                                                            @if ($parent->is_emergency_contact)
                                                            <span class="badge bg-success">Yes</span>
                                                            @else
                                                            <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($parent->address_line1)
                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-12">
                                                        <small class="text-muted">Address</small>
                                                        <p class="mb-0">{{ $parent->address_line1 }}
                                                        </p>
                                                    </div>
                                                </div>
                                                @endif
                                                <!-- Hidden input to maintain parent relationship -->
                                                <input type="hidden" name="existing_parents[]"
                                                    value="{{ $parent->parent_id }}">
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    <!-- Parent Selector (Hidden by default) -->
                                    <div id="parentSelector" style="display: none;">
                                        <div class="card border-dashed">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Link Existing Parent</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="input-group input-group-outline mb-3">
                                                            <select name="parents[]" class="form-control"
                                                                multiple>
                                                                @foreach ($parents as $parent)
                                                                <option value="{{ $parent->parent_id }}"
                                                                    {{ isset($student) && $student->parents->contains('parent_id', $parent->parent_id) ? 'selected' : '' }}>
                                                                    {{ $parent->full_name }}
                                                                    ({{ $parent->parent_code }})
                                                                    -
                                                                    {{ ucfirst($parent->relationship_type) }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <small class="form-text text-muted">Hold Ctrl/Cmd to select
                                                            multiple parents</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Parent Forms Container -->
                                    <div id="parentContainer">
                                        <!-- New parent forms will be added here dynamically -->
                                    </div>

                                    <div class="text-center mt-3">
                                        <small class="text-muted">You can add new parents or link existing parents
                                            to this student</small>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Submit Buttons -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-12 text-end">
                                        <a href="{{ route('admin.management.students.index') }}"
                                            class="btn btn-outline-secondary me-2">
                                            <i class="material-symbols-rounded me-1">cancel</i>Cancel
                                        </a>
                                        <button type="button" class="btn btn-outline-warning me-2"
                                            onclick="document.getElementById('studentForm').reset(); resetForm();">
                                            <i class="material-symbols-rounded me-1">restart_alt</i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="material-symbols-rounded me-1">save</i>
                                            {{ $id ? 'Update' : 'Create' }} Student
                                        </button>
                                    </div>
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

@section('js')
@php
$classesForJs = $classes->map(function($c) {
return ['id' => $c->id, 'class_name' => $c->class_name, 'grade_level' => $c->grade_level, 'section' => $c->section];
});
$selectedClassId = old('class_id', $student->class_id ?? null);
$selectedSubjectsArr = old('subjects', isset($student) ? $student->subjects->pluck('id')->toArray() : []);
@endphp
<script>
    window.isEditMode = @json($id ? true : false);
    window.generateCodeUrl = @json(route('admin.management.students.generate-code'));
    window.subjectsByGradeUrl = @json(route('admin.management.students.subjects-by-grade'));
    window.classesByGradeUrl = @json(route('admin.management.students.classes-by-grade'));
    window.selectedSubjects = @json($selectedSubjectsArr);
    window.allClasses = @json($classesForJs);
    window.selectedClassId = @json($selectedClassId);
</script>
@vite('resources/js/admin/student-form.js')
@endsection