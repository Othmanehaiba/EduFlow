@extends('layouts.app')
@section('title', 'Mon espace')

@section('content')

<h2 class="fw-bold mb-4">Mon espace étudiant</h2>

{{-- Tabs navigation --}}
<ul class="nav nav-tabs mb-4" id="studentTabs">
    <li class="nav-item">
        <button class="nav-link active" onclick="showTab('enrolled')">
            <i class="bi bi-book me-1"></i>Mes cours
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="showTab('favorites')">
            <i class="bi bi-heart me-1"></i>Favoris
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="showTab('recommended')">
            <i class="bi bi-stars me-1"></i>Recommandés
        </button>
    </li>
</ul>

{{-- Tab: Enrolled courses --}}
<div id="tab-enrolled">
    <div class="loading-center" id="loading-enrolled">
        <div class="spinner-border text-dark" role="status"></div>
    </div>
    <div class="row g-4" id="enrolled-list"></div>
    <div class="empty-state d-none" id="empty-enrolled">
        <i class="bi bi-journal-x"></i>
        <p>Vous n'êtes inscrit à aucun cours.</p>
        <a href="/courses" class="btn btn-dark btn-sm">Parcourir les cours</a>
    </div>
</div>

{{-- Tab: Favorites --}}
<div id="tab-favorites" class="d-none">
    <div class="loading-center" id="loading-favorites">
        <div class="spinner-border text-dark" role="status"></div>
    </div>
    <div class="row g-4" id="favorites-list"></div>
    <div class="empty-state d-none" id="empty-favorites">
        <i class="bi bi-heart"></i>
        <p>Aucun cours sauvegardé.</p>
        <a href="/courses" class="btn btn-dark btn-sm">Parcourir les cours</a>
    </div>
</div>

{{-- Tab: Recommended --}}
<div id="tab-recommended" class="d-none">
    <div class="loading-center" id="loading-recommended">
        <div class="spinner-border text-dark" role="status"></div>
    </div>
    <div class="row g-4" id="recommended-list"></div>
    <div class="empty-state d-none" id="empty-recommended">
        <i class="bi bi-stars"></i>
        <p>Aucune recommandation. Ajoutez des intérêts à votre profil.</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
requireStudent();

// Track which tabs have been loaded to avoid duplicate API calls
const loaded = { enrolled: false, favorites: false, recommended: false };

document.addEventListener('DOMContentLoaded', function () {
    loadEnrolled(); // Load the first tab immediately
});

function showTab(tab) {
    // Hide all tabs
    ['enrolled', 'favorites', 'recommended'].forEach(t => {
        document.getElementById('tab-' + t).classList.add('d-none');
    });

    // Show selected tab
    document.getElementById('tab-' + tab).classList.remove('d-none');

    // Update active button
    document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    // Load data only once per tab
    if (tab === 'favorites'    && !loaded.favorites)    loadFavorites();
    if (tab === 'recommended'  && !loaded.recommended)  loadRecommended();
}

// --- Enrolled courses ---
async function loadEnrolled() {
    const result = await apiGet('/student/enrollments');
    document.getElementById('loading-enrolled').classList.add('d-none');
    loaded.enrolled = true;

    if (!result || result.status !== 200) return;

    // Only show active enrollments
    const enrollments = result.data.filter(e => e.status === 'enrolled');

    if (enrollments.length === 0) {
        document.getElementById('empty-enrolled').classList.remove('d-none');
        return;
    }

    document.getElementById('enrolled-list').innerHTML = enrollments.map(e => `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold">${e.course.title}</h5>
                    <p class="text-muted small">${truncate(e.course.description, 80)}</p>
                    ${e.group
                        ? `<span class="badge bg-success mb-2">
                               <i class="bi bi-people me-1"></i>${e.group.name}
                           </span>`
                        : ''}
                    <div class="mt-2">
                        <span class="badge bg-success">Inscrit</span>
                    </div>
                    <button class="btn btn-outline-danger btn-sm mt-3 w-100"
                            onclick="unenroll(${e.course_id}, this)">
                        <i class="bi bi-x-circle me-1"></i>Se désinscrire
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// --- Favorites ---
async function loadFavorites() {
    const result = await apiGet('/favorites');
    document.getElementById('loading-favorites').classList.add('d-none');
    loaded.favorites = true;

    if (!result || result.status !== 200) return;

    const favorites = result.data;

    if (favorites.length === 0) {
        document.getElementById('empty-favorites').classList.remove('d-none');
        return;
    }

    document.getElementById('favorites-list').innerHTML = favorites.map(fav => `
        <div class="col-md-6 col-lg-4" id="fav-${fav.course_id}">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold">${fav.course.title}</h5>
                    <p class="text-muted small">${truncate(fav.course.description, 80)}</p>
                    <div class="price-badge mb-3">${formatPrice(fav.course.price)}</div>
                    <div class="d-flex gap-2">
                        <a href="/courses/${fav.course_id}"
                           class="btn btn-dark btn-sm flex-grow-1">
                            Voir le cours
                        </a>
                        <button class="btn btn-outline-danger btn-sm"
                                onclick="removeFavorite(${fav.course_id})">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// --- Recommended ---
async function loadRecommended() {
    const result = await apiGet('/courses/recommended');
    document.getElementById('loading-recommended').classList.add('d-none');
    loaded.recommended = true;

    if (!result || result.status !== 200) return;

    const courses = result.data.data || result.data;

    if (!courses || courses.length === 0) {
        document.getElementById('empty-recommended').classList.remove('d-none');
        return;
    }

    document.getElementById('recommended-list').innerHTML = courses.map(course => `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm course-card"
                 onclick="window.location='/courses/${course.id}'">
                <div class="card-body p-4">
                    ${course.interest
                        ? `<span class="badge bg-secondary mb-2">${course.interest.name}</span>`
                        : ''}
                    <h5 class="fw-bold">${course.title}</h5>
                    <p class="text-muted small">${truncate(course.description, 80)}</p>
                    <div class="price-badge">${formatPrice(course.price)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

async function unenroll(courseId, btn) {
    if (!confirm('Voulez-vous vraiment vous désinscrire de ce cours ?')) return;

    btn.disabled = true;
    btn.textContent = 'Désinscription...';

    const result = await apiDelete('/unenroll/' + courseId);

    if (result && result.status === 200) {
        showToast('Désinscription effectuée.', 'info');
        btn.closest('.col-md-6').remove();
    } else {
        showToast('Erreur lors de la désinscription.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Se désinscrire';
    }
}

async function removeFavorite(courseId) {
    const result = await apiDelete('/favorites/' + courseId);
    if (result && result.status === 200) {
        document.getElementById('fav-' + courseId).remove();
        showToast('Retiré des favoris.', 'info');
    }
}
</script>
@endpush