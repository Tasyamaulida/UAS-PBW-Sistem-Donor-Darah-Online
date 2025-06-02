<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Donor Darah')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicon (optional) -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Custom Global Styles -->
    <style>
        body {
            background-color: #fff;
            color: #000;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .btn-red {
            background-color: #e53935;
            color: white;
        }
        .btn-red:hover {
            background-color: #c62828;
        }
        .card-red {
            border: 1px solid #e53935;
        }
        .card-red .card-body {
            color: #e53935;
        }
    </style>

    <!-- Page-Specific Styles -->
    @stack('styles')
</head>
<body>

    @include('partials.navbar')

    <main class="container mt-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS (for modal, dropdown, etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page-Specific Scripts -->
    @stack('scripts')
</body>
</html>
