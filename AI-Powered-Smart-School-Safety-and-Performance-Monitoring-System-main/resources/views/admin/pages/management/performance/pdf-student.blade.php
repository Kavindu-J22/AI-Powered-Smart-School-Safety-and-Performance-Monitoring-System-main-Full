<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Performance Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
    h1 { font-size: 18px; color: #1a237e; margin-bottom: 4px; }
    h2 { font-size: 14px; color: #444; margin: 0 0 16px 0; }
    .header { border-bottom: 3px solid #1a237e; padding-bottom: 10px; margin-bottom: 16px; }
    .meta { display: flex; gap: 30px; margin-bottom: 16px; }
    .meta-item { }
    .meta-item .label { font-size: 9px; text-transform: uppercase; color: #888; font-weight: bold; }
    .meta-item .value { font-size: 13px; font-weight: bold; color: #222; }
    .stats { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .stats td { border: 1px solid #ddd; padding: 8px 12px; font-size: 11px; }
    .stats .stat-label { background: #f5f5f5; font-weight: bold; color: #555; width: 45%; }
    table.main { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.main th { background: #1a237e; color: #fff; font-size: 9px; text-transform: uppercase; padding: 7px 8px; text-align: left; }
    table.main td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
    table.main tr:nth-child(even) td { background: #fafafa; }
    .grade-badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-weight: bold; font-size: 10px; }
    .grade-A { background: #c8e6c9; color: #1b5e20; }
    .grade-B { background: #fff9c4; color: #f57f17; }
    .grade-C { background: #ffe0b2; color: #e65100; }
    .grade-D { background: #ffccbc; color: #bf360c; }
    .grade-F { background: #ffcdd2; color: #b71c1c; }
    .footer { margin-top: 24px; font-size: 9px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 8px; }
    .subject-avg { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .subject-avg th { background: #e8eaf6; color: #333; font-size: 9px; text-transform: uppercase; padding: 6px 10px; text-align: left; }
    .subject-avg td { padding: 5px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
</style>
</head>
<body>

<div class="header">
    <h1>&#127891; Student Performance Report</h1>
    <h2>{{ $student->first_name }} {{ $student->last_name }} &mdash; {{ $student->student_code ?? 'N/A' }}</h2>
</div>

<table class="stats">
    <tr>
        <td class="stat-label">Grade / Class</td>
        <td>Grade {{ $student->grade_level ?? 'N/A' }} &mdash; {{ $student->schoolClass->class_name ?? 'N/A' }}</td>
        <td class="stat-label">Report Date</td>
        <td>{{ now()->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="stat-label">Total Attempted</td>
        <td>{{ $stats['total_submissions'] }}</td>
        <td class="stat-label">Average Score</td>
        <td><strong>{{ $stats['average_score'] }}%</strong> &nbsp;
            @if($avg !== null)
                @php $g = \App\Models\HomeworkSubmission::calculateGrade($avg); $gc = str_starts_with($g,'A') ? 'A' : (str_starts_with($g,'B') ? 'B' : (str_starts_with($g,'C') ? 'C' : (str_starts_with($g,'D') ? 'D' : 'F'))); @endphp
                <span class="grade-badge grade-{{ $gc }}">{{ $g }}</span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="stat-label">Highest Score</td>
        <td>{{ $stats['highest_score'] }}%</td>
        <td class="stat-label">On-Time Rate</td>
        <td>{{ $stats['on_time_rate'] }}%</td>
    </tr>
</table>

@if($subjectAverages->isNotEmpty())
<h3 style="font-size:12px;color:#1a237e;margin-bottom:6px;">Subject Performance Summary</h3>
<table class="subject-avg">
    <thead><tr><th>Subject</th><th>Average Score</th><th>Grade</th></tr></thead>
    <tbody>
        @foreach($subjectAverages as $subjectName => $subAvg)
        @php $g2 = \App\Models\HomeworkSubmission::calculateGrade($subAvg); $gc2 = str_starts_with($g2,'A') ? 'A' : (str_starts_with($g2,'B') ? 'B' : (str_starts_with($g2,'C') ? 'C' : (str_starts_with($g2,'D') ? 'D' : 'F'))); @endphp
        <tr>
            <td>{{ $subjectName }}</td>
            <td>{{ $subAvg }}%</td>
            <td><span class="grade-badge grade-{{ $gc2 }}">{{ $g2 }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<h3 style="font-size:12px;color:#1a237e;margin-bottom:6px;">Assignment Details</h3>
<table class="main">
    <thead>
        <tr>
            <th>#</th>
            <th>Assignment</th>
            <th>Subject</th>
            <th>Marks Obtained</th>
            <th>Percentage</th>
            <th>Grade</th>
            <th>Status</th>
            <th>On Time</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($submissions as $i => $sub)
        @php
            $pct = $sub->percentage ?? 0;
            $gr = $sub->grade ?? ($sub->status === 'graded' ? \App\Models\HomeworkSubmission::calculateGrade($pct) : '—');
            $gc3 = str_starts_with($gr,'A') ? 'A' : (str_starts_with($gr,'B') ? 'B' : (str_starts_with($gr,'C') ? 'C' : (str_starts_with($gr,'D') ? 'D' : 'F')));
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $sub->homework->title ?? '—' }}</td>
            <td>{{ $sub->homework->subject->subject_name ?? '—' }}</td>
            <td>{{ $sub->marks_obtained ?? '—' }} / {{ $sub->homework->total_marks ?? '—' }}</td>
            <td>{{ $sub->status === 'graded' ? number_format($pct, 1).'%' : '—' }}</td>
            <td>{{ $sub->status === 'graded' ? $gr : '—' }}</td>
            <td>{{ ucfirst($sub->status) }}</td>
            <td>{{ $sub->is_late ? 'Late' : 'On Time' }}</td>
            <td>{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;color:#aaa;">No submissions found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generated on {{ now()->format('d M Y, H:i') }} &mdash; School Performance Management System
</div>
</body>
</html>

