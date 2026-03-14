<?php

declare(strict_types=1);

use Src\Controllers\AlbumController;
use Src\Controllers\AuthController;
use Src\Controllers\HealthController;
use Src\Controllers\PhotoController;
use Src\Controllers\VoteController;
use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;
use Src\Core\Router;

$db = new Database();
$request = new Request();
$response = new Response();
$router = new Router($response);

$healthController = new HealthController($db, $response);
$authController = new AuthController($db, $response);
$albumController = new AlbumController($db, $response);
$photoController = new PhotoController($db, $response);
$voteController = new VoteController($db, $response);

require BASE_PATH . '/routes/api.php';

register_api_routes(
    $router,
    $healthController,
    $authController,
    $albumController,
    $photoController,
    $voteController
);

return [$router, $request];
