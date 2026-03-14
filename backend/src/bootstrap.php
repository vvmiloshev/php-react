<?php

declare(strict_types=1);

use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;
use Src\Core\Router;

$db = new Database();
$request = new Request();
$response = new Response();

$router = new Router($response, $db);

require BASE_PATH . '/routes/api.php';

register_api_routes($router);

return [$router, $request];