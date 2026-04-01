<?php   

use Illuminate\Support\Facades\Route;

// ================================================================
// PUBLIC PAGES — anyone can visit these without being logged in
// ================================================================

// "/" redirects to /courses so the home page is the courses list
Route::get('/', function () {
    return redirect('/courses');
});

// --- Auth pages ---
// fn() => view('auth.login') is a short way to write:
// function() { return view('auth.login'); }
// 'auth.login' means the file resources/views/auth/login.blade.php
Route::get('/login',           fn() => view('auth.login'));
Route::get('/register',        fn() => view('auth.register'));
Route::get('/forgot-password', fn() => view('auth.forgot'));
Route::get('/reset-password',  fn() => view('auth.reset'));

// --- Course pages (public — anyone can browse courses) ---
// /courses      → resources/views/courses/index.blade.php
// /courses/{id} → resources/views/courses/show.blade.php
Route::get('/courses',      fn() => view('courses.index'));
Route::get('/courses/{id}', fn() => view('courses.show'));

// ================================================================
// PAYMENT REDIRECT PAGES
// After payment on Stripe, the browser is sent back here.
// Stripe adds ?session_id=xxx to the success URL automatically.
// ================================================================
Route::get('/payment/success', fn() => view('payment.success'));
Route::get('/payment/cancel',  fn() => view('payment.cancel'));

// ================================================================
// STUDENT PAGES
// These pages check for login inside their own JavaScript.
// If the token is missing, JS redirects to /login automatically.
// ================================================================
Route::get('/student/dashboard',   fn() => view('student.dashboard'));
Route::get('/student/favorites',   fn() => view('student.favorites'));
Route::get('/student/recommended', fn() => view('student.recommended'));

// ================================================================
// TEACHER PAGES
// Same — JS handles the protection using requireTeacher()
// ================================================================
Route::get('/teacher/dashboard',          fn() => view('teacher.dashboard'));
Route::get('/teacher/courses/create',     fn() => view('teacher.create-course'));
Route::get('/teacher/courses/{id}/edit',  fn() => view('teacher.edit-course'));