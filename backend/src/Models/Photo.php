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
            'INSERT INTO photos (album_id, user_id, title, path, description)
             VALUES (:album_id, :user_id, :title, :path, :description)'
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

    public function findById(int $id): array|false
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM photos WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $photo = $statement->fetch();

        if ($photo === false) {
            return false;
        }

        return $this->mapPhoto($photo);
    }

    public function findByAlbumId(int $albumId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM photos WHERE album_id = :album_id ORDER BY id DESC'
        );

        $statement->execute([
            'album_id' => $albumId,
        ]);

        $photos = $statement->fetchAll();

        return array_map(fn (array $photo) => $this->mapPhoto($photo), $photos);
    }

    public function updateById(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE photos
             SET title = :title, path = :path, description = :description
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'title' => $data['title'],
            'path' => $data['path'],
            'description' => $data['description'],
        ]);
    }

    private function mapPhoto(array $photo): array
    {
        if (!empty($photo['path'])) {
            $photo['image_url'] = 'api/files/photos/' . str_replace('/uploads/photos/', '', $photo['path']);
        } else {
            $photo['image_url'] = null;
        }

        return $photo;
    }
}