<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Monthly Report - {{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 30px 35px; }
        .header { background: #1a237e; color: white; padding: 20px 25px; border-radius: 6px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin-bottom: 4px; }
        .header p { font-size: 11px; opacity: 0.85; }
        .school-name { font-size: 13px; font-weight: bold; letter-spacing: 0.5px; }
        .section { margin-bottom: 18px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1a237e; border-bottom: 2px solid #1a237e; padding-bottom: 4px; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; border-collapse: collapse; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 35%; font-weight: bold; color: #555; padding: 4px 0; }
        .info-value { display: table-cell; padding: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11px; }
        th { background: #1a237e; color: white; padding: 7px 10px; text-align: left; font-size: 11px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f5f7ff; }
        .grade-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 11px; }
        .grade-A { background: #c8e6c9; color: #1b5e20; }
        .grade-B { background: #bbdefb; color: #0d47a1; }
        .grade-C { background: #fff9c4; color: #f57f17; }
        .grade-D { background: #ffe0b2; color: #e65100; }
        .grade-F { background: #ffcdd2; color: #b71c1c; }
        .stats-row { display: table; width: 100%; margin-bottom: 14px; }
        .stat-cell { display: table-cell; width: 25%; text-align: center; padding: 10px 6px; border: 1px solid #e0e0e0; border-radius: 4px; }
        .stat-value { font-size: 20px; font-weight: bold; color: #1a237e; }
        .stat-label { font-size: 10px; color: #777; margin-top: 2px; }
        .footer { margin-top: 24px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #888; text-align: center; }
        .list-items { list-style: none; padding: 0; }
        .list-items li { padding: 3px 0; padding-left: 14px; position: relative; }
        .list-items li::before { content: "•"; position: absolute; left: 0; color: #1a237e; }
        .two-col { display: table; width: 100%; }
        .col-left { display: table-cell; width: 48%; vertical-align: top; padding-right: 10px; }
        .col-right { display: table-cell; width: 48%; vertical-align: top; padding-left: 10px; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="school-name">Smart School System</div>
        <h1>Monthly Performance Report</h1>
        <p>Period: {{ $report->getReportPeriod() }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y') }}</p>
    </div>

    <!-- Student Info -->
    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Student Name</div>
                <div class="info-value">{{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Student Code</div>
                <div class="info-value">{{ $report->student->student_code ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Grade Level</div>
                <div class="info-value">Grade {{ $report->grade_level }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Academic Year</div>
                <div class="info-value">{{ $report->academic_year }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Class Rank</div>
                <div class="info-value">{{ $report->getRankText() }}</div>
            </div>
        </div>
    </div>

    <!-- Overall Stats -->
    <div class="section">
        <div class="section-title">Overall Performance</div>
        <div class="stats-row">
            <div class="stat-cell">
                <div class="stat-value">{{ number_format($report->overall_average, 1) }}%</div>
                <div class="stat-label">Overall Average</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value">
                    @php $g = strtoupper($report->overall_grade ?? 'F'); $gc = str_starts_with($g,'A') ? 'A' : (str_starts_with($g,'B') ? 'B' : (str_starts_with($g,'C') ? 'C' : (str_starts_with($g,'D') ? 'D' : 'F'))); @endphp
                    <span class="grade-badge grade-{{ $gc }}">{{ $report->overall_grade }}</span>
                </div>
                <div class="stat-label">Overall Grade</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value">{{ $report->homework_completed ?? 0 }}/{{ $report->total_homework_assigned ?? 0 }}</div>
                <div class="stat-label">Assignments Done</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value">{{ number_format($report->completion_rate ?? 0, 1) }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
    </div>

    <!-- Subject Performance -->
    @if($report->subject_performance && count($report->subject_performance) > 0)
    <div class="section">
        <div class="section-title">Subject Performance Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Average Score</th>
                    <th>Grade</th>
                    <th>Assignments</th>
                    <th>Completed</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->subject_performance as $subject => $data)
                <tr>
                    <td><strong>{{ $subject }}</strong></td>
                    <td>{{ number_format($data['average'] ?? 0, 1) }}%</td>
                    <td>
                        @php $sg = strtoupper($data['grade'] ?? 'F'); $sgc = str_starts_with($sg,'A') ? 'A' : (str_starts_with($sg,'B') ? 'B' : (str_starts_with($sg,'C') ? 'C' : (str_starts_with($sg,'D') ? 'D' : 'F'))); @endphp
                        <span class="grade-badge grade-{{ $sgc }}">{{ $data['grade'] ?? 'N/A' }}</span>
                    </td>
                    <td>{{ $data['assigned'] ?? 0 }}</td>
                    <td>{{ $data['completed'] ?? 0 }}</td>
                    <td>{{ ucfirst($data['trend'] ?? 'stable') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Strengths & Improvements -->
    <div class="two-col">
        @if($report->strengths && count($report->strengths) > 0)
        <div class="col-left">
            <div class="section">
                <div class="section-title">Strengths</div>
                <ul class="list-items">
                    @foreach($report->strengths as $strength)
                    <li>{{ $strength }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        @if($report->areas_for_improvement && count($report->areas_for_improvement) > 0)
        <div class="col-right">
            <div class="section">
                <div class="section-title">Areas for Improvement</div>
                <ul class="list-items">
                    @foreach($report->areas_for_improvement as $area)
                    <li>{{ $area }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    @if($report->recommendations && count($report->recommendations) > 0)
    <div class="section">
        <div class="section-title">Teacher Recommendations</div>
        <ul class="list-items">
            @foreach($report->recommendations as $rec)
            <li>{{ $rec }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="footer">
        This report was automatically generated by the Smart School Safety &amp; Performance Monitoring System.
        &nbsp;&bull;&nbsp; {{ $report->getReportPeriod() }} &nbsp;&bull;&nbsp; Confidential
    </div>
</div>
</body>
</html>

