<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\HomeController;
use App\Infrastructure\Http\Controllers\UsuarioController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index']);

    $app->group('/usuarios', function (RouteCollectorProxy $group) {
        $group->get('', [UsuarioController::class, 'index']);
        $group->post('', [UsuarioController::class, 'store']);

        $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group) {
            $group->get('', [UsuarioController::class, 'show']);
            $group->put('', [UsuarioController::class, 'update']);
            $group->patch('', [UsuarioController::class, 'update']);
            $group->delete('', [UsuarioController::class, 'destroy']);
        });
    });
};