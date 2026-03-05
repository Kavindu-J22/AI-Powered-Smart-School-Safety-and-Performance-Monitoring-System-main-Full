@extends('admin.layouts.app')

@section('title', 'Homework Dashboard')

@push('styles')
<style>
    .hw-hero {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 18px;
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .hw-hero::after {
        content: 'assignment';
        font-family: 'Material Symbols Outlined';
        font-size: 140px;
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        opacity: .1;
        line-height: 1;
    }

    .hw-hero h2 {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .hw-hero p {
        font-size: .9rem;
        opacity: .85;
        margin: 0;
    }

    .btn-hw-create {
        background: #fff;
        color: #f5576c;
        border: none;
        border-radius: 10px;
        padding: 10px 24px;
        font-weight: 700;
        font-size: .88rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: box-shadow .2s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
    }

    .btn-hw-create:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, .22);
        color: #f5576c;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 16px rgba(102, 126, 234, .10);
        margin-bottom: 20px;
        transition: transform .2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon .material-symbols-outlined {
        font-size: 26px;
        color: #fff;
    }

    .stat-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #a0aec0;
        font-weight: 700;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #2d3748;
        line-height: 1.2;
    }

    .hw-table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(102, 126, 234, .08);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .hw-table-card .card-head {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f2fb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hw-table-card .card-head .ch-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hw-table-card .card-head h6 {
        margin: 0;
        font-size: .9rem;
        font-weight: 700;
        color: #1a2550;
    }

    .hw-table thead th {
        background: #f8f9ff;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #7c8db0;
        font-weight: 700;
        padding: 11px 16px;
        border: none;
    }

    .hw-table tbody td {
        padding: 11px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2fb;
        font-size: .85rem;
    }

    .hw-table tbody tr:last-child td {
        border-bottom: none;
    }

    .hw-table tbody tr:hover {
        background: #f8f9ff;
    }

    /* Overdue badge – force white text over red bg */
    .overdue-badge {
        display: inline-block;
        background: #dc2626 !important;
        color: #fff !important;
        border-radius: 50px;
        padding: 3px 10px;
        font-size: .75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* ── AI Feature Action Buttons ── */
    .ai-feature-btn {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        background: #fff;
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 16px;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        cursor: pointer;
        text-align: left;
        transition: all .22s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
    }

    .ai-feature-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: var(--ai-btn-color, #667eea);
        border-radius: 14px 0 0 14px;
        transition: width .22s ease;
    }

    .ai-feature-btn:hover {
        border-color: var(--ai-btn-color, #667eea);
        background: var(--ai-btn-bg, #f5f3ff);
        box-shadow: 0 6px 20px rgba(0, 0, 0, .10);
        transform: translateY(-2px);
    }

    .ai-feature-btn:hover::before {
        width: 7px;
    }

    .ai-feature-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    .ai-btn-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .ai-btn-icon .material-symbols-outlined {
        font-size: 22px;
        color: #fff;
    }

    .ai-btn-body {
        flex: 1;
        min-width: 0;
    }

    .ai-btn-body h6 {
        font-size: .88rem;
        font-weight: 700;
        color: #1a2550;
        margin: 0 0 2px;
    }

    .ai-btn-body p {
        font-size: .75rem;
        color: #718096;
        margin: 0;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ai-btn-arrow {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--ai-btn-color, #667eea);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: .75;
        transition: opacity .2s, transform .2s;
    }

    .ai-btn-arrow .material-symbols-outlined {
        font-size: 16px;
        color: #fff;
    }

    .ai-feature-btn:hover .ai-btn-arrow {
        opacity: 1;
        transform: translateX(3px);
    }

    .ai-section-header {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #a0aec0;
        font-weight: 700;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
</style>
@endpush

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">

        {{-- Hero Header --}}
        <div class="hw-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2><span class="material-symbols-outlined" style="font-size:1.5rem;vertical-align:middle;">assignment</span> AI-Powered Homework</h2>
                    <p>Manage, generate, and schedule homework assignments with AI assistance</p>
                </div>
                <a href="{{ route('admin.management.homework.create') }}" class="btn-hw-create">
                    <span class="material-symbols-outlined" style="font-size:18px;">add_circle</span> Create Homework
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                        <span class="material-symbols-outlined">assignment</span>
                    </div>
                    <div>
                        <div class="stat-label">Total Homework</div>
                        <div class="stat-value">{{ $stats['total_homework'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value">{{ $stats['active_homework'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                        <span class="material-symbols-outlined">pending</span>
                    </div>
                    <div>
                        <div class="stat-label">Pending Submissions</div>
                        <div class="stat-value">{{ $stats['pending_submissions'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                        <span class="material-symbols-outlined">grading</span>
                    </div>
                    <div>
                        <div class="stat-label">Graded Today</div>
                        <div class="stat-value">{{ $stats['graded_today'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="row">
            {{-- Recent Homework --}}
            <div class="col-lg-6">
                <div class="hw-table-card">
                    <div class="card-head">
                        <div class="ch-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                            <span class="material-symbols-outlined" style="font-size:16px;color:#fff;">history</span>
                        </div>
                        <h6>Recent Homework</h6>
                        <a href="{{ route('admin.management.homework.create') }}" class="ms-auto btn btn-sm btn-primary" style="border-radius:8px;font-size:.75rem;font-weight:600;padding:5px 12px;">
                            <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">add</span> New
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table hw-table mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentHomework ?? [] as $hw)
                                <tr>
                                    <td class="fw-600">{{ Str::limit($hw->title, 25) }}</td>
                                    <td><span class="badge" style="background:#ede9fe;color:#5b21b6;border-radius:50px;">{{ $hw->subject->subject_name ?? 'N/A' }}</span></td>
                                    <td>{{ $hw->due_date->format('M d, Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.management.homework.show', $hw->homework_id) }}" class="btn btn-sm btn-outline-info py-1 px-2" title="View">
                                            <span class="material-symbols-outlined" style="font-size:15px;">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.management.homework.edit', $hw->homework_id) }}" class="btn btn-sm btn-outline-warning py-1 px-2" title="Edit">
                                            <span class="material-symbols-outlined" style="font-size:15px;">edit</span>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent homework</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Overdue Homework --}}
            <div class="col-lg-6">
                <div class="hw-table-card">
                    <div class="card-head">
                        <div class="ch-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                            <span class="material-symbols-outlined" style="font-size:16px;color:#fff;">warning</span>
                        </div>
                        <h6 style="color:#e53e3e;">Overdue Homework</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table hw-table mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Class</th>
                                    <th>Was Due</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueHomework ?? [] as $hw)
                                <tr>
                                    <td class="fw-600 text-danger">{{ Str::limit($hw->title, 22) }}</td>
                                    <td>{{ $hw->schoolClass->class_name ?? 'N/A' }}</td>
                                    <td><span class="overdue-badge">{{ $hw->due_date->diffForHumans() }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.management.homework.show', $hw->homework_id) }}" class="btn btn-sm btn-outline-info py-1 px-2" title="View">
                                            <span class="material-symbols-outlined" style="font-size:15px;">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.management.homework.edit', $hw->homework_id) }}" class="btn btn-sm btn-outline-warning py-1 px-2" title="Edit">
                                            <span class="material-symbols-outlined" style="font-size:15px;">edit</span>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-success">🎉 No overdue homework!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Features --}}
        <div class="ai-section-header mt-3">
            <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span> AI-Powered Features
        </div>
        <div class="row">
            <div class="col-md-4">
                <button type="button" class="ai-feature-btn"
                    style="--ai-btn-color:#764ba2;--ai-btn-bg:#f5f3ff;"
                    data-bs-toggle="modal" data-bs-target="#generateQuestionsModal">
                    <div class="ai-btn-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                        <span class="material-symbols-outlined">psychology</span>
                    </div>
                    <div class="ai-btn-body">
                        <h6>Auto-Generate Questions</h6>
                        <p>Let AI create MCQ &amp; descriptive questions instantly</p>
                    </div>
                    <div class="ai-btn-arrow">
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" class="ai-feature-btn"
                    style="--ai-btn-color:#185a9d;--ai-btn-bg:#f0f9ff;"
                    data-bs-toggle="modal" data-bs-target="#scheduleWeeklyModal">
                    <div class="ai-btn-icon" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                        <span class="material-symbols-outlined">calendar_month</span>
                    </div>
                    <div class="ai-btn-body">
                        <h6>Schedule Weekly Homework</h6>
                        <p>Auto-create 2 assignments spaced over the week</p>
                    </div>
                    <div class="ai-btn-arrow" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </button>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.management.performance.dashboard') }}" class="ai-feature-btn"
                    style="--ai-btn-color:#0ea5e9;--ai-btn-bg:#f0f9ff;">
                    <div class="ai-btn-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                        <span class="material-symbols-outlined">analytics</span>
                    </div>
                    <div class="ai-btn-body">
                        <h6>Performance Analytics</h6>
                        <p>AI-powered insights, progress reports &amp; comparisons</p>
                    </div>
                    <div class="ai-btn-arrow" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Schedule Weekly Homework Modal --}}
    <div class="modal fade" id="scheduleWeeklyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#43cea2,#185a9d);color:#fff;padding:20px 24px 16px;">
                    <div>
                        <span class="material-symbols-outlined" style="font-size:28px;display:block;margin-bottom:4px;">calendar_month</span>
                        <h5 class="modal-title fw-bold mb-0">Schedule Weekly Homework</h5>
                        <p class="mb-0" style="font-size:.8rem;opacity:.85;">Auto-create 2 assignments spaced over the week</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Subject <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleSubject" style="border-radius:10px;">
                            <option value="">Select Subject</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Class <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleClass" style="border-radius:10px;">
                            <option value="">Select Class</option>
                            @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }} (Grade {{ $class->grade_level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Source Lesson <span class="text-danger">*</span></label>
                        <select class="form-control" id="scheduleLesson" style="border-radius:10px;">
                            <option value="">Select Lesson (Content Source)</option>
                            @foreach($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->lesson_id }}">{{ $lesson->title }} — {{ $lesson->subject->subject_name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="scheduleError" class="alert alert-danger d-none" style="border-radius:10px;"></div>
                    <div id="scheduleSuccess" class="alert alert-success d-none" style="border-radius:10px;"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="button" class="btn text-white" id="scheduleWeeklyBtn" style="background:linear-gradient(135deg,#43cea2,#185a9d);border-radius:10px;">
                        <span class="material-symbols-outlined me-1" style="font-size:16px;vertical-align:middle;">schedule</span>Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-Generate Questions Modal --}}
    <div class="modal fade" id="generateQuestionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px 24px 16px;">
                    <div>
                        <span class="material-symbols-outlined" style="font-size:28px;display:block;margin-bottom:4px;">psychology</span>
                        <h5 class="modal-title fw-bold mb-0">AI Question Generator</h5>
                        <p class="mb-0" style="font-size:.8rem;opacity:.85;">Generate questions automatically from lesson content</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Source Lesson <span class="text-danger">*</span></label>
                        <select class="form-control" id="generateLesson" style="border-radius:10px;">
                            <option value="">Select Lesson</option>
                            @foreach($lessons ?? [] as $lesson)
                            <option value="{{ $lesson->lesson_id }}">{{ $lesson->title }} — {{ $lesson->subject->subject_name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">MCQ</label>
                            <input type="number" class="form-control" id="numMcq" value="3" min="0" max="10" style="border-radius:10px;">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Short Answer</label>
                            <input type="number" class="form-control" id="numShort" value="2" min="0" max="10" style="border-radius:10px;">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#4a5568;">Descriptive</label>
                            <input type="number" class="form-control" id="numDesc" value="1" min="0" max="5" style="border-radius:10px;">
                        </div>
                    </div>
                    <div id="generateError" class="alert alert-danger d-none" style="border-radius:10px;"></div>
                    <div id="generateSuccess" class="alert alert-success d-none" style="border-radius:10px;"></div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="button" class="btn text-white" id="generateQuestionsBtn" style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:10px;">
                        <span class="material-symbols-outlined me-1" style="font-size:16px;vertical-align:middle;">auto_awesome</span>Generate
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