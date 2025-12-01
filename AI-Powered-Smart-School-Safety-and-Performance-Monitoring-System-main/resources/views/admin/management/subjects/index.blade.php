@extends('admin.layouts.app')

@section('title', 'Subjects Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="material-icons-outlined me-2">subject</i>
                            Subjects Management
                        </h3>
                        @if (checkPermission('admin management subjects form'))
                            <a href="{{ route('admin.management.subjects.form') }}" class="btn btn-primary">
                                <i class="material-icons-outlined me-1">add</i>
                                Add New Subject
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="subjectsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject Name</th>
                                        <th>Subject Code</th>
                                        <th>Grade Levels</th>
                                        <th>Category</th>
                                        <th>Credit Hours</th>
                                        <th>Teachers</th>
                                        <th>Status</th>
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
                $('#subjectsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('admin.management.subjects.index') }}',
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
                            <small class="text-muted">${row.subject_code || 'No Code'}</small>
                        </div>
                    `;
                            }
                        },
                        {
                            data: 'subject_code',
                            name: 'subject_code',
                            render: function(data, type, row) {
                                return data ?
                                    `<code class="bg-light px-2 py-1 rounded">${data}</code>` : 'N/A';
                            }
                        },
                        {
                            data: 'grade_levels',
                            name: 'grade_levels',
                            render: function(data, type, row) {
                                if (!data || data.length === 0)
                                return '<span class="text-muted">No Grades</span>';

                                const grades = Array.isArray(data) ? data : JSON.parse(data || '[]');
                                return grades.map(grade =>
                                    `<span class="badge bg-primary me-1">Grade ${grade}</span>`
                                ).join('');
                            }
                        },
                        {
                            data: 'category',
                            name: 'category',
                            render: function(data, type, row) {
                                const categoryColors = {
                                    'core': 'bg-success',
                                    'elective': 'bg-info',
                                    'practical': 'bg-warning',
                                    'extracurricular': 'bg-secondary',
                                    'language': 'bg-primary'
                                };
                                const color = categoryColors[data] || 'bg-secondary';
                                return `<span class="badge ${color}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : 'N/A'}</span>`;
                            }
                        },
                        {
                            data: 'credit_hours',
                            name: 'credit_hours',
                            render: function(data, type, row) {
                                return data ? `${data} ${data === 1 ? 'Hour' : 'Hours'}` : 'N/A';
                            }
                        },
                        {
                            data: 'teachers_count',
                            name: 'teachers_count',
                            render: function(data, type, row) {
                                const count = data || 0;
                                const badgeClass = count === 0 ? 'bg-warning' : 'bg-success';
                                return `<span class="badge ${badgeClass}">${count} ${count === 1 ? 'Teacher' : 'Teachers'}</span>`;
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function(data, type, row) {
                                const statusColors = {
                                    'active': 'bg-success',
                                    'inactive': 'bg-warning',
                                    'archived': 'bg-secondary'
                                };
                                const color = statusColors[data] || 'bg-secondary';
                                return `<span class="badge ${color}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : 'N/A'}</span>`;
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actions = '<div class="btn-group" role="group">';

                                @if (checkPermission('admin management subjects show'))
                                    actions += `<a href="/admin/management/subjects/show/${row.id}" class="btn btn-sm btn-info" title="View Details">
                        <i class="material-icons-outlined">visibility</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management subjects form'))
                                    actions += `<a href="/admin/management/subjects/form/${row.id}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="material-icons-outlined">edit</i>
                    </a>`;
                                @endif

                                @if (checkPermission('admin management subjects delete'))
                                    actions += `<button class="btn btn-sm btn-danger delete-subject" data-id="${row.id}" title="Delete">
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

                // Delete subject functionality
                $(document).on('click', '.delete-subject', function() {
                    const subjectId = $(this).data('id');

                    if (confirm(
                            'Are you sure you want to delete this subject? This action cannot be undone and will affect all related assignments and grades.'
                            )) {
                        $.ajax({
                            url: `/admin/management/subjects/delete/${subjectId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                notificationManager.success('Success',
                                    'Subject deleted successfully');
                                $('#subjectsTable').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                notificationManager.error('Error', 'Failed to delete subject');
                            }
                        });
                    }
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
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
