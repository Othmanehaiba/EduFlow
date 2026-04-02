@extends('layouts.app')
@section('title', 'Détail du cours')

@section('content')

{{-- Loading state --}}
<div class="loading-center" id="loading">
    <div class="spinner-border text-dark" role="status"></div>
</div>

{{-- Course content injected by JS --}}
<div class="d-none" id="course-content">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/courses">Cours</a></li>
            <li class="breadcrumb-item active" id="breadcrumb-title">...</li>
        </ol>
    </nav>

    <div class="row g-4">

        {{-- Left: course info --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4">
                <div id="interest-badge" class="mb-2"></div>
                <h1 class="fw-bold" id="course-title"></h1>
                <p class="text-muted" id="course-description"></p>

                <hr>

                <div class="d-flex gap-4 text-muted small">
                    <span>
                        <i class="bi bi-person-fill me-1"></i>
                        Enseignant : <strong id="teacher-name"></strong>
                    </span>
                    <span>
                        <i class="bi bi-people-fill me-1"></i>
                        <span id="enrolled-count">0</span> inscrits
                    </span>
                </div>
            </div>
        </div>

        {{-- Right: action card --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center sticky-top" style="top:80px">
                <div class="display-6 fw-bold text-success mb-3" id="course-price"></div>

                {{-- Shown only to students --}}
                <div id="student-actions" class="d-none">
                    <button class="btn btn-dark w-100 mb-2" id="btn-enroll"
                            onclick="enrollCourse()">
                        <i class="bi bi-credit-card me-2"></i>S'inscrire et payer
                    </button>
                    <button class="btn btn-outline-danger w-100" id="btn-favorite"
                            onclick="toggleFavorite()">
                        <i class="bi bi-heart me-2"></i>Ajouter aux favoris
                    </button>
                </div>

                {{-- Shown to guests --}}
                <div id="guest-actions">
                    <a href="/login" class="btn btn-dark w-100">
                        Connectez-vous pour vous inscrire
                    </a>
                </div>

                {{-- Shown after enrollment --}}
                <div class="alert alert-success d-none mt-3" id="enrolled-badge">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Vous êtes inscrit à ce cours
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Get the course ID from the URL — /courses/3 → id = 3
const courseId = window.location.pathname.split('/').pop();

let isFavorite   = false;
let isEnrolled   = false;

document.addEventListener('DOMContentLoaded', async function () {
    // Load course details — public route
    const result = await apiGet('/courses/' + courseId, false);

    document.getElementById('loading').classList.add('d-none');

    if (!result || result.status !== 200) {
        document.getElementById('course-content').innerHTML =
            '<div class="alert alert-danger">Cours introuvable.</div>';
        document.getElementById('course-content').classList.remove('d-none');
        return;
    }

    const course = result.data;

    // Fill in the page with course data
    document.getElementById('course-title').textContent       = course.title;
    document.getElementById('breadcrumb-title').textContent   = course.title;
    document.getElementById('course-description').textContent = course.description;
    document.getElementById('teacher-name').textContent       =
        course.teacher ? course.teacher.name : 'Non assigné';
    document.getElementById('course-price').textContent       = formatPrice(course.price);
    document.getElementById('enrolled-count').textContent     =
        course.enrollments ? course.enrollments.length : 0;

    if (course.interest) {
        document.getElementById('interest-badge').innerHTML =
            `<span class="badge bg-secondary">${course.interest.name}</span>`;
    }

    // Show correct action buttons based on login state
    if (isStudent()) {
        document.getElementById('student-actions').classList.remove('d-none');
        document.getElementById('guest-actions').classList.add('d-none');
        checkFavoriteStatus();
        checkEnrollmentStatus();
    }

    document.getElementById('course-content').classList.remove('d-none');
});

// Check if this course is already in favorites
async function checkFavoriteStatus() {
    const result = await apiGet('/favorites');
    if (!result || result.status !== 200) return;

    // Look through the favorites list for this course
    isFavorite = result.data.some(fav => fav.course_id == courseId);
    updateFavoriteButton();
}

// Check if already enrolled
async function checkEnrollmentStatus() {
    const user = getUser();
    // We check via the student's enrollment list
    const result = await apiGet('/student/enrollments');
    if (!result || result.status !== 200) return;

    isEnrolled = result.data.some(e => e.course_id == courseId && e.status === 'enrolled');
    if (isEnrolled) {
        document.getElementById('enrolled-badge').classList.remove('d-none');
        document.getElementById('btn-enroll').disabled = true;
        document.getElementById('btn-enroll').textContent = 'Déjà inscrit';
    }
}

function updateFavoriteButton() {
    const btn = document.getElementById('btn-favorite');
    if (isFavorite) {
        btn.innerHTML = '<i class="bi bi-heart-fill me-2 text-danger"></i>Retirer des favoris';
        btn.classList.add('active');
    } else {
        btn.innerHTML = '<i class="bi bi-heart me-2"></i>Ajouter aux favoris';
        btn.classList.remove('active');
    }
}

async function toggleFavorite() {
    if (!isLoggedIn()) {
        window.location.href = '/login';
        return;
    }

    if (isFavorite) {
        const result = await apiDelete('/favorites/' + courseId);
        if (result && result.status === 200) {
            isFavorite = false;
            showToast('Retiré des favoris', 'info');
        }
    } else {
        const result = await apiPost('/favorites/' + courseId, {});
        if (result && result.status === 200) {
            isFavorite = true;
            showToast('Ajouté aux favoris !', 'success');
        }
    }

    updateFavoriteButton();
}

async function enrollCourse() {
    if (!isLoggedIn()) {
        window.location.href = '/login';
        return;
    }

    const btn = document.getElementById('btn-enroll');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirection...';
    btn.disabled  = true;

    // Create a Stripe checkout session
    const result = await apiPost('/payment/checkout/' + courseId, {});

    if (!result || result.status !== 200) {
        btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>S\'inscrire et payer';
        btn.disabled  = false;
        showToast('Erreur lors de la création du paiement.', 'danger');
        return;
    }

    // Save payment_id so the success page can use it
    localStorage.setItem('pending_payment_id', result.data.payment_id);
    localStorage.setItem('pending_course_id',  courseId);

    // Redirect to Stripe payment page
    window.location.href = result.data.checkout_url;
}
</script>
@endpush