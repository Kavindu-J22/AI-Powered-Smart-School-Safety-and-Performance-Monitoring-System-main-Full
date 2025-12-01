<?php

namespace App\DataTables\Admin\Setup;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    protected $model = 'user';

    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $isCurrentUser = Auth::check() && Auth::id() === $row->id;
                $isAdminUser = $row->hasRole('admin');

                $show = checkPermission('admin.setup.users.show') ? view('admin.layouts.actions.show', [
                    'url' => route('admin.setup.users.show', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $edit = (checkPermission('admin.setup.users.edit') && ! $isCurrentUser) ? view('admin.layouts.actions.edit', [
                    'url' => route('admin.setup.users.form', ['id' => $row->id]),
                    'id' => $row->id,
                ])->render() : '';

                $delete = (checkPermission('admin.setup.users.delete') && ! $isCurrentUser && ! $isAdminUser) ? view('admin.layouts.actions.delete', [
                    'url' => route('admin.setup.users.delete', ['id' => $row->id]),
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
                        ' . $dropdownContent . '
                    </ul>
                </div>';

                return $dropdown;
            })
            ->addColumn('name', function ($row) {
                $rolesBadge = '';
                if ($row->roles->count() > 0) {
                    $primaryRole = $row->roles->first();
                    $badgeClass = match ($primaryRole->name) {
                        'admin' => 'bg-gradient-danger',
                        'teacher' => 'bg-gradient-primary',
                        'student' => 'bg-gradient-success',
                        'parent' => 'bg-gradient-info',
                        'security' => 'bg-gradient-warning',
                        default => 'bg-gradient-secondary'
                    };
                    $rolesBadge = '<span class="badge ' . $badgeClass . ' badge-sm me-2">' . strtoupper(substr($primaryRole->name, 0, 3)) . '</span>';
                }

                return '<div class="d-flex align-items-center">
                    ' . $rolesBadge . '
                    <span class="fw-bold">' . $row->name . '</span>
                </div>';
            })
            ->addColumn('roles', function ($row) {
                if ($row->roles->count() === 0) {
                    return '<span class="badge bg-gradient-secondary">No roles</span>';
                }

                $rolesBadges = '';
                foreach ($row->roles->take(2) as $role) {
                    $badgeClass = match ($role->name) {
                        'admin' => 'bg-gradient-danger',
                        'teacher' => 'bg-gradient-primary',
                        'student' => 'bg-gradient-success',
                        'parent' => 'bg-gradient-info',
                        'security' => 'bg-gradient-warning',
                        default => 'bg-gradient-secondary'
                    };
                    $rolesBadges .= '<span class="badge ' . $badgeClass . ' badge-sm me-1">' . ucfirst($role->name) . '</span>';
                }

                if ($row->roles->count() > 2) {
                    $rolesBadges .= '<span class="badge bg-gradient-dark badge-sm">+' . ($row->roles->count() - 2) . '</span>';
                }

                return $rolesBadges;
            })
            ->addColumn('status', function ($row) {
                $color = match ($row->status) {
                    2 => 'warning',
                    3 => 'danger',
                    default => 'success',
                };
                try {
                    return '<span class="badge badge-sm bg-gradient-' . $color . ' me-1">' . ucfirst(strtolower($row->status->name)) . '</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge badge-sm bg-gradient-danger me-1">Invalid</span>';
                }
            })
            ->addColumn('usertype', function ($row) {
                $color = match ($row->usertype) {
                    UserType::ADMIN => 'danger',
                    UserType::TEACHER => 'primary',
                    UserType::STUDENT => 'success',
                    UserType::PARENT => 'info',
                    UserType::SECURITY => 'warning',
                    default => 'secondary',
                };
                try {
                    return '<span class="badge badge-sm bg-gradient-' . $color . ' me-1">' . ucfirst(strtolower($row->usertype->name)) . '</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge badge-sm bg-gradient-danger me-1">Invalid</span>';
                }
            })
            ->addColumn('modified', function ($row) {
                return $row->updated_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['status', 'action', 'usertype', 'name', 'roles'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->with(['roles'])->orderBy('created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('user-table')
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
            Column::make('name')->title('NAME')->addClass('align-middle text-xs')->searchable(true),
            Column::make('email')->title('EMAIL')->addClass('text-start align-middle text-xs')->searchable(true),
            Column::make('roles')->title('ROLES')->addClass('text-center align-middle text-xs')->searchable(false)->orderable(false),
            Column::make('usertype')->title('USER TYPE')->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('status')->title('STATUS')->searchable(false)->orderable(false)->addClass('text-center align-middle text-xs'),
            Column::make('modified')->title('MODIFIED')->addClass('text-start align-middle text-xs')->searchable(false),
        ];

        if (
            checkPermission('admin.setup.users.show') ||
            checkPermission('admin.setup.users.form') ||
            checkPermission('admin.setup.users.edit') ||
            checkPermission('admin.setup.users.delete')
        ) {
            $columns[] = Column::computed('action')->title('ACTIONS')->addClass('text-end align-middle pt-3 pb-0 text-xs')->exportable(false)->printable(false)->orderable(false)->searchable(false);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
