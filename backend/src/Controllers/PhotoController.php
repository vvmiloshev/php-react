<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Request;

class PhotoController extends AppController
{
    public function index(Request $request): void
    {
        $this->json([
            'message' => 'Photos list endpoint',
        ]);
    }

    public function show(Request $request): void
    {
        $this->json([
            'message' => 'Photo details endpoint',
        ]);
    }

    public function store(Request $request): void
    {
        $this->json([
            'message' => 'Create photo endpoint',
        ], 201);
    }

    public function update(Request $request): void
    {
        $this->json([
            'message' => 'Update photo endpoint',
        ]);
    }

    public function delete(Request $request): void
    {
        $this->json([
            'message' => 'Delete photo endpoint',
        ]);
    }
}