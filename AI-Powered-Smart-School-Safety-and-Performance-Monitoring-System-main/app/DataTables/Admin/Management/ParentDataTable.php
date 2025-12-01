<?php

namespace App\DataTables\Admin\Management;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ParentDataTable extends DataTable
{
    protected $model = 'parents';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $show = checkPermission('admin.management.parents.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.management.' . $this->model . '.show', ['id' => $row->parent_id]),
                    'id' => $row->parent_id,
                ])->render() : '';

                if ($show) {
                    return '
                    <div class="text-center">
                        <a href="'.route('admin.management.'.$this->model.'.show', ['id' => $row->parent_id]).'"
                           class="btn btn-sm btn-outline-primary" title="View Parent Details">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                        </a>
                    </div>';
                }

                return '<span class="text-muted">' . __('common.no_actions') . '</span>';
            })
            ->addColumn('name', function ($row) {
                $avatar = '<div class="avatar avatar-sm rounded-circle bg-gradient-primary me-2">
                    <span class="text-white text-xs">'.strtoupper(substr($row->first_name, 0, 1)).'</span>
                </div>';

                return '<div class="d-flex align-items-center">
                    '.$avatar.'
                    <span class="fw-bold">'.$row->full_name.'</span>
                </div>';
            })
            ->addColumn('parent_code', function ($row) {
                return '<span class="text-secondary">'.$row->parent_code.'</span>';
            })
            ->addColumn('students', function ($row) {
                if ($row->students->count() === 0) {
                    return '<span class="text-muted">' . __('common.no_students') . '</span>';
                }

                $studentNames = $row->students->take(2)->map(function ($student) {
                    return '<span class="badge bg-gradient-success badge-sm me-1">'.$student->full_name.'</span>';
                })->toArray();

                $display = implode(' ', $studentNames);

                if ($row->students->count() > 2) {
                    $display .= ' <span class="text-primary">+' . ($row->students->count() - 2) . ' ' . __('common.more') . '</span>';
                }

                return $display;
            })
            ->addColumn('contact', function ($row) {
                $contact = [];
                if ($row->mobile_phone) {
                    $contact[] = '<span class="badge bg-gradient-info badge-sm me-1">📞 '.$row->mobile_phone.'</span>';
                }
                if ($row->email) {
                    $contact[] = '<span class="badge bg-gradient-secondary badge-sm">✉️ '.$row->email.'</span>';
                }

                return $contact ? implode('<br>', $contact) : '<span class="text-muted">' . __('common.no_contact') . '</span>';
            })
            ->addColumn('relationship', function ($row) {
                return $row->relationship_type ? '<span class="badge bg-gradient-warning badge-sm">' . $row->relationship_type . '</span>' : '<span class="text-muted">' . __('common.not_specified') . '</span>';
            })
            ->addColumn('occupation', function ($row) {
                return $row->occupation ? '<span class="text-primary">' . $row->occupation . '</span>' : '<span class="text-muted">' . __('common.not_specified') . '</span>';
            })
            ->addColumn('emergency', function ($row) {
                if ($row->is_emergency_contact) {
                    return '<span class="badge bg-gradient-danger badge-sm">' . __('common.emergency_contact') . '</span>';
                }

                return '<span class="text-muted">' . __('common.no') . '</span>';
            })
            ->addColumn('status', function ($row) {
                $color = $row->is_active ? 'success' : 'danger';
                $text = $row->is_active ? __('common.active') : __('common.inactive');

                return '<span class="badge badge-sm bg-gradient-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : __('common.never');
            })
            ->rawColumns(['action', 'name', 'parent_code', 'students', 'contact', 'relationship', 'occupation', 'emergency', 'status', 'modified'])
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
    public function query(ParentModel $model): QueryBuilder
    {
        return $model->with(['students'])
            ->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('parent-table')
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
            Column::make('parent_id')->title(__('common.id'))->addClass('text-start align-middle text-xs'),
            Column::make('parent_code')->title(__('common.code'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('name')->title(__('common.name'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('relationship')->title(__('common.relationship'))->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('students')->title(__('common.students'))->addClass('text-start align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('contact')->title(__('common.contact'))->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('occupation')->title(__('common.occupation'))->addClass('text-star align-middle text-xs')->searchable(true),
            Column::make('emergency')->title(__('common.emergency'))->addClass('text-center align-middle text-xs')->searchable(false),
            Column::make('status')->title(__('common.status'))->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('modified')->title(__('common.modified'))->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (checkPermission('admin.management.parents.show')) {
            $columns[] = Column::computed('action')->title(__('common.actions'))->addClass('text-center align-middle py-2  text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Parent_'.date('YmdHis');
    }
}
