<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;

abstract class AppController
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    protected function success(array $data = [], string $message = 'OK', int $statusCode = 200): void
    {
        $this->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function created(array $data = [], string $message = 'Created'): void
    {
        $this->success($data, $message, 201);
    }

    protected function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $payload = [
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        $this->json($payload, $statusCode);
    }
}