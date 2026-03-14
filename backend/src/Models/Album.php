<?php

declare(strict_types=1);

namespace Src\Models;

class Album extends AppModel
{
    protected string $table = 'albums';

    public function create(string $title, ?string $description, int $userId): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO albums (title, description, user_id) VALUES (:title, :description, :user_id)'
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
}
