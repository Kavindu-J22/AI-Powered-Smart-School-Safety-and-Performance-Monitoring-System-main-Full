@extends('admin.layouts.app')

@section('title', 'Edit Profile')

@section('css')
    @vite(['resources/css/admin/profile.css', 'resources/css/admin/forms.css', 'resources/css/components/utilities.css'])
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
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">
                                        <i class="material-symbols-rounded me-2">edit</i>
                                        Edit Profile
                                    </h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-secondary mb-0" href="{{ route('admin.profile.index') }}">
                                        <i class="material-symbols-rounded text-sm me-1">arrow_back</i>
                                        Back to Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data"
                                id="profileForm">
                                @csrf
                                @method('PUT')

                                <!-- Profile Image Section -->
                                <div class="row mb-4">
                                    <div class="col-12 text-center">
                                        <div class="profile-avatar-wrapper">
                                            <div class="profile-avatar">
                                                @if ($user->profile_image)
                                                    <img src="{{ Storage::url($user->profile_image) }}"
                                                        alt="Profile Picture" id="profileImage" class="profile-img">
                                                @else
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=200&background=667eea&color=ffffff&bold=true"
                                                        alt="Profile Picture" id="profileImage" class="profile-img">
                                                @endif
                                                <div class="avatar-overlay"
                                                    onclick="document.getElementById('avatarUpload').click()">
                                                    <i class="material-symbols-rounded">photo_camera</i>
                                                    <span>Change Photo</span>
                                                </div>
                                            </div>
                                            <div class="upload-progress" id="uploadProgress" style="display: none;">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" id="progressFill"></div>
                                                </div>
                                                <p class="progress-text" id="progressText">Uploading...</p>
                                            </div>
                                            <input type="file" id="avatarUpload" class="d-none"
                                                accept="image/jpeg,image/png,image/jpg">
                                            <p class="upload-hint mt-2">
                                                <i class="material-symbols-rounded text-xs">info</i>
                                                Click on image to upload. Max 2MB (JPG, PNG)
                                            </p>
                                            @if ($user->profile_image)
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                    onclick="deleteProfileImage()">
                                                    <i class="material-symbols-rounded text-sm">delete</i>
                                                    Remove Photo
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <hr class="horizontal dark my-4">

                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="mb-3">
                                            <i class="material-symbols-rounded me-2">person</i>
                                            Personal Information
                                        </h6>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="mb-3">
                                            <i class="material-symbols-rounded me-2">person</i>
                                            Personal Information
                                        </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Email Address *</label>
                                            <input type="email" name="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" name="phone"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                value="{{ old('phone', $user->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth"
                                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                                value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $user->address) }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Bio</label>
                                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="4"
                                                placeholder="Tell us something about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                            @error('bio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn bg-gradient-primary me-2">
                                            <i class="material-symbols-rounded text-sm me-1">save</i>
                                            Save Changes
                                        </button>
                                        <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-secondary">
                                            <i class="material-symbols-rounded text-sm me-1">cancel</i>
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
    </main>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle avatar upload
            const avatarUpload = document.getElementById('avatarUpload');
            if (avatarUpload) {
                avatarUpload.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        uploadProfileImage(e.target.files[0]);
                    }
                });
            }
        });

        function uploadProfileImage(file) {
            // Validate file size (2MB max)
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            if (file.size > maxSize) {
                showNotification('File size must be less than 2MB', 'error');
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                showNotification('Please upload a valid image file (JPG or PNG)', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('profile_image', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show progress
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const avatarOverlay = document.querySelector('.avatar-overlay');

            if (uploadProgress) {
                uploadProgress.style.display = 'block';
                progressFill.style.width = '0%';
                progressText.textContent = 'Uploading...';
            }

            if (avatarOverlay) {
                avatarOverlay.style.display = 'none';
            }

            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 10;
                if (progress <= 90) {
                    progressFill.style.width = progress + '%';
                }
            }, 100);

            fetch('{{ route('admin.profile.upload-image') }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    clearInterval(progressInterval);
                    progressFill.style.width = '100%';

                    if (data.success) {
                        progressText.textContent = 'Upload complete!';
                        document.getElementById('profileImage').src = data.image_url + '?t=' + new Date().getTime();
                        showNotification('Profile image updated successfully!', 'success');

                        // Hide progress after a short delay
                        setTimeout(() => {
                            if (uploadProgress) uploadProgress.style.display = 'none';
                            if (avatarOverlay) avatarOverlay.style.display = 'flex';
                        }, 1500);
                    } else {
                        progressText.textContent = 'Upload failed';
                        showNotification('Failed to upload image: ' + (data.message || 'Unknown error'), 'error');
                        setTimeout(() => {
                            if (uploadProgress) uploadProgress.style.display = 'none';
                            if (avatarOverlay) avatarOverlay.style.display = 'flex';
                        }, 2000);
                    }
                })
                .catch(error => {
                    clearInterval(progressInterval);
                    console.error('Error uploading image:', error);
                    progressText.textContent = 'Upload failed';
                    showNotification('An error occurred while uploading the image.', 'error');
                    setTimeout(() => {
                        if (uploadProgress) uploadProgress.style.display = 'none';
                        if (avatarOverlay) avatarOverlay.style.display = 'flex';
                    }, 2000);
                });
        }

        function deleteProfileImage() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                fetch('{{ route('admin.profile.delete-image') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('profileImage').src =
                                'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=200&background=667eea&color=ffffff&bold=true';
                            showNotification('Profile image removed successfully!', 'success');

                            // Hide delete button
                            const deleteBtn = document.querySelector('.btn-outline-danger');
                            if (deleteBtn) {
                                deleteBtn.style.display = 'none';
                            }
                        } else {
                            showNotification('Failed to remove profile image: ' + (data.message || 'Unknown error'),
                                'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while removing the image.', 'error');
                    });
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
@endsection
