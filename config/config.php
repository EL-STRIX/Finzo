<?php
/**
 * Finzo Banking System — Application Configuration
 * All application constants, SMTP config, paths, and environment variables.
 * Version 1.0 — April 2026
 */

// =====================================================
// ENVIRONMENT & ERROR REPORTING
// =====================================================
// Set APP_DEBUG=true in your .env for development only. NEVER true in production.
define('APP_DEBUG', getenv('APP_DEBUG') === 'true' ? true : false);

define('STORAGE_PATH', dirname(__DIR__) . '/storage/');

// Ensure PHP writes errors to our secure log file
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . 'logs/error.log');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // In production, log all errors securely but do not display them
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// =====================================================
// TIMEZONE
// =====================================================
date_default_timezone_set('Asia/Kolkata');

// =====================================================
// APPLICATION CONSTANTS
// =====================================================
define('APP_NAME', 'Finzo');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/Finzo');
define('APP_VERSION', '1.0');

// =====================================================
// DATABASE CREDENTIALS
// =====================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'finzo_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// =====================================================
// SESSION CONFIGURATION
// =====================================================
define('SESSION_LIFETIME', 3600); // 1 hour

if (!defined('FINZO_TESTING') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.save_path', STORAGE_PATH . 'sessions');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax'); // Added industry-standard SameSite attribute
    ini_set('session.cookie_path', '/');
    session_start();
}

// =====================================================
// SECURITY CONSTANTS
// =====================================================
// Overridden by system_settings table if exists
// get_setting($pdo, 'max_failed_login_attempts') returns live value
define('MAX_LOGIN_ATTEMPTS', 5);
// Overridden by system_settings table if exists
// get_setting($pdo, 'customer_lockout_duration') returns live value
define('LOCKOUT_DURATION', 1800); // 30 minutes in seconds

// =====================================================
// FILE UPLOAD CONFIGURATION
// =====================================================
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads/');
define('UPLOAD_ID_DOCUMENTS_PATH', UPLOAD_PATH . 'id-documents/');
define('UPLOAD_BRANCH_CERTS_PATH', UPLOAD_PATH . 'branch-certs/');
define('UPLOAD_STAFF_PHOTOS_PATH', UPLOAD_PATH . 'staff-photos/');
define('UPLOAD_PROFILES_PATH', UPLOAD_PATH . 'profiles/');
define('UPLOAD_ADMIN_PHOTOS_PATH', UPLOAD_PATH . 'admin-photos/');
define('UPLOAD_AVATARS_PATH', UPLOAD_PATH . 'avatars/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png']);

// =====================================================
// SMTP / EMAIL CONFIGURATION
// =====================================================
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@finzo.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Finzo Banking');
define('SMTP_ENCRYPTION', 'tls');

// =====================================================
// ACCOUNT / BANKING CONSTANTS
// =====================================================
define('ACCOUNT_NUMBER_PREFIX', 'FNZ');
// Overridden by system_settings table if exists
// get_setting($pdo, 'min_initial_deposit') returns live value
define('MIN_INITIAL_DEPOSIT', 500);
// Overridden by system_settings table if exists
// get_setting($pdo, 'daily_transfer_limit') returns live value
define('DAILY_TRANSFER_LIMIT', 10000);
define('DEFAULT_INTEREST_RATE', 4.25);
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// =====================================================
// STAFF ID PREFIX
// =====================================================
define('STAFF_ID_PREFIX', 'STF');
