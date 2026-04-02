@extends('layouts.app')
@section('title', 'Créer un cours')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="/teacher/dashboard" class="btn btn-outline-secondary btn-sm">
                        ← Retour
                    </a>
                    <h3 class="fw-bold mb-0">Créer un cours</h3>
                </div>

                <div class="alert alert-danger d-none" id="error-box"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Titre du cours</label>
                    <input type="text" class="form-control" id="title"
                           placeholder="Ex: Introduction à Laravel">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="description" rows="4"
                              placeholder="Décrivez le contenu du cours..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Prix (€)</label>
                    <input type="number" class="form-control" id="price"
                           placeholder="0" min="0" step="0.01">
                    <div class="form-text">Mettez 0 pour un cours gratuit.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Domaine / Intérêt</label>
                    <select class="form-select" id="interest_id">
                        <option value="">-- Aucun --</option>
                    </select>
                </div>

                <button class="btn btn-dark w-100 py-2" id="submit-btn"
                        onclick="createCourse()">
                    Créer le cours
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
requireTeacher();

// Load interests into the select dropdown
document.addEventListener('DOMContentLoaded', async function () {
    const result = await apiGet('/interests', false);
    if (!result || result.status !== 200) return;

    const select = document.getElementById('interest_id');
    result.data.forEach(function (interest) {
        const option = document.createElement('option');
        option.value       = interest.id;
        option.textContent = interest.name;
        select.appendChild(option);
    });
});

async function createCourse() {
    const title       = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const price       = document.getElementById('price').value;
    const interest_id = document.getElementById('interest_id').value;

    if (!title || !description || price === '') {
        showError('Veuillez remplir le titre, la description et le prix.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';
    btn.disabled  = true;

    const body = { title, description, price: parseFloat(price) };
    if (interest_id) body.interest_id = parseInt(interest_id);

    const result = await apiPost('/courses', body);

    btn.innerHTML = 'Créer le cours';
    btn.disabled  = false;

    if (!result) return;

    if (result.status === 201) {
        showToast('Cours créé avec succès !', 'success');
        setTimeout(() => window.location.href = '/teacher/dashboard', 1000);
    } else {
        if (result.data.errors) {
            showError(Object.values(result.data.errors)[0][0]);
        } else {
            showError(result.data.message || 'Erreur lors de la création.');
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