<?php

declare(strict_types=1);

namespace Src\Core;

class Request
{
    private array $queryParams;
    private array $bodyParams;
    private array $routeParams = [];
    private string $method;
    private string $uriPath;
    private ?array $jsonBodyCache = null;

    public function __construct()
    {
        $this->queryParams = $_GET ?? [];
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uriPath = $this->detectPath();
        $this->bodyParams = $this->detectBodyParams();
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->uriPath;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->bodyParams)) {
            return $this->bodyParams[$key];
        }

        if (array_key_exists($key, $this->queryParams)) {
            return $this->queryParams[$key];
        }

        if (array_key_exists($key, $this->routeParams)) {
            return $this->routeParams[$key];
        }

        return $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams, $this->routeParams);
    }

    public function body(): array
    {
        return $this->bodyParams;
    }

    public function params(): array
    {
        return $this->routeParams;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->bodyParams)
            || array_key_exists($key, $this->queryParams)
            || array_key_exists($key, $this->routeParams);
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return $_SERVER[$headerKey] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');

        if (!is_string($header)) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function detectPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return $path;
    }

    private function detectBodyParams(): array
    {
        if (in_array($this->method, ['GET', 'DELETE'], true)) {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            return $this->parseJsonBody();
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        $rawInput = file_get_contents('php://input');

        if (!is_string($rawInput) || trim($rawInput) === '') {
            return [];
        }

        parse_str($rawInput, $parsed);

        return is_array($parsed) ? $parsed : [];
    }

    private function parseJsonBody(): array
    {
        if ($this->jsonBodyCache !== null) {
            return $this->jsonBodyCache;
        }

        $rawInput = file_get_contents('php://input');

        if (!is_string($rawInput) || trim($rawInput) === '') {
            $this->jsonBodyCache = [];
            return $this->jsonBodyCache;
        }

        $decoded = json_decode($rawInput, true);

        if (!is_array($decoded)) {
            $this->jsonBodyCache = [];
            return $this->jsonBodyCache;
        }

        $this->jsonBodyCache = $decoded;

        return $this->jsonBodyCache;
    }
}