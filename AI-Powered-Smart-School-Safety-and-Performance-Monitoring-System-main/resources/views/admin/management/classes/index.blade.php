@extends('admin.layouts.app')

@section('title', 'Classes Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">school</i>
                            Classes Management
                        </h3>
                        @if (checkPermission('admin management classes form'))
                            <a href="{{ route('admin.management.classes.form') }}" class="btn btn-primary">
                                <i class="material-icons-outlined me-1">add</i>
                                Add New Class
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="classesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Class Name</th>
                                        <th>Grade Level</th>
                                        <th>Section</th>
                                        <th>Class Teacher</th>
                                        <th>Students Count</th>
                                        <th>Room Number</th>
                                        <th>Academic Year</th>
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
                $('#classesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('admin.management.classes.index') }}',
                    columns: [{
                            data: 'id',
                            name: 'id'
                        },
                        {
                            data: 'name',
                            name: 'name',
                            render: function(data, type, row) {
                                return `
                        <div>
                            <h6 class="mb-0">${data}</h6>
                            <small class="text-muted">Class ID: ${row.id}</small>
                        </div>
                    `;
                            }
                        },
                        {
                            data: 'grade',
                            name: 'grade',
                            render: function(data, type, row) {
                                return `<span class="badge bg-primary">Grade ${data}</span>`;
                            }
                        },
                        {
                            data: 'section',
                            name: 'section',
                            render: function(data, type, row) {
                                return `<span class="badge bg-secondary">Section ${data}</span>`;
                            }
                        },
                        {
                            data: 'class_teacher_name',
                            name: 'class_teacher_name',
                            render: function(data, type, row) {
                                return data ? `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <img src="${row.class_teacher_photo || '/images/default-avatar.png'}"
                                     class="rounded-circle img-fluid" alt="Teacher">
                            </div>
                            <div>
                                <small class="fw-bold">${data}</small>
                            </div>
                        </div>
                    ` : '<span class="text-muted">Not Assigned</span>';
                            }
                        },
                        {
                            data: 'students_count',
                            name: 'students_count',
                            render: function(data, type, row) {
                                const count = data || 0;
                                const badgeClass = count === 0 ? 'bg-warning' : (count < 20 ?
                                    'bg-info' : 'bg-success');
                                return `<span class="badge ${badgeClass}">${count} Students</span>`;
                            }
                        },
                        {
                            data: 'room_number',
                            name: 'room_number',
                            render: function(data, type, row) {
                                return data ?
                                    `<i class="material-icons-outlined me-1">meeting_room</i>${data}` :
                                    'N/A';
                            }
                        },
                        {
                            data: 'academic_year',
                            name: 'academic_year',
                            render: function(data, type, row) {
                                return `<span class="badge bg-info">${data}</span>`;
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actions = '<div class="btn-group" role="group">';

                                @if (checkPermission('admin management classes show'))
                                    actions += `<a href="/admin/management/classes/show/${row.id}" class="btn btn-sm btn-info" title="View Details">
                        <i class="material-icons-outlined">visibility</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management classes form'))
                                    actions += `<a href="/admin/management/classes/form/${row.id}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="material-icons-outlined">edit</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management classes delete'))
                                    actions += `<button class="btn btn-sm btn-danger delete-class" data-id="${row.id}" title="Delete">
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

                // Delete class functionality
                $(document).on('click', '.delete-class', function() {
                    const classId = $(this).data('id');

                    if (confirm(
                            'Are you sure you want to delete this class? This action cannot be undone and will affect all students enrolled in this class.'
                            )) {
                        $.ajax({
                            url: `/admin/management/classes/delete/${classId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                notificationManager.success('Success',
                                'Class deleted successfully');
                                $('#classesTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                notificationManager.error('Error', 'Failed to delete class');
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
                width: 32px;
                height: 32px;
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
        </style>
    @endpush
@endsection
