<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Request;

class HealthController extends AppController
{
    public function index(Request $request): void
    {
        $this->json([
            'status' => 'ok',
            'message' => 'API is working',
        ]);
    }
}