<?php

declare(strict_types=1);

namespace Src\Models;

class Album extends AppModel
{
    protected string $table = 'albums';

    public function create(string $title, ?string $description, int $userId): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO albums (title, description, user_id)
             VALUES (:title, :description, :user_id)'
        );

        $statement->execute([
            'title' => $title,
            'description' => $description,
            'user_id' => $userId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM albums WHERE user_id = :user_id ORDER BY id DESC'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function findAll(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM albums ORDER BY id DESC'
        );

        $statement->execute();

        $albums = $statement->fetchAll();

        foreach ($albums as &$album) {
            $coverStatement = $this->pdo->prepare(
                'SELECT path
             FROM photos
             WHERE album_id = :album_id
             ORDER BY id ASC
             LIMIT 1'
            );

            $coverStatement->execute([
                'album_id' => $album['id'],
            ]);

            $coverPhoto = $coverStatement->fetch();

            $album['cover_image_url'] = null;

            if ($coverPhoto && !empty($coverPhoto['path'])) {
                $album['cover_image_url'] = '/api/files/photos/' . basename((string) $coverPhoto['path']);
            }
        }

        unset($album);

        return $albums;
    }

    public function updateById(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE albums
             SET title = :title, description = :description
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
        ]);
    }
}