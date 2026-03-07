<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Performance Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 18px; }
    h1 { font-size: 17px; color: #1a237e; margin-bottom: 4px; }
    .header { border-bottom: 3px solid #1a237e; padding-bottom: 8px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-right { text-align: right; font-size: 9px; color: #888; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    thead th { background: #1a237e; color: #fff; font-size: 8.5px; text-transform: uppercase; padding: 6px 8px; text-align: left; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9.5px; }
    tbody tr:nth-child(even) td { background: #f7f7ff; }
    .grade-badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-weight: bold; font-size: 9px; }
    .grade-A { background: #c8e6c9; color: #1b5e20; }
    .grade-B { background: #fff9c4; color: #f57f17; }
    .grade-C { background: #ffe0b2; color: #e65100; }
    .grade-D { background: #ffccbc; color: #bf360c; }
    .grade-F { background: #ffcdd2; color: #b71c1c; }
    .grade-NA { background: #eeeeee; color: #888; }
    .footer { margin-top: 14px; font-size: 8px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 6px; }
    .summary-row td { background: #e8eaf6 !important; font-weight: bold; font-size: 9px; }
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>&#128202; Student Performance Report</h1>
        <div style="font-size:10px;color:#555;">Filter: <strong>{{ $filterLabel }}</strong></div>
    </div>
    <div class="header-right">
        Generated: {{ now()->format('d M Y, H:i') }}<br>
        Total Students: <strong>{{ $studentPerformanceData->count() }}</strong>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Student Code</th>
            <th>Grade</th>
            <th>Class</th>
            <th style="text-align:center;">Attempted</th>
            <th style="text-align:center;">Avg Score</th>
            <th style="text-align:center;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @forelse($studentPerformanceData as $i => $row)
        @php
            $avg = $row['average_score'];
            $gr  = $row['grade'];
            $gc  = $avg === null ? 'NA' : (str_starts_with($gr,'A') ? 'A' : (str_starts_with($gr,'B') ? 'B' : (str_starts_with($gr,'C') ? 'C' : (str_starts_with($gr,'D') ? 'D' : 'F'))));
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['student']->first_name }} {{ $row['student']->last_name }}</td>
            <td>{{ $row['student']->student_code ?? '—' }}</td>
            <td>{{ $row['student']->grade_level ?? '—' }}</td>
            <td>{{ $row['student']->schoolClass->class_name ?? '—' }}</td>
            <td style="text-align:center;">{{ $row['total_attempted'] }}</td>
            <td style="text-align:center;">{{ $avg !== null ? number_format($avg, 1).'%' : '—' }}</td>
            <td style="text-align:center;"><span class="grade-badge grade-{{ $gc }}">{{ $gr }}</span></td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#aaa;padding:12px;">No student data available.</td></tr>
        @endforelse
    </tbody>
    @if($studentPerformanceData->count() > 0)
    @php
        $allScores  = $studentPerformanceData->pluck('average_score')->filter();
        $totalAttempted = $studentPerformanceData->sum('total_attempted');
        $overallAvg = $allScores->isNotEmpty() ? number_format($allScores->avg(), 1) : '—';
    @endphp
    <tfoot>
        <tr class="summary-row">
            <td colspan="5" style="text-align:right;">TOTALS / AVERAGE:</td>
            <td style="text-align:center;">{{ $totalAttempted }}</td>
            <td style="text-align:center;">{{ $overallAvg }}{{ $allScores->isNotEmpty() ? '%' : '' }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">
    School Performance Management System &mdash; Report generated on {{ now()->format('d M Y') }}
</div>
</body>
</html>

