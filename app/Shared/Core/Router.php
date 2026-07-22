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

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        // Convert path to regex, e.g., /admin/profile/{id} -> /admin/profile/(?<id>[a-zA-Z0-9_-]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '(?<$1>[a-zA-Z0-9_-]+)', $path);
        $pattern = '#^' . $pattern . '$#i';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
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
                } catch (ApiException $e) {
                    $payload = ['success' => false, 'message' => $e->getMessage()];
                    $customPayload = $e->getPayload();
                    if (!empty($customPayload)) {
                        $payload = array_merge($payload, $customPayload);
                    }
                    json_response($payload, $e->getStatusCode());
                } catch (\App\Shared\Core\Exceptions\TestResponseException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    \App\Shared\Core\Logging\Logger::getInstance()->error('Unhandled Error: ', ['context' => $e->getMessage()]);
                    json_response(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
                }

                return;
            }
        }

        // Legacy Strangler Fig Fallback
        // If the route was not found in the new API mappings, check if the old procedural controller exists.
        // The URI will look like /user/dashboard.php
        $legacyFilePath = FINZO_ROOT . '/app/Presentation/Controllers' . $uri;
        if (file_exists($legacyFilePath) && is_file($legacyFilePath)) {
            require $legacyFilePath;
            return;
        }

        json_response(['success' => false, 'message' => 'Not Found - ' . $uri], 404);
    }

    private function instantiateController(string $controllerClass)
    {
        // Simple DI container implementation
        $reflection = new \ReflectionClass($controllerClass);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $controllerClass();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();

                // If it's a Service, it might need a Repository
                if (str_ends_with($dependencyClass, 'Service')) {
                    $dependencies[] = $this->instantiateController($dependencyClass);
                } elseif (str_ends_with($dependencyClass, 'Repository')) {
                    $dependencies[] = new $dependencyClass();
                } else {
                    $dependencies[] = new $dependencyClass();
                }
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
