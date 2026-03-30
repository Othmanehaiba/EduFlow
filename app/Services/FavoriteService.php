<?php

// Ce fichier gère toute la logique métier des favoris (wishlist)
// Un étudiant peut ajouter/retirer/voir ses cours favoris

namespace App\Services;

use App\Models\Favorite; // On importe le modèle Favorite
use App\Models\User;     // On importe le modèle User

class FavoriteService
{
    // -------------------------------------------------------
    // Ajouter un cours aux favoris d'un étudiant
    // -------------------------------------------------------
    // $courseId : l'ID du cours à ajouter
    // $user     : l'objet User de l'étudiant connecté
    public function add(int $courseId, User $user): void
    {
        // firstOrCreate() : cherche d'abord si ce favori existe déjà
        // Si oui → rien ne se passe (pas de doublon)
        // Si non → il le crée automatiquement
        Favorite::firstOrCreate([
            'student_id' => $user->id,  // l'ID de l'étudiant
            'course_id'  => $courseId,  // l'ID du cours
        ]);
    }

    // -------------------------------------------------------
    // Retirer un cours des favoris d'un étudiant
    // -------------------------------------------------------
    public function remove(int $courseId, User $user): void
    {
        // On cherche le favori exact et on le supprime
        Favorite::where('student_id', $user->id)
                ->where('course_id', $courseId)
                ->delete(); // delete() supprime l'enregistrement trouvé
    }

    // -------------------------------------------------------
    // Récupérer tous les favoris d'un étudiant
    // -------------------------------------------------------
    public function myFavoretlist(User $user)
    {
        // On récupère tous les favoris de cet étudiant
        // avec() charge les informations du cours lié (titre, prix, etc.)
        return Favorite::where('student_id', $user->id)
                       ->with('course') // charge les détails du cours
                       ->get();         // retourne tous les résultats
    }
}