<?php

declare(strict_types=1);

namespace Src\Models;

class Photo extends AppModel
{
    protected string $table = 'photos';

    public function create(
        int $albumId,
        int $userId,
        string $title,
        string $path,
        ?string $description = null
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO photos (album_id, user_id, title, path, description) VALUES (:album_id, :user_id, :title, :path, :description)'
        );

        $statement->execute([
            'album_id' => $albumId,
            'user_id' => $userId,
            'title' => $title,
            'path' => $path,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByAlbumId(int $albumId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM photos WHERE album_id = :album_id ORDER BY id DESC'
        );

        $statement->execute([
            'album_id' => $albumId,
        ]);

        return $statement->fetchAll();
    }
}
