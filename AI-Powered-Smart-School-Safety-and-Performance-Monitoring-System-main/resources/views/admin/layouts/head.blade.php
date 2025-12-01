<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.ico') }}">

    <title>
        {{ env('APP_NAME') }}{{ pageTitleForHead() }}
    </title>
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link id="pagestyle" href="{{ asset('assets/css/material-dashboard.css?v=3.2.0') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom-overrides.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/theme-system.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="//cdn.datatables.net/2.1.6/css/dataTables.dataTables.min.css" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

    <!-- jQuery Confirm Plugin -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

    <!-- Dynamic Theme Colors -->
    @php
        $settings = \App\Models\Setting::first() ?? new \App\Models\Setting();
        $themeColors = $settings->theme_colors ?? [];
    @endphp
    <style>
        :root {
            --primary-green: {{ $themeColors['--primary-color'] ?? '#06C167' }};
            --light-green: {{ $themeColors['--secondary-color'] ?? '#10B981' }};
            --dark-green: {{ $themeColors['--secondary-color'] ?? '#10B981' }};
            --accent-green: {{ $themeColors['--accent-color'] ?? '#F0FDF4' }};
            --soft-green: #DCFCE7;
            --success-green: {{ $themeColors['--success-color'] ?? '#10B981' }};
            --info-blue: {{ $themeColors['--info-color'] ?? '#3B82F6' }};
            --warning-orange: {{ $themeColors['--warning-color'] ?? '#F59E0B' }};
            --danger-red: {{ $themeColors['--danger-color'] ?? '#EF4444' }};
            --light-gray: #F9FAFB;
            --medium-gray: #F3F4F6;
            --dark-gray: #6B7280;
            --text-dark: #374151;
            --white: #FFFFFF;
            --shadow-soft: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-large: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

            /* Apply theme colors to Bootstrap variables */
            --bs-primary: {{ $themeColors['--primary-color'] ?? '#06C167' }};
            --bs-primary-rgb: {{ hexToRgb($themeColors['--primary-color'] ?? '#06C167') }};
            --bs-secondary: {{ $themeColors['--secondary-color'] ?? '#10B981' }};
            --bs-secondary-rgb: {{ hexToRgb($themeColors['--secondary-color'] ?? '#10B981') }};
            --bs-success: {{ $themeColors['--success-color'] ?? '#10B981' }};
            --bs-success-rgb: {{ hexToRgb($themeColors['--success-color'] ?? '#10B981') }};
            --bs-info: {{ $themeColors['--info-color'] ?? '#3B82F6' }};
            --bs-info-rgb: {{ hexToRgb($themeColors['--info-color'] ?? '#3B82F6') }};
            --bs-warning: {{ $themeColors['--warning-color'] ?? '#F59E0B' }};
            --bs-warning-rgb: {{ hexToRgb($themeColors['--warning-color'] ?? '#F59E0B') }};
            --bs-danger: {{ $themeColors['--danger-color'] ?? '#EF4444' }};
            --bs-danger-rgb: {{ hexToRgb($themeColors['--danger-color'] ?? '#EF4444') }};

            /* RGB values for transparency effects */
            --primary-rgb: {{ hexToRgb($themeColors['--primary-color'] ?? '#06C167') }};
            --secondary-rgb: {{ hexToRgb($themeColors['--secondary-color'] ?? '#10B981') }};
            --accent-rgb: {{ hexToRgb($themeColors['--accent-color'] ?? '#F0FDF4') }};
        }

        /* Primary theme elements */
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
        }

        .bg-gradient-dark {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green)) !important;
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, var(--light-green), var(--primary-green)) !important;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, var(--success-green), var(--primary-green)) !important;
        }

        .bg-primary {
            background-color: var(--primary-green) !important;
        }

        .text-primary {
            color: var(--primary-green) !important;
        }

        /* Button theming */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            border-color: var(--primary-green) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--light-green), var(--dark-green)) !important;
            border-color: var(--dark-green) !important;
        }

        .btn-outline-primary {
            color: var(--primary-green) !important;
            border-color: var(--primary-green) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-green) !important;
            border-color: var(--primary-green) !important;
        }

        /* Stat icons theming */
        .stat-icon,
        .stat-icon i,
        .stat-icon span {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            color: white !important;
        }

        /* Quick action buttons theming */
        .quick-action-btn {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            border-color: var(--primary-green) !important;
            color: white !important;
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, var(--light-green), var(--dark-green)) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        /* Navigation theming */
        .navbar-brand {
            color: var(--primary-green) !important;
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            color: white !important;
        }

        .sidenav .nav-link.active {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
        }

        /* Card and component theming */
        .card-primary .card-header {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            color: white !important;
        }

        /* Progress bars */
        .progress-bar {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
        }

        /* Form controls focus */
        .form-control:focus {
            border-color: var(--primary-green) !important;
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25) !important;
        }

        /* Badges */
        .badge-primary {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
        }

        /* Alerts */
        .alert-primary {
            background-color: rgba(var(--primary-rgb), 0.1) !important;
            border-color: var(--primary-green) !important;
            color: var(--primary-green) !important;
        }

        /* Links */
        a {
            color: var(--primary-green);
        }

        a:hover {
            color: var(--dark-green);
        }

        /* Tables */
        .table-primary {
            background-color: rgba(var(--primary-rgb), 0.1) !important;
        }

        /* Material Dashboard specific overrides */
        .bg-gradient-faded-primary,
        .bg-gradient-faded-success {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.8), rgba(var(--secondary-rgb), 0.8)) !important;
        }

        /* Additional gradient classes for consistency */
        .bg-gradient-primary-dark {
            background: linear-gradient(135deg, var(--primary-green), #047857) !important;
        }

        .bg-gradient-light-primary {
            background: linear-gradient(135deg, #D1FAE5, var(--primary-green)) !important;
        }

        /* Icon backgrounds */
        .icon-background,
        .avatar-primary,
        .notification-primary {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
            color: white !important;
        }

        /* Dashboard cards */
        .dashboard-card .card-header {
            background: linear-gradient(135deg, var(--primary-green), var(--light-green)) !important;
        }

        /* School customization - hide animations if disabled */
        @if (!($settings->enable_animations ?? true))
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        @endif
    </style>

    @yield('css')
</head>
