@extends('admin.layouts.app')

@section('title', 'Lessons')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Lesson Management</h6>
                    <a href="{{ route('admin.management.lessons.create') }}" class="btn btn-primary btn-sm">
                        <i class="material-symbols-outlined">add</i> Add Lesson
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Subject</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Grade</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Unit</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lessons ?? [] as $lesson)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ Str::limit($lesson->title, 40) }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ count($lesson->topics ?? []) }} topics
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-info">{{ $lesson->subject->subject_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm">Grade {{ $lesson->grade_level }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ Str::limit($lesson->unit, 25) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($lesson->status === 'published')
                                            <span class="badge bg-success">Published</span>
                                        @elseif($lesson->status === 'draft')
                                            <span class="badge bg-warning">Draft</span>
                                        @else
                                            <span class="badge bg-secondary">Archived</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.management.lessons.show', $lesson->lesson_id) }}" 
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="material-symbols-outlined">visibility</i>
                                        </a>
                                        <a href="{{ route('admin.management.lessons.edit', $lesson->lesson_id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="material-symbols-outlined">edit</i>
                                        </a>
                                        <form action="{{ route('admin.management.lessons.destroy', $lesson->lesson_id) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this lesson?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="material-symbols-outlined">delete</i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-2">No lessons found</p>
                                        <a href="{{ route('admin.management.lessons.create') }}" class="btn btn-sm btn-primary">
                                            Add First Lesson
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($lessons) && $lessons->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $lessons->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

