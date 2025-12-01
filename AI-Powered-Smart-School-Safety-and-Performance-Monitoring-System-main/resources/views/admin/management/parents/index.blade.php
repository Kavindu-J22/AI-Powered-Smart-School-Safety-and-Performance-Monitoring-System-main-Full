@extends('admin.layouts.app')

@section('title', 'Parents Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">people</i>
                            Parents Management
                        </h3>
                        @if (checkPermission('admin management parents form'))
                            <a href="{{ route('admin.management.parents.form') }}" class="btn btn-primary">
                                <i class="material-icons-outlined me-1">add</i>
                                Add New Parent
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="parentsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Children</th>
                                        <th>Address</th>
                                        <th>Registration Date</th>
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
                $('#parentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('admin.management.parents.index') }}',
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
                                     class="rounded-circle img-fluid" alt="Parent Avatar">
                            </div>
                            <div>
                                <h6 class="mb-0">${data}</h6>
                                <small class="text-muted">Parent ID: ${row.id}</small>
                            </div>
                        </div>
                    `;
                            }
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'children_count',
                            name: 'children_count',
                            render: function(data, type, row) {
                                return `
                        <span class="badge bg-info">
                            ${data} ${data === 1 ? 'Child' : 'Children'}
                        </span>
                    `;
                            }
                        },
                        {
                            data: 'address',
                            name: 'address',
                            render: function(data, type, row) {
                                return data ? data.substring(0, 50) + (data.length > 50 ? '...' : '') :
                                    'N/A';
                            }
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data, type, row) {
                                return moment(data).format('MMM DD, YYYY');
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actions = '<div class="btn-group" role="group">';

                                @if (checkPermission('admin management parents show'))
                                    actions += `<a href="/admin/management/parents/show/${row.id}" class="btn btn-sm btn-info" title="View Details">
                        <i class="material-icons-outlined">visibility</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management parents form'))
                                    actions += `<a href="/admin/management/parents/form/${row.id}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="material-icons-outlined">edit</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management parents delete'))
                                    actions += `<button class="btn btn-sm btn-danger delete-parent" data-id="${row.id}" title="Delete">
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

                // Delete parent functionality
                $(document).on('click', '.delete-parent', function() {
                    const parentId = $(this).data('id');

                    if (confirm('Are you sure you want to delete this parent? This action cannot be undone.')) {
                        $.ajax({
                            url: `/admin/management/parents/delete/${parentId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                notificationManager.success('Success',
                                    'Parent deleted successfully');
                                $('#parentsTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                notificationManager.error('Error', 'Failed to delete parent');
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
        </style>
    @endpush
@endsection
