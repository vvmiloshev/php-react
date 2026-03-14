<?php

declare(strict_types=1);

namespace Src\Core;

class Router
{
    private Response $response;
    private Database $database;
    private array $routes = [];

    public function __construct(Response $response, Database $database)
    {
        $this->response = $response;
        $this->database = $database;
    }

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->method();
        $requestPath = $this->normalizePath($request->path());

        $pathMatchedForAnotherMethod = false;

        foreach ($this->routes as $route) {
            $routePath = $this->normalizePath($route['path']);

            $params = $this->matchPath($routePath, $requestPath);

            if ($params === false) {
                continue;
            }

            if ($route['method'] !== $requestMethod) {
                $pathMatchedForAnotherMethod = true;
                continue;
            }

            $request->setRouteParams($params);

            [$controllerClass, $action] = $route['handler'];

            $controller = new $controllerClass($this->database, $this->response);
            $controller->{$action}($request);

            return;
        }

        if ($pathMatchedForAnotherMethod) {
            $this->respondJson([
                'message' => 'Method not allowed.',
            ], 405);

            return;
        }

        $this->respondJson([
            'message' => 'Route not found.',
        ], 404);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    private function matchPath(string $routePath, string $requestPath): array|false
    {
        $routeSegments = $this->segments($routePath);
        $requestSegments = $this->segments($requestPath);

        if (count($routeSegments) !== count($requestSegments)) {
            return false;
        }

        $params = [];

        foreach ($routeSegments as $index => $routeSegment) {
            $requestSegment = $requestSegments[$index];

            if ($this->isParameterSegment($routeSegment)) {
                $paramName = trim($routeSegment, '{}');
                $params[$paramName] = $requestSegment;
                continue;
            }

            if ($routeSegment !== $requestSegment) {
                return false;
            }
        }

        return $params;
    }

    private function isParameterSegment(string $segment): bool
    {
        return preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $segment) === 1;
    }

    private function segments(string $path): array
    {
        $trimmed = trim($path, '/');

        if ($trimmed === '') {
            return [];
        }

        return explode('/', $trimmed);
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        return $normalized === '//' ? '/' : $normalized;
    }

    private function respondJson(array $data, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}