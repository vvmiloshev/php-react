<?php

declare(strict_types=1);

namespace Src\Core;

class Response
{
    private int $statusCode = 200;

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
        http_response_code($statusCode);
    }

    public function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}