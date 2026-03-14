<?php

declare(strict_types=1);

namespace Src\Core;

class Request
{
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        if (str_starts_with($path, '/api')) {
            $path = substr($path, 4) ?: '/';
        }

        return $path;
    }

    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getBody(): array
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return $_POST ?? [];
    }
}