<?php

namespace App\DataTables\Admin\Setup;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RoleDataTable extends DataTable
{
    protected $model = 'role';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $isAdminRole = $row->name === 'admin';

                $show = checkPermission('admin.setup.role.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.setup.'.$this->model.'.show', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $edit = (checkPermission('admin.setup.role.edit') && ! $isAdminRole) ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.setup.'.$this->model.'.form', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $delete = (checkPermission('admin.setup.role.delete') && ! $isAdminRole) ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.setup.'.$this->model.'.delete', ['id' => $row->id]),
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
                $badgeClass = match ($row->name) {
                    'admin' => 'bg-gradient-danger',
                    'teacher' => 'bg-gradient-primary',
                    'student' => 'bg-gradient-success',
                    'parent' => 'bg-gradient-info',
                    'security' => 'bg-gradient-warning',
                    default => 'bg-gradient-secondary'
                };

                return '<div class="d-flex align-items-center">
                    <span class="badge '.$badgeClass.' me-2">'.strtoupper(substr($row->name, 0, 3)).'</span>
                    <span class="fw-bold">'.ucfirst($row->name).'</span>
                </div>';
            })
            ->addColumn('permissions_count', function ($row) {
                $count = $row->permissions()->count();

                return '<span class="badge bg-gradient-info">'.$count.' permissions</span>';
            })
            ->addColumn('users_count', function ($row) {
                $count = $row->users()->count();
                $badgeClass = $count > 0 ? 'bg-gradient-success' : 'bg-gradient-secondary';

                return '<span class="badge '.$badgeClass.'">'.$count.' users</span>';
            })
            ->addColumn('description', function ($row) {
                return $row->description ?? '<span class="text-muted">No description</span>';
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['action', 'name', 'permissions_count', 'users_count', 'description'])
            ->setRowId('id');
    }

    public function query(Role $model): QueryBuilder
    {
        return $model->withCount(['permissions', 'users'])->newQuery()->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('role-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
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
            Column::make('name')->title('ROLE NAME')->addClass('align-middle text-xs')->searchable(true),
            Column::make('description')->title('DESCRIPTION')->addClass('align-middle text-xs')->searchable(true)->orderable(false),
            Column::make('permissions_count')->title('PERMISSIONS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('users_count')->title('USERS')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('modified')->title('MODIFIED')->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.setup.role.show') ||
            checkPermission('admin.setup.role.form') ||
            checkPermission('admin.setup.role.edit') ||
            checkPermission('admin.setup.role.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-end align-middle pt-3 pb-0 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Roles_'.date('YmdHis');
    }
}
