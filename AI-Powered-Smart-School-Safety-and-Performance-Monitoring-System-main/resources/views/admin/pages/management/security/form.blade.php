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
                                        <i class="material-symbols-rounded me-2">security</i>
                                        {{ isset($security) ? 'Edit Security Staff' : 'Create Security Staff' }}
                                    </h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-secondary mb-0" href="{{ route('admin.management.security.index') }}">
                                        <i class="material-symbols-rounded text-sm me-1">arrow_back</i>
                                        Back to Security Staff
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.management.security.enroll') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @if (isset($security))
                                    <input type="hidden" name="id" value="{{ $security->security_id }}">
                                @endif

                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">person</i>
                                        Personal Information
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="security_code" title="Security Code" :isRequired="true"
                                                :value="old('security_code', $security->security_code ?? '')" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="employee_id" title="Employee ID" :value="old('employee_id', $security->employee_id ?? '')" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-input name="first_name" title="First Name" :isRequired="true"
                                                :value="old('first_name', $security->first_name ?? '')" />
                                        </div>

                                        <div class="col-md-4">
                                            <x-input name="middle_name" title="Middle Name" :value="old('middle_name', $security->middle_name ?? '')" />
                                        </div>

                                        <div class="col-md-4">
                                            <x-input name="last_name" title="Last Name" :isRequired="true"
                                                :value="old('last_name', $security->last_name ?? '')" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="date_of_birth" type="date" title="Date of Birth"
                                                :value="old(
                                                    'date_of_birth',
                                                    isset($security) && $security->date_of_birth
                                                        ? $security->date_of_birth->format('Y-m-d')
                                                        : '',
                                                )" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="gender" type="select" title="Gender"
                                                placeholder="Select Gender" :options="['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']" :value="old('gender', $security->gender ?? '')" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-outline">
                                                <label class="form-label">Nationality</label>
                                                <input type="text" name="nationality" class="form-control"
                                                    value="{{ old('nationality', $security->nationality ?? '') }}">
                                            </div>
                                            @error('nationality')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="email" type="email" title="Email" :value="old('email', $security->email ?? '')"
                                                placeholder="Enter email address" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Employment Information Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">work</i>
                                        Employment Information
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="joining_date" type="date" title="Joining Date"
                                                :value="old(
                                                    'joining_date',
                                                    isset($security) && $security->joining_date
                                                        ? $security->joining_date->format('Y-m-d')
                                                        : '',
                                                )" placeholder="Select joining date" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="position" type="select" title="Position" :value="old('position', $security->position ?? '')"
                                                placeholder="Select Position" :options="[
                                                    'Security Guard' => 'Security Guard',
                                                    'Security Supervisor' => 'Security Supervisor',
                                                    'Security Manager' => 'Security Manager',
                                                    'Gate Keeper' => 'Gate Keeper',
                                                    'Campus Security' => 'Campus Security',
                                                ]" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="shift" type="select" title="Shift" :value="old('shift', $security->shift ?? '')"
                                                placeholder="Select Shift" :options="[
                                                    'Morning' => 'Morning (6:00 AM - 2:00 PM)',
                                                    'Afternoon' => 'Afternoon (2:00 PM - 10:00 PM)',
                                                    'Night' => 'Night (10:00 PM - 6:00 AM)',
                                                    'Rotating' => 'Rotating Shifts',
                                                ]" />
                                        </div>
                                        <div class="col-md-6">
                                            <x-input name="is_active" type="select" title="Status" :value="old('is_active', $security->is_active ?? '1')"
                                                placeholder="Select Status" :options="[
                                                    '1' => 'Active',
                                                    '0' => 'Inactive',
                                                ]" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">contact_phone</i>
                                        Contact Information
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="mobile_phone" type="text" title="Mobile Phone"
                                                :value="old('mobile_phone', $security->mobile_phone ?? '')" placeholder="Enter mobile phone number" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="home_phone" type="text" title="Home Phone"
                                                :value="old('home_phone', $security->home_phone ?? '')" placeholder="Enter home phone number" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-input name="address_line1" type="text" title="Address Line 1"
                                                :value="old('address_line1', $security->address_line1 ?? '')" placeholder="Enter address line 1" />
                                        </div>

                                        <div class="col-md-6">
                                            <x-input name="address_line2" type="text" title="Address Line 2"
                                                :value="old('address_line2', $security->address_line2 ?? '')" placeholder="Enter address line 2 (optional)" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-input name="city" type="text" title="City" :value="old('city', $security->city ?? '')"
                                                placeholder="Enter city" />
                                        </div>

                                        <div class="col-md-3">
                                            <x-input name="state" type="text" title="State" :value="old('state', $security->state ?? '')"
                                                placeholder="Enter state" />
                                        </div>

                                        <div class="col-md-3">
                                            <x-input name="postal_code" type="text" title="Postal Code"
                                                :value="old('postal_code', $security->postal_code ?? '')" placeholder="Enter postal code" />
                                        </div>

                                        <div class="col-md-3">
                                            <x-input name="country" type="text" title="Country" :value="old('country', $security->country ?? '')"
                                                placeholder="Enter country" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Upload Section -->
                                <div class="form-section">
                                    <h6>
                                        <i class="material-symbols-rounded me-2" style="color: #5e72e4;">photo_camera</i>
                                        Photo
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="photo-upload">
                                                <input type="file" name="photo" class="form-control"
                                                    accept="image/*" onchange="previewPhoto(this)">
                                                <div class="mt-2">
                                                    <i class="material-symbols-rounded"
                                                        style="font-size: 2rem; color: #6c757d;">upload</i>
                                                    <p class="text-muted mb-0">Click to upload photo</p>
                                                    <small class="text-muted">Max file size: 2MB</small>
                                                </div>
                                            </div>
                                            @error('photo')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <div id="photoPreview">
                                                @if (isset($security) && $security->photo_path)
                                                    <img src="{{ asset('storage/' . $security->photo_path) }}"
                                                        alt="Current Photo" class="photo-preview">
                                                    <p class="text-muted mt-2">Current Photo</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <a href="{{ route('admin.management.security.index') }}"
                                            class="btn btn-secondary me-2">
                                            <i class="material-symbols-rounded me-1">cancel</i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="material-symbols-rounded me-1">
                                                {{ isset($security) ? 'update' : 'add' }}
                                            </i>
                                            {{ isset($security) ? 'Update Security Staff' : 'Create Security Staff' }}
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

@section('js')
    <script>
        function previewPhoto(input) {
            const preview = document.getElementById('photoPreview');
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Photo Preview" class="photo-preview">
                        <p class="text-muted mt-2">Photo Preview</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

        // Auto-generate security code if empty
        document.addEventListener('DOMContentLoaded', function() {
            const securityCodeInput = document.querySelector('input[name="security_code"]');
            if (!securityCodeInput.value) {
                generateSecurityCode();
            }
        });

        function generateSecurityCode() {
            const year = new Date().getFullYear();
            const random = Math.floor(Math.random() * 9999) + 1;
            const securityCode = `SEC${year}${random.toString().padStart(4, '0')}`;
            document.querySelector('input[name="security_code"]').value = securityCode;
        }
    </script>
@endsection
