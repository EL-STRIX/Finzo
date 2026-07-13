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
 * Sanitize user input: trim whitespace recursively.
 * Note: HTML entity encoding is deliberately omitted here; it should be applied on output via html_encode().
 * @param mixed $data Raw input string or array
 * @return mixed Trimmed string or array
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return trim((string)$data);
}

/**
 * Encode HTML entities for safe output display.
 * @param string $data Raw string
 * @return string Encoded string
 */
function html_encode($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
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
    if (defined('FINZO_TESTING') && FINZO_TESTING) {
        if (is_array($data)) {
            $data['status'] = $status;
        }
        throw new \Exception(json_encode($data));
    }
    
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

    // Get the max existing account number for this year and lock it to prevent race conditions
    $stmt = $pdo->prepare("SELECT account_no FROM accounts WHERE account_no LIKE ? ORDER BY account_no DESC LIMIT 1 FOR UPDATE");
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

    // Only check the staff table — admin IDs are independent
    $stmt = $pdo->query("
        SELECT MAX(CAST(SUBSTRING(staff_id, 4) AS UNSIGNED)) as max_id
        FROM staff WHERE staff_id LIKE 'STF%'
    ");
    $max_id = $stmt->fetchColumn() ?: 0;
    $nextNum = $max_id + 1;
    $staffId = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

    // Ensure uniqueness (defensive check)
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
    $ip = get_client_ip();
    
    // 1. Resolve actor_id to its alphanumeric string format (like STF001) and specific role
    $resolved_actor_id = $actor_id;
    $resolved_actor_role = $actor_role;

    if ($actor_role === 'admin') {
        $stmt = $pdo->prepare("SELECT admin_id, admin_role FROM admin_users WHERE id = ? OR admin_id = ?");
        $stmt->execute([$actor_id, $actor_id]);
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
        $stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE id = ? OR staff_id = ?");
        $stmt->execute([$actor_id, $actor_id]);
        $staff_id = $stmt->fetchColumn();
        if ($staff_id) {
            $resolved_actor_id = $staff_id;
        }
        $resolved_actor_role = 'Staff';
    } else if ($actor_role === 'system') {
        $resolved_actor_id = 'SYSTEM';
        $resolved_actor_role = 'System';
    } else if ($actor_role === 'user') {
        $stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ? OR customer_id = ?");
        $stmt->execute([$actor_id, $actor_id]);
        $customer_id = $stmt->fetchColumn();
        if ($customer_id) {
            $resolved_actor_id = $customer_id;
        }
        $resolved_actor_role = 'User';
    }

    // 2. Resolve entity_id to its alphanumeric string format if applicable
    $resolved_entity_id = $entity_id;
    if ($entity_type === 'admin' && !empty($entity_id)) {
        $stmt = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ? OR admin_id = ?");
        $stmt->execute([$entity_id, $entity_id]);
        $admin_id = $stmt->fetchColumn();
        if ($admin_id) {
            $resolved_entity_id = $admin_id;
        }
    } else if ($entity_type === 'staff' && !empty($entity_id)) {
        $stmt = $pdo->prepare("SELECT staff_id FROM staff WHERE id = ? OR staff_id = ?");
        $stmt->execute([$entity_id, $entity_id]);
        $staff_id = $stmt->fetchColumn();
        if ($staff_id) {
            $resolved_entity_id = $staff_id;
        }
    } else if ($entity_type === 'user' && !empty($entity_id)) {
        $stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ? OR customer_id = ?");
        $stmt->execute([$entity_id, $entity_id]);
        $customer_id = $stmt->fetchColumn();
        if ($customer_id) {
            $resolved_entity_id = $customer_id;
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
    $stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $customer_id = $stmt->fetchColumn();

    if ($customer_id) {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$customer_id, $type, $title, $message]);
    }
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
    // Resolve to customer_id if numeric
    if (is_numeric($user_id)) {
        $r = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
        $r->execute([$user_id]);
        $user_id = $r->fetchColumn() ?: $user_id;
    }
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
function get_client_ip(): string {
    $candidates = [];

    // X-Forwarded-For can be a comma-separated list; the leftmost is the original client
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
            $candidates[] = trim($ip);
        }
    }

    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $candidates[] = trim($_SERVER['HTTP_X_REAL_IP']);
    }

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $candidates[] = trim($_SERVER['HTTP_CLIENT_IP']);
    }

    // REMOTE_ADDR is always last — it is the actual TCP peer and cannot be spoofed
    $candidates[] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    foreach ($candidates as $ip) {
        // filter_var validates that the IP is syntactically correct (IPv4 or IPv6)
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    // Fallback: return REMOTE_ADDR even if private/reserved (covers localhost dev environments)
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
        'delete_staff'
        // 'add_staff' removed — admin role is allowed to create staff members
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

// =====================================================
// SYSTEM SETTINGS HELPERS
// =====================================================

/**
 * Retrieve a system setting from the system_settings table.
 * Casts the returned value based on setting_type.
 * @param PDO $pdo Database connection
 * @param string $key Unique setting key
 * @param mixed $default Default value if setting is not found
 * @return mixed Cast setting value
 */
function get_setting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return $default;
        }
        $value = $row['setting_value'];
        $type = $row['setting_type'];
        switch ($type) {
            case 'number':
                return (float)$value;
            case 'boolean':
                return $value === 'true';
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return (string)$value;
        }
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Update a system setting in the system_settings table.
 * @param PDO $pdo Database connection
 * @param string $key Unique setting key
 * @param mixed $value New value for the setting
 * @param int $admin_id ID of the admin updating the setting
 * @return bool True on success, false on failure
 */
function update_setting($pdo, $key, $value, $admin_id) {
    // Resolve to alphanumeric admin_id if numeric
    if (is_numeric($admin_id)) {
        $r = $pdo->prepare("SELECT admin_id FROM admin_users WHERE id = ?");
        $r->execute([$admin_id]);
        $admin_id = $r->fetchColumn() ?: $admin_id;
    }
    try {
        $stmt = $pdo->prepare("
            UPDATE system_settings 
            SET setting_value = ?, updated_by = ?, updated_at = NOW() 
            WHERE setting_key = ? LIMIT 1
        ");
        return $stmt->execute([$value, $admin_id, $key]);
    } catch (PDOException $e) {
        return false;
    }
}
