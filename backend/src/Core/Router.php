<?php

declare(strict_types=1);

namespace Src\Core;

class Router
{
    private Response $response;
    private Database $db;
    private array $routes = [];

    public function __construct(Response $response, Database $db)
    {
        $this->response = $response;
        $this->db = $db;
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

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            $this->response->setStatusCode(404);
            $this->response->json([
                'success' => false,
                'message' => 'Route not found',
            ]);
            return;
        }

        [$controllerClass, $action] = $handler;

        $controller = new $controllerClass($this->db, $this->response);

        $controller->$action($request);
    }
}