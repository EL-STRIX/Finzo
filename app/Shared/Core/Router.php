<?php

namespace App\Shared\Core;

use App\Shared\Core\Exceptions\ApiException;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    
    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        // Convert path to regex, e.g., /admin/profile/{id} -> /admin/profile/(?<id>[a-zA-Z0-9_-]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '(?<$1>[a-zA-Z0-9_-]+)', $path);
        $pattern = '#^' . $pattern . '$#i';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Run Global Security Middleware
        $security = new \App\Presentation\Middleware\SecurityMiddleware();
        $security->handle();

        // Run Global CSRF Middleware
        $csrf = new \App\Presentation\Middleware\CsrfMiddleware();
        $csrf->handle();

        // Strip query string and base path
        $uri = parse_url($uri, PHP_URL_PATH);

        $basePaths = [
            '/Finzo/public/api',
            '/Finzo/public/assets/api',
            '/Finzo/app/Controllers',
            '/Finzo/api'
        ];

        foreach ($basePaths as $base) {
            if (strpos($uri, $base) === 0) {
                $uri = substr($uri, strlen($base));
                break;
            }
        }

        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Execute middleware
                foreach ($route['middleware'] as $mw) {
                    $mwInstance = new $mw();
                    $mwInstance->handle();
                }

                // Filter out numeric keys from matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                try {
                    $handler = $route['handler'];
                    if (is_array($handler) && count($handler) === 2) {
                        $controllerClass = $handler[0];
                        $methodName = $handler[1];

                        // Basic DI for controllers (instantiate via a simple container if needed, but for now we'll rely on manual or simple reflection)
                        // Actually, Finzo passes Services into Controllers. We need a simple container or factory.
                        // For this simple router, let's use a Factory or simple mapping.
                        // Better: just instantiate the controller. If we use dependency injection, we can use a basic implementation here.
                        $controller = $this->instantiateController($controllerClass);
                        $response = call_user_func_array([$controller, $methodName], $params);
                    } else {
                        $response = call_user_func_array($handler, $params);
                    }

                    // Controllers should ideally just return arrays now, and the router handles json_response
                    // But if controllers still call json_response directly, that's fine for now during transition.
                    if (is_array($response)) {
                        json_response($response, $response['code'] ?? (isset($response['success']) && $response['success'] === false ? 400 : 200));
                    }
                } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    \App\Shared\Core\Exceptions\ExceptionHandler::handle($e);
                }

                return;
            }
        }

        // The legacy fallback logic has been intentionally removed to resolve a Directory Traversal (LFI) vulnerability.

        json_response(['success' => false, 'message' => 'The requested resource was not found.'], 404);
    }

    private function instantiateController(string $controllerClass)
    {
        $container = \App\Shared\Core\Container::getInstance();
        return $container->get($controllerClass);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}