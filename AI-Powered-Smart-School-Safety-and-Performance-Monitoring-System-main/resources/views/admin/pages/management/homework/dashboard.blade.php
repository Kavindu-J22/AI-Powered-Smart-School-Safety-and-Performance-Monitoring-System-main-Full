@extends('admin.layouts.app')

@section('title', 'Homework Dashboard')

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>AI-Powered Homework Management</h6>
                        <a href="{{ route('admin.management.homework.create') }}" class="btn btn-primary btn-sm">
                            <i class="material-symbols-outlined">add</i> Create Homework
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">assignment</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Total Homework</p>
                            <h4 class="mb-0">{{ $stats['total_homework'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">check_circle</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Active</p>
                            <h4 class="mb-0">{{ $stats['active_homework'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">pending</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Pending Submissions</p>
                            <h4 class="mb-0">{{ $stats['pending_submissions'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-outlined opacity-10">grading</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Graded Today</p>
                            <h4 class="mb-0">{{ $stats['graded_today'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Homework & Overdue -->
        <div class="row mt-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Recent Homework</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Subject</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Due Date</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentHomework ?? [] as $hw)
                                    <tr>
                                        <td>
                                            <span class="text-sm font-weight-bold">{{ Str::limit($hw->title, 25) }}</span>
                                        </td>
                                        <td><span class="badge bg-gradient-info">{{ $hw->subject->subject_name ?? 'N/A' }}</span></td>
                                        <td class="text-center text-sm">{{ $hw->due_date->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.management.homework.show', $hw->homework_id) }}"
                                                class="btn btn-sm btn-outline-info py-1 px-2" title="View">
                                                <i class="material-symbols-outlined" style="font-size: 16px;">visibility</i>
                                            </a>
                                            <a href="{{ route('admin.management.homework.edit', $hw->homework_id) }}"
                                                class="btn btn-sm btn-outline-warning py-1 px-2" title="Edit">
                                                <i class="material-symbols-outlined" style="font-size: 16px;">edit</i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No recent homework</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="text-danger">Overdue Homework</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Class</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Was Due</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($overdueHomework ?? [] as $hw)
                                    <tr>
                                        <td>
                                            <span class="text-sm text-danger font-weight-bold">{{ Str::limit($hw->title, 20) }}</span>
                                        </td>
                                        <td class="text-sm">{{ $hw->schoolClass->class_name ?? 'N/A' }}</td>
                                        <td class="text-center text-danger text-sm">{{ $hw->due_date->diffForHumans() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.management.homework.show', $hw->homework_id) }}"
                                                class="btn btn-sm btn-outline-info py-1 px-2" title="View">
                                                <i class="material-symbols-outlined" style="font-size: 16px;">visibility</i>
                                            </a>
                                            <a href="{{ route('admin.management.homework.edit', $hw->homework_id) }}"
                                                class="btn btn-sm btn-outline-warning py-1 px-2" title="Edit">
                                                <i class="material-symbols-outlined" style="font-size: 16px;">edit</i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-success">No overdue homework 🎉</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>AI-Powered Features</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#generateQuestionsModal">
                                    <i class="material-symbols-outlined me-2">auto_awesome</i>
                                    Auto-Generate Questions
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#scheduleWeeklyModal">
                                    <i class="material-symbols-outlined me-2">schedule</i>
                                    Schedule Weekly Homework
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('admin.management.performance.dashboard') }}" class="btn btn-outline-info w-100">
                                    <i class="material-symbols-outlined me-2">analytics</i>
                                    View Performance Analytics
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Weekly Homework Modal -->
    <div class="modal fade" id="scheduleWeeklyModal" tabindex="-1" aria-labelledby="scheduleWeeklyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleWeeklyModalLabel">
                        <i class="material-symbols-outlined me-2">schedule</i>Schedule Weekly Homework
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">This will automatically create 2 homework assignments for the selected class - one due in 3 days and one due in 6 days.</p>

                    <div class="mb-3">
                        <label for="scheduleSubject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleSubject" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="scheduleClass" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleClass" required>
                            <option value="">Select Class</option>
                            @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }} (Grade {{ $class->grade_level }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="scheduleLesson" class="form-label">Source Lesson <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleLesson" required>
                            <option value="">Select Lesson (Content Source)</option>
                            @foreach($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->lesson_id }}">{{ $lesson->title }} - {{ $lesson->subject->subject_name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="scheduleError" class="alert alert-danger d-none"></div>
                    <div id="scheduleSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="scheduleWeeklyBtn">
                        <i class="material-symbols-outlined me-1">schedule</i>Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Generate Questions Modal -->
    <div class="modal fade" id="generateQuestionsModal" tabindex="-1" aria-labelledby="generateQuestionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateQuestionsModalLabel">
                        <i class="material-symbols-outlined me-2">auto_awesome</i>Auto-Generate Questions
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Select a lesson and the AI will automatically generate questions based on the lesson content.</p>

                    <div class="mb-3">
                        <label for="generateLesson" class="form-label">Source Lesson <span class="text-danger">*</span></label>
                        <select class="form-control" id="generateLesson" required>
                            <option value="">Select Lesson</option>
                            @foreach($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->lesson_id }}">{{ $lesson->title }} - {{ $lesson->subject->subject_name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="numMcq" class="form-label">MCQ</label>
                            <input type="number" class="form-control" id="numMcq" value="3" min="0" max="10">
                        </div>
                        <div class="col-4">
                            <label for="numShort" class="form-label">Short Answer</label>
                            <input type="number" class="form-control" id="numShort" value="2" min="0" max="10">
                        </div>
                        <div class="col-4">
                            <label for="numDesc" class="form-label">Descriptive</label>
                            <input type="number" class="form-control" id="numDesc" value="1" min="0" max="5">
                        </div>
                    </div>

                    <div id="generateError" class="alert alert-danger d-none"></div>
                    <div id="generateSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="generateQuestionsBtn">
                        <i class="material-symbols-outlined me-1">auto_awesome</i>Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Schedule Weekly Homework
        const scheduleBtn = document.getElementById('scheduleWeeklyBtn');
        if (scheduleBtn) {
            scheduleBtn.addEventListener('click', async function() {
                const subjectId = document.getElementById('scheduleSubject').value;
                const classId = document.getElementById('scheduleClass').value;
                const lessonId = document.getElementById('scheduleLesson').value;

                const errorDiv = document.getElementById('scheduleError');
                const successDiv = document.getElementById('scheduleSuccess');

                errorDiv.classList.add('d-none');
                successDiv.classList.add('d-none');

                if (!subjectId || !classId || !lessonId) {
                    errorDiv.textContent = 'Please fill in all required fields.';
                    errorDiv.classList.remove('d-none');
                    return;
                }

                scheduleBtn.disabled = true;
                scheduleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Scheduling...';

                try {
                    const response = await fetch('{{ route("admin.management.homework.schedule-weekly") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            subject_id: subjectId,
                            class_id: classId,
                            lesson_id: lessonId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        successDiv.textContent = data.message || '2 homework assignments scheduled successfully!';
                        successDiv.classList.remove('d-none');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        errorDiv.textContent = data.error || 'Failed to schedule homework. Please try again.';
                        errorDiv.classList.remove('d-none');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    errorDiv.textContent = 'An error occurred. Please check if the AI service is running.';
                    errorDiv.classList.remove('d-none');
                } finally {
                    scheduleBtn.disabled = false;
                    scheduleBtn.innerHTML = '<i class="material-symbols-outlined me-1">schedule</i>Schedule';
                }
            });
        }

        // Auto-Generate Questions
        const generateBtn = document.getElementById('generateQuestionsBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', async function() {
                const lessonId = document.getElementById('generateLesson').value;
                const numMcq = document.getElementById('numMcq').value;
                const numShort = document.getElementById('numShort').value;
                const numDesc = document.getElementById('numDesc').value;

                const errorDiv = document.getElementById('generateError');
                const successDiv = document.getElementById('generateSuccess');

                errorDiv.classList.add('d-none');
                successDiv.classList.add('d-none');

                if (!lessonId) {
                    errorDiv.textContent = 'Please select a lesson.';
                    errorDiv.classList.remove('d-none');
                    return;
                }

                generateBtn.disabled = true;
                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';

                try {
                    const response = await fetch('{{ route("admin.management.homework.generate-questions") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            lesson_id: lessonId,
                            num_mcq: parseInt(numMcq),
                            num_short: parseInt(numShort),
                            num_descriptive: parseInt(numDesc)
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        successDiv.innerHTML = `Questions generated successfully! <a href="{{ route('admin.management.homework.create') }}?lesson_id=${lessonId}" class="alert-link">Create homework with these questions</a>`;
                        successDiv.classList.remove('d-none');
                    } else {
                        errorDiv.textContent = data.error || 'Failed to generate questions. Please try again.';
                        errorDiv.classList.remove('d-none');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    errorDiv.textContent = 'An error occurred. Please check if the AI service is running.';
                    errorDiv.classList.remove('d-none');
                } finally {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="material-symbols-outlined me-1">auto_awesome</i>Generate';
                }
            });
        }
    });
</script>
@endpush