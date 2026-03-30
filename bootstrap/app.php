<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Le fichier de routes API
        api: __DIR__ . '/../routes/api.php',
        // Le préfixe /api sera automatiquement ajouté à toutes les routes
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrer le middleware "role" avec son alias
        // Dans les routes, on peut maintenant écrire middleware('role:student')
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })
    ->create();