@extends('admin.layouts.app')

@section('title', 'View Lesson')

@push('styles')
<style>
    .show-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .show-hero::after {
        content: 'menu_book';
        font-family: 'Material Symbols Outlined';
        font-size: 130px;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        opacity: .10;
        line-height: 1;
    }

    .show-hero h3 {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .show-hero-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .hchip {
        background: rgba(255, 255, 255, .18);
        border-radius: 50px;
        padding: 5px 14px;
        font-size: .8rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        backdrop-filter: blur(4px);
    }

    .hchip .material-symbols-outlined {
        font-size: 15px;
    }

    .status-hero-pill {
        border-radius: 50px;
        padding: 5px 16px;
        font-size: .8rem;
        font-weight: 700;
    }

    .status-published {
        background: #d4edda;
        color: #1a7a3c;
    }

    .status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-archived {
        background: #e2e8f0;
        color: #64748b;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .info-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 2px 12px rgba(102, 126, 234, .08);
    }

    .info-card .ic-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #a0aec0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 6px;
    }

    .info-card .ic-label .material-symbols-outlined {
        font-size: 14px;
    }

    .info-card .ic-value {
        font-size: .95rem;
        font-weight: 700;
        color: #2d3748;
    }

    .section-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(102, 126, 234, .08);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .section-card-header {
        background: linear-gradient(90deg, #f5f6ff, #f0f4ff);
        padding: 13px 20px;
        border-bottom: 1px solid #e8eaf6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-card-header .sh-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .section-card-header .sh-icon .material-symbols-outlined {
        font-size: 16px;
        color: #fff;
    }

    .section-card-header h6 {
        margin: 0;
        font-size: .88rem;
        font-weight: 700;
        color: #1a2550;
    }

    .section-card-body {
        padding: 20px;
    }

    .topic-chip {
        background: linear-gradient(135deg, #667eea22, #764ba222);
        color: #4c3d99;
        border-radius: 50px;
        padding: 4px 14px;
        font-size: .78rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin: 3px;
    }

    .topic-chip .material-symbols-outlined {
        font-size: 14px;
    }

    .outcome-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed #e8eaf6;
    }

    .outcome-item:last-child {
        border-bottom: none;
    }

    .outcome-item .oi-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #43cea2, #185a9d);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .outcome-item .oi-icon .material-symbols-outlined {
        font-size: 13px;
        color: #fff;
    }

    .outcome-item span {
        font-size: .88rem;
        color: #4a5568;
    }

    .content-box {
        background: #f8f9ff;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #667eea;
        font-size: .9rem;
        line-height: 1.8;
        color: #2d3748;
    }

    .hw-table thead th {
        background: #f8f9ff;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #7c8db0;
        font-weight: 700;
        padding: 12px 16px;
        border: none;
    }

    .hw-table tbody td {
        padding: 11px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2fb;
        font-size: .85rem;
    }

    .hw-table tbody tr:hover {
        background: #f5f6ff;
    }

    .action-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .btn-edit-lesson {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 22px;
        font-weight: 700;
        font-size: .88rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-edit-lesson:hover {
        opacity: .9;
        color: #fff;
    }

    .btn-back-list {
        border-radius: 10px;
        padding: 9px 18px;
        font-weight: 600;
        font-size: .88rem;
    }
</style>
@endpush

@section('content')
@include('admin.layouts.sidebar')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">

        {{-- Hero --}}
        <div class="show-hero">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div style="flex:1; min-width:200px;">
                    <h3>{{ $lesson->title }}</h3>
                    <div class="show-hero-chips">
                        <span class="hchip"><span class="material-symbols-outlined">subject</span>{{ $lesson->subject->subject_name ?? 'N/A' }}</span>
                        <span class="hchip"><span class="material-symbols-outlined">school</span>Grade {{ $lesson->grade_level }}</span>
                        <span class="hchip"><span class="material-symbols-outlined">signal_cellular_alt</span>{{ ucfirst($lesson->difficulty ?? 'beginner') }}</span>
                        <span class="hchip"><span class="material-symbols-outlined">schedule</span>{{ $lesson->duration_minutes ?? 60 }} min</span>
                    </div>
                </div>
                <div>
                    @if($lesson->status === 'published')
                    <span class="status-hero-pill status-published">✓ Published</span>
                    @elseif($lesson->status === 'draft')
                    <span class="status-hero-pill status-draft">◷ Draft</span>
                    @else
                    <span class="status-hero-pill status-archived">⊘ Archived</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="action-bar">
            <a href="{{ route('admin.management.lessons.edit', $lesson->lesson_id) }}" class="btn-edit-lesson">
                <span class="material-symbols-outlined" style="font-size:17px;">edit</span> Edit Lesson
            </a>
            <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-back-list">
                <span class="material-symbols-outlined" style="font-size:17px;vertical-align:middle;">arrow_back</span> Back to List
            </a>
        </div>

        {{-- Info Grid --}}
        <div class="info-grid">
            <div class="info-card">
                <div class="ic-label"><span class="material-symbols-outlined">folder_open</span>Unit / Chapter</div>
                <div class="ic-value">{{ $lesson->unit ?? '—' }}</div>
            </div>
            <div class="info-card">
                <div class="ic-label"><span class="material-symbols-outlined">person</span>Teacher</div>
                <div class="ic-value">{{ ($lesson->teacher->first_name ?? '') . ' ' . ($lesson->teacher->last_name ?? '') ?: '—' }}</div>
            </div>
            <div class="info-card">
                <div class="ic-label"><span class="material-symbols-outlined">quiz</span>Topics Count</div>
                <div class="ic-value">{{ count($lesson->topics ?? []) }} topics</div>
            </div>
            <div class="info-card">
                <div class="ic-label"><span class="material-symbols-outlined">assignment</span>Homework</div>
                <div class="ic-value">{{ $lesson->homework ? $lesson->homework->count() : 0 }} assigned</div>
            </div>
            <div class="info-card">
                <div class="ic-label"><span class="material-symbols-outlined">calendar_today</span>Created</div>
                <div class="ic-value">{{ $lesson->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                {{-- Topics --}}
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="sh-icon"><span class="material-symbols-outlined">tag</span></div>
                        <h6>Topics Covered</h6>
                    </div>
                    <div class="section-card-body">
                        @forelse($lesson->topics ?? [] as $topic)
                        <span class="topic-chip"><span class="material-symbols-outlined">fiber_manual_record</span>{{ $topic }}</span>
                        @empty
                        <span class="text-muted" style="font-size:.85rem;">No topics defined for this lesson.</span>
                        @endforelse
                    </div>
                </div>

                {{-- Lesson Content --}}
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="sh-icon"><span class="material-symbols-outlined">article</span></div>
                        <h6>Lesson Content</h6>
                        <span class="ms-auto badge" style="background:#ede9fe;color:#5b21b6;font-size:.72rem;">AI Source</span>
                    </div>
                    <div class="section-card-body">
                        <div class="content-box">{!! nl2br(e($lesson->content)) !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Learning Outcomes --}}
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="sh-icon"><span class="material-symbols-outlined">emoji_events</span></div>
                        <h6>Learning Outcomes</h6>
                    </div>
                    <div class="section-card-body">
                        @forelse($lesson->learning_outcomes ?? [] as $outcome)
                        <div class="outcome-item">
                            <div class="oi-icon"><span class="material-symbols-outlined">check</span></div>
                            <span>{{ $outcome }}</span>
                        </div>
                        @empty
                        <span class="text-muted" style="font-size:.85rem;">No learning outcomes defined.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Homework --}}
        @if($lesson->homework && $lesson->homework->count() > 0)
        <div class="section-card">
            <div class="section-card-header">
                <div class="sh-icon"><span class="material-symbols-outlined">assignment</span></div>
                <h6>Related Homework <span class="ms-2 badge" style="background:#ede9fe;color:#5b21b6;">{{ $lesson->homework->count() }}</span></h6>
                <a href="{{ route('admin.management.homework.create') }}?lesson_id={{ $lesson->lesson_id }}" class="ms-auto btn btn-sm btn-primary" style="border-radius:8px;font-size:.78rem;font-weight:600;padding:5px 14px;">
                    <span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;">add</span> New Homework
                </a>
            </div>
            <div class="section-card-body p-0">
                <div class="table-responsive">
                    <table class="table hw-table mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lesson->homework as $hw)
                            <tr>
                                <td class="fw-600">{{ Str::limit($hw->title, 40) }}</td>
                                <td>{{ $hw->due_date->format('M d, Y') }}</td>
                                <td><span class="badge bg-{{ $hw->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($hw->status) }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.management.homework.show', $hw->homework_id) }}" class="btn btn-sm btn-outline-info py-1 px-2">
                                        <span class="material-symbols-outlined" style="font-size:15px;">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</main>
@endsection