<?php

declare(strict_types=1);

use PDO;

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "
            CREATE TABLE IF NOT EXISTS votes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                photo_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                value TINYINT NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_votes_photo_id
                    FOREIGN KEY (photo_id) REFERENCES photos(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_votes_user_id
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE,
                CONSTRAINT uq_votes_photo_user UNIQUE (photo_id, user_id),
                INDEX idx_votes_photo_id (photo_id),
                INDEX idx_votes_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        );

        $count = (int) $pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $statement = $pdo->prepare(
            '
            INSERT INTO votes (photo_id, user_id, value)
            VALUES (:photo_id, :user_id, :value)
            '
        );

        $votes = [
            [
                'photo_id' => 1,
                'user_id' => 2,
                'value' => 1,
            ],
            [
                'photo_id' => 2,
                'user_id' => 2,
                'value' => 1,
            ],
            [
                'photo_id' => 3,
                'user_id' => 1,
                'value' => 1,
            ],
        ];

        foreach ($votes as $vote) {
            $statement->execute($vote);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS votes');
    }
};