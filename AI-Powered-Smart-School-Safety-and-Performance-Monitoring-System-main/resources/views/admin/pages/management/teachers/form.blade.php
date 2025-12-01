@extends('admin.layouts.app')

@section('title', pageTitle())

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
                                        href="{{ route('admin.management.teachers.index') }}">
                                        <i class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>Back
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.management.teachers.enroll') }}" method="POST" id="teacherForm"
                                enctype="multipart/form-data">
                                @csrf
                                @if ($id)
                                    <input type="hidden" name="id" value="{{ $id }}">
                                @endif

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">person</i>
                                            Personal Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="avatar avatar-xl position-relative mb-3">
                                                        @if (isset($teacher) && $teacher->photo_path)
                                                            <img id="profilePreview"
                                                                src="{{ asset('storage/' . $teacher->photo_path) }}"
                                                                alt="Teacher Photo" class="w-100 rounded-circle shadow-sm">
                                                        @else
                                                            <div id="profilePreview"
                                                                class="w-100 rounded-circle shadow-sm bg-gradient-primary d-flex align-items-center justify-content-center"
                                                                style="height: 70px; width: 70px;">
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
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <x-input name="teacher_code" title="Teacher Code" :isRequired="true"
                                                            attr="maxlength='50'" :value="old(
                                                                'teacher_code',
                                                                $teacher->teacher_code ?? '',
                                                            )" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-input name="first_name" title="First Name" :isRequired="true"
                                                            attr="maxlength='100'" :value="old('first_name', $teacher->first_name ?? '')" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-input name="last_name" title="Last Name" :isRequired="true"
                                                            attr="maxlength='100'" :value="old('last_name', $teacher->last_name ?? '')" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-input name="date_of_birth" type="date" title="Date of Birth"
                                                    :value="old('date_of_birth', $teacher->date_of_birth ?? '')" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="gender" type="select" title="Gender"
                                                    placeholder="Select Gender" :options="[
                                                        'Male' => 'Male',
                                                        'Female' => 'Female',
                                                        'Other' => 'Other',
                                                    ]" :value="old('gender', $teacher->gender ?? '')" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="nic_number" title="NIC Number" attr="maxlength='20'"
                                                    :value="old('nic_number', $teacher->nic_number ?? '')" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-input name="phone" title="Phone Number" attr="maxlength='15'"
                                                    :value="old('phone', $teacher->phone ?? '')" />
                                            </div>
                                            <div class="col-md-6">
                                                <x-input name="address" type="textarea" title="Address" attr="rows='3'"
                                                    :value="old('address', $teacher->address ?? '')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">school</i>
                                            Professional Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-input name="specialization" title="Specialization" attr="maxlength='255'"
                                                    :value="old('specialization', $teacher->specialization ?? '')" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="experience_years" type="number" title="Experience (Years)"
                                                    attr="min='0' max='50'" :value="old(
                                                        'experience_years',
                                                        $teacher->experience_years ?? '',
                                                    )" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="hire_date" type="date" title="Hire Date"
                                                    :value="old('hire_date', $teacher->hire_date ?? '')" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-input name="teaching_level" type="select" id="teaching_level"
                                                    title="Teaching Level" :isRequired="true"
                                                    placeholder="Select Teaching Level" :options="[
                                                        'Primary' => 'Primary Education (Grades 1-5)',
                                                        'Secondary' => 'Secondary Education (Grades 6-11)',
                                                        'A/L-Arts' => 'A/L - Arts Stream',
                                                        'A/L-Commerce' => 'A/L - Commerce Stream',
                                                        'A/L-Science' => 'A/L - Science Stream',
                                                        'A/L-Technology' => 'A/L - Technology Stream',
                                                    ]"
                                                    :value="old('teaching_level', $teacher->teaching_level ?? '')" />
                                                <small class="text-muted">Select the education level you teach</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <x-input name="qualifications" type="textarea" title="Qualifications"
                                                    placeholder="Enter qualifications and certifications" attr="rows='3'"
                                                    :value="old('qualifications', $teacher->qualifications ?? '')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">subject</i>
                                            Subject Assignment
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="subjectSelectionInfo" class="alert alert-info mb-3"
                                            style="display: none;">
                                            <i class="material-symbols-rounded me-2">info</i>
                                            <span id="teachingLevelText">Please select a teaching level to view available
                                                subjects</span>
                                        </div>

                                        <div id="subjectsList" class="row">
                                            <div class="col-12 text-center text-muted py-4">
                                                <i class="material-symbols-rounded" style="font-size: 48px;">school</i>
                                                <p class="mt-2">Select a teaching level above to see available subjects
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">account_circle</i>
                                            User Account Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-input name="email" type="email" title="Email Address"
                                                    :value="old('email', $teacher->user->email ?? '')" />
                                            </div>
                                            @if (!$id)
                                                <div class="col-md-6">
                                                    <x-input name="password" type="password" title="Password"
                                                        attr="minlength='8'"
                                                        placeholder="Leave empty to auto-generate password" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-input name="is_active" type="select" title="Status"
                                                    :isRequired="true" :options="['1' => 'Active', '0' => 'Inactive']" :value="old(
                                                        'is_active',
                                                        isset($teacher) ? ($teacher->is_active ? '1' : '0') : '1',
                                                    )" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="col-12 text-end">
                                            <a href="{{ route('admin.management.teachers.index') }}"
                                                class="btn btn-outline-secondary">Cancel</a>
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="document.getElementById('teacherForm').reset()">Reset</button>
                                            <button type="submit" class="btn btn-success">Submit</button>
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
    <script>
        // Store previously selected subjects for edit mode
        const previouslySelectedSubjects = @json(old('subjects', $teacherSubjects ?? []));

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate teacher code if empty
            const teacherCodeInput = document.querySelector('input[name="teacher_code"]');
            if (!teacherCodeInput.value) {
                const currentYear = new Date().getFullYear();
                const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                teacherCodeInput.value = `TEA${currentYear}${randomNum}`;
            }

            // Profile image preview
            const profileInput = document.getElementById('profileImage');
            const profilePreview = document.getElementById('profilePreview');

            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
                        e.target.value = '';
                        return;
                    }

                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Image size should not exceed 2MB');
                        e.target.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.innerHTML =
                            `<img src="${e.target.result}" alt="Profile Preview" class="w-100 border-radius-lg shadow-sm">`;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Teaching level change handler
            const teachingLevelSelect = document.getElementById('teaching_level');
            if (teachingLevelSelect) {
                teachingLevelSelect.addEventListener('change', function() {
                    loadSubjectsByTeachingLevel(this.value);
                });

                // Load subjects on page load if teaching level is already selected
                if (teachingLevelSelect.value) {
                    loadSubjectsByTeachingLevel(teachingLevelSelect.value);
                }
            }
        });

        // Function to load subjects based on teaching level
        function loadSubjectsByTeachingLevel(teachingLevel) {
            const subjectsList = document.getElementById('subjectsList');
            const infoBox = document.getElementById('subjectSelectionInfo');
            const levelText = document.getElementById('teachingLevelText');

            if (!teachingLevel) {
                subjectsList.innerHTML = `
                    <div class="col-12 text-center text-muted py-4">
                        <i class="material-symbols-rounded" style="font-size: 48px;">school</i>
                        <p class="mt-2">Select a teaching level above to see available subjects</p>
                    </div>
                `;
                infoBox.style.display = 'none';
                return;
            }

            // Show loading state
            subjectsList.innerHTML = `
                <div class="col-12 text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects...</p>
                </div>
            `;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            // Fetch subjects from server
            fetch(`/admin/management/teachers/subjects-by-level?teaching_level=${encodeURIComponent(teachingLevel)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.subjects) {
                        infoBox.style.display = 'block';
                        levelText.textContent = data.message || `Showing subjects for ${teachingLevel}`;

                        if (data.subjects.length === 0) {
                            subjectsList.innerHTML = `
                            <div class="col-12 text-center text-warning py-4">
                                <i class="material-symbols-rounded" style="font-size: 48px;">warning</i>
                                <p class="mt-2">No subjects available for this teaching level</p>
                            </div>
                        `;
                        } else {
                            renderSubjects(data.subjects);
                        }
                    } else {
                        subjectsList.innerHTML = `
                        <div class="col-12 text-center text-danger py-4">
                            <i class="material-symbols-rounded" style="font-size: 48px;">error</i>
                            <p class="mt-2">Error loading subjects: ${data.message || 'Unknown error'}</p>
                        </div>
                    `;
                        infoBox.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                    subjectsList.innerHTML = `
                    <div class="col-12 text-center text-danger py-4">
                        <i class="material-symbols-rounded" style="font-size: 48px;">error</i>
                        <p class="mt-2">Failed to load subjects. Please check your connection.</p>
                    </div>
                `;
                    infoBox.style.display = 'none';
                });
        }

        // Function to render subjects
        function renderSubjects(subjects) {
            const subjectsList = document.getElementById('subjectsList');
            subjectsList.innerHTML = '';

            subjects.forEach(subject => {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-3';

                const isChecked = previouslySelectedSubjects.includes(subject.id);

                col.innerHTML = `
                    <div class="form-check subject-check-box p-3 border rounded ${isChecked ? 'border-primary bg-light' : ''}">
                        <input class="form-check-input" type="checkbox" name="subjects[]"
                            value="${subject.id}"
                            id="subject_${subject.id}"
                            ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label w-100 cursor-pointer" for="subject_${subject.id}">
                            <strong>${subject.subject_name}</strong>
                            <br><small class="text-secondary">${subject.subject_code}</small>
                            ${subject.category ? `<br><span class="badge bg-info mt-1">${subject.category}</span>` : ''}
                            ${subject.stream ? `<br><span class="badge bg-success mt-1">${subject.stream}</span>` : ''}
                        </label>
                    </div>
                `;

                // Add click handler to highlight selected subjects
                const checkbox = col.querySelector('input[type="checkbox"]');
                const checkBox = col.querySelector('.subject-check-box');

                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkBox.classList.add('border-primary', 'bg-light');
                    } else {
                        checkBox.classList.remove('border-primary', 'bg-light');
                    }
                });

                subjectsList.appendChild(col);
            });
        }
    </script>

    <style>
        .subject-check-box {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .subject-check-box:hover {
            border-color: #5e72e4 !important;
            background-color: rgba(94, 114, 228, 0.05);
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endsection
