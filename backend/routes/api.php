<?php

declare(strict_types=1);

use Src\Controllers\AlbumController;
use Src\Controllers\AuthController;
use Src\Controllers\HealthController;
use Src\Controllers\PhotoController;
use Src\Controllers\PollController;
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
    $router->put('/api/photos/{id}', [PhotoController::class, 'update']);
    $router->delete('/api/photos/{id}', [PhotoController::class, 'delete']);
    $router->post('/api/photos', [PhotoController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/api/files/photos/{file}', [PhotoController::class, 'serve']);


    $router->get('/api/polls', [PollController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/api/polls/active', [PollController::class, 'active']);
    $router->get('/api/polls/closed', [PollController::class, 'closed']);
    $router->get('/api/polls/{id}', [PollController::class, 'show']);

    $router->post('/api/polls', [PollController::class, 'store'], [AuthMiddleware::class]);
    $router->post('/api/polls/{id}/activate', [PollController::class, 'activate'], [AuthMiddleware::class]);
    $router->post('/api/polls/{id}/close', [PollController::class, 'close'], [AuthMiddleware::class]);
    $router->post('/api/polls/{id}/vote', [PollController::class, 'vote'], [AuthMiddleware::class]);
    $router->get('/api/polls/{id}/results', [PollController::class, 'results'], [AuthMiddleware::class]);
    $router->put('/api/polls/{id}', [PollController::class, 'update'], [AuthMiddleware::class]);

}