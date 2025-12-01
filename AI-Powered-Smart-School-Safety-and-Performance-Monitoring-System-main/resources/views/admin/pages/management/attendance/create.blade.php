@extends('admin.layouts.app')

@section('title', __('Manual Attendance Entry'))

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>{{ __('Manual Attendance Entry') }}</h6>
                                    <p class="text-sm mb-0">{{ __('Record attendance manually by entering student code') }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.management.attendance.dashboard') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="material-symbols-rounded text-sm">arrow_back</i> {{ __('Back to Dashboard') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Student Search -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border">
                                        <div class="card-header bg-gradient-primary">
                                            <h6 class="text-white mb-0">{{ __('Step 1: Find Student') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="input-group input-group-outline mb-3">
                                                <label class="form-label">{{ __('Enter Student Code') }}</label>
                                                <input type="text" class="form-control" id="studentCodeInput" autofocus>
                                            </div>
                                            <button type="button" class="btn btn-primary w-100" onclick="searchStudent()">
                                                <i class="material-symbols-rounded text-sm">search</i> {{ __('Search Student') }}
                                            </button>

                                            <!-- Student Info Display -->
                                            <div id="studentInfo" class="mt-4" style="display: none;">
                                                <div class="alert alert-success">
                                                    <h6 class="mb-2">{{ __('Student Found') }}</h6>
                                                    <div class="row">
                                                        <div class="col-6"><strong>{{ __('Name') }}:</strong></div>
                                                        <div class="col-6" id="studentName">-</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6"><strong>{{ __('Code') }}:</strong></div>
                                                        <div class="col-6" id="studentCode">-</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6"><strong>{{ __('Class') }}:</strong></div>
                                                        <div class="col-6" id="studentClass">-</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6"><strong>{{ __('Grade') }}:</strong></div>
                                                        <div class="col-6" id="studentGrade">-</div>
                                                    </div>
                                                </div>

                                                <!-- Today's Attendance Status -->
                                                <div id="todayStatus" class="alert alert-info mt-3">
                                                    <h6 class="mb-2">{{ __("Today's Status") }}</h6>
                                                    <div id="statusContent">
                                                        <p class="mb-0">{{ __('No attendance recorded yet today') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="studentError" class="alert alert-danger mt-3" style="display: none;">
                                                <p class="mb-0" id="errorMessage">{{ __('Student not found') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Form -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-gradient-success">
                                            <h6 class="text-white mb-0">{{ __('Step 2: Record Attendance') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <form id="attendanceForm">
                                                @csrf
                                                <input type="hidden" id="selectedStudentCode" name="student_code">

                                                <div class="input-group input-group-outline mb-3">
                                                    <label class="form-label">{{ __('Attendance Type') }}</label>
                                                    <select class="form-control" id="attendanceType" name="attendance_type"
                                                        required>
                                                        <option value="">{{ __('Select Type') }}</option>
                                                        <option value="check_in">{{ __('Check In') }}</option>
                                                        <option value="check_out">{{ __('Check Out') }}</option>
                                                        <option value="absent">{{ __('Mark Absent') }}</option>
                                                    </select>
                                                </div>

                                                <div class="input-group input-group-outline mb-3">
                                                    <label class="form-label">{{ __('Date') }}</label>
                                                    <input type="date" class="form-control" name="date"
                                                        id="attendanceDate" value="{{ date('Y-m-d') }}">
                                                </div>

                                                <div id="checkInTimeGroup" class="input-group input-group-outline mb-3"
                                                    style="display: none;">
                                                    <label class="form-label">{{ __('Check In Time') }}</label>
                                                    <input type="time" class="form-control" name="check_in_time"
                                                        id="checkInTime">
                                                </div>

                                                <div id="checkOutTimeGroup" class="input-group input-group-outline mb-3"
                                                    style="display: none;">
                                                    <label class="form-label">{{ __('Check Out Time') }}</label>
                                                    <input type="time" class="form-control" name="check_out_time"
                                                        id="checkOutTime">
                                                </div>

                                                <div class="input-group input-group-outline mb-3">
                                                    <label class="form-label">{{ __('Notes (Optional)') }}</label>
                                                    <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-success w-100" id="submitBtn"
                                                    disabled>
                                                    <i class="material-symbols-rounded text-sm">save</i>
                                                    {{ __('Record Attendance') }}
                                                </button>
                                            </form>

                                            <div id="successMessage" class="alert alert-success mt-3"
                                                style="display: none;">
                                                <p class="mb-0" id="successText">
                                                    {{ __('Attendance recorded successfully!') }}</p>
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

        <script>
            let currentStudent = null;

            // Search student by code
            async function searchStudent() {
                const code = document.getElementById('studentCodeInput').value.trim();

                if (!code) {
                    alert('{{ __('Please enter a student code') }}');
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.management.attendance.search-student') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            code: code
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        currentStudent = result.data.student;
                        displayStudentInfo(result.data);
                        document.getElementById('selectedStudentCode').value = currentStudent.student_code;
                        document.getElementById('submitBtn').disabled = false;
                    } else {
                        showError(result.message);
                    }
                } catch (error) {
                    showError('{{ __('Error searching for student') }}');
                    console.error(error);
                }
            }

            // Display student information
            function displayStudentInfo(data) {
                document.getElementById('studentName').textContent = data.student.full_name;
                document.getElementById('studentCode').textContent = data.student.student_code;
                document.getElementById('studentClass').textContent = data.student.class_name;
                document.getElementById('studentGrade').textContent = data.student.grade_level;

                if (data.today_attendance) {
                    const status = data.today_attendance;
                    document.getElementById('statusContent').innerHTML = `
            <div class="row">
                <div class="col-6"><strong>{{ __('Status') }}:</strong></div>
                <div class="col-6">${status.status}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>{{ __('Check In') }}:</strong></div>
                <div class="col-6">${status.check_in_time || '-'}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>{{ __('Check Out') }}:</strong></div>
                <div class="col-6">${status.check_out_time || '-'}</div>
            </div>
            ${status.is_late ? '<p class="text-warning mb-0 mt-2"><i class="material-symbols-rounded text-sm">schedule</i> {{ __('Late arrival') }}</p>' : ''}
        `;
                    document.getElementById('todayStatus').classList.remove('alert-info');
                    document.getElementById('todayStatus').classList.add('alert-warning');
                }

                document.getElementById('studentInfo').style.display = 'block';
                document.getElementById('studentError').style.display = 'none';
            }

            // Show error message
            function showError(message) {
                document.getElementById('errorMessage').textContent = message;
                document.getElementById('studentError').style.display = 'block';
                document.getElementById('studentInfo').style.display = 'none';
                document.getElementById('submitBtn').disabled = true;
            }

            // Handle attendance type change
            document.getElementById('attendanceType').addEventListener('change', function() {
                const type = this.value;

                document.getElementById('checkInTimeGroup').style.display = type === 'check_in' ? 'block' : 'none';
                document.getElementById('checkOutTimeGroup').style.display = type === 'check_out' ? 'block' : 'none';

                // Set default times
                if (type === 'check_in') {
                    document.getElementById('checkInTime').value = new Date().toTimeString().slice(0, 5);
                }
                if (type === 'check_out') {
                    document.getElementById('checkOutTime').value = new Date().toTimeString().slice(0, 5);
                }
            });

            // Handle form submission
            document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                try {
                    const response = await fetch('{{ route('admin.management.attendance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('successText').textContent = result.message;
                        document.getElementById('successMessage').style.display = 'block';

                        // Reset form after 2 seconds
                        setTimeout(() => {
                            this.reset();
                            document.getElementById('studentCodeInput').value = '';
                            document.getElementById('studentInfo').style.display = 'none';
                            document.getElementById('successMessage').style.display = 'none';
                            document.getElementById('submitBtn').disabled = true;
                            currentStudent = null;
                        }, 2000);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('{{ __('Error recording attendance') }}');
                    console.error(error);
                }
            });

            // Allow Enter key to search
            document.getElementById('studentCodeInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchStudent();
                }
            });
        </script>
    </main>
@endsection
