-- =====================================================
-- FINZO BANKING SYSTEM — COMPLETE DATABASE SCHEMA
-- MySQL 8.x | UTF8MB4 | InnoDB
-- Version 1.0 — April 2026
-- =====================================================

CREATE DATABASE IF NOT EXISTS finzo_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE finzo_db;

-- =====================================================
-- TABLE 1: branches (created first — referenced by staff)
-- =====================================================
CREATE TABLE IF NOT EXISTS branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    branch_code VARCHAR(20) NOT NULL UNIQUE,
    state VARCHAR(100) NOT NULL,
    description TEXT NULL,
    manager_id INT UNSIGNED NULL,
    cert_path VARCHAR(500) NULL,
    type ENUM('system', 'custom') NOT NULL DEFAULT 'system',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 2: admin_users
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 3: users (customers)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'under_review', 'active', 'suspended', 'rejected') NOT NULL DEFAULT 'pending',

    -- KYC Step 1: Personal Info
    dob DATE NULL DEFAULT NULL,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NULL DEFAULT NULL,
    father_name VARCHAR(150) NULL DEFAULT NULL,
    occupation VARCHAR(100) NULL DEFAULT NULL,
    annual_income VARCHAR(50) NULL DEFAULT NULL,

    -- KYC Step 2: Address
    street VARCHAR(255) NULL DEFAULT NULL,
    city VARCHAR(100) NULL DEFAULT NULL,
    state VARCHAR(100) NULL DEFAULT NULL,
    pincode CHAR(6) NULL DEFAULT NULL,
    country VARCHAR(50) NOT NULL DEFAULT 'India',

    -- KYC Step 3: Identity Documents
    id_type VARCHAR(50) NULL DEFAULT NULL,
    id_number VARCHAR(100) NULL DEFAULT NULL,
    id_front_path VARCHAR(500) NULL DEFAULT NULL,
    id_back_path VARCHAR(500) NULL DEFAULT NULL,

    -- KYC Status
    kyc_submitted_at TIMESTAMP NULL DEFAULT NULL,
    rejection_reason TEXT NULL DEFAULT NULL,

    -- Soft Delete
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at TIMESTAMP NULL DEFAULT NULL,

    -- Login Security
    failed_attempts TINYINT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) NULL DEFAULT NULL,

    -- Timestamps
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 4: staff
-- =====================================================
CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    branch_id INT UNSIGNED NULL DEFAULT NULL,
    role ENUM('teller', 'manager', 'supervisor') NOT NULL DEFAULT 'teller',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) NULL DEFAULT NULL,
    failed_attempts TINYINT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY fk_staff_branch (branch_id) REFERENCES branches(id) ON DELETE SET NULL,

    INDEX idx_staff_id (staff_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Now add the foreign key on branches.manager_id → staff.id
ALTER TABLE branches
    ADD CONSTRAINT fk_branches_manager
    FOREIGN KEY (manager_id) REFERENCES staff(id) ON DELETE SET NULL;

-- =====================================================
-- TABLE 5: accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    account_no VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('savings', 'current') NOT NULL,
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    txn_pin_hash VARCHAR(255) NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL DEFAULT 4.25,
    daily_limit DECIMAL(15, 2) NOT NULL DEFAULT 10000.00,
    status ENUM('pending', 'active', 'suspended', 'rejected', 'closed') NOT NULL DEFAULT 'pending',
    opened_date DATE NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY fk_accounts_user (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_account_no (account_no),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 6: transactions
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    txn_id VARCHAR(50) NOT NULL UNIQUE,
    from_account_id INT UNSIGNED NULL DEFAULT NULL,
    to_account_id INT UNSIGNED NULL DEFAULT NULL,
    type ENUM('deposit', 'withdrawal', 'transfer', 'interest_credit') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NULL DEFAULT NULL,
    description VARCHAR(255) NULL DEFAULT NULL,
    reference_note VARCHAR(255) NULL DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed', 'reversed') NOT NULL DEFAULT 'pending',
    processed_by_staff_id INT UNSIGNED NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY fk_txn_from_account (from_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY fk_txn_to_account (to_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY fk_txn_staff (processed_by_staff_id) REFERENCES staff(id) ON DELETE SET NULL,

    INDEX idx_txn_id (txn_id),
    INDEX idx_from_account (from_account_id),
    INDEX idx_to_account (to_account_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_daily_limit (from_account_id, type, status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 7: beneficiaries
-- =====================================================
CREATE TABLE IF NOT EXISTS beneficiaries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    nickname VARCHAR(100) NOT NULL,
    account_no VARCHAR(20) NOT NULL COMMENT 'No FK — account_no may not yet exist at save time; validation is handled at the API layer',
    bank_name VARCHAR(150) NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY fk_beneficiary_user (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY uniq_user_account (user_id, account_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 8: notifications
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('transaction', 'security', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY fk_notification_user (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 9: audit_log
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NULL DEFAULT NULL,
    entity_type VARCHAR(50) NULL DEFAULT NULL,
    actor_id INT UNSIGNED NOT NULL,
    actor_role ENUM('admin', 'staff', 'user', 'system') NOT NULL,
    details TEXT NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_action_type (action_type),
    INDEX idx_actor_id (actor_id),
    INDEX idx_entity_type (entity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 10: interest_rates
-- =====================================================
CREATE TABLE IF NOT EXISTS interest_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate DECIMAL(5, 2) NOT NULL,
    apply_from_type ENUM('Immediately', 'Next Month', 'Custom Date') NOT NULL,
    apply_from_date DATE NULL DEFAULT NULL,
    changed_by_admin_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY fk_interest_admin (changed_by_admin_id) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 11: password_resets
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY fk_reset_user (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 12: login_attempts (tracking failed logins)
-- =====================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL COMMENT 'email or staff_id',
    attempts TINYINT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    last_attempt_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_identifier (identifier),
    INDEX idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SEED DATA
-- =====================================================

-- Seed Admin User: admin@finzo.com / Admin@123
-- Password hash generated by PHP: password_hash('Admin@123', PASSWORD_BCRYPT)
INSERT INTO admin_users (admin_id, full_name, email, password_hash, status) VALUES
('STF001', 'Super Admin', 'admin@finzo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Seed System Branches (3 branches as specified)
INSERT INTO branches (name, branch_code, state, type, status) VALUES
('Barasat HQ', 'FNZ-WB-001', 'West Bengal', 'system', 'active'),
('North Branch', 'FNZ-WB-002', 'West Bengal', 'system', 'active'),
('South Branch', 'FNZ-WB-003', 'West Bengal', 'system', 'active');

-- Seed Initial Interest Rate
INSERT INTO interest_rates (rate, apply_from_type, apply_from_date, changed_by_admin_id) VALUES
(4.25, 'Immediately', CURDATE(), 1);


-- =====================================================
-- NOTES
-- =====================================================
-- 1. The admin password hash above is a placeholder bcrypt hash.
--    For production, generate with: php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT);"
--    and replace the hash value before importing.
--
-- 2. All tables use InnoDB for transaction support (BEGIN/COMMIT/ROLLBACK).
--
-- 3. The branches table is created before staff to allow the FK reference.
--    The manager_id FK is added via ALTER TABLE after staff table creation.
--
-- 4. DECIMAL(15,2) supports balances up to 9,999,999,999,999.99 INR.
--
-- 5. All TIMESTAMP columns use MySQL's CURRENT_TIMESTAMP default.
--
-- 6. Soft-delete pattern used for users (is_deleted + deleted_at).
-- =====================================================
