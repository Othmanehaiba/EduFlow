<?php

// Controller pour les cours
// Remplace l'ancienne version — ajoute la méthode recommended() et teacherStudents()

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    // GET /courses — Liste tous les cours disponibles
    public function index()
    {
        return response()->json($this->courseService->listCourses());
    }

    // GET /courses/{id} — Détails d'un cours
    public function show(int $id)
    {
        return response()->json($this->courseService->showCourse($id));
    }

    // POST /courses — Créer un cours (enseignant seulement)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'interest_id' => ['nullable', 'exists:interests,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
        ]);

        $course = $this->courseService->createCourse($validated, auth('api')->id());

        return response()->json([
            'message' => 'Cours créé avec succès',
            'data'    => $course,
        ], 201);
    }

    // PUT /courses/{id} — Modifier un cours
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'interest_id' => ['nullable', 'exists:interests,id'],
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
        ]);

        $course = $this->courseService->updateCourse($id, $validated);

        return response()->json([
            'message' => 'Cours mis à jour',
            'data'    => $course,
        ]);
    }

    // DELETE /courses/{id} — Supprimer un cours
    public function destroy(int $id)
    {
        $this->courseService->deleteCourse($id);

        return response()->json([
            'message' => 'Cours supprimé',
        ]);
    }

    // -------------------------------------------------------
    // GET /teacher/courses — Les cours d'un enseignant avec stats
    // -------------------------------------------------------
    public function myCourses()
    {
        // Retourner les cours de l'enseignant connecté avec le nb d'inscrits
        $courses = $this->courseService->teacherCourses(auth('api')->id());

        return response()->json([
            'data' => $courses,
        ]);
    }
}