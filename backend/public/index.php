<?php

declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

[$router, $request] = require BASE_PATH . '/src/bootstrap.php';

$router->dispatch($request);
