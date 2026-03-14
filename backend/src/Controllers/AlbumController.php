<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Request;

class AlbumController extends AppController
{
    public function index(Request $request): void
    {
        $this->json([
            'message' => 'Albums list endpoint',
        ]);
    }

    public function show(Request $request): void
    {
        $this->json([
            'message' => 'Album details endpoint',
        ]);
    }

    public function store(Request $request): void
    {
        $this->json([
            'message' => 'Create album endpoint',
        ], 201);
    }

    public function update(Request $request): void
    {
        $this->json([
            'message' => 'Update album endpoint',
        ]);
    }

    public function delete(Request $request): void
    {
        $this->json([
            'message' => 'Delete album endpoint',
        ]);
    }
}