<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Payment;

class EnrollmentService
{
    public function enroll(int $studentId, int $courseId, int $paymentId)
    {
        $alreadyEnrolled = Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->exists();

        if ($alreadyEnrolled) {
            throw new \Exception('Tu es déjà inscrit à ce cours.');
        }

        $payment = Payment::findOrFail($paymentId);

        if ($payment->status !== 'paid') {
            throw new \Exception('Le paiement doit être validé avant l’inscription.');
        }

        $group = Group::where('course_id', $courseId)
            ->withCount('enrollments')
            ->get()
            ->first(function ($group) {
                return $group->enrollments_count < $group->max_students;
            });

        if (! $group) {
            $groupsCount = Group::where('course_id', $courseId)->count();

            $group = Group::create([
                'course_id' => $courseId,
                'name' => 'Group ' . ($groupsCount + 1),
                'max_students' => 25,
            ]);
        }

        return Enrollment::create([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'group_id' => $group->id,
            'payment_id' => $paymentId,
            'status' => 'enrolled',
        ]);
    }

    public function unenroll(int $studentId, int $courseId)
    {
        $enrollment = Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $enrollment->update([
            'status' => 'cancelled'
        ]);

        return $enrollment;
    }
}