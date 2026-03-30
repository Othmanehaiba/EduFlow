<?php

// Service pour gérer les inscriptions et désinscriptions aux cours
// Ce fichier REMPLACE l'ancien EnrollmentService.php

namespace App\Services;

use App\Interfaces\EnrollmentRepositoryInterface;
use App\Models\Course; // Pour les statistiques

class EnrollmentService
{
    // On injecte le repository dans le constructeur
    public function __construct(
        protected EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    // -------------------------------------------------------
    // Inscrire un étudiant à un cours (après paiement)
    // -------------------------------------------------------
    public function enroll(int $studentId, int $courseId, int $paymentId)
    {
        // On délègue au repository qui fait le vrai travail
        return $this->enrollmentRepository->enroll($studentId, $courseId, $paymentId);
    }

    // -------------------------------------------------------
    // Désinscrire un étudiant d'un cours
    // -------------------------------------------------------
    public function unenroll(int $studentId, int $courseId)
    {
        return $this->enrollmentRepository->unenroll($studentId, $courseId);
    }

    // -------------------------------------------------------
    // Un enseignant voit ses inscrits + statistiques
    // -------------------------------------------------------
    public function getStudentsByCourse(int $courseId)
    {
        return $this->enrollmentRepository->getStudentsByCourse($courseId);
    }

    // -------------------------------------------------------
    // Statistiques pour un enseignant sur ses cours
    // -------------------------------------------------------
    // $teacherId : l'ID de l'enseignant connecté
    public function getTeacherStats(int $teacherId)
    {
        // Charger tous les cours de cet enseignant
        // withCount ajoute "enrollments_count" automatiquement
        $courses = Course::where('teacher_id', $teacherId)
                         ->withCount('enrollments') // nombre d'inscrits par cours
                         ->get();

        // Calculer les statistiques globales
        $totalCourses     = $courses->count();            // nombre de cours
        $totalStudents    = $courses->sum('enrollments_count'); // total inscrits
        $totalRevenue     = 0; // On calculera le revenu ci-dessous

        // Calculer le revenu total en sommant les paiements "paid" de chaque cours
        foreach ($courses as $course) {
            // sum() additionne la colonne 'amount' de tous les paiements payés
            $totalRevenue += $course->payments()
                                    ->where('status', 'paid')
                                    ->sum('amount');
        }

        // Retourner un tableau avec toutes les stats
        return [
            'total_courses'  => $totalCourses,
            'total_students' => $totalStudents,
            'total_revenue'  => $totalRevenue,
            'courses'        => $courses, // la liste détaillée des cours
        ];
    }
}