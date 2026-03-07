@extends('admin.layouts.app')

@section('title', 'Lessons')

@push('styles')
<style>
    .lesson-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .lesson-hero::after {
        content: 'menu_book';
        font-family: 'Material Symbols Outlined';
        font-size: 120px;
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.12;
        line-height: 1;
    }

    .lesson-hero h4 {
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .lesson-hero p {
        opacity: 0.85;
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .stat-chip {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 50px;
        padding: 6px 18px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        margin-right: 8px;
        backdrop-filter: blur(4px);
    }

    .stat-chip .material-symbols-outlined {
        font-size: 16px;
    }

    .lesson-table-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(102, 126, 234, .10);
    }

    .lesson-table thead th {
        background: #f8f9ff;
        border-bottom: 2px solid #e8eaf6;
        font-size: 0.72rem;
        letter-spacing: .06em;
        color: #7c8db0;
        font-weight: 700;
        padding: 14px 16px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lesson-table tbody tr {
        transition: background .15s;
    }

    .lesson-table tbody tr:hover {
        background: #f5f6ff;
    }

    .lesson-table tbody td {
        padding: 13px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2fb;
    }

    .lesson-title-cell h6 {
        font-size: 0.88rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 2px;
    }

    .lesson-title-cell small {
        color: #a0aec0;
        font-size: 0.76rem;
    }

    .lesson-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea22, #764ba222);
        margin-right: 12px;
        flex-shrink: 0;
    }

    .lesson-icon-wrap .material-symbols-outlined {
        font-size: 20px;
        color: #667eea;
    }

    .diff-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
    }

    .diff-beginner {
        background: #e6f9f0;
        color: #2d9f6c;
    }

    .diff-intermediate {
        background: #fff7e6;
        color: #d08a00;
    }

    .diff-advanced {
        background: #fde8e8;
        color: #c0392b;
    }

    .status-pill {
        font-size: 0.73rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 50px;
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

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all .2s;
        font-size: 0px;
    }

    .action-btn .material-symbols-outlined {
        font-size: 17px;
    }

    .action-btn-view {
        background: #e0f2fe;
        color: #0284c7;
    }

    .action-btn-view:hover {
        background: #0284c7;
        color: #fff;
    }

    .action-btn-edit {
        background: #fef3c7;
        color: #d97706;
    }

    .action-btn-edit:hover {
        background: #d97706;
        color: #fff;
    }

    .action-btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .action-btn-delete:hover {
        background: #dc2626;
        color: #fff;
    }

    .search-filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 18px;
        box-shadow: 0 2px 12px rgba(102, 126, 234, .08);
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap .material-symbols-outlined {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 20px;
    }

    .search-input-wrap input {
        padding-left: 40px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8f9ff;
        font-size: 0.88rem;
    }

    .search-input-wrap input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, .12);
        background: #fff;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state .es-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea22, #764ba222);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .empty-state .es-icon .material-symbols-outlined {
        font-size: 40px;
        color: #667eea;
    }

    .grade-badge {
        background: #ede9fe;
        color: #6d28d9;
        font-size: 0.73rem;
        padding: 3px 10px;
        border-radius: 50px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
@include('admin.layouts.sidebar')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')
    <div class="container-fluid py-4">

        {{-- Hero Header --}}
        <div class="lesson-hero">
            <h4><span class="material-symbols-outlined" style="vertical-align:middle;font-size:28px;margin-right:8px;">menu_book</span>Lesson Management</h4>
            <p>Manage and organize curriculum lessons for AI-powered homework generation</p>
            <div>
                <span class="stat-chip"><span class="material-symbols-outlined">library_books</span>{{ ($lessons ?? collect())->total() ?? ($lessons ?? collect())->count() }} Total Lessons</span>
                <span class="stat-chip"><span class="material-symbols-outlined">check_circle</span>{{ ($lessons ?? collect())->where('status','published')->count() }} Published</span>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="search-filter-bar d-flex align-items-center gap-3 flex-wrap">
            <div class="search-input-wrap flex-grow-1" style="max-width:360px;">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="lessonSearch" class="form-control" placeholder="Search lessons by title or unit…">
            </div>
            <select id="statusFilter" class="form-select" style="width:160px;border-radius:10px;border:1px solid #e2e8f0;font-size:.88rem;background:#f8f9ff;">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
            <select id="gradeFilter" class="form-select" style="width:150px;border-radius:10px;border:1px solid #e2e8f0;font-size:.88rem;background:#f8f9ff;">
                <option value="">All Grades</option>
                @for($g=6;$g<=11;$g++)<option value="{{ $g }}">Grade {{ $g }}</option>@endfor
            </select>
            <div class="ms-auto">
                <a href="{{ route('admin.management.lessons.create') }}" class="btn btn-primary d-flex align-items-center gap-1" style="border-radius:10px;font-weight:600;padding:8px 20px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">add</span> Add Lesson
                </a>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card lesson-table-card">
            <div class="card-body px-0 pt-0 pb-0">
                <div class="table-responsive">
                    <table class="table lesson-table mb-0" id="lessonsTable">
                        <thead>
                            <tr>
                                <th style="padding-left:24px;">Lesson</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Difficulty</th>
                                <th>Unit / Chapter</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lessons ?? [] as $lesson)
                            <tr data-title="{{ strtolower($lesson->title) }}" data-unit="{{ strtolower($lesson->unit) }}" data-status="{{ $lesson->status }}" data-grade="{{ $lesson->grade_level }}">
                                <td style="padding-left:24px;">
                                    <div class="d-flex align-items-center">
                                        <div class="lesson-icon-wrap">
                                            <span class="material-symbols-outlined">menu_book</span>
                                        </div>
                                        <div class="lesson-title-cell">
                                            <h6 class="mb-0">{{ Str::limit($lesson->title, 42) }}</h6>
                                            <small>{{ count($lesson->topics ?? []) }} topics &bull; {{ $lesson->duration_minutes ?? 60 }} min</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-gradient-info" style="font-size:.74rem;">{{ $lesson->subject->subject_name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="grade-badge">Grade {{ $lesson->grade_level }}</span>
                                </td>
                                <td>
                                    <span class="diff-badge diff-{{ $lesson->difficulty ?? 'beginner' }}">{{ ucfirst($lesson->difficulty ?? 'beginner') }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-secondary">{{ Str::limit($lesson->unit, 28) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($lesson->status === 'published')
                                    <span class="status-pill status-published">✓ Published</span>
                                    @elseif($lesson->status === 'draft')
                                    <span class="status-pill status-draft">◷ Draft</span>
                                    @else
                                    <span class="status-pill status-archived">⊘ Archived</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('admin.management.lessons.show', $lesson->lesson_id) }}"
                                            class="action-btn action-btn-view" title="View Lesson">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.management.lessons.edit', $lesson->lesson_id) }}"
                                            class="action-btn action-btn-edit" title="Edit Lesson">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <form action="{{ route('admin.management.lessons.destroy', $lesson->lesson_id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this lesson? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-btn-delete" title="Delete Lesson">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="es-icon">
                                            <span class="material-symbols-outlined">menu_book</span>
                                        </div>
                                        <h6 style="color:#2d3748;font-weight:700;">No Lessons Yet</h6>
                                        <p class="text-muted mb-3" style="font-size:.88rem;">Start building your curriculum by adding the first lesson.</p>
                                        <a href="{{ route('admin.management.lessons.create') }}" class="btn btn-primary" style="border-radius:10px;font-weight:600;">
                                            <span class="material-symbols-outlined" style="font-size:17px;vertical-align:middle;">add</span> Add First Lesson
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($lessons) && method_exists($lessons, 'hasPages') && $lessons->hasPages())
                <div class="d-flex justify-content-center py-4">
                    {{ $lessons->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

</main>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('lessonSearch');
        const statusFilter = document.getElementById('statusFilter');
        const gradeFilter = document.getElementById('gradeFilter');

        function filterTable() {
            const q = searchInput.value.toLowerCase();
            const status = statusFilter.value;
            const grade = gradeFilter.value;
            document.querySelectorAll('#lessonsTable tbody tr[data-title]').forEach(row => {
                const titleMatch = !q || row.dataset.title.includes(q) || row.dataset.unit.includes(q);
                const statusMatch = !status || row.dataset.status === status;
                const gradeMatch = !grade || row.dataset.grade === grade;
                row.style.display = (titleMatch && statusMatch && gradeMatch) ? '' : 'none';
            });
        }
        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        gradeFilter.addEventListener('change', filterTable);
    });
</script>
@endpush
@endsection