<?php

declare(strict_types=1);

use Src\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<SQL
            CREATE TABLE poll_options (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                poll_id INT UNSIGNED NOT NULL,
                answer_text VARCHAR(255) NOT NULL,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_poll_options_poll_id
                    FOREIGN KEY (poll_id) REFERENCES polls(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );

        $pdo->exec('CREATE INDEX idx_poll_options_poll_id ON poll_options(poll_id)');
        $pdo->exec('CREATE INDEX idx_poll_options_poll_sort ON poll_options(poll_id, sort_order)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS poll_options');
    }
};