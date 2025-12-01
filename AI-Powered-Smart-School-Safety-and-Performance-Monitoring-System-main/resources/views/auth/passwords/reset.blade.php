@extends('admin.layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
@endsection

@section('content')
    <main class="main-content @if (env('DEFAULT_THEME') == 'dark') bg-dark @endif mt-0">
        <div class="page-header align-items-start min-vh-100">
            <div class="container my-auto">
                <div id="particles-js"></div>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card card-body custom-class-for-Glassmorphism">
                            <img class="w-20 align-self-sm-center" src="{{ asset('assets/img/favicon.ico') }}"
                                alt="">
                            <h3 class="text-center text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif">
                                {{ __('passwords.reset-password-title') }}</h3>
                            <small class="text-center">{{ __('passwords.reset-password-second-title') }}</small>
                            <div class="card-body ">
                                <form method="POST" action="{{ route('password.update') }}">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <div class="input-group input-group-outline my-3">
                                        <x-input name="email" type="email" title="{{ __('Email Address') }}"
                                            class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif form-control @error('email') is-invalid @enderror"
                                            :isRequired="true" attr="autocomplete='email' autofocus readonly id='email'"
                                            :value="$email ?? old('email')" />
                                    </div>

                                    <div class="input-group input-group-outline my-3">
                                        <x-input name="password" type="password" title="{{ __('Password') }}"
                                            class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif form-control @error('password') is-invalid @enderror"
                                            :isRequired="true" attr="autocomplete='new-password' id='password'" />
                                    </div>

                                    <div class="input-group input-group-outline my-3">
                                        <x-input name="password_confirmation" type="password"
                                            title="{{ __('Confirm Password') }}"
                                            class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif form-control"
                                            :isRequired="true" attr="autocomplete='new-password' id='password-confirm'" />
                                    </div>

                                    <div class="row mb-0">
                                        <div class="d-flex justify-content-center w-100">
                                            <button type="submit" class="btn bg-gradient-dark w-60 my-4 mb-2">
                                                {{ __('Reset Password') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.layouts.footer')
        </div>
    </main>
@endsection

@section('script')
    <script src="{{ asset('assets/js/custom.js') }}" defer data-deferred="1"></script>
@endsection
