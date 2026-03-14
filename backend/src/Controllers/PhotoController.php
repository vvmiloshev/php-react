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
            $this->success(
                $this->photos->findByAlbumId($albumId),
                'Photos fetched successfully.'
            );
            return;
        }

        $this->success(
            $this->photos->all(),
            'Photos fetched successfully.'
        );
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid photo id.', 422);
            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->error('Photo not found.', 404);
            return;
        }

        $this->success($photo, 'Photo fetched successfully.');
    }

    public function store(Request $request): void
    {
        $albumId = (int) ($request->input('album_id') ?? 0);
        $userId = (int) ($request->input('user_id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $path = trim((string) ($request->input('path') ?? ''));
        $description = $request->input('description');

        if ($albumId <= 0) {
            $this->error('Valid album_id is required.', 422);
            return;
        }

        if ($userId <= 0) {
            $this->error('Valid user_id is required.', 422);
            return;
        }

        if ($title === '') {
            $this->error('Title is required.', 422);
            return;
        }

        if ($path === '') {
            $this->error('Path is required.', 422);
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

        $this->created($photo ?: [], 'Photo created successfully.');
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $path = trim((string) ($request->input('path') ?? ''));
        $description = $request->input('description');

        if ($id <= 0) {
            $this->error('Invalid photo id.', 422);
            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->error('Photo not found.', 404);
            return;
        }

        if ($title === '') {
            $this->error('Title is required.', 422);
            return;
        }

        if ($path === '') {
            $this->error('Path is required.', 422);
            return;
        }

        $updated = $this->photos->updateById($id, [
            'title' => $title,
            'path' => $path,
            'description' => $description !== null ? (string) $description : null,
        ]);

        if ($updated === false) {
            $this->error('Photo could not be updated.', 500);
            return;
        }

        $this->success(
            $this->photos->findById($id) ?: [],
            'Photo updated successfully.'
        );
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid photo id.', 422);
            return;
        }

        $photo = $this->photos->findById($id);

        if ($photo === false) {
            $this->error('Photo not found.', 404);
            return;
        }

        $deleted = $this->photos->deleteById($id);

        if ($deleted === false) {
            $this->error('Photo could not be deleted.', 500);
            return;
        }

        $this->success([], 'Photo deleted successfully.');
    }
}