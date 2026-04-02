@extends('layouts.app')
@section('title', 'Paiement annulé')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <div class="card border-0 shadow-sm p-5">
            <div class="display-1 mb-3">❌</div>
            <h2 class="fw-bold text-danger">Paiement annulé</h2>
            <p class="text-muted mb-4">
                Votre paiement a été annulé. Vous n'avez pas été débité.
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="/courses" class="btn btn-outline-dark px-4">
                    Retour aux cours
                </a>
                <a href="/student/dashboard" class="btn btn-dark px-4">
                    Mon espace
                </a>
            </div>
        </div>
    </div>
</div>
@endsection