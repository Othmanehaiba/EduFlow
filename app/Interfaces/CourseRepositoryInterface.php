<?php

namespace App\Interfaces;

interface CourseRepositoryInterface
{
    public function all();
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getByTeacher(int $teacherId);
    public function getRecommendedForStudent(array $interestIds);
}