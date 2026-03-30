<?php

// Interface pour les groupes
// Définit les méthodes obligatoires du GroupRepository

namespace App\Interfaces;

interface GroupRepositoryInterface
{
    // Récupérer tous les groupes d'un cours
    public function getGroupsByCourse(int $courseId);

    // Récupérer les étudiants dans un groupe précis
    public function getStudentsByGroup(int $groupId);
}