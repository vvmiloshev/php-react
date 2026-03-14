<?php

declare(strict_types=1);

use PDO;

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "
            CREATE TABLE IF NOT EXISTS photos (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                album_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(150) NOT NULL,
                description TEXT NULL,
                image_url VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_photos_album_id
                    FOREIGN KEY (album_id) REFERENCES albums(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_photos_user_id
                    FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE,
                INDEX idx_photos_album_id (album_id),
                INDEX idx_photos_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        );

        $count = (int) $pdo->query('SELECT COUNT(*) FROM photos')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $statement = $pdo->prepare(
            '
            INSERT INTO photos (album_id, user_id, title, description, image_url)
            VALUES (:album_id, :user_id, :title, :description, :image_url)
            '
        );

        $photos = [
            [
                'album_id' => 1,
                'user_id' => 1,
                'title' => 'Forest View',
                'description' => 'Demo forest image.',
                'image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470',
            ],
            [
                'album_id' => 1,
                'user_id' => 1,
                'title' => 'Mountain Lake',
                'description' => 'Demo mountain image.',
                'image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee',
            ],
            [
                'album_id' => 2,
                'user_id' => 2,
                'title' => 'Night Street',
                'description' => 'Demo city image.',
                'image_url' => 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df',
            ],
        ];

        foreach ($photos as $photo) {
            $statement->execute($photo);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS photos');
    }
};