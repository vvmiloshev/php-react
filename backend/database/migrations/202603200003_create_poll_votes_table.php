<?php

declare(strict_types=1);

use Src\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<SQL
            CREATE TABLE poll_votes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                poll_id INT UNSIGNED NOT NULL,
                poll_option_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_poll_votes_poll_id
                    FOREIGN KEY (poll_id) REFERENCES polls(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_poll_votes_poll_option_id
                    FOREIGN KEY (poll_option_id) REFERENCES poll_options(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_poll_votes_user_id
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE,
                CONSTRAINT uq_poll_votes_poll_user UNIQUE (poll_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );

        $pdo->exec(
            'CREATE INDEX idx_poll_votes_poll_id ON poll_votes(poll_id)'
        );

        $pdo->exec(
            'CREATE INDEX idx_poll_votes_option_id ON poll_votes(poll_option_id)'
        );

        $pdo->exec(
            'CREATE INDEX idx_poll_votes_user_id ON poll_votes(user_id)'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS poll_votes');
    }
};