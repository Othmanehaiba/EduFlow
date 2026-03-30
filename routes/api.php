<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\FavoretlistController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\PasswordResetController;

Route::post('/register',[AuthController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);

Route::post('/forgot-password',[PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password',[PasswordResetController::class, 'resetPassword']);

Route::get('/courses',[CourseController::class, 'index']);
Route::get('/courses/{id}',[CourseController::class, 'show']);


Route::post('/payment/webhook',[PaymentController::class, 'webhook']);


Route::middleware('auth:api')->group(function () {

    Route::get('/me',[AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);
    Route::post('/refresh',[AuthController::class, 'refresh']);

    Route::middleware('role:student')->group(function () {

        Route::get('/courses/recommended',[RecommendationController::class, 'index']);

        Route::get('/favorites',[FavoretlistController::class, 'index']);
        Route::post('/favorites/{courseId}',[FavoretlistController::class, 'store']);
        Route::delete('/favorites/{courseId}',[FavoretlistController::class, 'destroy']);

        Route::post('/payment/checkout/{courseId}',[PaymentController::class, 'checkout']);

        Route::post('/enroll/{courseId}',[EnrollmentController::class, 'enroll']);
        Route::delete('/unenroll/{courseId}',[EnrollmentController::class, 'unenroll']);
    });

    Route::middleware('role:teacher')->group(function () {

        Route::post('/courses',[CourseController::class, 'store']);
        Route::put('/courses/{id}',[CourseController::class, 'update']);
        Route::delete('/courses/{id}',[CourseController::class, 'destroy']);
        Route::get('/teacher/courses',[CourseController::class, 'myCourses']);

        Route::get('/teacher/stats',[EnrollmentController::class, 'teacherStats']);

        Route::get('/courses/{courseId}/students',[EnrollmentController::class, 'students']);

        Route::get('/courses/{courseId}/groups',[GroupController::class, 'index']);
        Route::get('/groups/{groupId}/students',[GroupController::class, 'students']);
    });
});