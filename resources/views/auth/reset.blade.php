@extends('layouts.app')
@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h2 class="fw-bold mb-2">Nouveau mot de passe</h2>
                <p class="text-muted small mb-4">
                    Entrez le token reçu par email et choisissez un nouveau mot de passe.
                </p>

                <div class="alert alert-danger d-none" id="error-box"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="vous@exemple.com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Token reçu par email</label>
                    <input type="text" class="form-control" id="token"
                           placeholder="Collez le token ici">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="password"
                           placeholder="Minimum 6 caractères">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="password_confirmation"
                           placeholder="Répétez le mot de passe">
                </div>

                <button class="btn btn-dark w-100 py-2" id="submit-btn"
                        onclick="doReset()">
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Pre-fill email if it was passed in the URL (?email=...)
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const email  = params.get('email');
    if (email) {
        document.getElementById('email').value = email;
    }
});

async function doReset() {
    const email                 = document.getElementById('email').value.trim();
    const token                 = document.getElementById('token').value.trim();
    const password              = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    if (!email || !token || !password || !password_confirmation) {
        showError('Veuillez remplir tous les champs.');
        return;
    }

    if (password !== password_confirmation) {
        showError('Les mots de passe ne correspondent pas.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Réinitialisation...';
    btn.disabled  = true;

    const result = await apiPost('/reset-password',
        { email, token, password, password_confirmation }, false);

    btn.innerHTML = 'Réinitialiser';
    btn.disabled  = false;

    if (!result) return;

    if (result.status === 200) {
        showToast('Mot de passe réinitialisé avec succès !', 'success');
        setTimeout(() => window.location.href = '/login', 1500);
    } else {
        showError(result.data.message || 'Token invalide ou expiré.');
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('d-none');
}
</script>
@endpush