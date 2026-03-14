<?php

declare(strict_types=1);

use PDO;

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "
            CREATE TABLE IF NOT EXISTS albums (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(150) NOT NULL,
                description TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_albums_user_id
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE,
                INDEX idx_albums_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        );

        $count = (int) $pdo->query('SELECT COUNT(*) FROM albums')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $statement = $pdo->prepare(
            '
            INSERT INTO albums (user_id, title, description)
            VALUES (:user_id, :title, :description)
            '
        );

        $albums = [
            [
                'user_id' => 1,
                'title' => 'Nature Collection',
                'description' => 'A demo album with nature photos.',
            ],
            [
                'user_id' => 2,
                'title' => 'City Life',
                'description' => 'A demo album with city photos.',
            ],
        ];

        foreach ($albums as $album) {
            $statement->execute($album);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS albums');
    }
};