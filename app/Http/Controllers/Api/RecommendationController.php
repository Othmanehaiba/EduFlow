<?php

// Ce controller retourne les cours recommandés pour un étudiant
// basé sur ses centres d'intérêt

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {}

    // -------------------------------------------------------
    // GET /courses/recommended
    // Recommandations personnalisées pour l'étudiant connecté
    // -------------------------------------------------------
    public function index()
    {
        // Récupérer l'étudiant connecté
        $user = auth('api')->user();

        // Appeler le service pour avoir les cours recommandés
        $courses = $this->recommendationService->getRecommended($user);

        return response()->json([
            'data' => $courses,
        ]);
    }
}