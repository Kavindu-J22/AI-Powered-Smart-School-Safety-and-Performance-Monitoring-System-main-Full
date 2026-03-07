<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Class Monthly Report - {{ $class->class_name ?? '' }} - {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #333; background: #fff; }
        .page { padding: 28px 32px; }
        .header { background: #1a237e; color: white; padding: 18px 22px; border-radius: 6px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; margin-bottom: 3px; }
        .header p  { font-size: 10px; opacity: 0.85; }
        .school-name { font-size: 12px; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 4px; }
        .section-title { font-size: 12px; font-weight: bold; color: #1a237e; border-bottom: 2px solid #1a237e; padding-bottom: 3px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 18px; }
        th { background: #1a237e; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        tr:nth-child(even) td { background: #f5f7ff; }
        .grade-badge { display: inline-block; padding: 1px 7px; border-radius: 10px; font-weight: bold; font-size: 10px; }
        .grade-A { background: #c8e6c9; color: #1b5e20; }
        .grade-B { background: #bbdefb; color: #0d47a1; }
        .grade-C { background: #fff9c4; color: #f57f17; }
        .grade-D { background: #ffe0b2; color: #e65100; }
        .grade-F { background: #ffcdd2; color: #b71c1c; }
        .rank-cell { font-weight: bold; text-align: center; }
        .summary-box { background: #f5f7ff; border: 1px solid #c5cae9; border-radius: 4px; padding: 10px 14px; margin-bottom: 14px; }
        .summary-grid { display: table; width: 100%; }
        .summary-col  { display: table-cell; width: 25%; text-align: center; padding: 4px; }
        .summary-val  { font-size: 18px; font-weight: bold; color: #1a237e; }
        .summary-lbl  { font-size: 9px; color: #777; }
        .page-break   { page-break-after: always; }
        .student-section { margin-bottom: 24px; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px; }
        .student-header { background: #283593; color: white; padding: 8px 12px; border-radius: 3px; margin-bottom: 10px; }
        .student-header h3 { font-size: 13px; margin: 0; }
        .student-header p  { font-size: 10px; opacity: 0.85; margin: 0; }
        .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="school-name">Smart School System</div>
        <h1>Class Monthly Performance Report</h1>
        <p>
            Class: {{ $class->class_name ?? 'N/A' }}
            &nbsp;|&nbsp; Period: {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
            &nbsp;|&nbsp; Generated: {{ now()->format('d M Y') }}
        </p>
    </div>

    @php
        $totalStudents = $reports->count();
        $classAvg      = $totalStudents > 0 ? round($reports->avg('overall_average'), 1) : 0;
        $passCount     = $reports->filter(fn($r) => ($r->overall_average ?? 0) >= 40)->count();
        $passRate      = $totalStudents > 0 ? round($passCount / $totalStudents * 100, 1) : 0;
    @endphp

    <!-- Class Summary -->
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-col">
                <div class="summary-val">{{ $totalStudents }}</div>
                <div class="summary-lbl">Total Students</div>
            </div>
            <div class="summary-col">
                <div class="summary-val">{{ $classAvg }}%</div>
                <div class="summary-lbl">Class Average</div>
            </div>
            <div class="summary-col">
                <div class="summary-val">{{ $passRate }}%</div>
                <div class="summary-lbl">Pass Rate</div>
            </div>
            <div class="summary-col">
                <div class="summary-val">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</div>
                <div class="summary-lbl">Report Period</div>
            </div>
        </div>
    </div>

    <!-- Class Ranking Table -->
    <div class="section-title">Class Ranking Overview</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%;">Rank</th>
                <th style="width:25%;">Student Name</th>
                <th style="width:15%;">Student Code</th>
                <th style="width:10%; text-align:center;">Average</th>
                <th style="width:8%; text-align:center;">Grade</th>
                <th style="width:12%; text-align:center;">Assignments</th>
                <th style="width:12%; text-align:center;">Completed</th>
                <th style="width:13%; text-align:center;">Completion %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $i => $report)
            @php
                $gc = strtoupper(substr($report->overall_grade ?? 'F', 0, 1));
                $gc = in_array($gc, ['A','B','C','D']) ? $gc : 'F';
            @endphp
            <tr>
                <td class="rank-cell">{{ $i + 1 }}</td>
                <td>{{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}</td>
                <td>{{ $report->student->student_code ?? 'N/A' }}</td>
                <td style="text-align:center;">{{ number_format($report->overall_average, 1) }}%</td>
                <td style="text-align:center;">
                    <span class="grade-badge grade-{{ $gc }}">{{ $report->overall_grade ?? 'N/A' }}</span>
                </td>
                <td style="text-align:center;">{{ $report->total_homework_assigned ?? 0 }}</td>
                <td style="text-align:center;">{{ $report->homework_completed ?? 0 }}</td>
                <td style="text-align:center;">{{ number_format($report->completion_rate ?? 0, 1) }}%</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center; padding:14px; color:#888;">No reports found for this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Individual Student Sections -->
    <div class="section-title">Individual Student Details</div>

    @foreach($reports as $i => $report)
    @if($i > 0 && $i % 3 === 0)<div class="page-break"></div>@endif
    <div class="student-section">
        <div class="student-header">
            <h3>{{ $i + 1 }}. {{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}
                &nbsp;&mdash;&nbsp; Grade: {{ $report->overall_grade ?? 'N/A' }}
                &nbsp;({{ number_format($report->overall_average, 1) }}%)</h3>
            <p>Code: {{ $report->student->student_code ?? 'N/A' }}
               &nbsp;|&nbsp; Assignments: {{ $report->homework_completed ?? 0 }}/{{ $report->total_homework_assigned ?? 0 }}
               &nbsp;|&nbsp; Completion: {{ number_format($report->completion_rate ?? 0, 1) }}%</p>
        </div>

        @if($report->subject_performance && count($report->subject_performance) > 0)
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th style="text-align:center;">Average</th>
                    <th style="text-align:center;">Grade</th>
                    <th style="text-align:center;">Assigned</th>
                    <th style="text-align:center;">Completed</th>
                    <th style="text-align:center;">Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->subject_performance as $subject => $data)
                @php $sg = strtoupper(substr($data['grade'] ?? 'F', 0, 1)); $sg = in_array($sg, ['A','B','C','D']) ? $sg : 'F'; @endphp
                <tr>
                    <td>{{ $subject }}</td>
                    <td style="text-align:center;">{{ number_format($data['average'] ?? 0, 1) }}%</td>
                    <td style="text-align:center;"><span class="grade-badge grade-{{ $sg }}">{{ $data['grade'] ?? 'N/A' }}</span></td>
                    <td style="text-align:center;">{{ $data['assigned'] ?? 0 }}</td>
                    <td style="text-align:center;">{{ $data['completed'] ?? 0 }}</td>
                    <td style="text-align:center;">{{ ucfirst($data['trend'] ?? 'stable') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color:#888; font-size:10px; padding:6px 0;">No subject breakdown available.</p>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Class Monthly Report &bull; {{ $class->class_name ?? '' }} &bull; {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}
        &bull; Smart School Safety &amp; Performance Monitoring System &bull; Confidential
    </div>
</div>
</body>
</html>

