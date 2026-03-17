<?php

declare(strict_types=1);

namespace Src\Core;

class Request
{
    private array $queryParams;
    private array $bodyParams;
    private array $routeParams = [];
    private array $files;
    private array $server;
    private string $method;
    private string $uriPath;
    private ?array $jsonBodyCache = null;
    private ?string $rawBodyCache = null;
    private ?array $authenticatedUser = null;

    public function __construct()
    {
        $this->server = $_SERVER ?? [];
        $this->queryParams = $_GET ?? [];
        $this->files = $_FILES ?? [];
        $this->method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
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
        $normalized = strtoupper(str_replace('-', '_', $name));

        if ($normalized === 'CONTENT_TYPE') {
            return $this->server['CONTENT_TYPE'] ?? $default;
        }

        if ($normalized === 'CONTENT_LENGTH') {
            return $this->server['CONTENT_LENGTH'] ?? $default;
        }

        $headerKey = 'HTTP_' . $normalized;

        if (isset($this->server[$headerKey])) {
            return $this->server[$headerKey];
        }

        if ($normalized === 'AUTHORIZATION') {
            return $this->server['REDIRECT_HTTP_AUTHORIZATION']
                ?? $this->server['PHP_AUTH_DIGEST']
                ?? (isset($this->server['PHP_AUTH_USER'], $this->server['PHP_AUTH_PW'])
                    ? 'Basic ' . base64_encode($this->server['PHP_AUTH_USER'] . ':' . $this->server['PHP_AUTH_PW'])
                    : $default);
        }

        return $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');

        if (!is_string($header) || trim($header) === '') {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    public function rawBody(): string
    {
        if ($this->rawBodyCache !== null) {
            return $this->rawBodyCache;
        }

        $rawInput = file_get_contents('php://input');
        $this->rawBodyCache = is_string($rawInput) ? $rawInput : '';

        return $this->rawBodyCache;
    }

    public function isJson(): bool
    {
        $contentType = (string) ($this->header('Content-Type', '') ?? '');

        return str_contains(strtolower($contentType), 'application/json');
    }

    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    public function hasFile(string $key): bool
    {
        if (!array_key_exists($key, $this->files)) {
            return false;
        }

        $file = $this->files[$key];

        if (!is_array($file)) {
            return false;
        }

        if (isset($file['error']) && is_int($file['error'])) {
            return $file['error'] !== UPLOAD_ERR_NO_FILE;
        }

        if (isset($file['name']) && is_array($file['name'])) {
            foreach ($file['name'] as $index => $name) {
                if (($file['error'][$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    return true;
                }
            }
        }

        return false;
    }

    public function setUser(?array $user): void
    {
        $this->authenticatedUser = $user;
    }

    public function user(): ?array
    {
        return $this->authenticatedUser;
    }

    public function guest(): bool
    {
        return $this->authenticatedUser === null;
    }

    private function detectPath(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return rtrim($path, '/') ?: '/';
    }

    private function detectBodyParams(): array
    {
        if (in_array($this->method, ['GET', 'DELETE'], true)) {
            return [];
        }

        if ($this->isJson()) {
            return $this->parseJsonBody();
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        $rawInput = $this->rawBody();

        if (trim($rawInput) === '') {
            return [];
        }

        $parsed = [];
        parse_str($rawInput, $parsed);

        return is_array($parsed) ? $parsed : [];
    }

    private function parseJsonBody(): array
    {
        if ($this->jsonBodyCache !== null) {
            return $this->jsonBodyCache;
        }

        $rawInput = $this->rawBody();

        if (trim($rawInput) === '') {
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