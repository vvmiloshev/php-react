<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Request;

class AuthController extends AppController
{
    public function login(Request $request): void
    {
        $this->json([
            'message' => 'Login endpoint',
        ]);
    }

    public function register(Request $request): void
    {
        $this->json([
            'message' => 'Register endpoint',
        ]);
    }
}