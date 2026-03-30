<?php

// AppServiceProvider : c'est ici qu'on "branche" les interfaces sur leurs implémentations
// Quand Laravel voit "EnrollmentRepositoryInterface", il sait qu'il doit utiliser "EnrollmentRepository"
// C'est le cœur du Repository Pattern

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// On importe toutes les interfaces
use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\EnrollmentRepositoryInterface;
use App\Interfaces\GroupRepositoryInterface;

// On importe toutes les implémentations concrètes (les repositories)
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GroupRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // bind() = "quand quelqu'un demande l'interface A, donne-lui la classe B"

        // Cours
        $this->app->bind(
            CourseRepositoryInterface::class,
            CourseRepository::class
        );

        // Inscriptions
        $this->app->bind(
            EnrollmentRepositoryInterface::class,
            EnrollmentRepository::class
        );

        // Groupes
        $this->app->bind(
            GroupRepositoryInterface::class,
            GroupRepository::class
        );
    }

    public function boot(): void
    {
        // Rien à faire ici pour l'instant
    }
}