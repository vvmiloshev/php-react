<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;

class PhotoController
{
    public function __construct(
        private Database $db,
        private Response $response
    ) {
    }

    public function index(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }

    public function store(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }
}
