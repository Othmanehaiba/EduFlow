<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\CourseRepositoryInterface;
use App\Repositories\CourseRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
    }

    public function boot(): void
    {
        //
    }
}