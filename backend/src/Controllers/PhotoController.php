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
        $user = $request->user();

        if (!$user) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $albumId = (int) ($request->input('album_id') ?? 0);
        $title = trim((string) ($request->input('title') ?? ''));
        $description = $request->input('description');

        if ($albumId <= 0) {
            $this->error('Valid album_id is required.', 422);
            return;
        }

        if ($title === '') {
            $this->error('Title is required.', 422);
            return;
        }

        if (!$request->hasFile('image')) {
            $this->error('Image file is required.', 422);
            return;
        }

        $file = $request->file('image');

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->error('Invalid uploaded file.', 422);
            return;
        }

        $tmpPath = $file['tmp_name'] ?? '';
        $fileSize = (int) ($file['size'] ?? 0);

        if (!is_uploaded_file($tmpPath)) {
            $this->error('Invalid upload source.', 422);
            return;
        }

        if ($fileSize <= 0 || $fileSize > 5 * 1024 * 1024) {
            $this->error('Image must be up to 5 MB.', 422);
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $tmpPath) : null;

        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!$mimeType || !isset($allowedMimeTypes[$mimeType])) {
            $this->error('Only JPG, PNG, WEBP and GIF files are allowed.', 422);
            return;
        }

        $extension = $allowedMimeTypes[$mimeType];
        $generatedFileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadDirectory = BASE_PATH . '/public/uploads/photos';

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
            $this->error('Upload directory could not be created.', 500);
            return;
        }

        $destinationPath = $uploadDirectory . '/' . $generatedFileName;

        if (!move_uploaded_file($tmpPath, $destinationPath)) {
            $this->error('File upload failed.', 500);
            return;
        }

        $relativePath = '/uploads/photos/' . $generatedFileName;

        $photoId = $this->photos->create(
            $albumId,
            (int) $user['id'],
            $title,
            $relativePath,
            $description !== null ? (string) $description : null
        );

        $photo = $this->photos->findById($photoId);

        $this->created($photo ?: [], 'Photo uploaded successfully.');
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

    public function serve(Request $request): void
    {
        $fileName = (string) ($request->param('file') ?? '');

        if ($fileName === '') {
            $this->error('File name is required.', 422);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) {
            $this->error('Invalid file name.', 422);
            return;
        }

        $filePath = BASE_PATH . '/public/uploads/photos/' . $fileName;

        if (!is_file($filePath) || !is_readable($filePath)) {
            $this->error('File not found.', 404);
            return;
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        http_response_code(200);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($filePath));
        header('Cache-Control: public, max-age=86400');

        readfile($filePath);
        exit;
    }
}