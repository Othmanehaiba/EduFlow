@extends('layouts.app')
@section('title', 'Tableau de bord enseignant')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Tableau de bord</h2>
    <a href="/teacher/courses/create" class="btn btn-dark">
        <i class="bi bi-plus-lg me-2"></i>Nouveau cours
    </a>
</div>

{{-- Stats cards --}}
<div class="row g-3 mb-5" id="stats-row">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total cours</div>
            <div class="stat-number" id="stat-courses">—</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total étudiants inscrits</div>
            <div class="stat-number" id="stat-students">—</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Revenus totaux</div>
            <div class="stat-number text-success" id="stat-revenue">—</div>
        </div>
    </div>
</div>

{{-- Courses table --}}
<h4 class="fw-bold mb-3">Mes cours</h4>

<div class="loading-center" id="loading">
    <div class="spinner-border text-dark" role="status"></div>
</div>

<div class="d-none" id="courses-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Titre</th>
                    <th>Prix</th>
                    <th>Inscrits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="courses-tbody"></tbody>
        </table>
    </div>
</div>

<div class="empty-state d-none" id="no-courses">
    <i class="bi bi-journal-x"></i>
    <p>Vous n'avez pas encore créé de cours.</p>
    <a href="/teacher/courses/create" class="btn btn-dark btn-sm">Créer mon premier cours</a>
</div>

{{-- Groups modal --}}
<div class="modal fade" id="groupsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Groupes du cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="groups-modal-body">
                <div class="loading-center">
                    <div class="spinner-border text-dark" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Protect this page — teacher only
requireTeacher();

document.addEventListener('DOMContentLoaded', async function () {
    // Load stats and courses in parallel using Promise.all
    const [statsResult, coursesResult] = await Promise.all([
        apiGet('/teacher/stats'),
        apiGet('/teacher/courses'),
    ]);

    document.getElementById('loading').classList.add('d-none');

    // Fill stats
    if (statsResult && statsResult.status === 200) {
        const s = statsResult.data.data;
        document.getElementById('stat-courses').textContent  = s.total_courses;
        document.getElementById('stat-students').textContent = s.total_students;
        document.getElementById('stat-revenue').textContent  = formatPrice(s.total_revenue);
    }

    // Fill courses table
    if (coursesResult && coursesResult.status === 200) {
        const courses = coursesResult.data.data;

        if (!courses || courses.length === 0) {
            document.getElementById('no-courses').classList.remove('d-none');
            return;
        }

        document.getElementById('courses-table-wrap').classList.remove('d-none');

        const tbody = document.getElementById('courses-tbody');
        tbody.innerHTML = courses.map(course => `
            <tr>
                <td class="fw-semibold">${course.title}</td>
                <td>${formatPrice(course.price)}</td>
                <td>
                    <span class="badge bg-secondary">
                        ${course.enrollments_count} étudiant(s)
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="/teacher/courses/${course.id}/edit"
                           class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-info"
                                onclick="viewGroups(${course.id})">
                            <i class="bi bi-people"></i> Groupes
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteCourse(${course.id}, this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
});

async function deleteCourse(id, btn) {
    if (!confirm('Supprimer ce cours ? Cette action est irréversible.')) return;

    btn.disabled = true;

    const result = await apiDelete('/courses/' + id);

    if (result && result.status === 200) {
        showToast('Cours supprimé.', 'info');
        // Remove the row from the table without reloading
        btn.closest('tr').remove();
    } else {
        showToast('Erreur lors de la suppression.', 'danger');
        btn.disabled = false;
    }
}

async function viewGroups(courseId) {
    // Open the modal
    const modal = new bootstrap.Modal(document.getElementById('groupsModal'));
    modal.show();

    const body = document.getElementById('groups-modal-body');
    body.innerHTML = '<div class="loading-center"><div class="spinner-border text-dark" role="status"></div></div>';

    const result = await apiGet('/courses/' + courseId + '/groups');

    if (!result || result.status !== 200) {
        body.innerHTML = '<p class="text-danger">Erreur lors du chargement des groupes.</p>';
        return;
    }

    const groups = result.data.data;

    if (!groups || groups.length === 0) {
        body.innerHTML = '<p class="text-muted text-center py-3">Aucun groupe pour ce cours.</p>';
        return;
    }

    body.innerHTML = groups.map(group => `
        <div class="card mb-3 border-0 bg-light">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">${group.name}</h6>
                    <span class="badge bg-dark">
                        ${group.enrollments_count} / ${group.max_students} étudiants
                    </span>
                </div>
                <button class="btn btn-sm btn-outline-dark mt-2"
                        onclick="viewGroupStudents(${group.id}, this)">
                    Voir les étudiants
                </button>
                <div class="mt-2" id="students-${group.id}"></div>
            </div>
        </div>
    `).join('');
}

async function viewGroupStudents(groupId, btn) {
    btn.disabled = true;
    btn.textContent = 'Chargement...';

    const result = await apiGet('/groups/' + groupId + '/students');
    const container = document.getElementById('students-' + groupId);

    if (!result || result.status !== 200) {
        container.innerHTML = '<p class="text-danger small">Erreur.</p>';
        return;
    }

    const students = result.data.data;

    if (!students || students.length === 0) {
        container.innerHTML = '<p class="text-muted small">Aucun étudiant.</p>';
        return;
    }

    container.innerHTML = students.map(e => `
        <span class="badge bg-secondary me-1 mb-1">
            <i class="bi bi-person me-1"></i>${e.student.name}
        </span>
    `).join('');

    btn.classList.add('d-none'); // hide button after loading
}
</script>
@endpush