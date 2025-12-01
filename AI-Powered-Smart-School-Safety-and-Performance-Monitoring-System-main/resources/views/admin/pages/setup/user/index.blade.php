@extends('admin.layouts.app')




@section('css')
    @vite('resources/css/admin/tables.css')
@endsection


@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    @include('admin.layouts.flash')
                    <div class="card my-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                </div>
                                <div class="col-6 text-end">
                                    @can('admin.setup.users.form')
                                        <a class="btn bg-gradient-dark mb-0" href="{{ route('admin.setup.users.form') }}"><i
                                                class="material-symbols-rounded text-sm">add</i>&nbsp;&nbsp;Create</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                {{ $dataTable->table() }}

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

@vite(['resources/js/common/confirm.js'])
@vite(['resources/js/common/viewModal.js'])
