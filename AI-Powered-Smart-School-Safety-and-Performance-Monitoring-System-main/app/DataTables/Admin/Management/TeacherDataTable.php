<?php

namespace App\DataTables\Admin\Management;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TeacherDataTable extends DataTable
{
    protected $model = 'teachers';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('DT_RowIndex', function ($row) {
                static $index = 0;

                return ++$index;
            })
            ->addColumn('action', function ($row) {
                $show = checkPermission('admin.management.teachers.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.management.' . $this->model . '.show', ['id' => $row->teacher_id]),
                    'id' => $row->teacher_id,
                ])->render() : '';

                $edit = checkPermission('admin.management.teachers.edit') ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.management.' . $this->model . '.form', ['id' => $row->teacher_id]),
                    'id' => $row->teacher_id,
                ])->render() : '';

                $delete = checkPermission('admin.management.teachers.delete') ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.management.' . $this->model . '.delete', ['id' => $row->teacher_id]),
                    'id' => $row->teacher_id,
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
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2 w-100">
                        ' . $dropdownContent . '
                    </ul>
                </div>';

                return $dropdown;
            })
            ->addColumn('name', function ($row) {
                // Map teaching level to badge type
                $teachingLevelBadges = [
                    'Primary' => ['text' => 'PRI', 'class' => 'bg-gradient-success'],
                    'Secondary' => ['text' => 'SEC', 'class' => 'bg-gradient-info'],
                    'Arts' => ['text' => 'ART', 'class' => 'bg-gradient-warning'],
                    'Commerce' => ['text' => 'COM', 'class' => 'bg-gradient-primary'],
                    'Science' => ['text' => 'SCI', 'class' => 'bg-gradient-danger'],
                    'Technology' => ['text' => 'TEC', 'class' => 'bg-gradient-secondary'],
                ];

                $badge = $teachingLevelBadges[$row->teaching_level] ?? ['text' => 'TEA', 'class' => 'bg-gradient-info'];

                return '<div class="d-flex align-items-center">
                    <span class="badge ' . $badge['class'] . ' badge-sm me-2">' . $badge['text'] . '</span>
                    <span class="fw-bold">' . $row->full_name . '</span>
                </div>';
            })
            ->addColumn('teacher_code', function ($row) {
                return '<span class="text-secondary">' . $row->teacher_code . '</span>';
            })
            ->addColumn('specialization', function ($row) {
                return $row->specialization ? '<span class="text-primary">' . $row->specialization . '</span>' : '<span class="text-muted">' . __('common.not_specified') . '</span>';
            })
            ->addColumn('subjects', function ($row) {
                if ($row->subjects->count() === 0) {
                    return '<span class="text-muted">' . __('common.no_subjects') . '</span>';
                }

                $subjectNames = $row->subjects->take(3)->pluck('subject_name')->toArray();
                $display = implode(', ', $subjectNames);

                if ($row->subjects->count() > 3) {
                    $display .= ' <span class="text-primary">+' . ($row->subjects->count() - 3) . ' ' . __('common.more') . '</span>';
                }

                return $display;
            })
            ->addColumn('experience', function ($row) {
                if ($row->experience_years) {
                    $years = floor($row->experience_years);

                    return '<span class="badge bg-gradient-warning badge-sm">' . $years . ' ' . __('common.year') . ($years != 1 ? 's' : '') . '</span>';
                }

                return '<span class="text-muted">' . __('common.not_specified') . '</span>';
            })
            ->addColumn('email', function ($row) {
                return $row->user ? $row->user->email : '<span class="text-muted">' . __('common.no_email') . '</span>';
            })
            ->addColumn('status', function ($row) {
                $color = $row->is_active ? 'success' : 'danger';
                $text = $row->is_active ? __('common.active') : __('common.inactive');

                return '<span class="badge badge-sm bg-gradient-' . $color . ' me-1">' . $text . '</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : __('common.never');
            })
            ->rawColumns(['action', 'name', 'teacher_code', 'specialization', 'subjects', 'experience', 'email', 'status', 'modified'])
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
    public function query(Teacher $model): QueryBuilder
    {
        return $model->with(['user', 'subjects'])->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('teacher-table')
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
            Column::make('DT_RowIndex')->title(__('common.id'))->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('teacher_code')->title(__('common.code'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('name')->title(__('common.name'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('specialization')->title(__('common.specialization'))->addClass('text-star align-middle text-xs')->searchable(true),
            Column::make('subjects')->title(__('common.subjects'))->addClass('text-star align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('experience')->title(__('common.experience'))->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('email')->title(__('common.email'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('status')->title(__('common.status'))->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('modified')->title(__('common.modified'))->addClass('text-center align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.teachers.show') ||
            checkPermission('admin.management.teachers.form') ||
            checkPermission('admin.management.teachers.edit') ||
            checkPermission('admin.management.teachers.delete')
        ) {
            $columns[] = Column::computed('action')->title(__('common.actions'))->addClass('text-center align-middle text-xs ')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Teacher_' . date('YmdHis');
    }
}
