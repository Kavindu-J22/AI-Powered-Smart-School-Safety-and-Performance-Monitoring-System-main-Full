<?php

namespace App\DataTables\Admin\Management;

use App\Models\SecurityStaff;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SecurityStaffDataTable extends DataTable
{
    protected $model = 'security';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $show = checkPermission('admin.management.security.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.management.' . $this->model . '.show', ['id' => $row->security_id]),
                    'id' => $row->security_id,
                ])->render() : '';

                $edit = checkPermission('admin.management.security.edit') ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.management.' . $this->model . '.form', ['id' => $row->security_id]),
                    'id' => $row->security_id,
                ])->render() : '';

                $delete = checkPermission('admin.management.security.delete') ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.management.' . $this->model . '.delete', ['id' => $row->security_id]),
                    'id' => $row->security_id,
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
                        ' . $dropdownContent . '
                    </ul>
                </div>';

                return $dropdown;
            })
            ->addColumn('name', function ($row) {
                $shiftColors = [
                    'Morning' => 'bg-gradient-warning',
                    'Afternoon' => 'bg-gradient-info',
                    'Evening' => 'bg-gradient-primary',
                    'Night' => 'bg-gradient-dark',
                ];

                $shiftColor = $shiftColors[$row->shift] ?? 'bg-gradient-secondary';

                return '<div class="d-flex align-items-center">
                    <span class="badge ' . $shiftColor . ' badge-sm me-2">' . substr($row->shift, 0, 1) . '</span>
                    <span class="fw-bold">' . $row->full_name . '</span>
                </div>';
            })
            ->addColumn('security_code', function ($row) {
                return '<span class="text-secondary fw-bold">' . $row->security_code . '</span>';
            })
            ->addColumn('position', function ($row) {
                return '<span class="text-primary">' . $row->position . '</span>';
            })
            ->addColumn('shift', function ($row) {
                $shiftColors = [
                    'Morning' => 'bg-gradient-warning',
                    'Evening' => 'bg-gradient-info',
                    'Night' => 'bg-gradient-dark',
                    'Rotating' => 'bg-gradient-secondary',
                ];

                $shiftColor = $shiftColors[$row->shift] ?? 'bg-gradient-primary';

                return '<span class="badge ' . $shiftColor . ' badge-sm">' . $row->shift . '</span>';
            })
            ->addColumn('contact', function ($row) {
                $contact = [];
                if ($row->mobile_phone) {
                    $contact[] = '<span class="badge bg-gradient-info badge-sm me-1">📞 ' . $row->mobile_phone . '</span>';
                }
                if ($row->home_phone) {
                    $contact[] = '<span class="badge bg-gradient-secondary badge-sm">🏠 ' . $row->home_phone . '</span>';
                }

                return $contact ? implode('<br> <br>', $contact) : '<span class="text-muted">No contact</span>';
            })
            ->addColumn('joining_date', function ($row) {
                return $row->joining_date ? '<span class="badge bg-gradient-success badge-sm">' . $row->joining_date->format('M d, Y') . '</span>' : '<span class="text-muted">Not set</span>';
            })
            ->addColumn('employee_id', function ($row) {
                return $row->employee_id ? '<span class="badge bg-gradient-warning badge-sm">' . $row->employee_id . '</span>' : '<span class="text-muted">Not set</span>';
            })
            ->addColumn('email', function ($row) {
                return $row->email ? $row->email : '<span class="text-muted">No email</span>';
            })
            ->addColumn('status', function ($row) {
                $statusColors = [
                    'On Duty' => 'success',
                    'Off Duty' => 'secondary',
                    'On Leave' => 'warning',
                    'Emergency' => 'danger',
                    'Inactive' => 'dark',
                ];

                $statusText = $row->is_active ? 'On Duty' : 'Inactive';
                $color = $statusColors[$statusText] ?? 'secondary';

                return '<span class="badge badge-sm bg-gradient-' . $color . ' me-1">' . $statusText . '</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at ? $row->updated_at->format('M d, Y') : 'Never';
            })
            ->rawColumns(['action', 'name', 'security_code', 'position', 'shift', 'contact', 'joining_date', 'employee_id', 'email', 'status', 'modified'])
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
    public function query(SecurityStaff $model): QueryBuilder
    {
        return $model->with(['user'])
            ->orderBy('position', 'asc')
            ->orderBy('shift', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('security-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'scrollX' => true,
                'autoWidth' => false,
                'drawCallback' => 'function(settings) {
                    // Add horizontal scroll styles
                    $("#security-table_wrapper .dt-layout-table").css({
                        "overflow": "hidden",
                        "overflow-x": "auto"
                    });
                }',
                'initComplete' => 'function(settings, json) {
                    // Apply horizontal scroll on initialization
                    $("#security-table_wrapper .dt-layout-table").css({
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
            Column::make('security_id')->title('#')->addClass('text-start align-middle text-xs'),
            Column::make('security_code')->title('CODE')->addClass('align-middle text-xs')->searchable(true),
            Column::make('name')->title('NAME')->addClass('align-middle text-xs')->searchable(true),
            Column::make('position')->title('POSITION')->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('shift')->title('SHIFT')->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('joining_date')->title('JOINED')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('employee_id')->title('EMP ID')->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('contact')->title('CONTACT')->addClass('text-center align-middle text-xs')->searchable(true),
            Column::make('email')->title('EMAIL')->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('status')->title('STATUS')->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('modified')->title('MODIFIED')->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.management.security.show') ||
            checkPermission('admin.management.security.form') ||
            checkPermission('admin.management.security.edit') ||
            checkPermission('admin.management.security.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-end align-middle pt-3 pb-0 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'SecurityStaff_' . date('YmdHis');
    }
}
