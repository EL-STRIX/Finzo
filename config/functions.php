<?php
/**
 * Finzo Banking System — Helper Functions
 * Shared utility functions used across all API endpoints.
 * Version 1.0 — April 2026
 */

require_once __DIR__ . '/config.php';

// =====================================================
// INPUT SANITIZATION
// =====================================================

/**
 * Sanitize user input: trim whitespace and encode HTML entities.
 * @param string $data Raw input string
 * @return string Sanitized string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// =====================================================
// CSRF TOKEN MANAGEMENT
// =====================================================

/**
 * Generate a new CSRF token and store it in the session.
 * @return string The generated token
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Validate a submitted CSRF token against the session token.
 * Regenerates the token after successful validation to prevent reuse.
 * @param string $token The submitted token
 * @return bool True if valid, false otherwise
 */
function validate_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    return $valid;
}

// =====================================================
// JSON RESPONSE HELPER
// =====================================================

/**
 * Send a JSON response with appropriate headers and status code.
 * @param mixed $data Data to encode as JSON
 * @param int $status HTTP status code (default 200)
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// =====================================================
// ACCOUNT NUMBER GENERATION
// =====================================================

/**
 * Generate the next unique account number in format FNZ{YEAR}{4DIGIT}.
 * Example: FNZ20260001, FNZ20260002, ...
 * @param PDO $pdo Database connection
 * @return string Unique account number
 */
function generate_account_number($pdo) {
    $year = date('Y');
    $prefix = ACCOUNT_NUMBER_PREFIX . $year;

    // Get the max existing account number for this year
    $stmt = $pdo->prepare("SELECT account_no FROM accounts WHERE account_no LIKE ? ORDER BY account_no DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        // Extract the numeric suffix and increment
        $lastNum = intval(substr($last, strlen($prefix)));
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }

    // Pad to 4 digits
    $accountNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // Ensure uniqueness (defensive check)
    $check = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE account_no = ?");
    $check->execute([$accountNo]);
    while ($check->fetchColumn() > 0) {
        $nextNum++;
        $accountNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        $check->execute([$accountNo]);
    }

    return $accountNo;
}

// =====================================================
// STAFF ID GENERATION
// =====================================================

/**
 * Generate the next unique staff ID in format STF{3DIGIT}.
 * Example: STF001, STF002, ...
 * @param PDO $pdo Database connection
 * @return string Unique staff ID
 */
function generate_staff_id($pdo) {
    $prefix = STAFF_ID_PREFIX;

    // Check both staff AND admin_users — both share the STF prefix
    $stmt = $pdo->query("
        SELECT MAX(num) as max_id FROM (
            SELECT CAST(SUBSTRING(staff_id, 4) AS UNSIGNED) as num
            FROM staff WHERE staff_id LIKE 'STF%'
            UNION ALL
            SELECT CAST(SUBSTRING(admin_id, 4) AS UNSIGNED) as num
            FROM admin_users WHERE admin_id LIKE 'STF%'
        ) combined
    ");
    $max_id = $stmt->fetchColumn() ?: 0;
    $nextNum = $max_id + 1;
    $staffId = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    // Ensure uniqueness in staff table (defensive check)
    $check = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE staff_id = ?");
    $check->execute([$staffId]);
    while ($check->fetchColumn() > 0) {
        $nextNum++;
        $staffId = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        $check->execute([$staffId]);
    }

    return $staffId;
}

// =====================================================
// TRANSACTION ID GENERATION
// =====================================================

/**
 * Generate a unique transaction ID.
 * Format: TXN + 16 hex chars from cryptographic random bytes
 * @return string Transaction ID
 */
function generate_transaction_id() {
    return 'TXN' . strtoupper(bin2hex(random_bytes(8)));
}

// =====================================================
// AUDIT LOG WRITER
// =====================================================

/**
 * Write an entry to the audit_log table.
 * @param PDO $pdo Database connection
 * @param string $action_type Type of action (e.g., 'user_login', 'kyc_approved')
 * @param string|null $entity_id ID of the entity acted upon
 * @param string|null $entity_type Type of entity (e.g., 'user', 'staff', 'transaction')
 * @param int $actor_id ID of the actor performing the action
 * @param string $actor_role Role of the actor ('admin', 'staff', 'system')
 * @param string|null $details Additional details about the action
 */
function write_audit_log($pdo, $action_type, $entity_id, $entity_type, $actor_id, $actor_role, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // 1. Resolve actor_id to its alphanumeric string format (like STF001) and specific role
    $resolved_actor_id = $actor_id;
    $resolved_actor_role = $actor_role;

    if ($actor_role === 'admin') {
        $stmt = $pdo->prepare("SELECT admin_id, admin_role FROM admin_users WHERE id = ?");
        $stmt->execute([$actor_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $resolved_actor_id = $admin['admin_id'];
            if ($admin['admin_role'] === 'super_admin') {
                $resolved_actor_role = 'Super Admin';
            } else if ($admin['admin_role'] === 'moderator') {
                $resolved_actor_role = 'Moderator';
            } else {
                $resolved_actor_role = 'Admin';
            }
        }
    } else if ($actor_role === 'staff') {
        $stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE id = ?");
        $stmt->execute([$actor_id]);
        $staff_id = $stmt->fetchColumn();
        if ($staff_id) {
            $resolved_actor_id = $staff_id;
        }
        $resolved_actor_role = 'Staff';
    } else if ($actor_role === 'system') {
        $resolved_actor_id = 'SYSTEM';
        $resolved_actor_role = 'System';
    } else if ($actor_role === 'user') {
        $resolved_actor_role = 'User';
    }

    // 2. Resolve entity_id to its alphanumeric string format if applicable
    $resolved_entity_id = $entity_id;
    if ($entity_type === 'admin' && !empty($entity_id) && is_numeric($entity_id)) {
        $stmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ?");
        $stmt->execute([$entity_id]);
        $admin_id = $stmt->fetchColumn();
        if ($admin_id) {
            $resolved_entity_id = $admin_id;
        }
    } else if ($entity_type === 'staff' && !empty($entity_id) && is_numeric($entity_id)) {
        $stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE id = ?");
        $stmt->execute([$entity_id]);
        $staff_id = $stmt->fetchColumn();
        if ($staff_id) {
            $resolved_entity_id = $staff_id;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO audit_log (action_type, entity_id, entity_type, actor_id, actor_role, details, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $action_type,
        $resolved_entity_id,
        $entity_type,
        $resolved_actor_id,
        $resolved_actor_role,
        $details,
        $ip
    ]);
}

// =====================================================
// NOTIFICATION SENDER
// =====================================================

/**
 * Create an in-app notification for a user.
 * @param PDO $pdo Database connection
 * @param int $user_id Target user ID
 * @param string $type Notification type ('transaction', 'security', 'system')
 * @param string $title Notification title
 * @param string $message Notification message body
 */
function send_notification($pdo, $user_id, $type, $title, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$user_id, $type, $title, $message]);
}

// =====================================================
// CURRENCY FORMATTING
// =====================================================

/**
 * Format an amount as Indian Rupee currency string.
 * @param float $amount The amount to format
 * @return string Formatted currency string (e.g., ₹1,00,000.00)
 */
function format_currency($amount) {
    // Indian number formatting: 1,00,000.00
    $amount = number_format((float)$amount, 2, '.', '');
    $parts = explode('.', $amount);
    $whole = $parts[0];
    $decimal = $parts[1];

    $isNegative = false;
    if ($whole[0] === '-') {
        $isNegative = true;
        $whole = substr($whole, 1);
    }

    $len = strlen($whole);
    if ($len > 3) {
        $last3 = substr($whole, -3);
        $remaining = substr($whole, 0, $len - 3);
        // Add commas every 2 digits for the remaining part (Indian format)
        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        $whole = $remaining . ',' . $last3;
    }

    return ($isNegative ? '-' : '') . CURRENCY_SYMBOL . $whole . '.' . $decimal;
}

// =====================================================
// PIN VALIDATION
// =====================================================

/**
 * Verify a 4-digit transaction PIN against the hashed PIN in accounts table.
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @param string $pin The 4-digit PIN to verify
 * @return bool True if PIN is correct
 */
function validate_pin($pdo, $user_id, $pin) {
    $stmt = $pdo->prepare("SELECT txn_pin_hash FROM accounts WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $hash = $stmt->fetchColumn();

    if (!$hash) {
        return false;
    }

    return password_verify($pin, $hash);
}

// =====================================================
// SESSION HELPERS
// =====================================================

/**
 * Check if the current session has expired.
 * @return bool True if session is expired
 */
function is_session_expired() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_LIFETIME) {
            return true;
        }
    }
    return false;
}

/**
 * Refresh session activity timestamp.
 */
function refresh_session_activity() {
    $_SESSION['last_activity'] = time();
}

/**
 * Destroy the current session completely.
 */
function destroy_session() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

// =====================================================
// VALIDATION HELPERS
// =====================================================

/**
 * Validate an Indian mobile phone number (10 digits starting with 6-9).
 * @param string $phone Phone number to validate
 * @return bool True if valid
 */
function validate_phone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Validate an Indian pincode (6 digits, first digit 1-9).
 * @param string $pincode Pincode to validate
 * @return bool True if valid
 */
function validate_pincode($pincode) {
    return preg_match('/^[1-9]\d{5}$/', $pincode);
}

/**
 * Validate password strength.
 * Minimum 8 characters required.
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_password_strength($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    return ['valid' => true, 'message' => 'Password meets requirements'];
}

/**
 * Get the client's IP address.
 * @return string IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get time-based greeting string.
 * @return string "Good Morning", "Good Afternoon", or "Good Evening"
 */
function get_greeting() {
    $hour = (int) date('H');
    if ($hour < 12) {
        return 'Good Morning';
    } elseif ($hour < 17) {
        return 'Good Afternoon';
    } else {
        return 'Good Evening';
    }
}

// =====================================================
// ADMIN ROLE & PERMISSIONS
// =====================================================

/**
 * Check if the admin has permission to perform a specific action.
 * @param PDO $pdo Database connection
 * @param int $admin_id The ID of the admin
 * @param string $action The action they are attempting to perform
 * @return bool True if allowed, otherwise responds with 403 JSON and exits.
 */
function check_admin_permission($pdo, $admin_id, $action) {
    $stmt = $pdo->prepare("SELECT admin_role FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $role = $stmt->fetchColumn();

    if (!$role) {
        json_response([
            'success' => false,
            'message' => 'Admin account not found'
        ], 403);
    }

    // Super Admin — zero restrictions, can do everything
    if ($role === 'super_admin') {
        return true;
    }

    // Admin role — blocked from these actions only
    $admin_blocked = [
        'suspend_user',
        'reinstate_user',
        'soft_delete_user',
        'change_interest_rate',
        'add_branch',
        'manage_admins',
        'suspend_staff',
        'delete_staff',
        'add_staff'
    ];
    
    if ($role === 'admin' && in_array($action, $admin_blocked)) {
        json_response([
            'success' => false,
            'message' => 'You do not have permission to perform this action'
        ], 403);
    }

    // Moderator role — can ONLY do these actions
    $moderator_allowed = [
        'view_dashboard',
        'view_users',
        'review_kyc_application',
        'approve_kyc_application',
        'reject_kyc_application'
    ];
    
    if ($role === 'moderator' && !in_array($action, $moderator_allowed)) {
        json_response([
            'success' => false,
            'message' => 'You do not have permission to perform this action'
        ], 403);
    }

    return true;
}
