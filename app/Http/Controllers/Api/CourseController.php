<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    public function index()
    {
        return response()->json($this->courseService->listCourses());
    }

    public function show(int $id)
    {
        return response()->json($this->courseService->showCourse($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'interest_id' => ['nullable', 'exists:interests,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $course = $this->courseService->createCourse($validated, auth('api')->id());

        return response()->json([
            'message' => 'Cours créé avec succès',
            'data' => $course,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'interest_id' => ['nullable', 'exists:interests,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $course = $this->courseService->updateCourse($id, $validated);

        return response()->json([
            'message' => 'Cours mis à jour',
            'data' => $course,
        ]);
    }

    public function destroy(int $id)
    {
        $this->courseService->deleteCourse($id);

        return response()->json([
            'message' => 'Cours supprimé',
        ]);
    }
}