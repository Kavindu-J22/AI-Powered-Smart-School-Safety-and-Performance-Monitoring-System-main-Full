@extends('admin.layouts.app')

@section('title', 'Security Staff Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">security</i>
                            Security Staff Management
                        </h3>
                        @if (checkPermission('admin management security form'))
                            <a href="{{ route('admin.management.security.form') }}" class="btn btn-primary">
                                <i class="material-icons-outlined me-1">add</i>
                                Add New Security Staff
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="securityTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Contact</th>
                                        <th>Shift</th>
                                        <th>Area Assignment</th>
                                        <th>Status</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#securityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('admin.management.security.index') }}',
                    columns: [{
                            data: 'id',
                            name: 'id'
                        },
                        {
                            data: 'name',
                            name: 'name',
                            render: function(data, type, row) {
                                return `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <img src="${row.profile_photo_url || '/images/default-avatar.png'}"
                                     class="rounded-circle img-fluid" alt="Security Staff Avatar">
                            </div>
                            <div>
                                <h6 class="mb-0">${data}</h6>
                                <small class="text-muted">ID: ${row.employee_id || 'N/A'}</small>
                            </div>
                        </div>
                    `;
                            }
                        },
                        {
                            data: 'employee_id',
                            name: 'employee_id',
                            render: function(data, type, row) {
                                return data ?
                                    `<code class="bg-light px-2 py-1 rounded">${data}</code>` : 'N/A';
                            }
                        },
                        {
                            data: 'contact',
                            name: 'contact',
                            render: function(data, type, row) {
                                return `
                        <div>
                            <div><i class="material-icons-outlined me-1" style="font-size: 16px;">phone</i>${row.phone || 'N/A'}</div>
                            <div><i class="material-icons-outlined me-1" style="font-size: 16px;">email</i>${row.email || 'N/A'}</div>
                        </div>
                    `;
                            }
                        },
                        {
                            data: 'shift',
                            name: 'shift',
                            render: function(data, type, row) {
                                const shiftColors = {
                                    'morning': 'bg-warning',
                                    'afternoon': 'bg-info',
                                    'evening': 'bg-primary',
                                    'night': 'bg-dark',
                                    'rotating': 'bg-secondary'
                                };
                                const color = shiftColors[data] || 'bg-secondary';
                                const displayText = data ? data.charAt(0).toUpperCase() + data.slice(
                                    1) : 'N/A';
                                return `<span class="badge ${color}">${displayText}</span>`;
                            }
                        },
                        {
                            data: 'area_assignment',
                            name: 'area_assignment',
                            render: function(data, type, row) {
                                return data ?
                                    `<i class="material-icons-outlined me-1">location_on</i>${data}` :
                                    'Not Assigned';
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function(data, type, row) {
                                const statusColors = {
                                    'active': 'bg-success',
                                    'inactive': 'bg-warning',
                                    'on_leave': 'bg-info',
                                    'terminated': 'bg-danger'
                                };
                                const color = statusColors[data] || 'bg-secondary';
                                const displayText = data ? data.replace('_', ' ').replace(/\b\w/g, l =>
                                    l.toUpperCase()) : 'N/A';
                                return `<span class="badge ${color}">${displayText}</span>`;
                            }
                        },
                        {
                            data: 'hire_date',
                            name: 'hire_date',
                            render: function(data, type, row) {
                                return data ? moment(data).format('MMM DD, YYYY') : 'N/A';
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actions = '<div class="btn-group" role="group">';

                                @if (checkPermission('admin management security show'))
                                    actions += `<a href="/admin/management/security/show/${row.id}" class="btn btn-sm btn-info" title="View Details">
                        <i class="material-icons-outlined">visibility</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management security form'))
                                    actions += `<a href="/admin/management/security/form/${row.id}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="material-icons-outlined">edit</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management security delete'))
                                    actions += `<button class="btn btn-sm btn-danger delete-security" data-id="${row.id}" title="Delete">
                        <i class="material-icons-outlined">delete</i>
                    </button>`;
                                @endif

                                actions += '</div>';
                                return actions;
                            }
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ],
                    pageLength: 25,
                    responsive: true,
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                // Delete security staff functionality
                $(document).on('click', '.delete-security', function() {
                    const securityId = $(this).data('id');

                    if (confirm(
                            'Are you sure you want to delete this security staff member? This action cannot be undone.'
                            )) {
                        $.ajax({
                            url: `/admin/management/security/delete/${securityId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                notificationManager.success('Success',
                                    'Security staff deleted successfully');
                                $('#securityTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                notificationManager.error('Error',
                                    'Failed to delete security staff');
                            }
                        });
                    }
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .avatar-sm {
                width: 40px;
                height: 40px;
            }

            .avatar-sm img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .material-icons-outlined {
                font-size: 18px;
                vertical-align: middle;
            }

            .btn-group .btn {
                margin-right: 2px;
            }

            .btn-group .btn:last-child {
                margin-right: 0;
            }

            .badge {
                font-size: 0.75em;
            }

            code {
                font-size: 0.8em;
            }
        </style>
    @endpush
@endsection
