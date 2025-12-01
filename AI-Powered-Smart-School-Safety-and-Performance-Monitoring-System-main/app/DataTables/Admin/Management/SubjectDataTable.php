<?php

namespace App\DataTables\Admin\Management;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SubjectDataTable extends DataTable
{
    protected $model = 'subjects';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $show = checkPermission('admin.management.subjects.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.management.'.$this->model.'.show', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $edit = checkPermission('admin.management.subjects.edit') ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.management.'.$this->model.'.form', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $delete = checkPermission('admin.management.subjects.delete') ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.management.'.$this->model.'.delete', ['id' => $row->id]),
                    'id' => $row->id,
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
                    return '<span class="text-muted">No actions</span>';
                }

                $dropdownContent = implode('<li><hr class="dropdown-divider"></li>', $dropdownItems);

                $dropdown = '
                <div class="dropdown text-end">
                    <button class="btn btn-icon border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-outlined text-lg">more_vert</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2 w-100">
                        '.$dropdownContent.'
                    </ul>
                </div>';

                return $dropdown;
            })
            ->addColumn('name', function ($row) {
                $typeColors = [
                    'core' => 'bg-gradient-primary',
                    'elective' => 'bg-gradient-success',
                    'extracurricular' => 'bg-gradient-warning',
                ];

                $typeColor = $typeColors[$row->type] ?? 'bg-gradient-dark';

                return '<div class="d-flex align-items-center">
                    <span class="badge '.$typeColor.' badge-sm me-2">'.ucfirst($row->type).'</span>
                    <span class="fw-bold">'.$row->subject_name.'</span>
                </div>';
            })
            ->addColumn('subject_code', function ($row) {
                return '<span class="text-secondary fw-bold">'.$row->subject_code.'</span>';
            })
            ->addColumn('grade_levels', function ($row) {
                return '<span class="badge bg-gradient-info badge-sm">Grade '.$row->grade_level.'</span>';
            })
            ->addColumn('teachers_count', function ($row) {
                $count = $row->teachers->count();
                $color = $count > 0 ? 'success' : 'secondary';

                return '<span class="badge bg-gradient-'.$color.' badge-sm">'.$count.' teacher'.($count != 1 ? 's' : '').'</span>';
            })
            ->addColumn('classes_count', function ($row) {
                $count = $row->classes->count();
                $color = $count > 0 ? 'info' : 'secondary';

                return '<span class="badge bg-gradient-'.$color.' badge-sm">'.$count.' class'.($count != 1 ? 'es' : '').'</span>';
            })
            ->addColumn('students_count', function ($row) {
                $count = $row->students->count();
                $color = $count > 0 ? 'primary' : 'secondary';

                return '<span class="badge bg-gradient-'.$color.' badge-sm">'.$count.' student'.($count != 1 ? 's' : '').'</span>';
            })
            ->addColumn('credits', function ($row) {
                if ($row->credits) {
                    return '<span class="badge bg-gradient-warning badge-sm">'.$row->credits.' credit'.($row->credits != 1 ? 's' : '').'</span>';
                }

                return '<span class="text-muted">Not specified</span>';
            })
            ->addColumn('status', function ($row) {
                $color = $row->status === 'active' ? 'success' : 'danger';
                $text = $row->status === 'active' ? 'Active' : 'Inactive';

                return '<span class="badge badge-sm bg-gradient-'.$color.' me-1">'.$text.'</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : 'Never';
            })
            ->rawColumns(['action', 'name', 'subject_code', 'grade_levels', 'teachers_count', 'classes_count', 'students_count', 'credits', 'status', 'modified'])
            ->orderColumn('name', function ($query, $order) {
                return $query->orderBy('type', $order)->orderBy('subject_name', $order);
            })
            ->orderColumn('modified', function ($query, $order) {
                return $query->orderBy('updated_at', $order);
            });
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Subject $model): QueryBuilder
    {
        return $model->with(['teachers', 'classes', 'students'])
            ->orderBy('type', 'asc')
            ->orderBy('subject_name', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('subject-table')
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
            Column::make('id')->title('#')->addClass('text-start align-middle text-xs'),
            Column::make('subject_code')->title('CODE')->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('name')->title('SUBJECT')->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('grade_levels')->title('GRADE LEVELS')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('teachers_count')->title('TEACHERS')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('classes_count')->title('CLASSES')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('students_count')->title('STUDENTS')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('credits')->title('CREDITS')->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('status')->title('STATUS')->searchable(false)->orderable(false)->addClass('text-start align-middle text-xs'),
            Column::make('modified')->title('MODIFIED')->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.subjects.show') ||
            checkPermission('admin.management.subjects.form') ||
            checkPermission('admin.management.subjects.edit') ||
            checkPermission('admin.management.subjects.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-end align-middle py=2 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Subject_'.date('YmdHis');
    }
}
