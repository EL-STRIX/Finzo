<?php
namespace App\Helpers;

class SecurityHelper {

    /**
     * Prevent XSS by escaping output
     */
    public static function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Clean input data (trim, strip tags)
     */
    public static function cleanInput(?string $data): string {
        if ($data === null) return '';
        $data = trim($data);
        $data = stripslashes($data);
        $data = strip_tags($data);
        return $data;
    }

    /**
     * Generate CSRF Token
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF Token
     */
    public static function validateCsrfToken(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
