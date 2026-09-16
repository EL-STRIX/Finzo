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
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', getenv('APP_DEBUG') === 'true' ? true : false);
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', dirname(__DIR__) . '/storage/');
}

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
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Finzo');
}
if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost/Finzo');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0');
}
if (!defined('APP_KEY')) {
    define('APP_KEY', getenv('APP_KEY') ?: ''); // Must be exactly 32 bytes base64 encoded for AES-256
}

// =====================================================
// DATABASE CREDENTIALS
// =====================================================
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'finzo_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

// =====================================================
// SESSION CONFIGURATION
// =====================================================
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 86400); // 24 hours
}
if (!defined('SESSION_DRIVER')) {
    define('SESSION_DRIVER', getenv('SESSION_DRIVER') ?: 'file');
}

// =====================================================
// CACHE & REDIS CONFIGURATION (Phase 7.3 Scalability)
// =====================================================
if (!defined('CACHE_DRIVER')) {
    define('CACHE_DRIVER', getenv('CACHE_DRIVER') ?: 'file');
}
if (!defined('REDIS_HOST')) {
    define('REDIS_HOST', getenv('REDIS_HOST') ?: '127.0.0.1');
}
if (!defined('REDIS_PASSWORD')) {
    define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: null);
}
if (!defined('REDIS_PORT')) {
    define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);
}
if (!defined('QUEUE_CONNECTION')) {
    define('QUEUE_CONNECTION', getenv('QUEUE_CONNECTION') ?: 'sync');
}

if (!defined('FINZO_TESTING')) {
    \App\Shared\Security\SessionManager::start();
}

// =====================================================
// SECURITY CONSTANTS
// =====================================================
// Overridden by system_settings table if exists
// get_setting($pdo, 'max_failed_login_attempts') returns live value
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
// Overridden by system_settings table if exists
// get_setting($pdo, 'customer_lockout_duration') returns live value
if (!defined('LOCKOUT_DURATION')) {
    define('LOCKOUT_DURATION', 1800); // 30 minutes in seconds
}

// =====================================================
// FILE UPLOAD CONFIGURATION
// =====================================================
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads/');
}
if (!defined('STORAGE_KYC_PATH')) {
    define('STORAGE_KYC_PATH', STORAGE_PATH . 'kyc-documents/');
}
if (!defined('UPLOAD_ID_DOCUMENTS_PATH')) {
    define('UPLOAD_ID_DOCUMENTS_PATH', UPLOAD_PATH . 'id-documents/'); // Keeping for legacy reference
}
if (!defined('UPLOAD_BRANCH_CERTS_PATH')) {
    define('UPLOAD_BRANCH_CERTS_PATH', UPLOAD_PATH . 'branch-certs/');
}
if (!defined('UPLOAD_STAFF_PHOTOS_PATH')) {
    define('UPLOAD_STAFF_PHOTOS_PATH', UPLOAD_PATH . 'staff-photos/');
}
if (!defined('UPLOAD_PROFILES_PATH')) {
    define('UPLOAD_PROFILES_PATH', UPLOAD_PATH . 'profiles/');
}
if (!defined('UPLOAD_ADMIN_PHOTOS_PATH')) {
    define('UPLOAD_ADMIN_PHOTOS_PATH', UPLOAD_PATH . 'admin-photos/');
}
if (!defined('UPLOAD_AVATARS_PATH')) {
    define('UPLOAD_AVATARS_PATH', UPLOAD_PATH . 'avatars/');
}
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
}
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png']);
}

// =====================================================
// SMTP / EMAIL CONFIGURATION
// =====================================================
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
}
if (!defined('SMTP_USER')) {
    define('SMTP_USER', getenv('SMTP_USER') ?: '');
}
if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
}
if (!defined('SMTP_FROM')) {
    define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@finzo.com');
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Finzo Banking');
}
if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'tls');
}

// =====================================================
// ACCOUNT / BANKING CONSTANTS
// =====================================================
if (!defined('ACCOUNT_NUMBER_PREFIX')) {
    define('ACCOUNT_NUMBER_PREFIX', 'FNZ');
}
// Overridden by system_settings table if exists
// get_setting($pdo, 'min_initial_deposit') returns live value
if (!defined('MIN_INITIAL_DEPOSIT')) {
    define('MIN_INITIAL_DEPOSIT', 500);
}
// Overridden by system_settings table if exists
// get_setting($pdo, 'daily_transfer_limit') returns live value
if (!defined('DAILY_TRANSFER_LIMIT')) {
    define('DAILY_TRANSFER_LIMIT', 10000);
}
if (!defined('DEFAULT_INTEREST_RATE')) {
    define('DEFAULT_INTEREST_RATE', 4.25);
}
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '₹');
}
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'INR');
}

// =====================================================
// STAFF ID PREFIX
// =====================================================
if (!defined('STAFF_ID_PREFIX')) {
    define('STAFF_ID_PREFIX', 'STF');
}
require_once __DIR__ . '/events.php';
