<?php

// Ce controller gère les inscriptions et désinscriptions
// Il reçoit les requêtes HTTP et appelle le service approprié

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // On injecte le service via le constructeur
    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    // -------------------------------------------------------
    // POST /enroll/{courseId}
    // S'inscrire à un cours (après paiement)
    // -------------------------------------------------------
    public function enroll(Request $request, int $courseId)
    {
        // Valider que payment_id est fourni dans le body de la requête
        $validated = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
        ]);

        try {
            // Appeler le service pour effectuer l'inscription
            $enrollment = $this->enrollmentService->enroll(
                auth('api')->id(),      // ID de l'étudiant connecté
                $courseId,              // ID du cours
                $validated['payment_id'] // ID du paiement
            );

            return response()->json([
                'message'    => 'Inscription réussie',
                'enrollment' => $enrollment,
            ], 201); // 201 = Created

        } catch (\Exception $e) {
            // Si une erreur survient (déjà inscrit, paiement non validé, etc.)
            return response()->json([
                'message' => $e->getMessage(),
            ], 400); // 400 = Bad Request
        }
    }

    // -------------------------------------------------------
    // DELETE /unenroll/{courseId}
    // Se désinscrire d'un cours
    // -------------------------------------------------------
    public function unenroll(int $courseId)
    {
        try {
            $enrollment = $this->enrollmentService->unenroll(
                auth('api')->id(), // ID de l'étudiant connecté
                $courseId
            );

            return response()->json([
                'message'    => 'Désinscription réussie',
                'enrollment' => $enrollment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // -------------------------------------------------------
    // GET /courses/{courseId}/students
    // Un enseignant voit ses étudiants inscrits à un cours
    // -------------------------------------------------------
    public function students(int $courseId)
    {
        $students = $this->enrollmentService->getStudentsByCourse($courseId);

        return response()->json([
            'data' => $students,
        ]);
    }

    // -------------------------------------------------------
    // GET /teacher/stats
    // Statistiques d'un enseignant (cours, inscrits, revenus)
    // -------------------------------------------------------
    public function teacherStats()
    {
        $stats = $this->enrollmentService->getTeacherStats(
            auth('api')->id() // ID de l'enseignant connecté
        );

        return response()->json([
            'data' => $stats,
        ]);
    }
}