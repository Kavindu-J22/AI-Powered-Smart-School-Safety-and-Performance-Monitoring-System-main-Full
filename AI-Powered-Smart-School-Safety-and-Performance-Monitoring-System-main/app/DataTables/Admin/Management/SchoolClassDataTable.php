<?php

namespace App\DataTables\Admin\Management;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SchoolClassDataTable extends DataTable
{
    protected $model = 'classes';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $dropdownItems = [];

                if (checkPermission('admin.management.classes.show')) {
                    $dropdownItems[] = view('admin.layouts.actions.show', [
                        'url' => route('admin.management.' . $this->model . '.show', ['id' => $row->id]),
                        'id' => $row->id,
                    ])->render();
                }

                if (checkPermission('admin.management.classes.edit')) {
                    $dropdownItems[] = view('admin.layouts.actions.edit', [
                        'url' => route('admin.management.' . $this->model . '.form', ['id' => $row->id]),
                        'id' => $row->id,
                    ])->render();
                }

                if (checkPermission('admin.management.classes.delete')) {
                    $dropdownItems[] = view('admin.layouts.actions.delete', [
                        'url' => route('admin.management.' . $this->model . '.delete', ['id' => $row->id]),
                        'id' => $row->id,
                    ])->render();
                }

                if (empty($dropdownItems)) {
                    return '<span class="text-muted">No actions</span>';
                }

                $dropdownContent = implode('<li><hr class="dropdown-divider"></li>', $dropdownItems);

                return '
                <div class="dropdown text-end">
                    <button class="btn btn-icon border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-outlined text-lg">more_vert</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2 w-100 ">
                        '.$dropdownContent.'
                    </ul>
                </div>';
            })
            ->addColumn('name', function ($row) {
                $gradeColors = [
                    1 => 'bg-gradient-primary',
                    2 => 'bg-gradient-info',
                    3 => 'bg-gradient-success',
                    4 => 'bg-gradient-warning',
                    5 => 'bg-gradient-danger',
                    6 => 'bg-gradient-dark',
                    7 => 'bg-gradient-primary',
                    8 => 'bg-gradient-info',
                    9 => 'bg-gradient-success',
                    10 => 'bg-gradient-warning',
                    11 => 'bg-gradient-danger',
                    12 => 'bg-gradient-dark',
                    13 => 'bg-gradient-primary',
                ];

                $gradeColor = $gradeColors[$row->grade_level] ?? 'bg-gradient-secondary';

                return '<div class="d-flex align-items-center">
                    <span class="badge '.$gradeColor.' badge-sm me-2">Grade '.$row->grade_level.'</span>
                    <span class="fw-bold">'.$row->class_name.'</span>
                </div>';
            })
            ->addColumn('class_code', function ($row) {
                return '<span class="text-secondary fw-bold">'.$row->class_code.'</span>';
            })
            ->addColumn('class_teacher', function ($row) {
                return $row->classTeacher ? 
                    '<div class="d-flex align-items-center">
                        <span class="badge bg-gradient-success badge-sm me-2">CT</span>
                        <span>' . $row->classTeacher->full_name . '</span>
                    </div>' : 
                    '<span class="text-muted">No class teacher</span>';
            })
            ->addColumn('students_count', function ($row) {
                $count = $row->students->count();
                $color = $count > 30 ? 'warning' : ($count > 0 ? 'info' : 'secondary');

                return '<span class="badge bg-gradient-'.$color.' badge-sm">'.$count.' student'.($count != 1 ? 's' : '').'</span>';
            })
            ->addColumn('subjects_count', function ($row) {
                $count = $row->subjects->count();
                $color = $count > 0 ? 'success' : 'secondary';

                return '<span class="badge bg-gradient-'.$color.' badge-sm">'.$count.' subject'.($count != 1 ? 's' : '').'</span>';
            })
            ->addColumn('room_number', function ($row) {
                return $row->room_number ? 
                    '<span class="badge bg-gradient-primary badge-sm">Room ' . $row->room_number . '</span>' : 
                    '<span class="text-muted">No room</span>';
            })
            ->addColumn('capacity', function ($row) {
                if (! $row->capacity) {
                    return '<span class="text-muted">Not set</span>';
                }
                $studentsCount = $row->students->count();
                $percentage = ($studentsCount / $row->capacity) * 100;
                $color = $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
                $text = $percentage >= 100 ? 'Full' : ($percentage >= 80 ? 'Almost Full' : 'Available');
                return '<div class="text-center">
                    <span class="badge bg-gradient-'.$color.' badge-sm">'.$text.'</span>
                    <small class="d-block text-muted">'.$studentsCount.'/'.$row->capacity.'</small>
                </div>';
            })
            ->addColumn('status', function ($row) {
                $color = $row->is_active ? 'success' : 'danger';
                $text = $row->is_active ? 'Active' : 'Inactive';

                return '<span class="badge badge-sm bg-gradient-'.$color.' me-1">'.$text.'</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : 'Never';
            })
            ->rawColumns(['action', 'name', 'class_code', 'class_teacher', 'students_count', 'subjects_count', 'room_number', 'capacity', 'status', 'modified'])
            ->orderColumn('name', function ($query, $order) {
                return $query->orderBy('grade_level', $order)->orderBy('class_name', $order);
            })
            ->orderColumn('modified', function ($query, $order) {
                return $query->orderBy('updated_at', $order);
            });
    }

    public function query(SchoolClass $model): QueryBuilder
    {
        return $model->with(['classTeacher', 'students', 'subjects'])
            ->orderBy('grade_level', 'asc')
            ->orderBy('class_name', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('class-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'drawCallback' => 'function(settings) {
                    $("#class-table_wrapper .dt-layout-table").css({
                        "overflow": "hidden",
                        "overflow-x": "auto"
                    });
                }',
                'initComplete' => 'function(settings, json) {
                    $("#class-table_wrapper .dt-layout-table").css({
                        "overflow": "hidden",
                        "overflow-x": "auto"
                    });
                }',
                'rowCallback' => 'function(row, data, index) {
                    if (index % 2 === 0) {
                        $(row).css("background-color", "rgba(0, 0, 0, 0.05)");
                    }
                }',
            ]);
    }

    protected function getColumns(): array
    {
        $columns = [
            Column::make('id')->title('#')->addClass('text-start align-middle text-xs'),
            Column::make('class_code')->title('CODE')->addClass('align-middle text-xs')->searchable(true),
            Column::make('name')->title('CLASS')->addClass('align-middle text-xs')->searchable(true),
            Column::make('class_teacher')->title('CLASS TEACHER')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('students_count')->title('STUDENTS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('subjects_count')->title('SUBJECTS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('room_number')->title('ROOM')->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('capacity')->title('CAPACITY')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('status')->title('STATUS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('modified')->title('MODIFIED')->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.classes.show') ||
            checkPermission('admin.management.classes.form') ||
            checkPermission('admin.management.classes.edit') ||
            checkPermission('admin.management.classes.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-end align-middle pt-3 pb-0 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'SchoolClass_'.date('YmdHis');
    }
}