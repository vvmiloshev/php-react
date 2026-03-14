<?php

declare(strict_types=1);

namespace Src\Models;

class Vote extends AppModel
{
    protected string $table = 'votes';

    public function create(int $photoId, int $userId, int $value): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO votes (photo_id, user_id, value)
             VALUES (:photo_id, :user_id, :value)'
        );

        $statement->execute([
            'photo_id' => $photoId,
            'user_id' => $userId,
            'value' => $value,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByPhotoId(int $photoId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM votes WHERE photo_id = :photo_id ORDER BY id DESC'
        );

        $statement->execute([
            'photo_id' => $photoId,
        ]);

        return $statement->fetchAll();
    }

    public function findUserVoteForPhoto(int $photoId, int $userId): array|false
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM votes
             WHERE photo_id = :photo_id AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'photo_id' => $photoId,
            'user_id' => $userId,
        ]);

        return $statement->fetch();
    }

    public function updateValue(int $id, int $value): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE votes SET value = :value WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'value' => $value,
        ]);
    }
}