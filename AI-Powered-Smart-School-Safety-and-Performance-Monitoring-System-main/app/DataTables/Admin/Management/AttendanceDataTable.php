<?php

namespace App\DataTables\Admin\Management;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AttendanceDataTable extends DataTable
{
    protected $model = 'attendances';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('attendance_id', function ($row) {
                return $row->attendance_id;
            })
            ->addColumn('student_info', function ($row) {
                $student = $row->student;
                if (!$student) return 'N/A';

                return '<div class="d-flex flex-column">
                    <h6 class="mb-0 text-sm">' . e($student->full_name) . '</h6>
                    <p class="text-xs text-secondary mb-0">' . e($student->student_code) . '</p>
                </div>';
            })
            ->addColumn('class_info', function ($row) {
                $student = $row->student;
                if (!$student) return 'N/A';

                $className = $student->schoolClass->class_name ?? 'N/A';
                return '<div class="d-flex flex-column">
                    <p class="text-xs font-weight-bold mb-0">' . e($className) . '</p>
                    <p class="text-xs text-secondary mb-0">Grade ' . e($student->grade_level) . '</p>
                </div>';
            })
            ->editColumn('attendance_date', function ($row) {
                return $row->attendance_date ? $row->attendance_date->format('M d, Y') : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'present' => '<span class="badge badge-sm bg-gradient-success">Present</span>',
                    'absent' => '<span class="badge badge-sm bg-gradient-danger">Absent</span>',
                    'late' => '<span class="badge badge-sm bg-gradient-warning">Late</span>',
                    'excused' => '<span class="badge badge-sm bg-gradient-info">Excused</span>',
                ];

                $badge = $badges[$row->status] ?? '<span class="badge badge-sm bg-gradient-secondary">' . ucfirst($row->status) . '</span>';

                if ($row->is_late) {
                    $badge .= '<br><small class="text-warning">Late Arrival</small>';
                }

                return $badge;
            })
            ->editColumn('check_in_time', function ($row) {
                return $row->check_in_time ? $row->check_in_time->format('h:i A') : '-';
            })
            ->editColumn('check_out_time', function ($row) {
                return $row->check_out_time ? $row->check_out_time->format('h:i A') : '-';
            })
            ->addColumn('duration_display', function ($row) {
                return $row->duration ?? '-';
            })
            ->addColumn('device_badge', function ($row) {
                if ($row->device_id === 'nfc') {
                    return '<span class="badge badge-sm bg-gradient-primary">NFC</span>';
                } elseif ($row->device_id === 'manual') {
                    return '<span class="badge badge-sm bg-gradient-secondary">Manual</span>';
                } else {
                    return '<span class="badge badge-sm bg-gradient-info">' . e($row->device_id ?? 'Unknown') . '</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $items = [];

                if (checkPermission('admin.management.attendance.show')) {
                    $items[] = '<a href="' . route('admin.management.attendance.show', ['attendance' => $row->attendance_id]) . '"
                        class="dropdown-item border-radius-md">
                        <i class="material-symbols-rounded text-sm me-2">visibility</i>View Details
                    </a>';
                }

                if (checkPermission('admin.management.attendance.edit')) {
                    $items[] = '<a href="' . route('admin.management.attendance.edit', ['attendance' => $row->attendance_id]) . '"
                        class="dropdown-item border-radius-md">
                        <i class="material-symbols-rounded text-sm me-2">edit</i>Edit
                    </a>';
                }

                if (checkPermission('admin.management.attendance.delete')) {
                    $items[] = '<form action="' . route('admin.management.attendance.destroy', ['attendance' => $row->attendance_id]) . '"
                        method="POST" class="d-inline delete-form">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item border-radius-md text-danger delete-btn">
                            <i class="material-symbols-rounded text-sm me-2">delete</i>Delete
                        </button>
                    </form>';
                }

                if (empty($items)) {
                    return '<span class="text-muted">No actions</span>';
                }

                $content = implode('<li><hr class="dropdown-divider"></li>', $items);

                return '<div class="dropdown text-end">
                    <button class="btn btn-icon border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-rounded text-lg">more_vert</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2 w-100">'
                    . $content .
                    '</ul>
                </div>';
            })
            ->rawColumns(['student_info', 'class_info', 'status_badge', 'device_badge', 'action'])
            ->orderColumn('attendance_date', function ($query, $order) {
                $query->orderBy('attendance_date', $order);
            })
            ->orderColumn('check_in_time', function ($query, $order) {
                $query->orderBy('check_in_time', $order);
            });
    }

    public function query(Attendance $model): QueryBuilder
    {
        return $model->with(['student.schoolClass'])
            ->select('attendance.*')
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('attendance-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2, 'desc') // Order by date descending
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'responsive' => true,
                'deferRender' => true, // Defer rendering to improve initial load
                'processing' => true, // Show loading indicator
                'rowCallback' => 'function(row, data, index) { if (index % 2 === 0) { $(row).css("background-color", "rgba(0, 0, 0, 0.02)"); } }',
            ]);
    }

    protected function getColumns(): array
    {
        $columns = [
            Column::make('attendance_id')->title('#')->addClass('text-start align-middle text-xs')->width(50),
            Column::make('student_info')->title('STUDENT')->addClass('text-start align-middle text-xs')->searchable(false),
            Column::make('class_info')->title('CLASS')->addClass('text-start align-middle text-xs')->searchable(false),
            Column::make('attendance_date')->title('DATE')->addClass('text-center align-middle text-xs'),
            Column::make('status_badge')->title('STATUS')->addClass('text-center align-middle text-xs')->searchable(false),
            Column::make('check_in_time')->title('CHECK IN')->addClass('text-center align-middle text-xs'),
            Column::make('check_out_time')->title('CHECK OUT')->addClass('text-center align-middle text-xs'),
            Column::make('duration_display')->title('DURATION')->addClass('text-center align-middle text-xs')->searchable(false),
            Column::make('device_badge')->title('DEVICE')->addClass('text-center align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.attendance.show') ||
            checkPermission('admin.management.attendance.edit') ||
            checkPermission('admin.management.attendance.delete')
        ) {
            $columns[] = Column::computed('action')
                ->title('ACTIONS')
                ->addClass('text-end align-middle py-2 text-xs')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(80);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Attendance_' . date('YmdHis');
    }
}
