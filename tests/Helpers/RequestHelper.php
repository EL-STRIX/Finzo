<?php
namespace Tests\Helpers;

class RequestHelper {
    /**
     * Internal dispatcher that bridges tests to the modern MVC Router.
     * This supports both modernized mapped routes and legacy procedural controllers
     * (via the Router's Strangler Fig fallback).
     */
    private static function dispatchRoute(string $method, string $controllerPath, array $postData = [], array $getData = [], array $sessionData = [], array $fileData = []) {
        $_POST = $postData;
        $_GET = $getData;
        $_FILES = $fileData;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        // Strip .php extension and map to modern v1 routes if not already containing api/v1
        $cleanPath = str_replace('.php', '', ltrim($controllerPath, '/'));
        if (strpos($cleanPath, 'api/v1') === false) {
            $cleanPath = 'api/v1/' . ltrim(str_ireplace('api/', '', $cleanPath), '/');
        }
        $_SERVER['REQUEST_URI'] = '/' . ltrim($cleanPath, '/');
        $_SESSION = $sessionData;

        // Ensure database connection is ready for controllers
        require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

        ob_start();
        try {
            $router = require dirname(__DIR__, 2) . '/routes/api.php';
            $router->dispatch($method, $_SERVER['REQUEST_URI']);
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            ob_end_clean();
            $payload = ['success' => false, 'status' => $e->getCode(), 'message' => $e->getMessage()];
            if (!empty($e->getPayload())) $payload = array_merge($payload, $e->getPayload());
            throw new \App\Shared\Core\Exceptions\TestResponseException(json_encode($payload));
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        
        $output = ob_get_clean();
        return $output ?: '';
    }

    public static function post(string $controllerPath, array $postData, array $sessionData = [], array $fileData = []) {
        return self::dispatchRoute('POST', $controllerPath, $postData, [], $sessionData, $fileData);
    }

    public static function postJson(string $controllerPath, array $postData, array $sessionData = [], array $fileData = []) {
        $output = self::post($controllerPath, $postData, $sessionData, $fileData);
        if (empty($output)) return [];
        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        return $decoded;
    }

    public static function get(string $controllerPath, array $getData, array $sessionData = []) {
        return self::dispatchRoute('GET', $controllerPath, [], $getData, $sessionData);
    }

    public static function getJson(string $controllerPath, array $getData, array $sessionData = []) {
        $output = self::get($controllerPath, $getData, $sessionData);
        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        return $decoded;
    }

    public static function put(string $controllerPath, array $postData, array $sessionData = []) {
        return self::dispatchRoute('PUT', $controllerPath, $postData, [], $sessionData);
    }

    public static function putJson(string $controllerPath, array $postData, array $sessionData = []) {
        $output = self::put($controllerPath, $postData, $sessionData);
        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        return $decoded;
    }

    public static function delete(string $controllerPath, array $postData = [], array $sessionData = []) {
        return self::dispatchRoute('DELETE', $controllerPath, $postData, [], $sessionData);
    }

    public static function deleteJson(string $controllerPath, array $postData = [], array $sessionData = []) {
        $output = self::delete($controllerPath, $postData, $sessionData);
        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        return $decoded;
    }
}
