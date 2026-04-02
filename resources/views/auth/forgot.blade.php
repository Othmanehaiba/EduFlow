@extends('layouts.app')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h2 class="fw-bold mb-2">Mot de passe oublié</h2>
                <p class="text-muted small mb-4">
                    Entrez votre email et nous vous enverrons un code de réinitialisation.
                </p>

                <div class="alert alert-danger d-none" id="error-box"></div>
                <div class="alert alert-success d-none" id="success-box"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="vous@exemple.com">
                </div>

                <button class="btn btn-dark w-100 py-2" id="submit-btn"
                        onclick="doForgot()">
                    Envoyer le code
                </button>

                <div class="text-center mt-3">
                    <a href="/login" class="text-muted small">
                        ← Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function doForgot() {
    const email = document.getElementById('email').value.trim();

    if (!email) {
        showError('Veuillez entrer votre email.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi...';
    btn.disabled  = true;

    const result = await apiPost('/forgot-password', { email }, false);

    btn.innerHTML = 'Envoyer le code';
    btn.disabled  = false;

    if (!result) return;

    if (result.status === 200) {
        // Hide error, show success
        document.getElementById('error-box').classList.add('d-none');
        const successBox = document.getElementById('success-box');
        successBox.textContent = 'Email envoyé ! Consultez votre boîte mail et copiez le token reçu.';
        successBox.classList.remove('d-none');

        // After 2 seconds redirect to reset page with the email prefilled
        setTimeout(() => {
            window.location.href = '/reset-password?email=' + encodeURIComponent(email);
        }, 2000);
    } else {
        showError(result.data.message || 'Erreur. Vérifiez votre email.');
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('d-none');
}
</script>
@endpush