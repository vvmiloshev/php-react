<?php

declare(strict_types=1);

namespace Src\Models;

use PDO;
use Src\Core\Database;

class AuthToken
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->pdo();
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO auth_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );

        $statement->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findActiveByTokenHash(string $tokenHash): array|false
    {
        $statement = $this->pdo->prepare(
            'SELECT
                auth_tokens.id AS auth_token_id,
                auth_tokens.user_id,
                auth_tokens.expires_at,
                users.id,
                users.name,
                users.email,
                users.password
             FROM auth_tokens
             INNER JOIN users ON users.id = auth_tokens.user_id
             WHERE auth_tokens.token_hash = :token_hash
               AND auth_tokens.expires_at > NOW()
             LIMIT 1'
        );

        $statement->execute([
            'token_hash' => $tokenHash,
        ]);

        return $statement->fetch();
    }

    public function deleteByTokenHash(string $tokenHash): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM auth_tokens WHERE token_hash = :token_hash'
        );

        $statement->execute([
            'token_hash' => $tokenHash,
        ]);
    }

    public function deleteExpired(): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM auth_tokens WHERE expires_at <= NOW()'
        );

        $statement->execute();
    }
}