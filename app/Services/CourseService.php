<?php

namespace App\Services;

use App\Interfaces\CourseRepositoryInterface;

class CourseService
{
    public function __construct(
        protected CourseRepositoryInterface $courseRepository
    ) {}

    public function listCourses()
    {
        return $this->courseRepository->all();
    }

    public function showCourse(int $id)
    {
        return $this->courseRepository->findById($id);
    }

    public function createCourse(array $data, int $teacherId)
    {
        $data['teacher_id'] = $teacherId;
        return $this->courseRepository->create($data);
    }

    public function updateCourse(int $courseId, array $data)
    {
        return $this->courseRepository->update($courseId, $data);
    }

    public function deleteCourse(int $courseId)
    {
        return $this->courseRepository->delete($courseId);
    }

    public function recommendedCourses(array $interestIds)
    {
        return $this->courseRepository->getRecommendedForStudent($interestIds);
    }
}