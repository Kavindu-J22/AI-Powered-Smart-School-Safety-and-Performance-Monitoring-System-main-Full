@extends('admin.layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
@endsection
@section('content')
    <main class="main-content @if (env('DEFAULT_THEME') == 'dark') bg-dark @endif mt-0">
        <div class="page-header align-items-start min-vh-100">
            <div class="container my-auto">
                <div id="particles-js"></div>
                <div class="row">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div
                            class="card z-index-0 fadeIn3 fadeInBottom card-body-dark-mode-Glassmorphism custom-class-for-Glassmorphism">
                            <div class="card-body text-center ">
                                <img class="w-20" src="{{ asset('assets/img/favicon.ico') }}" alt="">
                                <h4 class="mt-4 text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif">
                                    {{ __('passwords.forget-password-title') }}</h4>
                                <small
                                    class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif">{{ __('passwords.forget-password-second-title') }}</small>

                                <hr />
                                @if (session('status'))
                                    <div class="alert alert-success bg-gradient-dark text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif"
                                        role="alert">
                                        <small>{{ session('status') }}</small>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf
                                    <div class="input-group input-group-outline my-3">
                                        <x-input name="email" type="email" title="{{ __('Email Address') }}"
                                            class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif form-control @error('email') is-invalid @enderror"
                                            :isRequired="true" attr="autocomplete='email' autofocus" :value="old('email')" />
                                    </div>
                                    <div class="text-center">
                                        <button type="submit"
                                            class="btn bg-gradient-dark w-100 my-4 mb-2">{{ __('Send') }}</button>
                                    </div>
                                    <p
                                        class="mt-1 text-sm text-center text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif">
                                        <a href="{{ route('login') }}"
                                            class="text-dark @if (env('DEFAULT_THEME') == 'dark') text-white @endif"><small>{{ __('auth.already_have_password') }}</small></a>
                                    </p>
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
