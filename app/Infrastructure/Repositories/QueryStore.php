<?php
namespace App\Infrastructure\Repositories;

class QueryStore
{
    private static array $queries = [
        'q_4623d5b9' => 'SELECT id, full_name, staff_id FROM staff WHERE id = ?',
        'q_a4dee608' => 'UPDATE staff SET password_hash = ? WHERE id = ?',
        'q_271aedbc' => 'SELECT password_hash FROM admin_users WHERE id = ?',
        'q_109dc119' => 'SELECT id, staff_id, full_name, personal_email as email, status FROM staff WHERE id = ?',
        'q_6013aa56' => 'UPDATE staff SET status = \'suspended\', suspension_reason = ?, suspended_by = ?, updated_at = NOW() WHERE id = ?',
        'q_953d62ef' => 'UPDATE staff SET status = \'active\', suspension_reason = NULL, suspended_by = NULL, updated_at = NOW() WHERE id = ?',
        'q_0bfb3963' => 'UPDATE staff SET password_hash = ?, updated_at = NOW() WHERE id = ?',
        'q_42144d21' => 'DELETE FROM staff WHERE id = ?',
        'q_960c6e0a' => 'SELECT id, profile_photo FROM staff WHERE id = ?',
        'q_28c17e7e' => 'UPDATE staff SET profile_photo = ? WHERE id = ?',
        'q_e828e2ca' => '
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, 
                    s.designation, s.department, s.status,
                    s.gender, s.date_of_birth, s.emergency_contact_name, s.emergency_contact_phone,
                    s.date_of_joining, s.created_by,
                    s.last_login_at, s.last_login_ip, s.created_at, s.profile_photo,
                    s.street, s.city, s.state, s.pincode, s.country,
                    s.id_type, s.id_number, s.front_image, s.back_image,
                    s.failed_attempts, s.locked_until, s.suspension_reason, s.suspended_by,
                    b.name as branch_name, b.branch_code, b.state as branch_state,
                    COALESCE(creator_admin.full_name, creator_staff.full_name, s.created_by, \'System\') as created_by_name,
                    COALESCE(suspender_admin.full_name, s.suspended_by) as suspended_by_name
            FROM staff s
            LEFT JOIN branches b ON s.branch_id = b.id
            LEFT JOIN admin_users creator_admin ON s.created_by = creator_admin.admin_id
            LEFT JOIN staff creator_staff ON s.created_by = creator_staff.staff_id
            LEFT JOIN admin_users suspender_admin ON s.suspended_by = suspender_admin.admin_id
            WHERE s.id = ?
        ',
        'q_897e4bad' => '
            SELECT COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN type = \'deposit\' THEN amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN type = \'withdrawal\' THEN amount ELSE 0 END), 0) as total_withdrawals,
                COUNT(CASE WHEN type = \'deposit\' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = \'withdrawal\' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = \'completed\'
        ',
        'q_478c127d' => '
            SELECT COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN type = \'deposit\' THEN amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN type = \'withdrawal\' THEN amount ELSE 0 END), 0) as total_withdrawals,
                COUNT(CASE WHEN type = \'deposit\' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = \'withdrawal\' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = \'completed\' AND DATE(created_at) = CURDATE()
        ',
        'q_d9c30801' => 'SELECT COUNT(DISTINCT DATE(created_at)) FROM transactions WHERE processed_by_staff_id = ?',
        'q_969d95c9' => '
            SELECT t.txn_id, t.type, t.amount, t.status, t.created_at,
                COALESCE(a.account_no, \'N/A\') as account_no, COALESCE(u.full_name, \'N/A\') as customer_name
            FROM transactions t
            LEFT JOIN accounts a ON COALESCE(t.to_account_id, t.from_account_id) = a.id
            LEFT JOIN users u ON a.user_id = u.customer_id
            WHERE t.processed_by_staff_id = ? ORDER BY t.created_at DESC LIMIT 20
        ',
        'q_79365276' => '
            SELECT DATE(created_at) as shift_date, MIN(created_at) as login_time, MAX(created_at) as logout_time
            FROM (
                SELECT created_at FROM transactions WHERE processed_by_staff_id = :id
                UNION ALL
                SELECT created_at FROM audit_log WHERE actor_id = :actor_id AND actor_role = \'Staff\'
            ) as activities
            GROUP BY shift_date ORDER BY shift_date DESC LIMIT 5
        ',
        'q_31441bc5' => 'SELECT id, action_type, entity_id, entity_type, details, ip_address, created_at FROM audit_log WHERE actor_id = ? AND actor_role IN (\'Staff\', \'staff\') ORDER BY created_at DESC LIMIT 20',
        'q_9f511115' => 'SELECT COUNT(*) FROM staff s WHERE {$where_sql}',
        'q_9199e7d3' => '
            SELECT s.id, s.staff_id, s.full_name, s.personal_email, s.phone, s.designation, s.department,
                s.status, s.profile_photo, s.date_of_joining, s.last_login_at, b.name as branch_name,
                (SELECT COUNT(*) FROM transactions WHERE processed_by_staff_id = s.id) as txn_count,
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE processed_by_staff_id = s.id AND status = \'completed\') as txn_amount
            FROM staff s LEFT JOIN branches b ON b.id = s.branch_id WHERE {$where_sql} ORDER BY s.created_at DESC LIMIT {$per_page} OFFSET {$offset}
        ',
        'q_94aa2694' => 'SELECT COUNT(*) FROM staff',
        'q_6ad2c8a1' => 'SELECT COUNT(*) FROM staff WHERE status = \'active\'',
        'q_9dda4608' => 'SELECT COUNT(*) FROM staff WHERE status = \'suspended\'',
        'q_db6f4d23' => 'SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()',
        'q_0b1bf48e' => '
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, s.designation as role, s.status,
                    s.suspension_reason, s.suspended_by, s.updated_at as suspended_at, s.created_at as onboarding_date, s.profile_photo, b.name as branch_name
            FROM staff s LEFT JOIN branches b ON s.branch_id = b.id WHERE s.id = ? AND s.status = \'suspended\'
        ',
        'q_6ba2bbee' => '
            SELECT COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_volume,
                COUNT(CASE WHEN type = \'deposit\' THEN 1 END) as deposit_count,
                COUNT(CASE WHEN type = \'withdrawal\' THEN 1 END) as withdrawal_count
            FROM transactions WHERE processed_by_staff_id = ? AND status = \'completed\'
        ',
        'q_2ad69a5c' => 'SELECT type, amount, status, created_at as timestamp FROM transactions WHERE processed_by_staff_id = ? ORDER BY created_at DESC LIMIT 10',
        'q_011f9128' => '
            SELECT s.id, s.staff_id, s.full_name, s.personal_email as email, s.phone, s.designation as role, s.status,
                    s.suspension_reason, s.suspended_by, s.updated_at, s.profile_photo, b.name as branch_name
            FROM staff s LEFT JOIN branches b ON s.branch_id = b.id WHERE {$where_sql} ORDER BY s.updated_at DESC LIMIT {$per_page} OFFSET {$offset}
        ',
        'q_ef776789' => 'UPDATE users SET status = \'under_review\', kyc_submitted_at = NOW(), rejection_reason = NULL, updated_at = NOW() WHERE id = ?',
        'q_371058c0' => 'UPDATE kyc_applications SET status = \'submitted\', submission_count = ?, profile_picture = ?, dob = ?, gender = ?, father_name = ?, mother_name = ?, marital_status = ?, nationality = ?, occupation = ?, annual_income = ?, source_of_income = ?, street = ?, city = ?, state = ?, pincode = ?, id_type = ?, id_number = ?, id_front_path = ?, id_back_path = ?, account_type = ?, initial_deposit = ?, submitted_at = NOW(), rejection_reason = NULL, reviewed_by = NULL, reviewed_at = NULL WHERE user_id = ?',
        'q_e07f7e3c' => 'INSERT INTO kyc_applications (user_id, profile_picture, application_no, status, submission_count, dob, gender, father_name, mother_name, marital_status, nationality, occupation, annual_income, source_of_income, street, city, state, pincode, id_type, id_number, id_front_path, id_back_path, account_type, initial_deposit, submitted_at, created_at, updated_at) VALUES (?, ?, ?, \'submitted\', 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())',
    ];

    public static function get(string $id): string
    {
        return self::$queries[$id] ?? '';
    }
}
