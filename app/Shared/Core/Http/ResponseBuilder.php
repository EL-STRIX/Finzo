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
            } elseif (is_string($data)) {
                $response['message'] = $data;
            } else {
                $response['message'] = 'An error occurred.';
            }

            // Extract validation errors if any
            if (is_array($data) && isset($data['errors'])) {
                $response['errors'] = $data['errors'];
            } elseif (is_array($data) && !isset($data['message']) && count($data) > 0) {
                // If data has other keys, might be error details
                $response['errors'] = $data;
                unset($response['errors']['success']);
                unset($response['errors']['code']);
            } else {
                $response['errors'] = (object)[];
            }
        }

        return $response;
    }
}