<?php

// Le service fait le lien entre le Controller et le Repository
// Il contient aussi les règles métier (ex: vérifier que c'est bien le prof du cours)

namespace App\Services;

use App\Interfaces\GroupRepositoryInterface;
use App\Models\Course; // Pour vérifier le propriétaire du cours

class GroupService
{
    // On injecte le repository dans le constructeur
    public function __construct(
        protected GroupRepositoryInterface $groupRepository
    ) {}

    // -------------------------------------------------------
    // Un enseignant récupère ses groupes pour un cours
    // -------------------------------------------------------
    // $courseId  : le cours concerné
    // $teacherId : l'ID du prof connecté
    public function getCourseGroups(int $courseId, int $teacherId)
    {
        // Vérifier que ce cours appartient bien à ce professeur
        $course = Course::findOrFail($courseId);

        // Si le prof connecté n'est pas le propriétaire → erreur
        if ($course->teacher_id !== $teacherId) {
            throw new \Exception('Ce cours ne vous appartient pas.', 403);
        }

        // Retourner les groupes via le repository
        return $this->groupRepository->getGroupsByCourse($courseId);
    }

    // -------------------------------------------------------
    // Voir les étudiants dans un groupe précis
    // -------------------------------------------------------
    public function getGroupStudents(int $groupId)
    {
        return $this->groupRepository->getStudentsByGroup($groupId);
    }
}