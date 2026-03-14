<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Response;

abstract class AppController
{
    protected Database $db;
    protected Response $response;

    public function __construct(Database $db, Response $response)
    {
        $this->db = $db;
        $this->response = $response;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->setStatusCode($statusCode);
        $this->response->json($data);
    }

    protected function success(array $data = [], int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'data' => $data,
        ], $statusCode);
    }

    protected function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}