@extends('admin.layouts.app')

@section('title', isset($security) ? 'Edit Security Staff' : 'Add New Security Staff')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">{{ isset($security) ? 'edit' : 'add' }}</i>
                            {{ isset($security) ? 'Edit Security Staff' : 'Add New Security Staff' }}
                        </h3>
                        <a href="{{ route('admin.management.security.index') }}" class="btn btn-secondary">
                            <i class="material-icons-outlined me-1">arrow_back</i>
                            Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.management.security.enroll') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @if (isset($security))
                                <input type="hidden" name="id" value="{{ $security->id }}">
                            @endif

                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">person</i>
                                                Personal Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('first_name') is-invalid @enderror"
                                                    id="first_name" name="first_name"
                                                    value="{{ old('first_name', $security->first_name ?? '') }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('last_name') is-invalid @enderror"
                                                    id="last_name" name="last_name"
                                                    value="{{ old('last_name', $security->last_name ?? '') }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="employee_id" class="form-label">Employee ID <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('employee_id') is-invalid @enderror"
                                                    id="employee_id" name="employee_id"
                                                    value="{{ old('employee_id', $security->employee_id ?? '') }}"
                                                    placeholder="e.g., SEC001, GUARD001" required>
                                                @error('employee_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address <span
                                                        class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" value="{{ old('email', $security->email ?? '') }}"
                                                    required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number <span
                                                        class="text-danger">*</span></label>
                                                <input type="tel"
                                                    class="form-control @error('phone') is-invalid @enderror" id="phone"
                                                    name="phone" value="{{ old('phone', $security->phone ?? '') }}"
                                                    required>
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                                <input type="date"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    id="date_of_birth" name="date_of_birth"
                                                    value="{{ old('date_of_birth', $security->date_of_birth ?? '') }}">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="gender" class="form-label">Gender</label>
                                                <select class="form-select @error('gender') is-invalid @enderror"
                                                    id="gender" name="gender">
                                                    <option value="">Select Gender</option>
                                                    <option value="male"
                                                        {{ old('gender', $security->gender ?? '') == 'male' ? 'selected' : '' }}>
                                                        Male</option>
                                                    <option value="female"
                                                        {{ old('gender', $security->gender ?? '') == 'female' ? 'selected' : '' }}>
                                                        Female</option>
                                                    <option value="other"
                                                        {{ old('gender', $security->gender ?? '') == 'other' ? 'selected' : '' }}>
                                                        Other</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employment Information -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">work</i>
                                                Employment Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="hire_date" class="form-label">Hire Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('hire_date') is-invalid @enderror"
                                                    id="hire_date" name="hire_date"
                                                    value="{{ old('hire_date', $security->hire_date ?? date('Y-m-d')) }}"
                                                    required>
                                                @error('hire_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="position" class="form-label">Position <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('position') is-invalid @enderror"
                                                    id="position" name="position" required>
                                                    <option value="">Select Position</option>
                                                    <option value="security_guard"
                                                        {{ old('position', $security->position ?? '') == 'security_guard' ? 'selected' : '' }}>
                                                        Security Guard</option>
                                                    <option value="head_security"
                                                        {{ old('position', $security->position ?? '') == 'head_security' ? 'selected' : '' }}>
                                                        Head of Security</option>
                                                    <option value="gate_keeper"
                                                        {{ old('position', $security->position ?? '') == 'gate_keeper' ? 'selected' : '' }}>
                                                        Gate Keeper</option>
                                                    <option value="patrol_officer"
                                                        {{ old('position', $security->position ?? '') == 'patrol_officer' ? 'selected' : '' }}>
                                                        Patrol Officer</option>
                                                    <option value="cctv_operator"
                                                        {{ old('position', $security->position ?? '') == 'cctv_operator' ? 'selected' : '' }}>
                                                        CCTV Operator</option>
                                                </select>
                                                @error('position')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="shift" class="form-label">Shift <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select @error('shift') is-invalid @enderror"
                                                    id="shift" name="shift" required>
                                                    <option value="">Select Shift</option>
                                                    <option value="morning"
                                                        {{ old('shift', $security->shift ?? '') == 'morning' ? 'selected' : '' }}>
                                                        Morning (6:00 AM - 2:00 PM)</option>
                                                    <option value="afternoon"
                                                        {{ old('shift', $security->shift ?? '') == 'afternoon' ? 'selected' : '' }}>
                                                        Afternoon (2:00 PM - 10:00 PM)</option>
                                                    <option value="night"
                                                        {{ old('shift', $security->shift ?? '') == 'night' ? 'selected' : '' }}>
                                                        Night (10:00 PM - 6:00 AM)</option>
                                                    <option value="rotating"
                                                        {{ old('shift', $security->shift ?? '') == 'rotating' ? 'selected' : '' }}>
                                                        Rotating Shifts</option>
                                                </select>
                                                @error('shift')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="area_assignment" class="form-label">Area Assignment</label>
                                                <select class="form-select @error('area_assignment') is-invalid @enderror"
                                                    id="area_assignment" name="area_assignment">
                                                    <option value="">Select Area</option>
                                                    <option value="main_gate"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'main_gate' ? 'selected' : '' }}>
                                                        Main Gate</option>
                                                    <option value="back_gate"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'back_gate' ? 'selected' : '' }}>
                                                        Back Gate</option>
                                                    <option value="building_entrance"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'building_entrance' ? 'selected' : '' }}>
                                                        Building Entrance</option>
                                                    <option value="playground"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'playground' ? 'selected' : '' }}>
                                                        Playground</option>
                                                    <option value="parking_area"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'parking_area' ? 'selected' : '' }}>
                                                        Parking Area</option>
                                                    <option value="roving_patrol"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'roving_patrol' ? 'selected' : '' }}>
                                                        Roving Patrol</option>
                                                    <option value="control_room"
                                                        {{ old('area_assignment', $security->area_assignment ?? '') == 'control_room' ? 'selected' : '' }}>
                                                        Control Room</option>
                                                </select>
                                                @error('area_assignment')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="salary" class="form-label">Monthly Salary</label>
                                                <input type="number"
                                                    class="form-control @error('salary') is-invalid @enderror"
                                                    id="salary" name="salary" min="0" step="0.01"
                                                    value="{{ old('salary', $security->salary ?? '') }}"
                                                    placeholder="e.g., 25000.00">
                                                @error('salary')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="status" class="form-label">Employment Status</label>
                                                <select class="form-select @error('status') is-invalid @enderror"
                                                    id="status" name="status">
                                                    <option value="active"
                                                        {{ old('status', $security->status ?? 'active') == 'active' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="inactive"
                                                        {{ old('status', $security->status ?? '') == 'inactive' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                    <option value="on_leave"
                                                        {{ old('status', $security->status ?? '') == 'on_leave' ? 'selected' : '' }}>
                                                        On Leave</option>
                                                    <option value="terminated"
                                                        {{ old('status', $security->status ?? '') == 'terminated' ? 'selected' : '' }}>
                                                        Terminated</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact & Emergency Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">location_on</i>
                                                Address Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">Home Address</label>
                                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                                    placeholder="Enter complete home address">{{ old('address', $security->address ?? '') }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="emergency_contact_name" class="form-label">Emergency Contact
                                                    Name</label>
                                                <input type="text"
                                                    class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                                    id="emergency_contact_name" name="emergency_contact_name"
                                                    value="{{ old('emergency_contact_name', $security->emergency_contact_name ?? '') }}">
                                                @error('emergency_contact_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="emergency_contact_phone" class="form-label">Emergency Contact
                                                    Phone</label>
                                                <input type="tel"
                                                    class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                                    id="emergency_contact_phone" name="emergency_contact_phone"
                                                    value="{{ old('emergency_contact_phone', $security->emergency_contact_phone ?? '') }}">
                                                @error('emergency_contact_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="emergency_contact_relationship"
                                                    class="form-label">Relationship</label>
                                                <select
                                                    class="form-select @error('emergency_contact_relationship') is-invalid @enderror"
                                                    id="emergency_contact_relationship"
                                                    name="emergency_contact_relationship">
                                                    <option value="">Select Relationship</option>
                                                    <option value="spouse"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'spouse' ? 'selected' : '' }}>
                                                        Spouse</option>
                                                    <option value="parent"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'parent' ? 'selected' : '' }}>
                                                        Parent</option>
                                                    <option value="child"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'child' ? 'selected' : '' }}>
                                                        Child</option>
                                                    <option value="sibling"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'sibling' ? 'selected' : '' }}>
                                                        Sibling</option>
                                                    <option value="friend"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'friend' ? 'selected' : '' }}>
                                                        Friend</option>
                                                    <option value="other"
                                                        {{ old('emergency_contact_relationship', $security->emergency_contact_relationship ?? '') == 'other' ? 'selected' : '' }}>
                                                        Other</option>
                                                </select>
                                                @error('emergency_contact_relationship')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">description</i>
                                                Additional Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="national_id" class="form-label">National ID/Passport
                                                    Number</label>
                                                <input type="text"
                                                    class="form-control @error('national_id') is-invalid @enderror"
                                                    id="national_id" name="national_id"
                                                    value="{{ old('national_id', $security->national_id ?? '') }}">
                                                @error('national_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="certifications" class="form-label">Security
                                                    Certifications</label>
                                                <textarea class="form-control @error('certifications') is-invalid @enderror" id="certifications"
                                                    name="certifications" rows="2" placeholder="List any security-related certifications">{{ old('certifications', $security->certifications ?? '') }}</textarea>
                                                @error('certifications')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="notes" class="form-label">Additional Notes</label>
                                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                                    placeholder="Any additional information">{{ old('notes', $security->notes ?? '') }}</textarea>
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                                <input type="file"
                                                    class="form-control @error('profile_photo') is-invalid @enderror"
                                                    id="profile_photo" name="profile_photo" accept="image/*">
                                                @error('profile_photo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if (isset($security) && $security->profile_photo)
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . $security->profile_photo) }}"
                                                            alt="Current Photo" class="img-thumbnail"
                                                            style="max-height: 100px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            @if (!isset($security))
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="material-icons-outlined me-2">account_circle</i>
                                                    Account Information
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="password" class="form-label">Password <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="password"
                                                                class="form-control @error('password') is-invalid @enderror"
                                                                id="password" name="password" required>
                                                            @error('password')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="password_confirmation" class="form-label">Confirm
                                                                Password <span class="text-danger">*</span></label>
                                                            <input type="password" class="form-control"
                                                                id="password_confirmation" name="password_confirmation"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-end">
                                            <a href="{{ route('admin.management.security.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="material-icons-outlined me-1">cancel</i>
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="material-icons-outlined me-1">save</i>
                                                {{ isset($security) ? 'Update Security Staff' : 'Create Security Staff' }}
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
                // Auto-generate employee ID
                $('#first_name, #last_name').on('blur', function() {
                    const firstName = $('#first_name').val().trim();
                    const lastName = $('#last_name').val().trim();
                    const currentId = $('#employee_id').val();

                    if (firstName && lastName && !currentId) {
                        const initials = firstName.charAt(0) + lastName.charAt(0);
                        const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0');
                        $('#employee_id').val('SEC' + initials.toUpperCase() + randomNum);
                    }
                });

                // Phone number formatting
                $('#phone, #emergency_contact_phone').on('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value.length >= 10) {
                        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                    }
                    this.value = value;
                });

                // Email validation
                $('#email').on('blur', function() {
                    const email = $(this).val();
                    if (email && !isValidEmail(email)) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').text('Please enter a valid email address.');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                function isValidEmail(email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(email);
                }

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

                    // Check password confirmation
                    const password = $('#password').val();
                    const passwordConfirm = $('#password_confirmation').val();
                    if (password && password !== passwordConfirm) {
                        $('#password_confirmation').addClass('is-invalid');
                        notificationManager.error('Validation Error', 'Passwords do not match');
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

            .img-thumbnail {
                border: 2px solid #dee2e6;
                border-radius: 8px;
            }

            #employee_id {
                font-family: 'Courier New', monospace;
                text-transform: uppercase;
            }
        </style>
    @endpush
@endsection
