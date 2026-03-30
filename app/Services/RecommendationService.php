<?php

// Ce service propose des cours à un étudiant selon ses centres d'intérêt
// Exemple : si l'étudiant aime "Web", on lui propose les cours de web

namespace App\Services;

use App\Models\Course; // On importe le modèle Course
use App\Models\User;   // On importe le modèle User

class RecommendationService
{
    // -------------------------------------------------------
    // Retourner les cours recommandés pour un étudiant
    // -------------------------------------------------------
    // $user : l'étudiant connecté
    public function getRecommended(User $user)
    {
        // Étape 1 : Récupérer les IDs des intérêts de l'étudiant
        // pluck('id') extrait seulement la colonne 'id' sous forme de liste
        $interestIds = $user->interests()->pluck('interests.id')->toArray();

        // Si l'étudiant n'a aucun intérêt, on retourne une liste vide
        if (empty($interestIds)) {
            return []; // tableau vide
        }

        // Étape 2 : Chercher tous les cours dont l'interest_id est dans la liste
        // whereIn() = WHERE interest_id IN (1, 2, 3, ...)
        return Course::whereIn('interest_id', $interestIds)
                     ->with(['teacher', 'interest']) // charge le prof et le domaine
                     ->latest()                      // du plus récent au plus ancien
                     ->get();                        // retourne tous les résultats
    }
}