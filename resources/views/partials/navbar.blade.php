<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center text-danger fw-bold" href="{{ route('guest.home') }}">
            <img src="{{ asset('images/blood.png') }}" style="width: 50px; height: 50px;" class="me-2" alt="Blood Icon">
            Donor Darah
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
            aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a href="{{ route('guest.tentang') }}" class="nav-link text-danger">Tentang Kami</a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('edukasi.index') }}" class="nav-link text-danger">Edukasi</a>
                </li>

                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link text-danger">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link text-danger">Register</a>
                    </li>
                @endguest

                @auth
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-danger">Dashboard</a>
                    </li>
                    <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link text-danger p-0">Logout</button>
                    </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
