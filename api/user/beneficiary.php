<?php
/**
 * Finzo Banking System — Beneficiary Management API
 * GET: list beneficiaries | POST: add beneficiary | DELETE: remove beneficiary
 * Version 1.0 — April 2026
 */

require_once dirname(__DIR__, 2) . '/middleware/auth-user.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT customer_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_id = $stmt->fetchColumn() ?: $user_id;

// =====================================================
// GET: LIST BENEFICIARIES
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT id, nickname, account_no, bank_name, created_at
            FROM beneficiaries
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        $beneficiaries = $stmt->fetchAll();

        json_response([
            'success'       => true,
            'beneficiaries' => $beneficiaries,
            'total'         => count($beneficiaries)
        ]);

    } catch (PDOException $e) {
        error_log("Beneficiaries fetch error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to load beneficiaries'], 500);
    }
}

// =====================================================
// POST: ADD BENEFICIARY
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for delete action via POST (for forms that don't support DELETE)
    $action = clean_input($_POST['action'] ?? 'add');

    if ($action === 'delete') {
        $beneficiary_id = intval($_POST['beneficiary_id'] ?? 0);
        if (!$beneficiary_id) {
            json_response(['success' => false, 'message' => 'Beneficiary ID is required'], 422);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM beneficiaries WHERE id = ? AND user_id = ?");
            $stmt->execute([$beneficiary_id, $user_id]);

            if ($stmt->rowCount() === 0) {
                json_response(['success' => false, 'message' => 'Beneficiary not found'], 404);
            }

            json_response(['success' => true, 'message' => 'Beneficiary removed successfully']);

        } catch (PDOException $e) {
            error_log("Beneficiary delete error: " . $e->getMessage());
            json_response(['success' => false, 'message' => 'Failed to remove beneficiary'], 500);
        }
    }

    // Add new beneficiary
    $nickname   = clean_input($_POST['nickname'] ?? '');
    $account_no = clean_input($_POST['account_no'] ?? '');
    $bank_name  = clean_input($_POST['bank_name'] ?? 'Finzo');

    $errors = [];

    if (empty($nickname) || strlen($nickname) < 2 || strlen($nickname) > 100) {
        $errors['nickname'] = 'Nickname must be between 2 and 100 characters';
    }

    if (empty($account_no)) {
        $errors['account_no'] = 'Account number is required';
    }

    if (!empty($errors)) {
        json_response(['success' => false, 'errors' => $errors], 422);
    }

    try {
        // Check for duplicate
        $stmt = $pdo->prepare("SELECT id FROM beneficiaries WHERE user_id = ? AND account_no = ?");
        $stmt->execute([$user_id, $account_no]);
        if ($stmt->fetch()) {
            json_response(['success' => false, 'message' => 'This beneficiary already exists'], 409);
        }

        $stmt = $pdo->prepare("
            INSERT INTO beneficiaries (user_id, nickname, account_no, bank_name, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $nickname, $account_no, $bank_name]);

        json_response([
            'success' => true,
            'message' => 'Beneficiary added successfully',
            'beneficiary' => [
                'id'         => (int)$pdo->lastInsertId(),
                'nickname'   => $nickname,
                'account_no' => $account_no,
                'bank_name'  => $bank_name
            ]
        ], 201);

    } catch (PDOException $e) {
        error_log("Beneficiary add error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to add beneficiary'], 500);
    }
}

// =====================================================
// DELETE: REMOVE BENEFICIARY
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents('php://input'), $delete_data);
    $beneficiary_id = intval($delete_data['beneficiary_id'] ?? $_GET['id'] ?? 0);

    if (!$beneficiary_id) {
        json_response(['success' => false, 'message' => 'Beneficiary ID is required'], 422);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM beneficiaries WHERE id = ? AND user_id = ?");
        $stmt->execute([$beneficiary_id, $user_id]);

        if ($stmt->rowCount() === 0) {
            json_response(['success' => false, 'message' => 'Beneficiary not found'], 404);
        }

        json_response(['success' => true, 'message' => 'Beneficiary removed successfully']);

    } catch (PDOException $e) {
        error_log("Beneficiary delete error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Failed to remove beneficiary'], 500);
    }
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);
