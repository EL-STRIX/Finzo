<?php
/**
 * Finzo Banking System — Verify Security Answer
 * POST endpoint: Verifies the security answer for a locked admin.
 */

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 3) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// 1. Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    json_response(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

// 2. Sanitize inputs
$email = clean_input($_POST['email'] ?? '');
$security_answer = $_POST['security_answer'] ?? '';

if (empty($email) || empty($security_answer)) {
    json_response(['success' => false, 'message' => 'Email and security answer are required'], 422);
}

// 3. Query admin_users
$stmt = $pdo->prepare("
    SELECT id, security_answer_hash, account_locked, answer_attempts, answer_locked_until, security_question_set
    FROM admin_users
    WHERE email = ?
");
$stmt->execute([$email]);
$admin = $stmt->fetch();

// 4. If not found
if (!$admin) {
    json_response(['success' => false, 'message' => 'Account not found'], 404);
}

// 5. If account_locked = 0
if ($admin['account_locked'] == 0) {
    json_response(['success' => false, 'message' => 'Account is not locked'], 400);
}

// 6. If answer_locked_until > NOW()
$answer_locked_until = $admin['answer_locked_until'];
if ($answer_locked_until && strtotime($answer_locked_until) > time()) {
    json_response([
        'success' => false,
        'answer_locked' => true,
        'message' => 'Too many wrong answers. Please wait.'
    ], 403);
}

// 7. Verify answer (case-insensitive for answers is a good practice, but the spec uses password_verify. 
// I'll assume the frontend or user enters exactly, or the user is warned. Wait, the spec says "Answer is case-insensitive" in part 4. 
// I will just use password_verify on the lowercase version if I want, but let's stick to the spec strictly: "Verify answer: password_verify($security_answer, $security_answer_hash)".)
// Wait, the spec says in part 4: "Note below: 'Answer is case-insensitive. Remember this — it unlocks your account.'"
// This means we should lowercase the answer before hashing and verifying.
$security_answer_lower = strtolower(trim($security_answer));
$security_answer_trimmed = trim($security_answer);

if (!password_verify($security_answer_lower, $admin['security_answer_hash']) && 
    !password_verify($security_answer_trimmed, $admin['security_answer_hash']) &&
    !password_verify($security_answer, $admin['security_answer_hash'])) {
    $new_answer_attempts = (int)$admin['answer_attempts'] + 1;
    
    if ($new_answer_attempts >= 5) {
        // Lock answering for 10 mins
        $updateStmt = $pdo->prepare("
            UPDATE admin_users 
            SET answer_attempts = 5, answer_locked_until = DATE_ADD(NOW(), INTERVAL 10 MINUTE), lock_reason = 'failed_answer'
            WHERE id = ?
        ");
        $updateStmt->execute([$admin['id']]);
        
        // Insert log
        $logStmt = $pdo->prepare("
            INSERT INTO admin_lock_log (admin_id, event_type, details) 
            SELECT admin_id, 'locked_answer', 'Locked after 5 wrong security answers'
            FROM admin_users WHERE id = ?
        ");
        $logStmt->execute([$admin['id']]);
        
        // Write audit log
        write_audit_log($pdo, 'admin_answer_locked', $admin['id'], 'admin', $admin['id'], 'admin', 'Answer locked for 10 minutes');
        
        json_response([
            'success' => false,
            'answer_locked' => true,
            'message' => 'Too many wrong answers. Account locked for 10 minutes.'
        ], 403);
    } else {
        // Increment attempts
        $updateStmt = $pdo->prepare("UPDATE admin_users SET answer_attempts = ? WHERE id = ?");
        $updateStmt->execute([$new_answer_attempts, $admin['id']]);
        
        // Insert log
        $logStmt = $pdo->prepare("
            INSERT INTO admin_lock_log (admin_id, event_type, details) 
            SELECT admin_id, 'answer_attempt_failed', 'Failed security answer attempt'
            FROM admin_users WHERE id = ?
        ");
        $logStmt->execute([$admin['id']]);
        
        $remaining = 5 - $new_answer_attempts;
        json_response([
            'success' => false,
            'message' => "Wrong answer. {$remaining} attempt(s) remaining."
        ], 401);
    }
} else {
    // CORRECT answer
    $reset_token = bin2hex(random_bytes(32));
    
    $updateStmt = $pdo->prepare("
        UPDATE admin_users 
        SET password_reset_token = ?, 
            password_reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE), 
            answer_attempts = 0, 
            answer_locked_until = NULL,
            auto_unlock_at = NULL,
            locked_at = NULL,
            lock_reason = NULL
        WHERE id = ?
    ");
    $updateStmt->execute([$reset_token, $admin['id']]);
    
    // Insert log
    $logStmt = $pdo->prepare("
        INSERT INTO admin_lock_log (admin_id, event_type, details) 
        SELECT admin_id, 'unlocked_self', 'Unlocked via correct security answer'
        FROM admin_users WHERE id = ?
    ");
    $logStmt->execute([$admin['id']]);
    
    // Write audit log
    write_audit_log($pdo, 'admin_self_unlocked', $admin['id'], 'admin', $admin['id'], 'admin', 'Admin unlocked account via security question');
    
    json_response([
        'success' => true,
        'reset_token' => $reset_token,
        'message' => 'Answer correct. Please set a new password.'
    ]);
}
