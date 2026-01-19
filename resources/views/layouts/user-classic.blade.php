<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ App\Services\ThemeService::getThemeClasses() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MeetFlow') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --bg-light: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-dark: #212529;
            --border-color: #dee2e6;
        }

        /* Dark Mode Colors */
        .dark-mode {
            --bg-light: #212529;
            --bg-secondary: #343a40;
            --text-dark: #f8f9fa;
            --border-color: #495057;
            background-color: #212529 !important;
            color: #f8f9fa !important;
        }

        .dark-mode .navbar {
            background-color: #343a40 !important;
            border-bottom: 2px solid #495057;
        }

        .dark-mode .card {
            background-color: #343a40 !important;
            color: #f8f9fa !important;
            border-color: #495057 !important;
        }

        .dark-mode .table {
            color: #f8f9fa !important;
            border-color: #495057 !important;
        }

        .dark-mode .table thead {
            background-color: #495057 !important;
        }

        .dark-mode .table tbody tr {
            border-color: #495057 !important;
        }

        .dark-mode .table tbody tr:hover {
            background-color: #495057 !important;
        }

        .dark-mode .footer {
            background-color: #343a40 !important;
            border-top: 2px solid #495057;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-dark);
        }

        /* Classic Navbar */
        .navbar {
            background-color: var(--primary-color) !important;
            border-bottom: 3px solid rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        /* Classic Container */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Classic Cards */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            background-color: var(--bg-light);
        }

        .card-header {
            background-color: var(--bg-secondary);
            border-bottom: 2px solid var(--border-color);
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Classic Buttons */
        .btn {
            border-radius: 5px;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Classic Table */
        .table {
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
        }

        .table thead {
            background-color: var(--bg-secondary);
            border-bottom: 2px solid var(--border-color);
        }

        .table thead th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
            padding: 1rem;
            color: var(--text-dark);
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: var(--bg-secondary);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* Classic Alerts */
        .alert {
            border-radius: 5px;
            padding: 1rem 1.25rem;
            border: 1px solid transparent;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }

        /* Classic Footer */
        .footer {
            margin-top: 3rem;
            padding: 2rem 0;
            background-color: var(--bg-secondary);
            border-top: 3px solid var(--border-color);
            text-align: center;
        }

        .footer p {
            margin: 0.5rem 0;
            color: var(--secondary-color);
        }

        /* Classic Badges */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Classic Dropdown */
        .dropdown-menu {
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            padding: 0.625rem 1.25rem;
        }

        .dropdown-item:hover {
            background-color: var(--bg-secondary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }

            .main-container {
                margin: 1rem auto;
            }

            .card-header {
                font-size: 1.1rem;
                padding: 0.875rem 1rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
        }

        /* Professional spacing */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        p {
            line-height: 1.6;
        }

        .text-muted {
            color: var(--secondary-color) !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Classic Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('user.bookings.index') }}">
                <i class="fas fa-calendar-alt me-2"></i>{{ config('app.name', 'MeetFlow') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.bookings.index') }}">
                                <i class="fas fa-book me-1"></i>My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transactions.index') }}">
                                <i class="fas fa-receipt me-1"></i>Transactions
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Classic Footer -->
    <div class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'MeetFlow') }}. All rights reserved.</p>
            <p>
                <small class="text-muted">Powered by Classic Theme</small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
