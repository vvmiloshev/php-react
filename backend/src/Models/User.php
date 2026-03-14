<?php

declare(strict_types=1);

namespace Src\Models;

class User extends AppModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );

        $statement->execute([
            'email' => $email,
        ]);

        return $statement->fetch();
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}