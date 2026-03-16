<?php

declare(strict_types=1);

use Src\Http\Cors;

session_start();

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

Cors::handle();

[$router, $request] = require BASE_PATH . '/src/bootstrap.php';

$router->dispatch($request);
