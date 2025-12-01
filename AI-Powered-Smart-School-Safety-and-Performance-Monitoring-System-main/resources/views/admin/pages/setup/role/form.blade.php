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
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a class="btn btn-outline-dark mb-0 d-flex align-items-center justify-content-center btn-back-auto"
                                        href="{{ route('admin.setup.role.index') }}">
                                        <i class="material-symbols-rounded me-1 icon-size-md">arrow_back</i>Back
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.setup.role.enroll') }}" method="POST" id="roleForm">
                                @csrf
                                @if ($id)
                                    <input type="hidden" name="id" value="{{ $id }}">
                                @endif

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">info</i>
                                            Role Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-input name="name" title="Role Name" :isRequired="true"
                                                    attr="maxlength='255'" :value="old('name', $role->name ?? '')" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="description" title="Description" attr="maxlength='500'"
                                                    :value="old('description', $role->description ?? '')" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input name="status" type="select" title="Status"
                                                    placeholder="Select Status" :options="App\Enums\Status::options()" :value="old(
                                                        'status',
                                                        $role->status ?? App\Enums\Status::ACTIVE->value,
                                                    )" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0 d-flex align-items-center">
                                            <i class="material-symbols-rounded me-2 icon-size-sm">security</i>
                                            Assign Permissions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach ($processedPermissionsGrouped as $groupName => $groupData)
                                            <div class="mb-4">
                                                <div class="border-bottom pb-2 mb-3">
                                                    <h5 class="text-dark mb-0 d-flex align-items-center">
                                                        <i
                                                            class="material-symbols-rounded me-2 icon-size-base">{{ $groupData['permissions'][0]['icon'] ?? 'folder' }}</i>
                                                        {{ $groupName }}
                                                    </h5>
                                                </div>

                                                <div class="row">
                                                    @foreach ($groupData['groupedByModule'] as $moduleName => $modulePermissions)
                                                        <div class="col-md-12 mb-3">
                                                            <div class="card border">
                                                                <div class="card-header bg-light py-2">
                                                                    <h6
                                                                        class="mb-0 fw-bold text-dark d-flex align-items-center">
                                                                        <i
                                                                            class="material-symbols-rounded me-2 icon-size-md">{{ $modulePermissions[0]['icon'] }}</i>
                                                                        {{ $moduleName }}
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body py-3">
                                                                    <div class="d-flex flex-wrap gap-3">
                                                                        @foreach ($modulePermissions as $permission)
                                                                            @if (strtolower($permission['action']) == 'enroll')
                                                                                @php continue; @endphp
                                                                            @endif
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="checkbox" name="permissions[]"
                                                                                    value="{{ $permission['name'] }}"
                                                                                    id="permission_{{ \Illuminate\Support\Str::slug($permission['name']) }}"
                                                                                    {{ in_array($permission['name'], $rolePermissions ?? []) ? 'checked' : '' }}>
                                                                                <label class="form-check-label"
                                                                                    for="permission_{{ \Illuminate\Support\Str::slug($permission['name']) }}">
                                                                                    {{ $permission['action'] }}
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="col-12 text-end">
                                            <a href="{{ route('admin.setup.role.index') }}"
                                                class="btn btn-outline-secondary">Cancel</a>
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="document.getElementById('roleForm').reset()">Reset</button>
                                            <button type="submit" class="btn btn-success">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script>
        // Form validation and other JS if needed
    </script>
@endsection
