<?php

namespace App\DataTables\Admin\Management;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StudentDataTable extends DataTable
{
    protected $model = 'students';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $show = checkPermission('admin.management.students.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.management.' . $this->model . '.show', ['id' => $row->student_id]),
                    'id' => $row->student_id,
                ])->render() : '';

                $edit = checkPermission('admin.management.students.edit') ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.management.' . $this->model . '.form', ['id' => $row->student_id]),
                    'id' => $row->student_id,
                ])->render() : '';

                $delete = checkPermission('admin.management.students.delete') ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.management.' . $this->model . '.delete', ['id' => $row->student_id]),
                    'id' => $row->student_id,
                ])->render() : '';

                $dropdownItems = [];

                if ($show) {
                    $dropdownItems[] = $show;
                }

                if ($edit) {
                    $dropdownItems[] = $edit;
                }

                if ($delete) {
                    $dropdownItems[] = $delete;
                }

                if (empty($dropdownItems)) {
                    return '<span class="text-muted">' . __('common.no_actions') . '</span>';
                }

                $dropdownContent = implode('<li><hr class="dropdown-divider"></li>', $dropdownItems);

                $dropdown = '
                <div class="dropdown text-end">
                    <button class="btn btn-icon border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-outlined text-lg">more_vert</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2 w-100 text-center">
                        '.$dropdownContent.'
                    </ul>
                </div>';

                return $dropdown;
            })
            ->addColumn('name', function ($row) {
                return '<div class="d-flex align-items-center">
                    <span class="badge bg-gradient-success badge-sm me-2">STU</span>
                    <span class="fw-bold">'.$row->full_name.'</span>
                </div>';
            })
            ->addColumn('student_code', function ($row) {
                return '<span class="text-secondary">'.$row->student_code.'</span>';
            })
            ->addColumn('grade_level', function ($row) {
                return '<span class="badge bg-gradient-primary badge-sm">' . __('common.grade') . ' ' . $row->grade_level . '</span>';
            })
            ->addColumn('class', function ($row) {
                if ($row->schoolClass) {
                    return '<span class="text-primary fw-medium">'.$row->schoolClass->full_name.'</span>';
                }

                return '<span class="text-muted">' . __('common.no_class_assigned') . '</span>';
            })
            ->addColumn('email', function ($row) {
                return $row->user ? $row->user->email : '<span class="text-muted">' . __('common.no_email') . '</span>';
            })
            ->addColumn('parents', function ($row) {
                if ($row->parents->count() === 0) {
                    return '<span class="text-muted">' . __('common.no_parents') . '</span>';
                }

                $parentNames = $row->parents->take(2)->pluck('full_name')->toArray();
                $display = implode(', ', $parentNames);

                if ($row->parents->count() > 2) {
                    $display .= ' <span class="text-primary">+' . ($row->parents->count() - 2) . ' ' . __('common.more') . '</span>';
                }

                return $display;
            })
            ->addColumn('status', function ($row) {
                $color = $row->is_active ? 'success' : 'danger';
                $text = $row->is_active ? __('common.active') : __('common.inactive');

                return '<span class="badge badge-sm bg-gradient-' . $color . ' me-1">' . $text . '</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : __('common.never');
            })
            ->rawColumns(['action', 'name', 'student_code', 'grade_level', 'class', 'email', 'parents', 'status', 'modified'])
            ->orderColumn('name', function ($query, $order) {
                return $query->orderBy('first_name', $order)->orderBy('last_name', $order);
            })
            ->orderColumn('modified', function ($query, $order) {
                return $query->orderBy('updated_at', $order);
            });
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Student $model): QueryBuilder
    {
        return $model->with(['user', 'schoolClass', 'parents'])->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('student-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'drawCallback' => 'function(settings) {
                    // Add horizontal scroll styles
                    $("#student-table_wrapper .dt-layout-table").css({
                        "overflow": "hidden",
                        "overflow-x": "auto"
                    });
                }',
                'initComplete' => 'function(settings, json) {
                    // Apply horizontal scroll on initialization
                    $("#student-table_wrapper .dt-layout-table").css({
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
            Column::make('student_id')->title(__('common.id'))->addClass('text-start align-middle text-xs'),
            Column::make('student_code')->title(__('common.code'))->addClass('align-middle text-xs')->searchable(true),
            Column::make('name')->title(__('common.name'))->addClass('align-middle text-xs')->searchable(true),
            Column::make('grade_level')->title(__('common.grade'))->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('class')->title(__('common.class'))->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('email')->title(__('common.email'))->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('parents')->title(__('common.parents'))->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('status')->title(__('common.status'))->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('modified')->title(__('common.modified'))->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.students.show') ||
            checkPermission('admin.management.students.form') ||
            checkPermission('admin.management.students.edit') ||
            checkPermission('admin.management.students.delete')
        ) {
            $columns[] = Column::computed('action')->title(__('common.actions'))->addClass('text-start align-middle text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Student_'.date('YmdHis');
    }
}
