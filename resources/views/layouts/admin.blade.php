<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.ico') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/css/admin.css'])
    @stack('scripts')
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark" style="background-color: #4c2d10;">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/orders') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                        <i class="bi bi-card-list"></i>
                        Заказы
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/ingredient-movements*') ? 'active' : '' }}" href="{{ route('admin.ingredient-movements.index') }}">
                        <i class="bi bi-card-list"></i>
                        Движение сырья
                    </a>
                </li>

                @if(auth()->user()->hasRole('admin'))
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/buses') ? 'active' : '' }}" href="{{ route('admin.buses.index') }}">
                            <i class="bi bi-bus-front"></i>
                            Бусы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/products') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                            <i class="bi bi-basket"></i>
                            Продукты
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/ingredients') ? 'active' : '' }}" href="{{ route('admin.ingredients.index') }}">
                            <i class="bi bi-egg-fried"></i>
                            Ингредиенты
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('admin/feedbacks*') ? 'active' : '' }}" href="{{ route('admin.feedbacks.index') }}">
                            <i class="bi bi-chat-left-text"></i>
                            Обратная связь
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <span class="navbar-text me-3" id="username-toggle" style="cursor: pointer;">
                            {{ auth()->user()->name }}
                        </span>
                        <ul class="dropdown-menu dropdown-menu-end" id="user-menu" style="display: none;">
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                    Выйти
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
    @include('notifications.notifications')
    @yield('content')
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const usernameToggle = document.getElementById('username-toggle');
    const userMenu = document.getElementById('user-menu');

    if (usernameToggle && userMenu) {
        // Показать/скрыть меню при клике на имя пользователя
        usernameToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (userMenu.style.display === 'none' || userMenu.style.display === '') {
                userMenu.style.display = 'block';
            } else {
                userMenu.style.display = 'none';
            }
        });

        // Скрыть меню при клике в любом другом месте
        document.addEventListener('click', function() {
            userMenu.style.display = 'none';
        });

        // Предотвратить скрытие при клике на само меню
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
</body>
</html>
