@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h2 class="text-center fw-bold mb-4">Connexion</h2>

                {{-- Error box, hidden until JS shows it --}}
                <div class="alert alert-danger d-none" id="error-box"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="vous@exemple.com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mot de passe</label>
                    <input type="password" class="form-control" id="password"
                           placeholder="••••••••">
                </div>

                <button class="btn btn-dark w-100 py-2" id="login-btn"
                        onclick="doLogin()">
                    Se connecter
                </button>

                <div class="text-center mt-3">
                    <a href="/forgot-password" class="text-muted small">
                        Mot de passe oublié ?
                    </a>
                </div>

                <hr class="my-3">

                <p class="text-center text-muted small mb-0">
                    Pas encore de compte ?
                    <a href="/register" class="fw-bold text-dark">S'inscrire</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// If already logged in, redirect immediately
if (isLoggedIn()) {
    window.location.href = isTeacher() ? '/teacher/dashboard' : '/student/dashboard';
}

async function doLogin() {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    // Basic front-end validation before calling the API
    if (!email || !password) {
        showError('Veuillez remplir tous les champs.');
        return;
    }

    // Show loading state so the user knows something is happening
    const btn = document.getElementById('login-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Connexion...';
    btn.disabled = true;

    // auth=false because we don't have a token yet
    const result = await apiPost('/login', { email, password }, false);

    // Reset the button no matter what happens
    btn.innerHTML = 'Se connecter';
    btn.disabled = false;

    if (!result) return; // network error — apiCall already showed toast

    if (result.status === 200) {
        // Save token and user info to localStorage
        saveToken(result.data.access_token);
        saveUser(result.data.user);

        showToast('Connexion réussie !', 'success');

        // Small delay so the user sees the success toast
        setTimeout(() => {
            window.location.href = isTeacher()
                ? '/teacher/dashboard'
                : '/student/dashboard';
        }, 700);

    } else {
        showError(result.data.message || 'Email ou mot de passe incorrect.');
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('d-none');
}

// Submit on Enter key
document.addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
});
</script>
@endpush