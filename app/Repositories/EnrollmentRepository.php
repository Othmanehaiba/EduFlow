<?php

// Ce fichier contient le code qui touche directement la base de données
// pour les inscriptions (enrollments)
// Il implémente l'interface pour respecter le "contrat"

namespace App\Repositories;

use App\Interfaces\EnrollmentRepositoryInterface;
use App\Models\Enrollment; // Modèle inscription
use App\Models\Group;      // Modèle groupe
use App\Models\Payment;    // Modèle paiement

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    // -------------------------------------------------------
    // Inscrire un étudiant à un cours
    // -------------------------------------------------------
    public function enroll(int $studentId, int $courseId, int $paymentId)
    {
        // Vérifier si l'étudiant est déjà inscrit
        // exists() retourne true/false
        $alreadyEnrolled = Enrollment::where('student_id', $studentId)
                                     ->where('course_id', $courseId)
                                     ->exists();

        // Si déjà inscrit, on lance une exception (erreur)
        if ($alreadyEnrolled) {
            throw new \Exception('Tu es déjà inscrit à ce cours.');
        }

        // Vérifier que le paiement est bien validé
        $payment = Payment::findOrFail($paymentId); // findOrFail = cherche ou erreur 404

        if ($payment->status !== 'paid') {
            throw new \Exception('Le paiement n\'est pas encore validé.');
        }

        // Chercher un groupe avec de la place (moins de 25 étudiants)
        // withCount() ajoute une colonne "enrollments_count" dynamiquement
        $group = Group::where('course_id', $courseId)
                      ->withCount('enrollments')
                      ->get()
                      ->first(function ($group) {
                          // La fonction cherche le premier groupe avec de la place
                          return $group->enrollments_count < $group->max_students;
                      });

        // Si aucun groupe disponible, on en crée un nouveau automatiquement
        if (!$group) {
            // Compter combien de groupes existent déjà pour ce cours
            $existingCount = Group::where('course_id', $courseId)->count();

            // Créer un nouveau groupe : "Group 1", "Group 2", etc.
            $group = Group::create([
                'course_id'    => $courseId,
                'name'         => 'Group ' . ($existingCount + 1),
                'max_students' => 25, // maximum 25 étudiants par groupe
            ]);
        }

        // Créer l'inscription dans la base de données
        return Enrollment::create([
            'student_id' => $studentId,
            'course_id'  => $courseId,
            'group_id'   => $group->id,   // assigner le groupe trouvé/créé
            'payment_id' => $paymentId,
            'status'     => 'enrolled',   // statut initial
        ]);
    }

    // -------------------------------------------------------
    // Désinscrire un étudiant d'un cours
    // -------------------------------------------------------
    public function unenroll(int $studentId, int $courseId)
    {
        // firstOrFail() : cherche ou retourne une erreur 404
        $enrollment = Enrollment::where('student_id', $studentId)
                                ->where('course_id', $courseId)
                                ->firstOrFail();

        // On met le statut à "cancelled" au lieu de supprimer
        // Pour garder un historique
        $enrollment->update(['status' => 'cancelled']);

        return $enrollment;
    }

    // -------------------------------------------------------
    // Voir les étudiants inscrits à un cours
    // -------------------------------------------------------
    public function getStudentsByCourse(int $courseId)
    {
        // On récupère toutes les inscriptions actives ("enrolled")
        // with('student') charge les informations de chaque étudiant
        return Enrollment::where('course_id', $courseId)
                         ->where('status', 'enrolled') // seulement les actifs
                         ->with('student')             // charge le profil étudiant
                         ->get();
    }
}