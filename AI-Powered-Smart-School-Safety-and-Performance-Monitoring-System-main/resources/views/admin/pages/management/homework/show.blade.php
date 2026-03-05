@extends('admin.layouts.app')

@section('title', 'View Homework')

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
        content: 'menu_book';
        font-family: 'Material Symbols Outlined';
        font-size: 140px;
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        opacity: .08;
        line-height: 1;
    }

    .hw-hero h2 {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .hw-hero p {
        font-size: .85rem;
        opacity: .85;
        margin: 0;
    }

    .hw-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 14px;
        border-radius: 50px;
        font-size: .78rem;
        font-weight: 700;
        background: rgba(255, 255, 255, .22);
        color: #fff;
        backdrop-filter: blur(4px);
    }

    /* Info grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .info-tile {
        background: #fff;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 2px 12px rgba(245, 87, 108, .07);
        border: 1.5px solid #f0f2fb;
    }

    .info-tile .it-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #a0aec0;
        margin-bottom: 6px;
    }

    .info-tile .it-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1a2550;
    }

    .info-tile .it-sub {
        font-size: .78rem;
        color: #718096;
        margin-top: 2px;
    }

    /* Section card */
    .section-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(245, 87, 108, .07);
        margin-bottom: 22px;
        overflow: hidden;
    }

    .section-card .sc-head {
        padding: 14px 20px;
        border-bottom: 1px solid #f0f2fb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-card .sc-head .sc-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .section-card .sc-head .sc-icon .material-symbols-outlined {
        font-size: 18px;
        color: #fff;
    }

    .section-card .sc-head h6 {
        margin: 0;
        font-size: .9rem;
        font-weight: 700;
        color: #1a2550;
    }

    .section-card .sc-body {
        padding: 20px;
    }

    /* Question cards */
    .q-view-card {
        background: #fafbff;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        margin-bottom: 14px;
        overflow: hidden;
        transition: border-color .2s;
    }

    .q-view-card:hover {
        border-color: #f5576c55;
    }

    .q-view-header {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid #f0f2fb;
    }

    .q-view-body {
        padding: 16px;
    }

    .q-type-pill {
        border-radius: 50px;
        padding: 3px 12px;
        font-size: .72rem;
        font-weight: 700;
    }

    .q-text {
        font-size: .92rem;
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 12px;
        line-height: 1.55;
    }

    .mcq-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .mcq-option {
        border-radius: 10px;
        padding: 9px 14px;
        font-size: .83rem;
        border: 1.5px solid #e2e8f0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mcq-option.correct {
        background: #ecfdf5;
        border-color: #10b981;
    }

    .mcq-option .opt-letter {
        font-weight: 800;
        color: #4a5568;
        min-width: 18px;
    }

    .mcq-option.correct .opt-letter {
        color: #059669;
    }

    .answer-box {
        background: #f0fdf4;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: .85rem;
        color: #1a2550;
    }

    .answer-box strong {
        color: #059669;
    }

    .key-points-list {
        list-style: none;
        padding: 0;
        margin: 10px 0 0;
    }

    .key-points-list li {
        font-size: .82rem;
        color: #4a5568;
        padding: 4px 0 4px 22px;
        position: relative;
    }

    .key-points-list li::before {
        content: '•';
        position: absolute;
        left: 8px;
        color: #667eea;
        font-weight: 900;
    }

    .explanation-note {
        font-size: .78rem;
        color: #718096;
        margin-top: 10px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    /* Stat cards */
    .stat-card {
        border-radius: 16px;
        padding: 20px 22px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-card .stat-num {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
    }

    .stat-card .stat-label {
        font-size: .78rem;
        opacity: .88;
        margin-top: 3px;
    }

    /* Action buttons */
    .btn-hw-edit {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 700;
        font-size: .88rem;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
    }

    .btn-hw-edit:hover {
        opacity: .9;
        color: #fff;
    }

    .btn-back {
        background: #f8f9fa;
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: .82rem;
        color: #4a5568;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-back:hover {
        background: #e9ecef;
        color: #2d3748;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Hero --}}
    <div class="hw-hero">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    @php
                    $statusColor = match($homework->status) {
                    'active' => '#22c55e',
                    'closed' => '#ef4444',
                    default => '#94a3b8',
                    };
                    @endphp
                    <span class="hw-status-pill">
                        <span style="width:8px;height:8px;border-radius:50%;background:#fff;display:inline-block;"></span>
                        {{ ucfirst($homework->status) }}
                    </span>
                    @if($homework->due_date && $homework->due_date->isPast() && $homework->status !== 'closed')
                    <span class="hw-status-pill" style="background:rgba(239,68,68,.3);">
                        <span class="material-symbols-outlined" style="font-size:13px;">schedule</span> Overdue
                    </span>
                    @endif
                </div>
                <h2>{{ $homework->title }}</h2>
                <p>
                    {{ $homework->subject->subject_name ?? 'N/A' }} &bull;
                    Grade {{ $homework->grade_level }} &bull;
                    Due {{ $homework->due_date ? $homework->due_date->format('M d, Y') : 'N/A' }}
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.management.homework.edit', $homework->homework_id) }}" class="btn-hw-edit">
                    <span class="material-symbols-outlined" style="font-size:17px;">edit</span> Edit
                </a>
                <a href="{{ route('admin.management.homework.dashboard') }}" class="btn-back">
                    <span class="material-symbols-outlined" style="font-size:17px;">arrow_back</span> Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Info tiles --}}
    <div class="info-grid">
        <div class="info-tile">
            <div class="it-label">Subject</div>
            <div class="it-value">{{ $homework->subject->subject_name ?? 'N/A' }}</div>
        </div>
        <div class="info-tile">
            <div class="it-label">Grade</div>
            <div class="it-value">Grade {{ $homework->grade_level }}</div>
        </div>
        <div class="info-tile">
            <div class="it-label">Class</div>
            <div class="it-value">{{ $homework->schoolClass->class_name ?? 'All Classes' }}</div>
        </div>
        <div class="info-tile">
            <div class="it-label">Total Marks</div>
            <div class="it-value">{{ $homework->total_marks }}</div>
        </div>
        <div class="info-tile">
            <div class="it-label">Due Date</div>
            <div class="it-value">{{ $homework->due_date ? $homework->due_date->format('M d, Y') : 'N/A' }}</div>
        </div>
        <div class="info-tile">
            <div class="it-label">Assigned By</div>
            <div class="it-value">{{ $homework->assignedBy->first_name ?? '' }} {{ $homework->assignedBy->last_name ?? '' }}</div>
            <div class="it-sub">{{ $homework->assigned_date ? $homework->assigned_date->format('M d, Y') : '' }}</div>
        </div>
    </div>

    @if($homework->description)
    <div class="section-card">
        <div class="sc-head">
            <div class="sc-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <span class="material-symbols-outlined">description</span>
            </div>
            <h6>Description</h6>
        </div>
        <div class="sc-body">
            <p style="font-size:.9rem;color:#4a5568;margin:0;line-height:1.65;">{{ $homework->description }}</p>
        </div>
    </div>
    @endif

    {{-- Submission Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                <div class="stat-icon"><span class="material-symbols-outlined" style="font-size:22px;">group</span></div>
                <div>
                    <div class="stat-num">{{ $submissionStats['total'] ?? 0 }}</div>
                    <div class="stat-label">Total Assigned</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                <div class="stat-icon"><span class="material-symbols-outlined" style="font-size:22px;">task_alt</span></div>
                <div>
                    <div class="stat-num">{{ $submissionStats['submitted'] ?? 0 }}</div>
                    <div class="stat-label">Submitted</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                <div class="stat-icon"><span class="material-symbols-outlined" style="font-size:22px;">pending</span></div>
                <div>
                    <div class="stat-num">{{ $submissionStats['pending'] ?? 0 }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card" style="background:linear-gradient(135deg,#f6d365,#fda085);">
                <div class="stat-icon"><span class="material-symbols-outlined" style="font-size:22px;">leaderboard</span></div>
                <div>
                    <div class="stat-num">{{ number_format($submissionStats['average_score'] ?? 0, 1) }}%</div>
                    <div class="stat-label">Average Score</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div class="section-card">
        <div class="sc-head">
            <div class="sc-icon" style="background:linear-gradient(135deg,#43cea2,#185a9d);">
                <span class="material-symbols-outlined">quiz</span>
            </div>
            <h6>Questions
                <span style="background:linear-gradient(135deg,#f093fb,#f5576c);color:#fff;border-radius:50px;padding:2px 10px;font-size:.75rem;font-weight:700;margin-left:6px;">
                    {{ count($homework->questions ?? []) }}
                </span>
            </h6>
        </div>
        <div class="sc-body">
            @forelse($homework->questions ?? [] as $index => $question)
            @php
            $typeColor = $question['question_type'] === 'MCQ' ? '#3b82f6' : ($question['question_type'] === 'SHORT_ANSWER' ? '#f59e0b' : '#10b981');
            $typeIcon = $question['question_type'] === 'MCQ' ? 'radio_button_checked' : ($question['question_type'] === 'SHORT_ANSWER' ? 'short_text' : 'article');
            @endphp
            <div class="q-view-card">
                <div class="q-view-header">
                    <div style="width:32px;height:32px;border-radius:10px;background:{{ $typeColor }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:17px;color:{{ $typeColor }};">{{ $typeIcon }}</span>
                    </div>
                    <span class="q-type-pill" style="background:{{ $typeColor }}22;color:{{ $typeColor }};">
                        {{ str_replace('_', ' ', $question['question_type']) }}
                    </span>
                    <span class="q-type-pill" style="background:#e2e8f0;color:#4a5568;">{{ $question['marks'] }} mark{{ $question['marks'] > 1 ? 's' : '' }}</span>
                    @if(isset($question['difficulty']))
                    <span class="q-type-pill" style="background:#1a255011;color:#1a2550;">{{ $question['difficulty'] }}</span>
                    @endif
                    <span style="font-size:.78rem;color:#a0aec0;margin-left:auto;">Q{{ $index + 1 }}</span>
                </div>
                <div class="q-view-body">
                    <p class="q-text">{{ $question['question_text'] }}</p>

                    {{-- MCQ Options --}}
                    @if(isset($question['options']) && is_array($question['options']))
                    <div class="mcq-grid">
                        @foreach($question['options'] as $optIndex => $option)
                        @php $letter = chr(65 + $optIndex); $isCorrect = ($question['correct_answer'] ?? '') === $letter; @endphp
                        <div class="mcq-option {{ $isCorrect ? 'correct' : '' }}">
                            <span class="opt-letter">{{ $letter }}.</span>
                            <span>{{ $option }}</span>
                            @if($isCorrect)
                            <span class="material-symbols-outlined ms-auto" style="font-size:16px;color:#10b981;">check_circle</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @if(isset($question['explanation']))
                    <div class="explanation-note">
                        <strong>Explanation:</strong> {{ $question['explanation'] }}
                    </div>
                    @endif
                    @endif

                    {{-- Short / Descriptive answer --}}
                    @if(in_array($question['question_type'], ['SHORT_ANSWER', 'DESCRIPTIVE']))
                    @if(isset($question['expected_answer']) || isset($question['answer_key']))
                    <div class="answer-box">
                        <strong>Expected Answer:</strong>
                        <p style="margin:6px 0 0;color:#2d3748;">{{ $question['expected_answer'] ?? $question['answer_key'] }}</p>
                    </div>
                    @endif
                    @if(isset($question['key_points']) && is_array($question['key_points']) && count($question['key_points']) > 0)
                    <ul class="key-points-list">
                        @foreach($question['key_points'] as $point)
                        <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="alert alert-warning" style="border-radius:12px;">No questions found for this homework.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection