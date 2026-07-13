<?php
namespace Tests\Helpers;

class RequestHelper {
    
    /**
     * Simulates a POST request and captures the output (e.g. from a controller).
     */
    public static function post(string $controllerPath, array $postData, array $sessionData = []) {
        // Reset state
        $_POST = $postData;
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SESSION = $sessionData;

        $path = dirname(__DIR__, 2) . '/app/Controllers/' . ltrim($controllerPath, '/');

        if (!file_exists($path)) {
            throw new \Exception("Controller not found at: $path");
        }

        ob_start();
        try {
            (function() use ($path) { require $path; })();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    public static function postJson(string $controllerPath, array $postData, array $sessionData = []) {
        $output = self::post($controllerPath, $postData, $sessionData);
        if (empty($output)) return [];
        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        return $decoded;
    }

    /**
     * Simulates a GET request and captures the output.
     */
    public static function get(string $controllerPath, array $getData, array $sessionData = []) {
        $_POST = [];
        $_GET = $getData;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SESSION = $sessionData;

        $path = dirname(__DIR__, 2) . '/app/Controllers/' . ltrim($controllerPath, '/');

        if (!file_exists($path)) {
            throw new \Exception("Controller not found at: $path");
        }

        ob_start();
        try {
            (function() use ($path) { require $path; })();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Simulates a GET request and parses the output as JSON.
     */
    public static function getJson(string $controllerPath, array $getData, array $sessionData = []) {
        $output = self::get($controllerPath, $getData, $sessionData);
        $decoded = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        
        return $decoded;
    }
}
