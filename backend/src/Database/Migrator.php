<?php

declare(strict_types=1);

namespace Src\Database;

use PDO;
use Throwable;

class Migrator
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = rtrim($migrationsPath, '/');
    }

    public function bootstrap(): void
    {
        $this->ensureMigrationsTableExists();
        $this->executeMissingMigrations();
    }

    public function executeMissingMigrations(): void
    {
        $this->ensureMigrationsTableExists();

        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        foreach ($migrationFiles as $migrationName => $filePath) {
            if (in_array($migrationName, $executedMigrations, true)) {
                continue;
            }

            $this->runMigration($migrationName, $filePath);
        }
    }

    public function runWithRecovery(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            if (!$this->isMissingTableException($exception)) {
                throw $exception;
            }

            $this->executeMissingMigrations();

            return $callback();
        }
    }

    private function ensureMigrationsTableExists(): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

        $this->pdo->exec($sql);
    }

    private function getExecutedMigrations(): array
    {
        $statement = $this->pdo->query('SELECT migration FROM migrations ORDER BY migration ASC');

        return $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];

        sort($files);

        $migrations = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            $migrations[$migrationName] = $file;
        }

        return $migrations;
    }

    private function runMigration(string $migrationName, string $filePath): void
    {
        $migration = require $filePath;

        if (!$migration instanceof Migration) {
            throw new \RuntimeException(
                sprintf(
                    'Migration file "%s" must return an instance of %s',
                    $filePath,
                    Migration::class
                )
            );
        }

        $migration->up($this->pdo);

        $statement = $this->pdo->prepare(
            'INSERT INTO migrations (migration) VALUES (:migration)'
        );

        $statement->execute([
            'migration' => $migrationName,
        ]);
    }

    private function isMissingTableException(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, '42s02')
            || str_contains($message, '1146')
            || str_contains($message, 'base table or view not found')
            || str_contains($message, 'doesn\'t exist');
    }
}