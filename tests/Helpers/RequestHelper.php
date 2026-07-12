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
        $_SESSION = $sessionData;
        
        // Output buffer to capture what the controller echoes
        ob_start();
        
        // Execute the controller
        $path = dirname(__DIR__, 2) . '/app/Controllers/' . ltrim($controllerPath, '/');
        
        if (!file_exists($path)) {
            ob_end_clean();
            throw new \Exception("Controller not found at: $path");
        }
        
        // Isolate scope
        (function() use ($path) {
            require $path;
        })();
        
        $output = ob_get_clean();
        
        return $output;
    }
    
    /**
     * Simulates a POST request and parses the output as JSON.
     */
    public static function postJson(string $controllerPath, array $postData, array $sessionData = []) {
        $output = self::post($controllerPath, $postData, $sessionData);
        $decoded = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON. Output was: \n" . $output);
        }
        
        return $decoded;
    }
}
