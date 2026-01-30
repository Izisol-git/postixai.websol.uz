<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Postix AI'))</title>

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        
        :root {
            --bg: #071427;
            --card: #0f2233;
            --text: #e7f4ff;
            --muted: #9fb7dd;
            --accent: #3b82f6;
            --accent-2: #facc15;
            --muted-2: rgba(255, 255, 255, 0.06);

            --sidebar-w: 300px;
            --overlay-bg: rgba(0, 0, 0, 0.45);
        }

        body.light {
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #0b1220;
            --muted: #6b7280;
            --accent: #2563eb;
            --accent-2: #d97706;
            --muted-2: rgba(0, 0, 0, 0.06);
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            background: var(--bg);
            color: var(--text);
            transition: background .2s ease, color .2s ease;
        }

        /* Layout */
        .layout {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar */
        .page-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            background: var(--bg);
            padding: 8px 16px;
            z-index: 2000;
            border-bottom: 1px solid var(--muted-2);
            flex-wrap: wrap;
        }

        .brand {
            font-weight: 800;
            font-size: 1.05rem;
        }

        .breadcrumbs a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumbs a:hover {
            color: var(--text);
        }

        /* Content */
        .content {
            flex: 1;
            padding: 18px 0;
            /* reduce side padding: container handles width */
        }

        /* container that uses 90% of viewport width (example: 100->90) */
        .container-max {
            width: 90vw;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Card */
        .card {
            background: var(--card);
            color: var(--text);
            border: 1px solid var(--muted-2);
            border-radius: 12px;
        }

        .btn-theme {
            background: transparent;
            border: 1px solid var(--muted-2);
            color: var(--text);
        }

        .profile-avatar {
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
            font-size: 14px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid var(--muted-2);
            background: transparent;
            color: inherit;
            font-weight: 600;
            text-decoration: none;
        }

        /* Sidebar off-canvas */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w, 300px);
            background: var(--card);
            border-right: 1px solid rgba(255, 255, 255, 0.03);
            transform: translateX(-100%);
            transition: transform .3s ease;
            z-index: 1300;
            overflow-y: auto;
            padding: 20px;
        }

        .app-sidebar.open {
            transform: translateX(0);
        }

        /* Sidebar overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s;
            z-index: 1250;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Sidebar buttons */
        .btn-sidebar {
            display: block;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 6px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: left;
            transition: background .2s;
        }

        .btn-sidebar:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Small helpers */
        .sidebar-section {
            margin-bottom: 16px;
        }

        .sidebar-section h6 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media(max-width: 768px) {
            .app-sidebar {
                width: 90vw;
            }
        }

        /* Sidebar (off-canvas) */
        /* Sidebar off-canvas */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w, 300px);
            background: var(--card);
            border-right: 1px solid rgba(255, 255, 255, 0.03);
            transform: translateX(-100%);
            transition: transform .3s ease;
            z-index: 1300;
            overflow-y: auto;
            padding: 20px;
        }

        .app-sidebar.open {
            transform: translateX(0);
        }

        /* Sidebar overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s;
            z-index: 1250;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }


        .sidebar-section {
            margin-bottom: 12px;
        }

        .sidebar-section h6 {
            margin-bottom: 8px;
            font-weight: 700;
        }

        .sidebar-link {
            display: block;
            padding: 8px 10px;
            border-radius: 8px;
            color: var(--text);
            text-decoration: none;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        @media(max-width: 576px) {
            :root {
                --sidebar-w: 92vw;
            }

            .container-max {
                width: 95vw;
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .page-topbar {
                padding: 8px 12px;
            }
        }

        @media (max-width: 768px) {
            .page-topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .brand {
                font-size: .95rem;
            }

            h4 {
                font-size: 1.1rem;
            }
        }

        /* small helpers */
        .muted-small {
            color: var(--muted);
            font-size: .9rem;
        }

        /* ===== OPTIMIZED SIDEBAR ===== */
        :root {
            --sidebar-w: clamp(240px, 85vw, 380px);
        }

        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w);
            background: var(--card);
            transform: translateX(-100%);
            transition: transform .22s ease;
            z-index: 1300;
            padding: 14px;
            overflow-y: auto;
            will-change: transform;
        }

        .app-sidebar.open {
            transform: translateX(0)
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: var(--overlay-bg);
            opacity: 0;
            visibility: hidden;
            transition: .18s;
            z-index: 1250;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible
        }

        .btn-sidebar {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            margin-bottom: 8px;
            border-radius: 12px;
            font-size: clamp(15px, 4vw, 18px);
            font-weight: 600;
            color: var(--text);
            background: rgba(255, 255, 255, .06);
            border: 1px solid var(--muted-2);
            text-decoration: none;
        }

        .btn-sidebar:hover {
            background: rgba(255, 255, 255, .1)
        }

        .sidebar-section h6 {
            font-size: clamp(14px, 3.5vw, 16px);
            font-weight: 700;
        }
    </style>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    <script>
        

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showSuccess(@json(session('success')));
            @endif
            @if (session('error'))
                showError(@json(session('error')));
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $err)
                    showError(@json($err));
                @endforeach
            @endif
        });
    </script>

    <div class="layout">

        <!-- TOPBAR -->
        <header class="page-topbar">

            <!-- Left -->
            <div class="d-flex align-items-center gap-3 topbar-left flex-wrap">

                <div class="brand">{{ config('app.name', 'Postix AI') }}</div>

                {{-- Sidebar toggle visible only if view requests it --}}
                @if (trim($__env->yieldContent('show-sidebar', '')) === 'true' || (isset($showSidebar) && $showSidebar))
                    <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary">☰</button>
                @endif

                @hasSection('show-back')
                    <a id="backBtn" href="{{ url()->previous() }}" class="back-btn">←
                        {{ __('messages.users.back_to_list') }}</a>
                @endif

                <div>
                    <h4 class="mb-0">@yield('page-title', __('messages.layout.page_title'))</h4>
                    <div class="text-muted small">@yield('page-subtitle')</div>
                </div>

                <div class="breadcrumbs small text-muted">
                    / <a
                        href="{{ route('departments.index') }}">{{ __('messages.layout.departments' ?? 'Departments') }}</a>
                </div>
            </div>

            <!-- Right -->
            <div class="d-flex align-items-center gap-2 topbar-right flex-wrap">

                <!-- Dashboard -->
                {{-- <a href="{{ route('departments.index') }}" class="btn btn-sm btn-theme">
                    {{ __('messages.layout.dashboard' ?? 'Dashboard') }}
                </a> --}}

                <!-- Departments -->
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-theme">
                    {{ __('messages.layout.departments' ?? 'Departments') }}
                </a>

                <!-- Theme -->
                <button id="themeToggleBtn" class="btn btn-sm btn-theme"
                    title="{{ __('messages.layout.toggle_theme') }}">☀️</button>

                <!-- Language -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        {{ strtoupper(app()->getLocale()) }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('/lang/uz') }}">Oʻzbekcha</a></li>
                        <li><a class="dropdown-item" href="{{ url('/lang/en') }}">English</a></li>
                        <li><a class="dropdown-item" href="{{ url('/lang/ru') }}">Русский</a></li>
                    </ul>
                </div>

                <!-- Profile -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown">

                        @if (auth()->user()->avatar ?? false)
                            <img src="{{ asset('storage/' . auth()->user()->avatar->path) }}" class="rounded-circle"
                                style="width:26px;height:26px;object-fit:cover">
                        @else
                            <span class="profile-avatar rounded-circle d-flex align-items-center justify-content-center"
                                style="width:26px;height:26px;font-weight:700;">
                                {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif

                        <span class="d-none d-md-inline">
                            {{ auth()->user()->name ?? __('messages.layout.profile') }}
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.users.show', auth()->user()->id) }}">
                                {{ __('messages.layout.profile') }}
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">{{ __('messages.layout.settings') }}</a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger">
                                    {{ __('messages.layout.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </header>

        {{-- Overlay for sidebar --}}
        <div id="sidebarOverlay" class="sidebar-overlay" tabindex="-1" aria-hidden="true"></div>

        {{-- Sidebar (render only when requested) --}}
        @if (trim($__env->yieldContent('show-sidebar', '')) === 'true' || (isset($showSidebar) && $showSidebar))
            <aside id="appSidebar" class="app-sidebar" role="complementary" aria-labelledby="sidebarTitle">
                {{-- Include partial (it should handle absent $department gracefully) --}}
                @includeWhen(View::exists('admin.partials.sidebar'), 'admin.partials.sidebar', [
                    'department' => $department ?? null,
                ])
            </aside>
        @endif

        <!-- MAIN -->
        <main class="content container-fluid p-0">
            <div class="container-max">
                @yield('content')
            </div>
        </main>


        <!-- FOOTER -->
        <footer class="py-3 text-center text-muted small">
            &copy; {{ date('Y') }} {{ config('app.name') }} —
            {{ __('messages.layout.rights_reserved' ?? 'All rights reserved') }}
        </footer>

    </div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* Theme toggle */
        (function() {
            const KEY = 'app_theme';
            const body = document.body;
            const btn = document.getElementById('themeToggleBtn');

            function apply(theme) {
                body.classList.toggle('light', theme === 'light');
                if (btn) btn.textContent = theme === 'light' ? '🌙' : '☀️';
            }

            let theme = localStorage.getItem(KEY) ||
                (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');

            apply(theme);

            if (btn) {
                btn.addEventListener('click', () => {
                    theme = body.classList.contains('light') ? 'dark' : 'light';
                    localStorage.setItem(KEY, theme);
                    apply(theme);
                });
            }
        })();

        /* Sidebar toggling */
        (function() {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('open');
                overlay.classList.add('show');
                body.classList.add('sidebar-open');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                body.classList.remove('sidebar-open');
                overlay.setAttribute('aria-hidden', 'true');
            }

            if (toggle) {
                toggle.addEventListener('click', () => {
                    if (sidebar.classList.contains('open')) closeSidebar();
                    else openSidebar();
                });
            }
            if (overlay) overlay.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeSidebar();
            });
        })();

        /* Back button */
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('backBtn');
            if (!btn) return;

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let ok = false;
                const pop = () => {
                    ok = true;
                    window.removeEventListener('popstate', pop);
                };
                window.addEventListener('popstate', pop);

                history.back();

                setTimeout(() => {
                    window.removeEventListener('popstate', pop);
                    if (!ok) window.location.href = btn.href;
                }, 250);
            });
        });

        /* small helpers for notifications (expected to exist or replace with your own) */
        function showSuccess(msg) {
            console.info('SUCCESS:', msg); /* replace with your toast */
        }

        function showError(msg) {
            console.error('ERROR:', msg); /* replace with your toast */
        }
    </script>
    @stack('scripts')
</body>

</html>
