@extends('admin.layouts.app')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/auth/login.css'])

    @php
        $settings = \App\Models\Setting::first() ?? new \App\Models\Setting();
        $themeColors = $settings->theme_colors ?? [];
        $primaryColor = $themeColors['--primary-color'] ?? '#06C167';
        $secondaryColor = $themeColors['--secondary-color'] ?? '#10B981';
        $accentColor = $themeColors['--accent-color'] ?? '#F0FDF4';
    @endphp

    <style>
        :root {
            --login-primary: {{ $primaryColor }};
            --login-secondary: {{ $secondaryColor }};
            --login-accent: {{ $accentColor }};
            --login-primary-rgb: {{ hexToRgb($primaryColor) }};
            --login-secondary-rgb: {{ hexToRgb($secondaryColor) }};
        }

        .login-page {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--login-primary) 0%, var(--login-secondary) 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(var(--login-primary-rgb), 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(var(--login-secondary-rgb), 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(var(--login-primary-rgb), 0.2) 0%, transparent 50%);
            z-index: 1;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 2;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            background: var(--login-primary);
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 20%;
            right: 10%;
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #45b7d1, #96ceb4);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 10%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #f093fb, #f5576c);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .login-container {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-card:hover::before {
            left: 100%;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .login-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-align: center;
        }

        .login-subtitle {
            color: #666;
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(var(--login-primary-rgb), 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            bottom: 0%;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-input:focus+.input-icon {
            color: var(--login-primary);
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            bottom: 0%;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .custom-checkbox {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #e1e5e9;
            border-radius: 4px;
            margin-right: 8px;
            transition: all 0.3s ease;
        }

        .custom-checkbox input:checked~.checkmark {
            background-color: var(--login-primary);
            border-color: var(--login-primary);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked~.checkmark:after {
            display: block;
        }

        .forgot-link {
            color: var(--login-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--login-secondary);
        }

        .login-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--login-primary) 0%, var(--login-secondary) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(var(--login-primary-rgb), 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button .btn-text {
            position: relative;
            z-index: 2;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--login-secondary) 0%, var(--login-primary) 100%);
            transition: left 0.3s ease;
            z-index: 1;
        }

        .login-button:hover::before {
            left: 0;
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c66;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading .btn-text::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-left: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 30px 20px;
                margin: 20px;
                border-radius: 15px;
            }

            .login-title {
                font-size: 24px;
            }

            .form-input {
                padding: 12px 15px 12px 45px;
                font-size: 15px;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        .dark-mode .login-card {
            background: rgba(30, 30, 30, 0.95);
            color: white;
        }

        .dark-mode .login-title {
            color: white;
        }

        .dark-mode .form-input {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .dark-mode .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);

            :root {
                --login-primary: {{ $primaryColor }};
                --login-secondary: {{ $secondaryColor }};
                --login-accent: {{ $accentColor }};
                --login-primary-rgb: {{ hexToRgb($primaryColor) }};
                --login-secondary-rgb: {{ hexToRgb($secondaryColor) }};
            }
    </style>
@endsection

@section('content')
    <div class="login-page @if (env('DEFAULT_THEME') == 'dark') dark-mode @endif">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <div class="login-container">
            <div class="login-card">
                <div class="logo-container">
                    <img src="{{ asset('assets/img/logo_text.png') }}" alt="School Logo">
                </div>

                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to access your school dashboard</p>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    @if ($errors->any())
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="form-group">

                        <x-input name="email" type="email" class="form-input @error('email') is-invalid @enderror"
                            placeholder="Enter your email address" :isRequired="true" attr="autocomplete='email' autofocus"
                            :value="old('email')" />
                        <i class="fas fa-envelope input-icon"></i>
                    </div>

                    <div class="form-group">
                        <x-input name="password" type="password" class="form-input @error('password') is-invalid @enderror"
                            placeholder="Enter your password" :isRequired="true"
                            attr="autocomplete='current-password' id='passwordInput'" />
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>

                    <div class="remember-forgot">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            Remember me
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="login-button" id="loginButton">
                        <span class="btn-text">Sign In</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('passwordInput');

            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                }
            });

            // Form submission with loading animation
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const btnText = loginButton.querySelector('.btn-text');

            loginForm.addEventListener('submit', function() {
                loginButton.classList.add('loading');
                btnText.textContent = 'Signing In...';
            });

            // Input focus animations
            const formInputs = document.querySelectorAll('.form-input');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });

                // Check if input has value on page load
                if (input.value !== '') {
                    input.parentElement.classList.add('focused');
                }
            });

            // Add smooth entrance animation
            setTimeout(() => {
                document.querySelector('.login-card').style.opacity = '1';
                document.querySelector('.login-card').style.transform = 'translateY(0)';
            }, 100);

            // Floating shapes animation
            function createFloatingShapes() {
                const shapesContainer = document.querySelector('.floating-shapes');

                // Add more dynamic floating elements
                for (let i = 0; i < 5; i++) {
                    const shape = document.createElement('div');
                    shape.className = 'dynamic-shape';
                    shape.style.cssText = `
                        position: absolute;
                        width: ${Math.random() * 60 + 20}px;
                        height: ${Math.random() * 60 + 20}px;
                        background: linear-gradient(${Math.random() * 360}deg,
                            rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.1),
                            rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.1));
                        border-radius: ${Math.random() * 50}%;
                        top: ${Math.random() * 100}%;
                        left: ${Math.random() * 100}%;
                        animation: float ${Math.random() * 10 + 5}s ease-in-out infinite;
                        animation-delay: ${Math.random() * 5}s;
                    `;
                    shapesContainer.appendChild(shape);
                }
            }

            createFloatingShapes();
        });
    </script>
@endsection
