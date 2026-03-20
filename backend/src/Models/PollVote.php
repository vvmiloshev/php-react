<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;
use PDO;
use PDOException;

class PollVote
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->pdo();
    }

    public function create(int $pollId, int $pollOptionId, int $userId): bool
    {
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO poll_votes (poll_id, poll_option_id, user_id)
                 VALUES (:poll_id, :poll_option_id, :user_id)'
            );

            $statement->execute([
                'poll_id' => $pollId,
                'poll_option_id' => $pollOptionId,
                'user_id' => $userId,
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findByPollId(int $pollId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM poll_votes WHERE poll_id = :poll_id ORDER BY id DESC'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return $statement->fetchAll();
    }

    public function findByPollIdAndUserId(int $pollId, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id LIMIT 1'
        );

        $statement->execute([
            'poll_id' => $pollId,
            'user_id' => $userId,
        ]);

        $vote = $statement->fetch();

        return $vote ?: null;
    }

    public function hasUserVoted(int $pollId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id LIMIT 1'
        );

        $statement->execute([
            'poll_id' => $pollId,
            'user_id' => $userId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function countByPollId(int $pollId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM poll_votes WHERE poll_id = :poll_id'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return (int) $statement->fetchColumn();
    }
}