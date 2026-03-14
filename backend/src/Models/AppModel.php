<?php

declare(strict_types=1);

namespace Src\Models;

use PDO;
use Src\Core\Database;

abstract class AppModel
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(Database $database)
    {
        $this->pdo = $database->pdo();
    }

    public function findById(int $id): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1"
        );

        $statement->execute([
            'id' => $id,
        ]);

        return $statement->fetch();
    }

    public function deleteById(int $id): bool
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE id = :id"
        );

        return $statement->execute([
            'id' => $id,
        ]);
    }

    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $allowedDirections = ['ASC', 'DESC'];
        $direction = strtoupper($direction);

        if (!in_array($direction, $allowedDirections, true)) {
            $direction = 'DESC';
        }

        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }
}
