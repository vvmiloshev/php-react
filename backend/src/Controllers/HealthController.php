<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;

class HealthController
{
    public function __construct(
        private Database $db,
        private Response $response
    ) {
    }

    public function index(Request $request): void
    {
        $this->response->json([
            'status' => 'ok',
            'message' => 'API is working'
        ]);
    }
}
