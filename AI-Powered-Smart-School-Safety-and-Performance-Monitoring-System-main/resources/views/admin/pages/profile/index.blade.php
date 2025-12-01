@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('css')
    @vite(['resources/css/admin/profile.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')

                    <!-- Profile Header Card -->
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">
                                        <i class="material-symbols-rounded me-2">person</i>
                                        My Profile
                                    </h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn bg-gradient-primary mb-0" href="{{ route('admin.profile.edit') }}">
                                        <i class="material-symbols-rounded text-sm me-1">edit</i>
                                        Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
                                    <div class="profile-avatar-wrapper">
                                        <div class="profile-avatar">
                                            @if ($user->profile_image)
                                                <img src="{{ Storage::url($user->profile_image) }}" alt="Profile Picture"
                                                    id="profileImage" class="profile-img">
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
                                        <input type="file" id="avatarUpload" class="avatar-upload d-none"
                                            accept="image/jpeg,image/png,image/jpg">
                                        <p class="upload-hint mt-2">
                                            <i class="material-symbols-rounded text-xs">info</i>
                                            Click on image to upload. Max 2MB (JPG, PNG)
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="profile-info">
                                        <h4 class="profile-name">{{ $user->name }}</h4>
                                        <p class="profile-role">
                                            @if ($user->getRoleNames()->isNotEmpty())
                                                <span
                                                    class="badge bg-gradient-primary">{{ $user->getRoleNames()->first() }}</span>
                                            @else
                                                <span class="badge bg-gradient-secondary">User</span>
                                            @endif
                                        </p>
                                        @if ($user->bio)
                                            <p class="profile-bio">{{ $user->bio }}</p>
                                        @else
                                            <p class="profile-bio text-muted">No bio available. Add one to tell others
                                                about yourself!</p>
                                        @endif

                                        <div class="profile-stats mt-4">
                                            <div class="row">
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <span class="stat-value" id="profileCompletion">0%</span>
                                                        <div class="stat-label">Profile Complete</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <span class="stat-value">{{ $user->login_count ?? 0 }}</span>
                                                        <div class="stat-label">Total Logins</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="stat-item">
                                                        <span
                                                            class="stat-value">{{ $user->created_at->format('M Y') }}</span>
                                                        <div class="stat-label">Member Since</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="profile-actions mt-4">
                                            <button class="btn btn-outline-primary" onclick="changePasswordModal()">
                                                <i class="material-symbols-rounded text-sm me-1">lock</i>
                                                Change Password
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon me-3">
                                            <i class="material-symbols-rounded">person</i>
                                        </div>
                                        <h6 class="mb-0">Personal Information</h6>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="detail-item">
                                        <span class="detail-label">Full Name</span>
                                        <span class="detail-value">{{ $user->name }}</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Email Address</span>
                                        <span class="detail-value">{{ $user->email }}</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Phone Number</span>
                                        <span class="detail-value">{{ $user->phone ?? 'Not provided' }}</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Date of Birth</span>
                                        <span class="detail-value">
                                            {{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'Not provided' }}
                                        </span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Address</span>
                                        <span class="detail-value">{{ $user->address ?? 'Not provided' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0">
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon me-3">
                                            <i class="material-symbols-rounded">settings</i>
                                        </div>
                                        <h6 class="mb-0">Account Information</h6>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="detail-item">
                                        <span class="detail-label">Account Status</span>
                                        <span class="detail-value">
                                            <span
                                                class="badge bg-gradient-success">{{ $user->status->value ?? 'Active' }}</span>
                                        </span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">User Type</span>
                                        <span class="detail-value">{{ $user->usertype->value ?? 'User' }}</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Member Since</span>
                                        <span class="detail-value">{{ $user->created_at->format('M d, Y') }}</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Last Login</span>
                                        <span class="detail-value">
                                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                        </span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Profile Completion</span>
                                        <div class="detail-value w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span id="completionPercentage">0%</span>
                                            </div>
                                            <div class="completion-bar">
                                                <div class="completion-fill" id="completionBar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.profile.change-password') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load profile stats
            loadProfileStats();

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

        function loadProfileStats() {
            fetch('{{ route('admin.profile.stats') }}')
                .then(response => response.json())
                .then(data => {
                    const profileCompletionEl = document.getElementById('profileCompletion');
                    const completionPercentageEl = document.getElementById('completionPercentage');
                    const completionBarEl = document.getElementById('completionBar');

                    if (profileCompletionEl) profileCompletionEl.textContent = data.profile_completion + '%';
                    if (completionPercentageEl) completionPercentageEl.textContent = data.profile_completion + '%';
                    if (completionBarEl) completionBarEl.style.width = data.profile_completion + '%';
                })
                .catch(error => {
                    console.error('Error loading profile stats:', error);
                });
        }

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
                        loadProfileStats();

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

        function changePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                new bootstrap.Modal(modal).show();
            }
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
@endsection
