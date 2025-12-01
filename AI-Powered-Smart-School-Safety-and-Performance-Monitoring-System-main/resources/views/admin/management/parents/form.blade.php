@extends('admin.layouts.app')

@section('title', isset($parent) ? 'Edit Parent' : 'Add New Parent')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">{{ isset($parent) ? 'edit' : 'add' }}</i>
                            {{ isset($parent) ? 'Edit Parent' : 'Add New Parent' }}
                        </h3>
                        <a href="{{ route('admin.management.parents.index') }}" class="btn btn-secondary">
                            <i class="material-icons-outlined me-1">arrow_back</i>
                            Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.management.parents.enroll') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @if (isset($parent))
                                <input type="hidden" name="id" value="{{ $parent->id }}">
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
                                                    value="{{ old('first_name', $parent->first_name ?? '') }}" required>
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
                                                    value="{{ old('last_name', $parent->last_name ?? '') }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address <span
                                                        class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" value="{{ old('email', $parent->email ?? '') }}"
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
                                                    name="phone" value="{{ old('phone', $parent->phone ?? '') }}"
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
                                                    value="{{ old('date_of_birth', $parent->date_of_birth ?? '') }}">
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
                                                        {{ old('gender', $parent->gender ?? '') == 'male' ? 'selected' : '' }}>
                                                        Male</option>
                                                    <option value="female"
                                                        {{ old('gender', $parent->gender ?? '') == 'female' ? 'selected' : '' }}>
                                                        Female</option>
                                                    <option value="other"
                                                        {{ old('gender', $parent->gender ?? '') == 'other' ? 'selected' : '' }}>
                                                        Other</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact & Address Information -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="material-icons-outlined me-2">location_on</i>
                                                Contact & Address Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">Home Address</label>
                                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                                    placeholder="Enter complete home address">{{ old('address', $parent->address ?? '') }}</textarea>
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
                                                    value="{{ old('emergency_contact_name', $parent->emergency_contact_name ?? '') }}">
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
                                                    value="{{ old('emergency_contact_phone', $parent->emergency_contact_phone ?? '') }}">
                                                @error('emergency_contact_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="occupation" class="form-label">Occupation</label>
                                                <input type="text"
                                                    class="form-control @error('occupation') is-invalid @enderror"
                                                    id="occupation" name="occupation"
                                                    value="{{ old('occupation', $parent->occupation ?? '') }}">
                                                @error('occupation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="workplace" class="form-label">Workplace</label>
                                                <input type="text"
                                                    class="form-control @error('workplace') is-invalid @enderror"
                                                    id="workplace" name="workplace"
                                                    value="{{ old('workplace', $parent->workplace ?? '') }}">
                                                @error('workplace')
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
                                                @if (isset($parent) && $parent->profile_photo)
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . $parent->profile_photo) }}"
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
                            @if (!isset($parent))
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
                                            <a href="{{ route('admin.management.parents.index') }}"
                                                class="btn btn-secondary me-2">
                                                <i class="material-icons-outlined me-1">cancel</i>
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="material-icons-outlined me-1">save</i>
                                                {{ isset($parent) ? 'Update Parent' : 'Create Parent' }}
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
        </style>
    @endpush
@endsection
