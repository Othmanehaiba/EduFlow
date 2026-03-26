<?php

namespace App\Repositories;

use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;

class CourseRepository implements CourseRepositoryInterface
{
    public function all()
    {
        return Course::with(['teacher', 'interest'])->latest()->get();
    }

    public function findById(int $id)
    {
        return Course::with(['teacher', 'interest', 'groups', 'enrollments.student'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Course::create($data);
    }

    public function update(int $id, array $data)
    {
        $course = Course::findOrFail($id);
        $course->update($data);
        return $course;
    }

    public function delete(int $id)
    {
        $course = Course::findOrFail($id);
        return $course->delete();
    }

    public function getByTeacher(int $teacherId)
    {
        return Course::where('teacher_id', $teacherId)->withCount('enrollments')->get();
    }

    public function getRecommendedForStudent(array $interestIds)
    {
        return Course::whereIn('interest_id', $interestIds)
            ->with(['teacher', 'interest'])
            ->latest()
            ->get();
    }
}