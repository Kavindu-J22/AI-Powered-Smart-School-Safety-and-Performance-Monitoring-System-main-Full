@extends('admin.layouts.app')

@section('css')
    @vite('resources/css/admin/tables.css')
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                    {{--
                                    <div class="ms-3">
                                        <small class="text-muted">
                                            <i class="material-symbols-rounded text-sm me-1">info</i>
                                            Parents are created through <a
                                                href="{{ route('admin.management.students.form') }}"
                                                class="text-decoration-none">Student Registration</a>
                                        </small>
                                    </div> --}}
                                </div>
                                <div class="col-4 text-end">
                                    <a class="btn bg-gradient-primary mb-0"
                                        href="{{ route('admin.management.students.form') }}">
                                        <i class="material-symbols-rounded text-sm me-1">person_add</i>Create Student
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="custom-table-responsive">
                                {{ $dataTable->table() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@isset($dataTable)
    {{ $dataTable->scripts(attributes: ['type' => 'module', 'class' => 'table table-bordered']) }}
@endisset

{{-- Include common scripts if they exist --}}
@if (file_exists(public_path('build/js/common/show.js')))
    @vite(['resources/js/common/show.js'])
@endif

@if (file_exists(public_path('build/js/common/confirm.js')))
    @vite(['resources/js/common/confirm.js'])
@endif

@if (file_exists(public_path('build/js/common/delete.js')))
    @vite(['resources/js/common/delete.js'])
@endif

@if (file_exists(public_path('build/js/common/edit.js')))
    @vite(['resources/js/common/edit.js'])
@endif
