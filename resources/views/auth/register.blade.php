@extends('layouts.app')
@section('title', 'Inscription')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h2 class="text-center fw-bold mb-4">Créer un compte</h2>

                <div class="alert alert-danger d-none" id="error-box"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom complet</label>
                    <input type="text" class="form-control" id="name"
                           placeholder="Ahmed Benali">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email"
                           placeholder="vous@exemple.com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mot de passe</label>
                    <input type="password" class="form-control" id="password"
                           placeholder="Minimum 6 caractères">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Je suis</label>
                    <select class="form-select" id="role" onchange="toggleInterests()">
                        <option value="student">Étudiant</option>
                        <option value="teacher">Enseignant</option>
                    </select>
                </div>

                {{-- Only shown when role = student --}}
                <div id="interests-section" class="mb-3">
                    <label class="form-label fw-semibold">
                        Mes centres d'intérêt
                        <span class="text-muted fw-normal small">(optionnel)</span>
                    </label>
                    <p class="text-muted small mb-2">
                        Choisissez des domaines pour recevoir des recommandations personnalisées.
                    </p>
                    {{-- Badges are injected here by JS --}}
                    <div id="interests-list" class="d-flex flex-wrap gap-2">
                        <span class="text-muted small">Chargement...</span>
                    </div>
                </div>

                <button class="btn btn-dark w-100 py-2 mt-2" id="register-btn"
                        onclick="doRegister()">
                    Créer mon compte
                </button>

                <p class="text-center text-muted small mt-3 mb-0">
                    Déjà inscrit ?
                    <a href="/login" class="fw-bold text-dark">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Stores the IDs of interests the student clicked
let selectedInterests = [];

// Load interests from the API when the page loads
document.addEventListener('DOMContentLoaded', async function () {
    const result = await apiGet('/interests', false);

    const container = document.getElementById('interests-list');

    if (!result || result.status !== 200) {
        container.innerHTML = '<span class="text-muted small">Impossible de charger les intérêts.</span>';
        return;
    }

    container.innerHTML = ''; // clear "Chargement..."

    result.data.forEach(function (interest) {
        const badge = document.createElement('span');
        badge.textContent  = interest.name;
        badge.className    = 'badge border bg-light text-dark interest-badge';
        badge.dataset.id   = interest.id;

        badge.addEventListener('click', function () {
            const id = parseInt(interest.id);

            if (selectedInterests.includes(id)) {
                // Deselect
                selectedInterests = selectedInterests.filter(i => i !== id);
                badge.classList.remove('selected');
            } else {
                // Select
                selectedInterests.push(id);
                badge.classList.add('selected');
            }
        });

        container.appendChild(badge);
    });
});

// Show or hide interests section based on selected role
function toggleInterests() {
    const role    = document.getElementById('role').value;
    const section = document.getElementById('interests-section');
    section.classList.toggle('d-none', role === 'teacher');
}

async function doRegister() {
    const name     = document.getElementById('name').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const role     = document.getElementById('role').value;

    if (!name || !email || !password) {
        showError('Veuillez remplir tous les champs.');
        return;
    }

    if (password.length < 6) {
        showError('Le mot de passe doit contenir au moins 6 caractères.');
        return;
    }

    const btn = document.getElementById('register-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';
    btn.disabled  = true;

    // Build request body
    const body = { name, email, password, role };

    // Only send interests if student selected some
    if (role === 'student' && selectedInterests.length > 0) {
        body.interest_ids = selectedInterests;
    }

    const result = await apiPost('/register', body, false);

    btn.innerHTML = 'Créer mon compte';
    btn.disabled  = false;

    if (!result) return;

    if (result.status === 201) {
        saveToken(result.data.access_token);
        saveUser(result.data.user);

        showToast('Compte créé avec succès !', 'success');

        setTimeout(() => {
            window.location.href = role === 'teacher'
                ? '/teacher/dashboard'
                : '/student/dashboard';
        }, 700);

    } else {
        // Laravel returns validation errors as { errors: { field: ['message'] } }
        if (result.data.errors) {
            const first = Object.values(result.data.errors)[0][0];
            showError(first);
        } else {
            showError(result.data.message || 'Erreur lors de l\'inscription.');
        }
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('d-none');
}
</script>
@endpush