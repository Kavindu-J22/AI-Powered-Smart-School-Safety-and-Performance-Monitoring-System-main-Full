@php
    $homework = $submission->homework;
    $isOverdue = $homework->due_date->isPast() && $type === 'pending';
    $daysLeft = now()->diffInDays($homework->due_date, false);
@endphp

<div class="card homework-card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-md bg-gradient-{{ $type === 'graded' ? 'success' : ($type === 'submitted' ? 'info' : 'warning') }} shadow text-center border-radius-lg me-3">
                        <i class="material-symbols-rounded text-white opacity-10">
                            {{ $type === 'graded' ? 'grading' : ($type === 'submitted' ? 'upload_file' : 'assignment') }}
                        </i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $homework->title }}</h6>
                        <p class="text-sm text-secondary mb-0">
                            <span class="badge bg-light text-dark me-1">{{ $homework->subject->subject_name ?? 'N/A' }}</span>
                            <span class="text-muted">{{ $homework->getQuestionCount() }} questions</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                @if($type === 'pending')
                    <p class="mb-0 {{ $isOverdue ? 'due-date-warning' : 'due-date-normal' }}">
                        <i class="material-symbols-rounded align-middle me-1" style="font-size: 16px;">
                            {{ $isOverdue ? 'warning' : 'event' }}
                        </i>
                        @if($isOverdue)
                            Overdue
                        @elseif($daysLeft == 0)
                            Due Today
                        @elseif($daysLeft == 1)
                            Due Tomorrow
                        @else
                            Due in {{ $daysLeft }} days
                        @endif
                    </p>
                    <small class="text-muted">{{ $homework->due_date->format('M d, Y') }}</small>
                @elseif($type === 'submitted')
                    <p class="mb-0 text-info">
                        <i class="material-symbols-rounded align-middle me-1" style="font-size: 16px;">schedule</i>
                        Awaiting Grading
                    </p>
                    <small class="text-muted">Submitted {{ $submission->submitted_at->diffForHumans() }}</small>
                @else
                    <div>
                        <h4 class="mb-0 {{ $submission->percentage >= 50 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($submission->percentage, 1) }}%
                        </h4>
                        <span class="badge bg-{{ $submission->percentage >= 75 ? 'success' : ($submission->percentage >= 50 ? 'warning' : 'danger') }}">
                            Grade: {{ $submission->grade }}
                        </span>
                        @if($submission->is_late)
                            <span class="badge bg-danger ms-1">Late</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="col-md-2 text-end">
                @if($type === 'pending')
                    <a href="{{ route('student.homework.show', $submission->submission_id) }}" class="btn btn-sm btn-primary">
                        <i class="material-symbols-rounded me-1">edit</i>
                        {{ $submission->status === 'in_progress' ? 'Continue' : 'Start' }}
                    </a>
                @elseif($type === 'submitted')
                    <span class="text-muted">
                        <i class="material-symbols-rounded" style="font-size: 24px;">hourglass_top</i>
                    </span>
                @else
                    <a href="{{ route('student.homework.results', $submission->submission_id) }}" class="btn btn-sm btn-outline-success">
                        <i class="material-symbols-rounded me-1">visibility</i>
                        View Results
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

