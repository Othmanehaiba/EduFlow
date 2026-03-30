<?php

// Ce controller permet à un enseignant de voir ses groupes
// et les étudiants dans chaque groupe

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroupService;

class GroupController extends Controller
{
    public function __construct(
        protected GroupService $groupService
    ) {}

    // -------------------------------------------------------
    // GET /courses/{courseId}/groups
    // Voir tous les groupes d'un cours (réservé au prof propriétaire)
    // -------------------------------------------------------
    public function index(int $courseId)
    {
        try {
            $groups = $this->groupService->getCourseGroups(
                $courseId,
                auth('api')->id() // ID du prof connecté
            );

            return response()->json([
                'data' => $groups,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403); // 403 = Forbidden (accès interdit)
        }
    }

    // -------------------------------------------------------
    // GET /groups/{groupId}/students
    // Voir les étudiants dans un groupe précis
    // -------------------------------------------------------
    public function students(int $groupId)
    {
        $students = $this->groupService->getGroupStudents($groupId);

        return response()->json([
            'data' => $students,
        ]);
    }
}