@extends('layouts.app')
@section('title', 'Paiement réussi')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">

        <div class="card border-0 shadow-sm p-5">
            <div id="loading-state">
                <div class="spinner-border text-success mb-3" role="status"></div>
                <h4>Confirmation de votre inscription...</h4>
                <p class="text-muted">Veuillez patienter.</p>
            </div>

            <div class="d-none" id="success-state">
                <div class="display-1 mb-3">✅</div>
                <h2 class="fw-bold text-success">Paiement réussi !</h2>
                <p class="text-muted mb-4">
                    Vous êtes maintenant inscrit au cours et avez été assigné à un groupe.
                </p>
                <a href="/student/dashboard" class="btn btn-dark px-5 py-2">
                    Voir mes cours
                </a>
            </div>

            <div class="d-none" id="error-state">
                <div class="display-1 mb-3">⚠️</div>
                <h2 class="fw-bold text-warning">Paiement reçu</h2>
                <p class="text-muted mb-4" id="error-msg">
                    Le paiement a été reçu mais l'inscription automatique a échoué.
                    Contactez le support.
                </p>
                <a href="/courses" class="btn btn-dark px-5 py-2">
                    Retour aux cours
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function () {
    // Retrieve the pending payment and course IDs saved before redirecting to Stripe
    const paymentId = localStorage.getItem('pending_payment_id');
    const courseId  = localStorage.getItem('pending_course_id');

    // If not logged in or missing data, redirect
    if (!isLoggedIn() || !paymentId || !courseId) {
        window.location.href = '/courses';
        return;
    }

    // Small delay to let Stripe webhook update the payment status first
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Call the enrollment API
    const result = await apiPost('/enroll/' + courseId, {
        payment_id: parseInt(paymentId)
    });

    // Clear the pending payment from storage
    localStorage.removeItem('pending_payment_id');
    localStorage.removeItem('pending_course_id');

    document.getElementById('loading-state').classList.add('d-none');

    if (result && (result.status === 201 || result.status === 200)) {
        document.getElementById('success-state').classList.remove('d-none');
        showToast('Inscription confirmée !', 'success');
    } else {
        const msg = result ? result.data.message : 'Erreur réseau.';
        document.getElementById('error-msg').textContent = msg;
        document.getElementById('error-state').classList.remove('d-none');
    }
});
</script>
@endpush