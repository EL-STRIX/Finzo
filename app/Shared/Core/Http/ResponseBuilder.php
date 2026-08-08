<?php

namespace App\Shared\Core\Http;

class ResponseBuilder
{
    public static function format($data, int $status = 200): array
    {
        $isSuccess = $status >= 200 && $status < 300;
        
        $response = [
            'success' => $isSuccess,
            'status' => $status,
            'message' => '',
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? bin2hex(random_bytes(8))
        ];

        if ($isSuccess) {
            // Extract known keys if they exist at root
            if (is_array($data) && isset($data['message'])) {
                $response['message'] = $data['message'];
                unset($data['message']);
            } else {
                $response['message'] = 'Request successful.';
            }

            $rootKeys = ['redirect', 'locked', 'mfa_required', 'mfa_token'];
            foreach ($rootKeys as $key) {
                if (is_array($data) && isset($data[$key])) {
                    $response[$key] = $data[$key];
                    unset($data[$key]);
                }
            }

            if (is_array($data) && isset($data['success'])) {
                unset($data['success']);
            }

            if (is_array($data) && isset($data['data'])) {
                $response['data'] = $data['data'];
                if (isset($data['pagination'])) {
                    $response['pagination'] = $data['pagination'];
                }
                if (isset($data['meta'])) {
                    $response['meta'] = $data['meta'];
                }
            } else {
                // If it's a flat array or empty array but contains the payload
                $response['data'] = $data;
            }
            
            // Ensure meta exists if pagination doesn't
            if (!isset($response['pagination'])) {
                $response['meta'] = $response['meta'] ?? [];
            }
        } else {
            // Error formatting
            if (is_array($data) && isset($data['message'])) {
                $response['message'] = $data['message'];
                unset($data['message']);
            } elseif (is_string($data)) {
                $response['message'] = $data;
            } else {
                $response['message'] = 'An error occurred.';
            }

            $rootKeys = ['redirect', 'locked', 'mfa_required', 'mfa_token'];
            foreach ($rootKeys as $key) {
                if (is_array($data) && isset($data[$key])) {
                    $response[$key] = $data[$key];
                    unset($data[$key]);
                }
            }

            // Extract validation errors if any
            if (is_array($data) && isset($data['errors'])) {
                $response['errors'] = $data['errors'];
            } elseif (is_array($data) && count($data) > 0) {
                // If data has other keys, might be error details
                $response['errors'] = $data;
                unset($response['errors']['success']);
                unset($response['errors']['code']);
            } else {
                $response['errors'] = (object)[];
            }
        }

                // Enterprise API Performance Telemetry
        if (!headers_sent()) {
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                $response['meta']['duration_ms'] = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            }
            $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
            header("X-Runtime: " . ($response['meta']['duration_ms'] ?? 0) . "ms");
            header("X-Memory: {$memory}MB");
        }
        return self::scrub($response);
    }

    private static function scrub($data)
    {
        if (is_string($data)) {
            // Globally sanitize string outputs to prevent XSS on the client-side
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (is_array($data)) {
            $sensitive = ['password', 'password_hash', 'token', 'reset_token', 'temp_password_expiry'];
            foreach ($sensitive as $key) {
                if (array_key_exists($key, $data)) {
                    unset($data[$key]);
                }
            }
            foreach ($data as $k => $v) {
                $data[$k] = self::scrub($v);
            }
        }
        return $data;
    }
}