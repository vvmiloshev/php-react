<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Models\Album;

class AlbumController extends AppController
{
    private Album $albums;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->albums = new Album($database);
    }

    public function index(Request $request): void
    {
        $userId = (int) ($request->input('user_id') ?? 0);

        if ($userId > 0) {
            $albums = $this->albums->findByUserId($userId);

            $this->json([
                'data' => $albums,
            ]);

            return;
        }

        $this->json([
            'data' => $this->albums->all(),
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid album id.',
            ], 422);

            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->json([
                'message' => 'Album not found.',
            ], 404);

            return;
        }

        $this->json([
            'data' => $album,
        ]);
    }

    public function store(Request $request): void
    {
        $title = trim((string) ($request->input('title') ?? ''));
        $description = $request->input('description');
        $userId = (int) ($request->input('user_id') ?? 0);

        if ($title === '') {
            $this->json([
                'message' => 'Title is required.',
            ], 422);

            return;
        }

        if ($userId <= 0) {
            $this->json([
                'message' => 'Valid user_id is required.',
            ], 422);

            return;
        }

        $albumId = $this->albums->create(
            $title,
            $description !== null ? (string) $description : null,
            $userId
        );

        $album = $this->albums->findById($albumId);

        $this->json([
            'message' => 'Album created successfully.',
            'data' => $album,
        ], 201);
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $description = $request->input('description');

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid album id.',
            ], 422);

            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->json([
                'message' => 'Album not found.',
            ], 404);

            return;
        }

        if ($title === '') {
            $this->json([
                'message' => 'Title is required.',
            ], 422);

            return;
        }

        $updated = $this->albums->updateById(
            $id,
            [
                'title' => $title,
                'description' => $description !== null ? (string) $description : null,
            ]
        );

        if ($updated === false) {
            $this->json([
                'message' => 'Album could not be updated.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Album updated successfully.',
            'data' => $this->albums->findById($id),
        ]);
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid album id.',
            ], 422);

            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->json([
                'message' => 'Album not found.',
            ], 404);

            return;
        }

        $deleted = $this->albums->deleteById($id);

        if ($deleted === false) {
            $this->json([
                'message' => 'Album could not be deleted.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Album deleted successfully.',
        ]);
    }
}