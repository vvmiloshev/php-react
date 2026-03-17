<?php

declare(strict_types=1);

use Src\Controllers\AlbumController;
use Src\Controllers\AuthController;
use Src\Controllers\HealthController;
use Src\Controllers\PhotoController;
use Src\Controllers\VoteController;
use Src\Core\Router;
use Src\Middleware\AuthMiddleware;

function register_api_routes(Router $router): void
{
    $router->get('/api/health', [HealthController::class, 'index']);

    $router->post('/api/auth/login', [AuthController::class, 'login']);
    $router->post('/api/auth/register', [AuthController::class, 'register']);
    $router->get('/api/auth/me', [AuthController::class, 'me']);
    $router->post('/api/auth/logout', [AuthController::class, 'logout']);

    $router->get('/api/albums', [AlbumController::class, 'index']);
    $router->get('/api/albums/{id}', [AlbumController::class, 'show']);
    $router->post('/api/albums', [AlbumController::class, 'store'], [AuthMiddleware::class]);
    $router->put('/api/albums/{id}', [AlbumController::class, 'update'], [AuthMiddleware::class]);
    $router->delete('/api/albums/{id}', [AlbumController::class, 'delete'], [AuthMiddleware::class]);

    $router->get('/api/photos', [PhotoController::class, 'index']);
    $router->get('/api/photos/{id}', [PhotoController::class, 'show']);
    $router->post('/api/photos', [PhotoController::class, 'store']);
    $router->put('/api/photos/{id}', [PhotoController::class, 'update']);
    $router->delete('/api/photos/{id}', [PhotoController::class, 'delete']);

    $router->post('/api/votes', [VoteController::class, 'store']);
    $router->delete('/api/votes', [VoteController::class, 'remove']);
}