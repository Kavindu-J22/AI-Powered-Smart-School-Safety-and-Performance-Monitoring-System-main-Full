@extends('admin.layouts.app')

@section('title', pageTitle())

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
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }} - {{ ucfirst($role->name) }}</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-dark mb-0 btn-back-auto"
                                        href="{{ route('admin.setup.role.index') }}">
                                        <i class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>Back
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card mb-4">
                                <div class="card-header bg-gradient-dark text-white">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <h5 class="mb-0 text-white">{{ ucfirst($role->name) }}</h5>
                                            <small
                                                class="text-white-50">{{ $role->description ?? 'No description provided' }}</small>
                                        </div>
                                        <div class="col-4 text-end">
                                            <h4 class="mb-0 text-white">{{ $role->permissions->count() }}</h4>
                                            <small class="text-white-50">Permissions</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card text-center border">
                                        <div class="card-body py-3">
                                            <i class="material-symbols-rounded text-primary mb-2"
                                                style="font-size: 2rem;">group</i>
                                            <h5 class="mb-1">{{ $role->users->count() }}</h5>
                                            <small class="text-muted">Users Assigned</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center border">
                                        <div class="card-body py-3">
                                            <i class="material-symbols-rounded text-info mb-2"
                                                style="font-size: 2rem;">security</i>
                                            <h5 class="mb-1">{{ $role->permissions->count() }}</h5>
                                            <small class="text-muted">Total Permissions</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center border">
                                        <div class="card-body py-3">
                                            <i class="material-symbols-rounded text-success mb-2"
                                                style="font-size: 2rem;">verified</i>
                                            <h5 class="mb-1">{{ ucfirst($role->guard_name) }}</h5>
                                            <small class="text-muted">Guard Type</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0 d-flex align-items-center">
                                        <i class="material-symbols-rounded me-2">lock</i>
                                        Assigned Permissions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if ($role->permissions->count() > 0)
                                        @foreach ($processedPermissionsGrouped as $groupName => $groupData)
                                            <div class="mb-4">
                                                <div class="border-bottom pb-2 mb-3">
                                                    <h5 class="text-dark mb-0 d-flex align-items-center">
                                                        <i
                                                            class="material-symbols-rounded me-2">{{ $groupData['permissions'][0]['icon'] ?? 'folder' }}</i>
                                                        {{ $groupName }}
                                                    </h5>
                                                    <small class="text-muted">{{ count($groupData['permissions']) }}
                                                        permission(s)</small>
                                                </div>

                                                <div class="row">
                                                    @foreach ($groupData['groupedByModule'] as $moduleName => $modulePermissions)
                                                        <div class="col-md-12 mb-3">
                                                            <div class="card border">
                                                                <div class="card-header bg-light py-2">
                                                                    <h6
                                                                        class="mb-0 fw-bold text-dark d-flex align-items-center">
                                                                        <i
                                                                            class="material-symbols-rounded me-2">{{ $modulePermissions[0]['icon'] }}</i>
                                                                        {{ $moduleName }}
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body py-3">
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        @foreach ($modulePermissions as $permission)
                                                                            <span
                                                                                class="badge bg-light text-dark border px-3 py-2 d-flex align-items-center">
                                                                                <span
                                                                                    class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2"
                                                                                    style="width: 16px; height: 16px; font-size: 10px;">
                                                                                    <i class="material-symbols-rounded"
                                                                                        style="font-size: 10px;">check</i>
                                                                                </span>
                                                                                {{ $permission['action'] }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4">
                                            <i class="material-symbols-rounded text-muted mb-2"
                                                style="font-size: 3rem;">lock_open</i>
                                            <p class="text-muted mb-0">No permissions assigned to this role</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0 d-flex align-items-center">
                                        <i class="material-symbols-rounded me-2">group</i>
                                        Users with this Role
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if ($role->users->count() > 0)
                                        <div class="row">
                                            @foreach ($role->users->take(6) as $user)
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-center p-2 border rounded">
                                                        <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="material-symbols-rounded">person</i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($role->users->count() > 6)
                                            <div class="text-center mt-3 pt-3 border-top">
                                                <small class="text-muted">And {{ $role->users->count() - 6 }} more
                                                    users...</small>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4">
                                            <i class="material-symbols-rounded text-muted mb-2"
                                                style="font-size: 3rem;">person_off</i>
                                            <p class="text-muted mb-0">No users assigned to this role</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
