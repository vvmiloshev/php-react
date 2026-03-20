<?php

declare(strict_types=1);

use Src\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<SQL
            CREATE TABLE polls (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                question VARCHAR(500) NOT NULL,
                status ENUM('inactive', 'active', 'closed') NOT NULL DEFAULT 'inactive',
                activated_at DATETIME NULL,
                closed_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_polls_user_id
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );

        $pdo->exec('CREATE INDEX idx_polls_status ON polls(status)');
        $pdo->exec('CREATE INDEX idx_polls_user_id ON polls(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS polls');
    }
};