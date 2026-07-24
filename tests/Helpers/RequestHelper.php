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
        $_SERVER['REQUEST_URI'] = '/Finzo/api/' . ltrim($controllerPath, '/');
        $_SESSION = $sessionData;

        // Ensure database connection is ready for controllers
        require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

        ob_start();
        try {
            $router = require dirname(__DIR__, 2) . '/routes/api.php';
            $router->dispatch($method, $_SERVER['REQUEST_URI']);
        } catch (\App\Shared\Core\Exceptions\ApiException $e) {
            $payload = ['success' => false, 'status' => $e->getCode(), 'message' => $e->getMessage()];
            if (!empty($e->getPayload())) $payload = array_merge($payload, $e->getPayload());
            // Since we are testing, we can format it like json_response does when testing
            throw new \Exception(json_encode($payload));
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        
        $output = ob_get_clean();
        if (!empty($output)) {
            throw new \Exception($output);
        }
        
        return $output;
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
}
