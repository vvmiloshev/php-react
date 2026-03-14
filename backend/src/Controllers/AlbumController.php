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
            $this->success(
                $this->albums->findByUserId($userId),
                'Albums fetched successfully.'
            );
            return;
        }

        $this->success(
            $this->albums->all(),
            'Albums fetched successfully.'
        );
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid album id.', 422);
            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->error('Album not found.', 404);
            return;
        }

        $this->success($album, 'Album fetched successfully.');
    }

    public function store(Request $request): void
    {
        $title = trim((string) ($request->input('title') ?? ''));
        $description = $request->input('description');
        $userId = (int) ($request->input('user_id') ?? 0);

        if ($title === '') {
            $this->error('Title is required.', 422);
            return;
        }

        if ($userId <= 0) {
            $this->error('Valid user_id is required.', 422);
            return;
        }

        $albumId = $this->albums->create(
            $title,
            $description !== null ? (string) $description : null,
            $userId
        );

        $album = $this->albums->findById($albumId);

        $this->created($album ?: [], 'Album created successfully.');
    }

    public function update(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $description = $request->input('description');

        if ($id <= 0) {
            $this->error('Invalid album id.', 422);
            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->error('Album not found.', 404);
            return;
        }

        if ($title === '') {
            $this->error('Title is required.', 422);
            return;
        }

        $updated = $this->albums->updateById($id, [
            'title' => $title,
            'description' => $description !== null ? (string) $description : null,
        ]);

        if ($updated === false) {
            $this->error('Album could not be updated.', 500);
            return;
        }

        $this->success(
            $this->albums->findById($id) ?: [],
            'Album updated successfully.'
        );
    }

    public function delete(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid album id.', 422);
            return;
        }

        $album = $this->albums->findById($id);

        if ($album === false) {
            $this->error('Album not found.', 404);
            return;
        }

        $deleted = $this->albums->deleteById($id);

        if ($deleted === false) {
            $this->error('Album could not be deleted.', 500);
            return;
        }

        $this->success([], 'Album deleted successfully.');
    }
}