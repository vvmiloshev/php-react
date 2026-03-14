<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Models\Photo;

class PhotoController extends AppController
{
    private Photo $photos;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->photos = new Photo($database);
    }

    public function index(Request $request): void
    {
        $albumId = (int) ($request->input('album_id') ?? 0);

        if ($albumId > 0) {
            $photos = $this->photos->findByAlbumId($albumId);

            $this->json([
                'data' => $photos,
            ]);

            return;
        }

        $this->json([
            'data' => $this->photos->all(),
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid photo id.',
            ], 422);

            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->json([
                'message' => 'Photo not found.',
            ], 404);

            return;
        }

        $this->json([
            'data' => $photo,
        ]);
    }

    public function store(Request $request): void
    {
        $albumId = (int) ($request->input('album_id') ?? 0);
        $userId = (int) ($request->input('user_id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $path = trim((string) ($request->input('path') ?? ''));
        $description = $request->input('description');

        if ($albumId <= 0) {
            $this->json([
                'message' => 'Valid album_id is required.',
            ], 422);

            return;
        }

        if ($userId <= 0) {
            $this->json([
                'message' => 'Valid user_id is required.',
            ], 422);

            return;
        }

        if ($title === '') {
            $this->json([
                'message' => 'Title is required.',
            ], 422);

            return;
        }

        if ($path === '') {
            $this->json([
                'message' => 'Path is required.',
            ], 422);

            return;
        }

        $photoId = $this->photos->create(
            $albumId,
            $userId,
            $title,
            $path,
            $description !== null ? (string) $description : null
        );

        $photo = $this->photos->findById($photoId);

        $this->json([
            'message' => 'Photo created successfully.',
            'data' => $photo,
        ], 201);
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $path = trim((string) ($request->input('path') ?? ''));
        $description = $request->input('description');

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid photo id.',
            ], 422);

            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->json([
                'message' => 'Photo not found.',
            ], 404);

            return;
        }

        if ($title === '') {
            $this->json([
                'message' => 'Title is required.',
            ], 422);

            return;
        }

        if ($path === '') {
            $this->json([
                'message' => 'Path is required.',
            ], 422);

            return;
        }

        $updated = $this->photos->updateById(
            $id,
            [
                'title' => $title,
                'path' => $path,
                'description' => $description !== null ? (string) $description : null,
            ]
        );

        if ($updated === false) {
            $this->json([
                'message' => 'Photo could not be updated.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Photo updated successfully.',
            'data' => $this->photos->findById($id),
        ]);
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->json([
                'message' => 'Invalid photo id.',
            ], 422);

            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->json([
                'message' => 'Photo not found.',
            ], 404);

            return;
        }

        $deleted = $this->photos->deleteById($id);

        if ($deleted === false) {
            $this->json([
                'message' => 'Photo could not be deleted.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Photo deleted successfully.',
        ]);
    }
}