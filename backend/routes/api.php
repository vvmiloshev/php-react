<?php

declare(strict_types=1);

use Src\Controllers\AlbumController;
use Src\Controllers\AuthController;
use Src\Controllers\HealthController;
use Src\Controllers\PhotoController;
use Src\Controllers\VoteController;
use Src\Core\Router;

function register_api_routes(Router $router): void
{
    $router->get('/health', [HealthController::class, 'index']);

    $router->post('/auth/login', [AuthController::class, 'login']);
    $router->post('/auth/register', [AuthController::class, 'register']);

    $router->get('/albums', [AlbumController::class, 'index']);
    $router->get('/albums/show', [AlbumController::class, 'show']);
    $router->post('/albums', [AlbumController::class, 'store']);
    $router->put('/albums', [AlbumController::class, 'update']);
    $router->delete('/albums', [AlbumController::class, 'delete']);

    $router->get('/photos', [PhotoController::class, 'index']);
    $router->get('/photos/show', [PhotoController::class, 'show']);
    $router->post('/photos', [PhotoController::class, 'store']);
    $router->put('/photos', [PhotoController::class, 'update']);
    $router->delete('/photos', [PhotoController::class, 'delete']);

    $router->post('/votes', [VoteController::class, 'store']);
    $router->delete('/votes', [VoteController::class, 'remove']);
}