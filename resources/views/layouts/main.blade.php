<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Donor Darah')</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #fff;
            color: #222;
        }
        header {
            background-color: #c0392b;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            background-color: #e74c3c;
            padding: 10px;
            text-align: center;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }
        main {
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Donor Darah</h1>
    </header>

    <nav>
        <a href="{{ route('guest.home') }}">Beranda</a>
        <a href="{{ route('guest.tentang') }}">Tentang</a>
        <a href="{{ route('dashboard.pendonor') }}">Dashboard Pendonor</a>
        <a href="{{ route('dashboard.penerima') }}">Dashboard Penerima</a>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>
