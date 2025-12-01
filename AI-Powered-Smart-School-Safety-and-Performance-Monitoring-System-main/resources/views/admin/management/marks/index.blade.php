@extends('admin.layouts.app')

@section('title', 'Marks Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">assessment</i>
                            Marks Management
                        </h3>
                        @if (checkPermission('admin management marks create'))
                            <a href="{{ route('admin.management.marks.create') }}" class="btn btn-primary">
                                <i class="material-icons-outlined me-1">add</i>
                                Add Marks
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.management.marks.index') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="grade" class="form-label">Grade</label>
                                    <select name="grade" id="grade" class="form-select">
                                        <option value="">All Grades</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade }}"
                                                {{ request('grade') == $grade ? 'selected' : '' }}>
                                                Grade {{ $grade }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="academic_year" class="form-label">Academic Year</label>
                                    <select name="academic_year" id="academic_year" class="form-select">
                                        <option value="">All Years</option>
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year }}"
                                                {{ request('academic_year') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="term" class="form-label">Term</label>
                                    <select name="term" id="term" class="form-select">
                                        <option value="">All Terms</option>
                                        @foreach ($terms as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ request('term') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="material-icons-outlined me-1">filter_list</i>
                                        Filter
                                    </button>
                                    <a href="{{ route('admin.management.marks.index') }}" class="btn btn-secondary">
                                        <i class="material-icons-outlined me-1">clear</i>
                                        Clear
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Marks Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Grade</th>
                                        <th>Subject</th>
                                        <th>Academic Year</th>
                                        <th>Term</th>
                                        <th>Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($marks as $mark)
                                        <tr>
                                            <td>{{ $mark->student->student_code }}</td>
                                            <td>{{ $mark->student->full_name }}</td>
                                            <td>Grade {{ $mark->grade_level }}</td>
                                            <td>{{ $mark->subject->subject_name }}</td>
                                            <td>{{ $mark->academic_year }}</td>
                                            <td>Term {{ $mark->term }}</td>
                                            <td>{{ $mark->marks }}/{{ $mark->total_marks }}</td>
                                            <td>
                                                <span
                                                    class="badge
                                                    @if ($mark->percentage >= 75) bg-success
                                                    @elseif($mark->percentage >= 60) bg-primary
                                                    @elseif($mark->percentage >= 50) bg-warning
                                                    @else bg-danger @endif">
                                                    {{ number_format($mark->percentage, 2) }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge
                                                    @if (in_array($mark->grade, ['A+', 'A', 'A-'])) bg-success
                                                    @elseif(in_array($mark->grade, ['B+', 'B', 'B-'])) bg-primary
                                                    @elseif(in_array($mark->grade, ['C+', 'C', 'C-'])) bg-warning
                                                    @else bg-danger @endif">
                                                    {{ $mark->grade }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.management.marks.show', $mark->mark_id) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="material-icons-outlined">visibility</i>
                                                    </a>
                                                    <a href="{{ route('admin.management.marks.edit', $mark->mark_id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="material-icons-outlined">edit</i>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.management.marks.destroy', $mark->mark_id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this mark entry?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="material-icons-outlined">delete</i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <i class="material-icons-outlined"
                                                    style="font-size: 48px; opacity: 0.3;">assignment</i>
                                                <p class="mt-2 text-muted">No marks found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $marks->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .material-icons-outlined {
            vertical-align: middle;
        }
    </style>
@endpush
