<?php

declare(strict_types=1);

namespace Src\Core;

class Router
{
    private array $routes = [];

    public function __construct(private Response $response)
    {
    }

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = rtrim($request->uri(), '/') ?: '/';

        $handler = $this->routes[$method][$uri] ?? null;

        if (!$handler) {
            $this->response->json([
                'message' => 'Route not found'
            ], 404);
        }

        [$controller, $action] = $handler;
        $controller->$action($request);
    }
}
