<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    {{--
        viewport meta tag = makes the page responsive on mobile.
        Without this, the page looks tiny on phones.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{--
        @yield('title') is a placeholder.
        Each page replaces it with @section('title', 'My Page Title')
        If no page sets a title, it falls back to 'EduFlow'
    --}}
    <title>@yield('title', 'EduFlow')</title>

    {{-- Bootstrap CSS — gives us the grid, cards, buttons, forms, etc. --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    {{-- Bootstrap Icons — icons like bi-heart, bi-trash, bi-pencil --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
        rel="stylesheet">

    {{-- Our own CSS file (public/css/app.css) --}}
    <link href="/css/app.css" rel="stylesheet">
</head>

<body>

{{-- ============================================================
     NAVBAR
     navbar-dark bg-dark = white text on dark background
     navbar-expand-lg   = collapse to hamburger on small screens
     ============================================================ --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">

    {{-- Brand / Logo — clicking it goes to courses list --}}
    <a class="navbar-brand fw-bold fs-4" href="/courses">
        📚 EduFlow
    </a>

    {{--
        Hamburger button — only visible on small screens (mobile).
        Clicking it shows/hides the nav links below.
        data-bs-target="#navbarNav" links it to the div with id="navbarNav"
    --}}
    <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    {{-- The collapsible menu area --}}
    <div class="collapse navbar-collapse" id="navbarNav">

        {{-- Left side links — visible to everyone --}}
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link" href="/courses">
                    <i class="bi bi-grid"></i> Cours
                </a>
            </li>
        </ul>

        {{-- Right side links — change based on login state --}}
        <ul class="navbar-nav align-items-center gap-2">

            {{--
                GUEST links — shown when NOT logged in.
                They are visible by default.
                JS will hide them after checking localStorage.
            --}}
            <li class="nav-item" id="nav-guest">
                <a class="nav-link" href="/login">Connexion</a>
            </li>
            <li class="nav-item" id="nav-guest-register">
                <a class="btn btn-outline-light btn-sm px-3" href="/register">
                    S'inscrire
                </a>
            </li>

            {{--
                STUDENT links — hidden by default (d-none class).
                JS removes d-none if the user is a student.
            --}}
            <li class="nav-item d-none" id="nav-student">
                <a class="nav-link" href="/student/dashboard">
                    <i class="bi bi-person-circle"></i> Mon espace
                </a>
            </li>
            <li class="nav-item d-none" id="nav-favorites">
                <a class="nav-link" href="/student/favorites">
                    <i class="bi bi-heart"></i> Favoris
                </a>
            </li>
            <li class="nav-item d-none" id="nav-recommended">
                <a class="nav-link" href="/student/recommended">
                    <i class="bi bi-stars"></i> Recommandés
                </a>
            </li>

            {{--
                TEACHER links — hidden by default.
                JS removes d-none if the user is a teacher.
            --}}
            <li class="nav-item d-none" id="nav-teacher">
                <a class="nav-link" href="/teacher/dashboard">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </li>

            {{--
                LOGGED-IN links — hidden by default.
                Shown for both students and teachers.
            --}}
            <li class="nav-item d-none" id="nav-username">
                {{-- This span's text is set by JS: navbar-name --}}
                <span class="nav-link text-warning fw-bold" id="navbar-name"></span>
            </li>
            <li class="nav-item d-none" id="nav-logout">
                <button
                    class="btn btn-outline-danger btn-sm"
                    onclick="logout()">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </button>
            </li>

        </ul>
    </div>
</nav>

{{-- ============================================================
     TOAST NOTIFICATION
     A small floating box that appears top-right for 3 seconds.
     showToast('message', 'success') or showToast('error', 'danger')
     ============================================================ --}}
<div
    class="position-fixed top-0 end-0 p-3"
    style="z-index: 9999">

    <div
        id="toast"
        class="toast align-items-center text-white border-0"
        role="alert"
        aria-live="assertive">

        <div class="d-flex">
            <div class="toast-body fw-bold" id="toast-message">
                Message ici
            </div>
            {{-- X button to close the toast manually --}}
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">
            </button>
        </div>

    </div>
</div>

{{-- ============================================================
     MAIN CONTENT AREA
     @yield('content') is replaced by the actual page content.
     The container class centers and pads the content.
     py-4 = padding top and bottom of 1.5rem
     ============================================================ --}}
<main class="container py-4">
    @yield('content')
</main>

{{-- ============================================================
     FOOTER
     ============================================================ --}}
<footer class="bg-dark text-white text-center py-3 mt-5">
    <small>EduFlow &copy; {{ date('Y') }} — Plateforme d'apprentissage</small>
</footer>

{{-- Bootstrap JS — needed for navbar toggle, toasts, modals --}}
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

{{--
    Our global JS helper file.
    Loaded on EVERY page — contains apiGet(), apiPost(), showToast(), etc.
    Must be loaded BEFORE the page scripts below.
--}}
<script src="/js/api.js"></script>

{{--
    This script runs on EVERY page as soon as it loads.
    It updates the navbar to show the right links
    based on whether the user is logged in and their role.
--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        updateNavbar();
    });
</script>

{{--
    @stack('scripts') is a collection point.
    Each page can add its own JS using @push('scripts') ... @endpush
    and it gets injected here at the bottom of the page.
    This is important — scripts at the bottom load AFTER the HTML
    so they can find elements by ID without errors.
--}}
@stack('scripts')

</body>
</html>