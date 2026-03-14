<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;

class AuthController
{
    public function __construct(
        private Database $db,
        private Response $response
    ) {
    }

    public function register(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }

    public function login(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }

    public function logout(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }

    public function me(Request $request): void
    {
        $this->response->json(['message' => 'Not implemented yet'], 501);
    }
}
