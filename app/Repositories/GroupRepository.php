<?php

// Ce fichier gère les requêtes base de données pour les groupes

namespace App\Repositories;

use App\Interfaces\GroupRepositoryInterface;
use App\Models\Group;      // Modèle groupe
use App\Models\Enrollment; // Modèle inscription

class GroupRepository implements GroupRepositoryInterface
{
    // -------------------------------------------------------
    // Récupérer tous les groupes d'un cours avec le nombre d'étudiants
    // -------------------------------------------------------
    public function getGroupsByCourse(int $courseId)
    {
        // withCount('enrollments') ajoute "enrollments_count" à chaque groupe
        // Très utile pour afficher : "Group 1 (12/25 étudiants)"
        return Group::where('course_id', $courseId)
                    ->withCount('enrollments')
                    ->get();
    }

    // -------------------------------------------------------
    // Voir tous les étudiants dans un groupe spécifique
    // -------------------------------------------------------
    public function getStudentsByGroup(int $groupId)
    {
        // On récupère les inscriptions actives de ce groupe
        // with('student') charge le profil de chaque étudiant
        return Enrollment::where('group_id', $groupId)
                         ->where('status', 'enrolled')
                         ->with('student') // charge nom, email, etc.
                         ->get();
    }
}