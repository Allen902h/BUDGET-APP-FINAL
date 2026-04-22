<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <title>@yield('title', config('app.name', 'Budget App'))</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    <style>
        .system-popup-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(35, 23, 61, 0.48);
            backdrop-filter: blur(10px);
            z-index: 1200;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 220ms cubic-bezier(.2,.8,.2,1), visibility 220ms linear;
        }

        .system-popup-overlay.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .system-popup {
            width: min(100%, 520px);
            padding: 24px;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background:
                linear-gradient(145deg, rgba(255, 249, 242, 0.98), rgba(246, 240, 255, 0.98));
            box-shadow: 0 24px 64px rgba(44, 33, 81, 0.24);
            color: #1f2940;
            transform: translateY(18px) scale(0.97);
            transition: transform 240ms cubic-bezier(.2,.8,.2,1), opacity 220ms cubic-bezier(.2,.8,.2,1);
            opacity: 0;
        }

        .system-popup-overlay.is-visible .system-popup {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .system-popup-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .system-popup-badge {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.35rem;
            font-weight: 900;
            color: #fffefc;
            background: linear-gradient(135deg, #ff9800, #ff8c6d 58%, #7865ff);
            box-shadow: 0 14px 28px rgba(120, 101, 255, 0.18);
            flex-shrink: 0;
        }

        .system-popup-title {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.1;
            color: #1b2340;
        }

        .system-popup-subtitle {
            margin: 6px 0 0;
            color: rgba(31, 41, 64, 0.7);
            line-height: 1.5;
        }

        .system-popup-close {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(120, 101, 255, 0.14);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            color: #2c2151;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .system-popup-list {
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .system-popup-item {
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(120, 101, 255, 0.1);
            background: rgba(255, 255, 255, 0.8);
            line-height: 1.55;
        }

        .system-popup-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .system-popup-button {
            min-height: 46px;
            padding: 0 18px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #ff9800, #ff8c6d 58%, #7865ff);
            color: #fffaf5;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(120, 101, 255, 0.16);
        }

        .system-popup[data-popup-tone="error"] .system-popup-badge {
            background: linear-gradient(135deg, #f97316, #dc2626 58%, #7c3aed);
        }
    </style>
</head>
<body class="@yield('body_class', 'app-shell')">
    <div class="app-loading-overlay is-active" data-loading-overlay aria-hidden="true">
        <div class="app-loading-card" role="status" aria-live="polite">
            <img class="app-loading-coin" src="{{ asset('images/Coin.gif') }}" alt="" aria-hidden="true">
            <div class="app-loading-dots" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <p class="app-loading-title">Loading your workspace</p>
            <p class="app-loading-text">Please wait while the system prepares your page.</p>
        </div>
    </div>

    <div class="ambient ambient-one"></div>
    <div class="ambient ambient-two"></div>

    @php
        $isAuthenticated = auth()->check();
        $brandName = 'KUYA ALLEN TECH SOLUTIONS';
        $brandTagline = 'Smart budget and tech workflow';
        $brandLogoFile = file_exists(public_path('images/kuya-allen-logo.png'))
            ? 'images/kuya-allen-logo.png'
            : (file_exists(public_path('images/wowlogo.png')) ? 'images/wowlogo.png' : 'icons/icon.svg');
        $brandLogo = asset($brandLogoFile).'?v='.(file_exists(public_path($brandLogoFile)) ? filemtime(public_path($brandLogoFile)) : time());
    @endphp

    <header class="topbar">
        <div class="shell topbar-inner">
            <div class="brand">
                <a href="{{ route('logo.viewer') }}" aria-label="View {{ $brandName }} logo">
                    <img class="brand-logo" src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
                </a>
                <a href="{{ $isAuthenticated ? route('dashboard') : route('welcome') }}">
                    <span>
                        <strong>{{ $brandName }}</strong>
                        <small>{{ $brandTagline }}</small>
                    </span>
                </a>
            </div>

            <nav class="topnav">
                @if ($isAuthenticated)
                    <span class="nav-coin-shell" aria-hidden="true">
                        <span class="nav-coin-orbit"></span>
                        <img class="nav-coin-image" src="{{ asset('images/Coin.gif') }}" alt="" aria-hidden="true">
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ghost-button">Logout</button>
                    </form>
                @else
                    <a href="{{ route('welcome') }}">Home</a>
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}" class="ghost-button">Get Started</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="shell page-content">
        @yield('content')
    </main>

    @php
        $popupMessages = [];
        $popupTone = null;
        $popupTitle = null;
        $popupSubtitle = null;

        if ($errors->any()) {
            $popupMessages = $errors->all();
            $popupTone = 'error';
            $popupTitle = 'Please check these errors';
            $popupSubtitle = 'Some information needs to be fixed before the system can continue.';
        } elseif (session('error')) {
            $popupMessages = [session('error')];
            $popupTone = 'error';
            $popupTitle = 'Something went wrong';
            $popupSubtitle = 'The system found a problem with your request.';
        } elseif (session('success')) {
            $popupMessages = [session('success')];
            $popupTone = 'success';
            $popupTitle = 'Action completed';
            $popupSubtitle = 'Your latest change was saved successfully.';
        }
    @endphp

    @if(! empty($popupMessages))
        <div class="system-popup-overlay is-visible" data-system-popup-overlay>
            <div class="system-popup" data-system-popup data-popup-tone="{{ $popupTone }}" role="alertdialog" aria-modal="true" aria-labelledby="system-popup-title">
                <div class="system-popup-header">
                    <div style="display:flex; gap:14px; align-items:flex-start;">
                        <div class="system-popup-badge">{{ $popupTone === 'error' ? '!' : 'OK' }}</div>
                        <div>
                            <h2 class="system-popup-title" id="system-popup-title">{{ $popupTitle }}</h2>
                            <p class="system-popup-subtitle">{{ $popupSubtitle }}</p>
                        </div>
                    </div>
                    <button type="button" class="system-popup-close" aria-label="Close popup" data-system-popup-close>&times;</button>
                </div>

                <ul class="system-popup-list">
                    @foreach($popupMessages as $message)
                        <li class="system-popup-item">{{ $message }}</li>
                    @endforeach
                </ul>

                <div class="system-popup-actions">
                    <button type="button" class="system-popup-button" data-system-popup-close>Okay</button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}"></script>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('sw.js') }}');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.querySelector('[data-system-popup-overlay]');
            const popup = document.querySelector('[data-system-popup]');

            if (!overlay || !popup) {
                return;
            }

            const closePopup = () => {
                overlay.classList.remove('is-visible');
            };

            overlay.querySelectorAll('[data-system-popup-close]').forEach((button) => {
                button.addEventListener('click', closePopup);
            });

            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    closePopup();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closePopup();
                }
            });
        });
    </script>
</body>
</html>
