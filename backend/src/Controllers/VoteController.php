<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Request;

class VoteController extends AppController
{
    public function store(Request $request): void
    {
        $this->json([
            'message' => 'Vote endpoint',
        ], 201);
    }

    public function remove(Request $request): void
    {
        $this->json([
            'message' => 'Remove vote endpoint',
        ]);
    }
}