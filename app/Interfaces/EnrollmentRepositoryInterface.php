<?php

// Une interface = un "contrat" : elle définit les méthodes que
// EnrollmentRepository DOIT obligatoirement avoir
// Cela permet de changer d'implémentation facilement

namespace App\Interfaces;

interface EnrollmentRepositoryInterface
{
    // Inscrire un étudiant à un cours
    public function enroll(int $studentId, int $courseId, int $paymentId);

    // Désinscrire un étudiant d'un cours
    public function unenroll(int $studentId, int $courseId);

    // Voir tous les étudiants inscrits à un cours spécifique
    public function getStudentsByCourse(int $courseId);
}