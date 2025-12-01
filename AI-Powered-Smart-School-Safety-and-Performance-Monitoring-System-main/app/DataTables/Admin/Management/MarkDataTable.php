<?php

namespace App\DataTables\Admin\Management;

use App\Models\Mark;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MarkDataTable extends DataTable
{
    protected $model = 'marks';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('mark_id', function ($row) {
                return $row->mark_id;
            })
            ->addColumn('student_info', function ($row) {
                if (!$row->student) return '<span class="text-muted">N/A</span>';

                return '<div class="d-flex align-items-center">
                    <span class="badge bg-gradient-success badge-sm me-2">STU</span>
                    <div>
                        <span class="fw-bold d-block">' . e($row->student->full_name) . '</span>
                        <span class="text-secondary text-xs">' . e($row->student->student_code) . '</span>
                    </div>
                </div>';
            })
            ->addColumn('grade_class', function ($row) {
                return '<span class="badge bg-gradient-primary badge-sm">Grade ' . $row->grade_level . '</span>';
            })
            ->addColumn('subject_name', function ($row) {
                return $row->subject ? '<span class="text-primary fw-medium">' . e($row->subject->subject_name) . '</span>' : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('academic_info', function ($row) {
                return '<div>
                    <span class="text-dark fw-medium d-block">' . e($row->academic_year) . '</span>
                    <span class="badge bg-gradient-info badge-sm">Term ' . $row->term . '</span>
                </div>';
            })
            ->addColumn('marks_display', function ($row) {
                return '<span class="fw-bold text-dark">' . number_format($row->marks, 0) . '/' . number_format($row->total_marks, 0) . '</span>';
            })
            ->addColumn('percentage_display', function ($row) {
                $percentage = $row->percentage ?? 0;
                $color = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                return '<span class="badge bg-gradient-' . $color . ' badge-sm">' . number_format($percentage, 1) . '%</span>';
            })
            ->addColumn('grade_display', function ($row) {
                $grade = $row->grade ?? '-';
                $colorMap = [
                    'A+' => 'success',
                    'A' => 'success',
                    'A-' => 'success',
                    'B+' => 'info',
                    'B' => 'info',
                    'B-' => 'info',
                    'C+' => 'warning',
                    'C' => 'warning',
                    'C-' => 'warning',
                    'D' => 'danger',
                    'F' => 'danger'
                ];
                $color = $colorMap[$grade] ?? 'secondary';
                return '<span class="badge bg-gradient-' . $color . ' badge-sm fw-bold">' . $grade . '</span>';
            })
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown text-center">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="material-symbols-outlined">more_vert</i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end m-0">';

                if (checkPermission('admin.management.marks.show')) {
                    $actions .= view('admin.layouts.actions.show', [
                        'url' => route('admin.management.marks.show', $row->mark_id),
                        'id' => $row->mark_id,
                    ])->render();
                }

                if (checkPermission('admin.management.marks.edit')) {
                    $actions .= view('admin.layouts.actions.edit', [
                        'url' => route('admin.management.marks.edit', $row->mark_id),
                        'id' => $row->mark_id,
                    ])->render();
                }

                if (checkPermission('admin.management.marks.delete')) {
                    $actions .= view('admin.layouts.actions.delete', [
                        'url' => route('admin.management.marks.destroy', $row->mark_id),
                        'id' => $row->mark_id,
                    ])->render();
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['student_info', 'grade_class', 'subject_name', 'academic_info', 'marks_display', 'percentage_display', 'grade_display', 'action'])
            ->orderColumn('percentage_display', function ($query, $order) {
                $query->orderBy('percentage', $order);
            });
    }

    public function query(Mark $model): QueryBuilder
    {
        return $model->with(['student', 'subject'])->select('marks.*');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mark-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'responsive' => true,
                'rowCallback' => 'function(row, data, index) { if (index % 2 === 0) { $(row).css("background-color", "rgba(0, 0, 0, 0.02)"); } }',
            ]);
    }

    protected function getColumns(): array
    {
        $columns = [
            Column::make('mark_id')->title('#')->addClass('text-start align-middle text-xs'),
            Column::make('student_info')->title('STUDENT')->addClass('text-start align-middle text-xs')->orderable(false),
            Column::make('grade_class')->title('GRADE')->addClass('text-center align-middle text-xs')->orderable(false),
            Column::make('subject_name')->title('SUBJECT')->addClass('text-start align-middle text-xs')->orderable(false),
            Column::make('academic_info')->title('ACADEMIC INFO')->addClass('text-start align-middle text-xs')->orderable(false),
            Column::make('marks_display')->title('MARKS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('percentage_display')->title('PERCENTAGE')->addClass('text-center align-middle text-xs')->searchable(false),
            Column::make('grade_display')->title('GRADE')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
        ];

        if (
            checkPermission('admin.management.marks.show') ||
            checkPermission('admin.management.marks.edit') ||
            checkPermission('admin.management.marks.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-center align-middle py-2 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Marks_' . date('YmdHis');
    }
}
