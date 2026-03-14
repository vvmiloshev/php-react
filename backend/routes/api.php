<?php

declare(strict_types=1);

use Src\Controllers\AlbumController;
use Src\Controllers\AuthController;
use Src\Controllers\HealthController;
use Src\Controllers\PhotoController;
use Src\Controllers\VoteController;
use Src\Core\Router;

function register_api_routes(
    Router $router,
    HealthController $healthController,
    AuthController $authController,
    AlbumController $albumController,
    PhotoController $photoController,
    VoteController $voteController
): void {
    $router->get('/api/health', [$healthController, 'index']);

    $router->post('/api/register', [$authController, 'register']);
    $router->post('/api/login', [$authController, 'login']);
    $router->post('/api/logout', [$authController, 'logout']);
    $router->get('/api/me', [$authController, 'me']);

    $router->get('/api/albums', [$albumController, 'index']);
    $router->post('/api/albums', [$albumController, 'store']);

    $router->get('/api/photos', [$photoController, 'index']);
    $router->post('/api/photos', [$photoController, 'store']);

    $router->post('/api/votes', [$voteController, 'store']);
}
