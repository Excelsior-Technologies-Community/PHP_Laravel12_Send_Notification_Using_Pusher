<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- 
        CSRF Token 
        --------------------------------------------------------------
        This token protects your application from Cross-Site Request 
        Forgery attacks. It is automatically included in all AJAX
        requests and Laravel forms.
    -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="auth-user-id" content="{{ auth()->id() }}">
    @endauth

    <!-- 
        Application Title
        --------------------------------------------------------------
        Uses value from config/app.php, fallback = "Laravel"
    -->
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts (Loaded from Bunny CDN for speed) -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- 
        Vite Asset Loading 
        --------------------------------------------------------------
        This loads both:
        - app.scss (Bootstrap + custom styles)
        - app.js   (Bootstrap JS + Echo + Pusher)
        
        Vite compiles, bundles & refreshes these assets.
    -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- 
        Extra Scripts Section 
        --------------------------------------------------------------
        This is used by child Blade views to inject custom JavaScript.
        Example: posts.blade.php injects real-time Echo listener script here.
    -->
    @yield('script')
</head>

<body>
    <div id="app">

        <!-- 
            Navbar Component 
            --------------------------------------------------------------
            This navigation bar is visible on every page. It displays:
            - Brand name
            - Login & Register buttons for guests
            - Posts link + Username + Logout button for authenticated users
        -->
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">

                <!-- App Brand / Home Link -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    Laravel Send Realtime Notification using Pusher
                </a>

                <!-- Responsive Toggle Button (Mobile View) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar Menu -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <!-- Left Side Empty (you can add menu items here later) -->
                    <ul class="navbar-nav me-auto"></ul>

                    <!-- Right Side Menu -->
                    <ul class="navbar-nav ms-auto">

                        <!-- If user is NOT logged in -->
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
                            <!-- Show Posts link only when user is logged in -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('posts.index') }}">{{ __('Posts') }}</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('notifications.index') }}">{{ __('Notifications') }}</a>
                            </li>

                            <!-- Notification Bell Dropdown -->
<li class="nav-item dropdown">
    <a
        id="notificationDropdown"
        class="nav-link dropdown-toggle position-relative"
        href="#"
        role="button"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <i class="fa fa-bell"></i>

        <span
            id="notification-badge"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
            style="font-size: 10px;"
        >
            0
        </span>
    </a>

    <div
        class="dropdown-menu dropdown-menu-end shadow"
        aria-labelledby="notificationDropdown"
        style="
            min-width: 350px;
            max-width: 400px;
            max-height: 450px;
            overflow-y: auto;
        "
    >

<div
    class="dropdown-header d-flex justify-content-between align-items-center"
>
    <strong>
        <i class="fa fa-bell"></i>
        Notifications
    </strong>

    <span
        class="badge bg-primary"
        id="notification-dropdown-count"
    >
        0 Unread
    </span>
</div>

        <div class="dropdown-divider"></div>

        <div id="notification-list">

            <div
                class="px-3 py-3 text-muted small text-center"
                data-empty-notification
            >
                Loading notifications...
            </div>

        </div>

        <div class="dropdown-divider"></div>

        <a
            class="dropdown-item text-center"
            href="{{ route('notifications.index') }}"
        >
            <i class="fa fa-list"></i>
            View All Notifications
        </a>

    </div>
</li>

                            <!-- User Dropdown Menu -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                                    <!-- Logout Button -->
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <!-- Hidden Logout Form -->
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>

                                </div>
                            </li>
                        @endguest

                    </ul>
                </div>
            </div>
        </nav>

        <!-- 
            Main Content Section 
            --------------------------------------------------------------
            All pages load their main content here through @yield('content').
        -->
        <main class="py-4">
            @yield('content')
        </main>

        <!-- Toast Container -->
        <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>

    </div>
</body>

</html>
