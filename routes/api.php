<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\FavoretlistController;
use App\Http\Controllers\Api\UserDomainController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Courses public 

Route::get('/courses/recommended',[CourseController::class, 'recommended']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // enseignant 
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // Etudiante
    Route::post('/wishlist/{courseId}', [FavoretlistController::class, 'store']);
    Route::get('/wishlist', [FavoretlistController::class, 'index']);
    Route::delete('/wishlist/{courseId}', [FavoretlistController::class, 'destroy']);

    Route::post('/user/domains',[UserDomainController::class,'store']);


});