<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;
use PDO;

class PollOption
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->pdo();
    }


    public function findByPollId(int $pollId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM poll_options WHERE poll_id = :poll_id ORDER BY sort_order ASC, id ASC'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM poll_options WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $option = $statement->fetch();

        return $option ?: null;
    }

    public function findByIdAndPollId(int $id, int $pollId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM poll_options WHERE id = :id AND poll_id = :poll_id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
            'poll_id' => $pollId,
        ]);

        $option = $statement->fetch();

        return $option ?: null;
    }

    public function countByPollId(int $pollId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM poll_options WHERE poll_id = :poll_id'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return (int) $statement->fetchColumn();
    }
}