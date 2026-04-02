@extends('layouts.app')
@section('title', 'Tous les cours')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h2 class="fw-bold mb-0">Tous les cours</h2>

    {{-- Search bar filters courses in real time without another API call --}}
    <input type="text"
           class="form-control"
           style="max-width: 280px;"
           id="search"
           placeholder="🔍 Rechercher un cours..."
           oninput="filterCourses()">
</div>

{{-- Loading spinner -- shown while API responds --}}
<div class="loading-center" id="loading">
    <div class="spinner-border text-dark" role="status"></div>
</div>

{{-- Course cards are injected here by JS --}}
<div class="row g-4" id="courses-container"></div>

{{-- Shown when search returns nothing --}}
<div class="empty-state d-none" id="no-results">
    <i class="bi bi-search"></i>
    <p>Aucun cours trouvé pour cette recherche.</p>
</div>

@endsection

@push('scripts')
<script>
// We store all courses here so search can filter locally without re-calling API
let allCourses = [];

document.addEventListener('DOMContentLoaded', async function () {
    // auth=false — public route, no token needed
    const result = await apiGet('/courses', false);

    document.getElementById('loading').classList.add('d-none');

    if (!result || result.status !== 200) {
        document.getElementById('courses-container').innerHTML =
            '<p class="text-danger">Erreur lors du chargement des cours.</p>';
        return;
    }

    allCourses = result.data;
    renderCourses(allCourses);
});

function renderCourses(courses) {
    const container = document.getElementById('courses-container');
    const noResults = document.getElementById('no-results');

    if (courses.length === 0) {
        container.innerHTML = '';
        noResults.classList.remove('d-none');
        return;
    }

    noResults.classList.add('d-none');

    container.innerHTML = courses.map(course => `
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 course-card border-0 shadow-sm"
                 onclick="window.location='/courses/${course.id}'">
                <div class="card-body d-flex flex-column p-4">

                    {{-- Interest badge if the course has one --}}
                    ${course.interest
                        ? `<span class="badge bg-secondary mb-2" style="width:fit-content">
                               ${course.interest.name}
                           </span>`
                        : ''}

                    <h5 class="card-title fw-bold">${course.title}</h5>

                    <p class="card-text text-muted flex-grow-1">
                        ${truncate(course.description, 110)}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="price-badge">${formatPrice(course.price)}</span>
                        <small class="text-muted">
                            <i class="bi bi-person-fill"></i>
                            ${course.teacher ? course.teacher.name : 'Non assigné'}
                        </small>
                    </div>

                    <a href="/courses/${course.id}"
                       class="btn btn-dark btn-sm mt-3 w-100">
                        Voir les détails →
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

// Filters the already-loaded courses without a new API call
function filterCourses() {
    const query = document.getElementById('search').value.toLowerCase();

    const filtered = allCourses.filter(course =>
        course.title.toLowerCase().includes(query) ||
        course.description.toLowerCase().includes(query) ||
        (course.teacher && course.teacher.name.toLowerCase().includes(query))
    );

    renderCourses(filtered);
}
</script>
@endpush