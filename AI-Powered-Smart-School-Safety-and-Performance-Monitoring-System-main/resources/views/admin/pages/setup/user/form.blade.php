@extends('admin.layouts.app')

@section('title', pageTitle())

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-3 d-flex align-items-center">
                                    <h6 class="mb-0">{{ pageTitle() }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.setup.users.enroll') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @if (isset($user))
                                    <input type="hidden" name="id" value="{{ $user->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-4">
                                        <x-input title="Name" type="text" name="name"
                                            value="{{ old('name', $user->name ?? '') }}" isRequired="true"
                                            attr="maxlength='255'" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="Email" type="email" name="email"
                                            value="{{ old('email', $user->email ?? '') }}" isRequired="true"
                                            attr="maxlength='255'" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="User Type" type="select" name="usertype"
                                            value="{{ old('usertype', $user->usertype ?? '') }}" :options="array_flip(App\Enums\UserType::options())"
                                            placeholder="Select User Type" isRequired="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="Status" type="select" name="status"
                                            value="{{ old('status', $user->status ?? '') }}" :options="array_flip(App\Enums\Status::options())"
                                            placeholder="Select Status" isRequired="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="Roles" type="select" name="roles[]"
                                            value="{{ old('roles', $user->roles->pluck('name')->toArray() ?? []) }}"
                                            :options="collect($roles)->pluck('name', 'name')->toArray()" placeholder="Select Roles" isRequired="true"
                                            attr="multiple" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="Password" type="password" name="password" value=""
                                            isRequired="{{ isset($user) ? 'false' : 'true' }}" attr="minlength='8'" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-input title="Confirm Password" type="password" name="password_confirmation"
                                            value="" isRequired="{{ isset($user) ? 'false' : 'true' }}"
                                            attr="minlength='8'" />
                                    </div>
                                    <div class="col-12 text-end pt-3">
                                        <a href="{{ route('admin.setup.users.index') }}"
                                            class="btn btn-outline-primary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Submit</button>
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
