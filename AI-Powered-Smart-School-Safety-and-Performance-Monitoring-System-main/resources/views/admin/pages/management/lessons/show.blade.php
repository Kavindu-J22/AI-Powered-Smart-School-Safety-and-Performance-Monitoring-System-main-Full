@extends('admin.layouts.app')

@section('title', 'View Lesson')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Lesson Details</h6>
                        <div>
                            <a href="{{ route('admin.management.lessons.edit', $lesson->lesson_id) }}" class="btn btn-warning btn-sm">
                                <i class="material-symbols-outlined">edit</i> Edit
                            </a>
                            <a href="{{ route('admin.management.lessons.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="material-symbols-outlined">arrow_back</i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>{{ $lesson->title }}</h4>
                            <p class="text-muted">
                                <span class="badge bg-info">{{ $lesson->subject->subject_name ?? 'N/A' }}</span>
                                <span class="badge bg-secondary">Grade {{ $lesson->grade_level }}</span>
                                <span class="badge bg-{{ $lesson->difficulty === 'beginner' ? 'success' : ($lesson->difficulty === 'intermediate' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($lesson->difficulty) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($lesson->status === 'published')
                                <span class="badge bg-success fs-6">Published</span>
                            @elseif($lesson->status === 'draft')
                                <span class="badge bg-warning fs-6">Draft</span>
                            @else
                                <span class="badge bg-secondary fs-6">Archived</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Unit/Chapter:</strong> {{ $lesson->unit }}
                        </div>
                        <div class="col-md-6">
                            <strong>Duration:</strong> {{ $lesson->duration_minutes ?? 60 }} minutes
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <strong>Topics Covered:</strong>
                            <div class="mt-2">
                                @forelse($lesson->topics ?? [] as $topic)
                                    <span class="badge bg-primary me-1">{{ $topic }}</span>
                                @empty
                                    <span class="text-muted">No topics defined</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <strong>Learning Outcomes:</strong>
                            <ul class="mt-2">
                                @forelse($lesson->learning_outcomes ?? [] as $outcome)
                                    <li>{{ $outcome }}</li>
                                @empty
                                    <li class="text-muted">No learning outcomes defined</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <strong>Lesson Content:</strong>
                            <div class="card bg-light mt-2">
                                <div class="card-body">
                                    {!! nl2br(e($lesson->content)) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Teacher:</strong> {{ $lesson->teacher->first_name ?? '' }} {{ $lesson->teacher->last_name ?? '' }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <strong>Created:</strong> {{ $lesson->created_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>

                    <!-- Related Homework -->
                    @if($lesson->homework && $lesson->homework->count() > 0)
                    <hr>
                    <h6>Related Homework ({{ $lesson->homework->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lesson->homework as $hw)
                                <tr>
                                    <td>{{ $hw->title }}</td>
                                    <td>{{ $hw->due_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-{{ $hw->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($hw->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

