@extends('layouts.app')
@section('title', 'Modifier le cours')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="/teacher/dashboard" class="btn btn-outline-secondary btn-sm">
                        ← Retour
                    </a>
                    <h3 class="fw-bold mb-0">Modifier le cours</h3>
                </div>

                <div class="loading-center" id="loading">
                    <div class="spinner-border text-dark" role="status"></div>
                </div>

                <div class="d-none" id="form-content">
                    <div class="alert alert-danger d-none" id="error-box"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre</label>
                        <input type="text" class="form-control" id="title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prix (€)</label>
                        <input type="number" class="form-control" id="price"
                               min="0" step="0.01">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Domaine</label>
                        <select class="form-select" id="interest_id">
                            <option value="">-- Aucun --</option>
                        </select>
                    </div>

                    <button class="btn btn-dark w-100 py-2" id="submit-btn"
                            onclick="updateCourse()">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
requireTeacher();

// Get course ID from URL: /teacher/courses/3/edit → 3
const pathParts = window.location.pathname.split('/');
const courseId  = pathParts[pathParts.length - 2];

document.addEventListener('DOMContentLoaded', async function () {
    // Load interests and course data at the same time
    const [interestsResult, courseResult] = await Promise.all([
        apiGet('/interests', false),
        apiGet('/courses/' + courseId, false),
    ]);

    document.getElementById('loading').classList.add('d-none');
    document.getElementById('form-content').classList.remove('d-none');

    // Fill interests dropdown
    if (interestsResult && interestsResult.status === 200) {
        const select = document.getElementById('interest_id');
        interestsResult.data.forEach(function (interest) {
            const option       = document.createElement('option');
            option.value       = interest.id;
            option.textContent = interest.name;
            select.appendChild(option);
        });
    }

    // Fill form with current course data
    if (courseResult && courseResult.status === 200) {
        const course = courseResult.data;
        document.getElementById('title').value       = course.title;
        document.getElementById('description').value = course.description;
        document.getElementById('price').value       = course.price;
        if (course.interest_id) {
            document.getElementById('interest_id').value = course.interest_id;
        }
    }
});

async function updateCourse() {
    const title       = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const price       = document.getElementById('price').value;
    const interest_id = document.getElementById('interest_id').value;

    if (!title || !description || price === '') {
        showError('Veuillez remplir tous les champs.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';
    btn.disabled  = true;

    const body = { title, description, price: parseFloat(price) };
    if (interest_id) body.interest_id = parseInt(interest_id);

    const result = await apiPut('/courses/' + courseId, body);

    btn.innerHTML = 'Enregistrer les modifications';
    btn.disabled  = false;

    if (!result) return;

    if (result.status === 200) {
        showToast('Cours mis à jour !', 'success');
        setTimeout(() => window.location.href = '/teacher/dashboard', 1000);
    } else {
        showError(result.data.message || 'Erreur lors de la mise à jour.');
    }
}

function showError(msg) {
    const box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('d-none');
}
</script>
@endpush