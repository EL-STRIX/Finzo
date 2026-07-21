-- =====================================================
-- FINZO BANKING SYSTEM — COMPLETE DATABASE SCHEMA
-- MySQL 8.x | UTF8MB4 | InnoDB
-- Version 2.0
-- =====================================================

CREATE DATABASE IF NOT EXISTS finzo_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE finzo_db;

-- =====================================================
-- TABLE 1: branches
-- =====================================================
CREATE TABLE IF NOT EXISTS branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    name VARCHAR(200) NOT NULL,
    branch_code VARCHAR(20) NOT NULL UNIQUE,
    state VARCHAR(100) NOT NULL,
    description TEXT NULL,
    manager_id INT UNSIGNED NULL,
    cert_path VARCHAR(500) NULL,
    type ENUM('system', 'custom') NOT NULL DEFAULT 'system',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 2: admin_users
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_users (
  id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id                VARCHAR(20) NOT NULL UNIQUE,
  full_name               VARCHAR(150) NOT NULL,
  date_of_birth           DATE NULL,
  gender                  ENUM('Male','Female','Other','Prefer not to say') NULL,
  profile_photo           VARCHAR(500) NULL,
  email                   VARCHAR(255) NOT NULL UNIQUE,
  phone                   VARCHAR(15) NULL,
  password_hash           VARCHAR(255) NOT NULL,
  two_fa_enabled TINYINT(1) DEFAULT 0,
  two_fa_secret           VARCHAR(255) NULL,
  admin_role              ENUM('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  access_level            ENUM('full','restricted') NOT NULL DEFAULT 'restricted',
  status                  ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  last_login_at           TIMESTAMP NULL,
  last_login_ip           VARCHAR(45) NULL,
  last_password_changed   TIMESTAMP NULL,
  failed_attempts         TINYINT DEFAULT 0,
  locked_until            TIMESTAMP NULL,
  created_by              VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- From update_admin_lock_schema.sql
  account_locked          TINYINT(1) DEFAULT 0,
  locked_at               TIMESTAMP NULL,
  lock_reason             VARCHAR(255) NULL,
  answer_attempts         TINYINT DEFAULT 0,
  answer_locked_until     TIMESTAMP NULL,
  security_question       VARCHAR(255) NULL,
  security_answer_hash    VARCHAR(255) NULL,
  password_reset_token    VARCHAR(100) NULL,
  password_reset_expires_at TIMESTAMP NULL,
  security_question_set   TINYINT(1) DEFAULT 0,
  auto_unlock_at          TIMESTAMP NULL,
  temp_password_expiry    TIMESTAMP NULL DEFAULT NULL,

  FOREIGN KEY (created_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_email (email),
  INDEX idx_role (admin_role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 3: users (customers)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    customer_id VARCHAR(20) UNIQUE,
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
    
    -- New columns from Audit prompt
    mother_name VARCHAR(150) NULL DEFAULT NULL,
    marital_status ENUM('Single','Married','Divorced','Widowed','Prefer not to say') NULL DEFAULT NULL,
    nationality VARCHAR(100) NULL DEFAULT NULL,
    source_of_income VARCHAR(100) NULL DEFAULT NULL,
    profile_picture VARCHAR(500) NULL DEFAULT NULL,
    temp_pass_status ENUM('none','pending') NOT NULL DEFAULT 'none',

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 4: staff
-- =====================================================
CREATE TABLE IF NOT EXISTS staff (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name             VARCHAR(150) NOT NULL,
  date_of_birth         DATE NULL,
  gender                ENUM('Male','Female','Other','Prefer not to say') NULL,
  profile_photo         VARCHAR(500) NULL,
  personal_email        VARCHAR(255) NOT NULL UNIQUE,
  phone                 VARCHAR(15) NOT NULL,
  emergency_contact_name  VARCHAR(150) NULL,
  emergency_contact_phone VARCHAR(15) NULL,
  staff_id              VARCHAR(20) NOT NULL UNIQUE,
  designation           ENUM('Teller','Supervisor','Manager') NOT NULL DEFAULT 'Teller',
  department            ENUM('Teller Operations') NOT NULL DEFAULT 'Teller Operations',
  branch_id             INT UNSIGNED NULL,
  date_of_joining       DATE NOT NULL DEFAULT (CURDATE()),
  street                VARCHAR(255) NULL,
  city                  VARCHAR(100) NULL,
  state                 VARCHAR(100) NULL,
  pincode               CHAR(6) NULL,
  country               VARCHAR(50) DEFAULT 'India',
  id_type               ENUM('Aadhaar','PAN','Passport') NULL,
  id_number             VARCHAR(100) NULL,
  front_image           VARCHAR(500) NULL,
  back_image            VARCHAR(500) NULL,
  password_hash         VARCHAR(255) NOT NULL,
  status                ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_by            VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login_at         TIMESTAMP NULL,
  last_login_ip         VARCHAR(45) NULL,

  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_staff_id (staff_id),
  INDEX idx_status (status),
  INDEX idx_branch_id (branch_id),
  INDEX idx_personal_email (personal_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE branches
    ADD CONSTRAINT fk_branches_manager
    FOREIGN KEY (manager_id) REFERENCES staff(id) ON DELETE SET NULL;

-- =====================================================
-- TABLE 5: accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    user_id VARCHAR(20) NOT NULL UNIQUE,
    account_no VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('savings', 'current') NOT NULL,
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00 CHECK (balance >= 0),
    txn_pin_hash VARCHAR(255) NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL DEFAULT 4.25 CHECK (interest_rate >= 0),
    daily_limit DECIMAL(15, 2) NOT NULL DEFAULT 10000.00,
    status ENUM('pending', 'active', 'suspended', 'rejected', 'closed') NOT NULL DEFAULT 'pending',
    opened_date DATE NULL DEFAULT NULL,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    frozen_at TIMESTAMP NULL DEFAULT NULL,
    closure_reason VARCHAR(255) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY fk_accounts_user (user_id) REFERENCES users(customer_id) ON DELETE RESTRICT,
    INDEX idx_account_no (account_no),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 6: transactions
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    txn_id VARCHAR(50) NOT NULL UNIQUE,
    from_account_id INT UNSIGNED NULL DEFAULT NULL,
    to_account_id INT UNSIGNED NULL DEFAULT NULL,
    type ENUM('deposit', 'withdrawal', 'transfer', 'interest_credit') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL CHECK (amount > 0),
    balance_after DECIMAL(15, 2) NULL DEFAULT NULL,
    dest_balance_after DECIMAL(15, 2) NULL DEFAULT NULL,
    description VARCHAR(255) NULL DEFAULT NULL,
    reference_note VARCHAR(255) NULL DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed', 'reversed') NOT NULL DEFAULT 'pending',
    processed_by_staff_id INT UNSIGNED NULL DEFAULT NULL,
    journal_entry_id INT UNSIGNED NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY fk_txn_from_account (from_account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY fk_txn_to_account (to_account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY fk_txn_staff (processed_by_staff_id) REFERENCES staff(id) ON DELETE SET NULL,
    FOREIGN KEY fk_txn_journal (journal_entry_id) REFERENCES journal_entries(id) ON DELETE RESTRICT,
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
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    user_id VARCHAR(20) NOT NULL,
    nickname VARCHAR(100) NOT NULL,
    account_no VARCHAR(20) NOT NULL,
    bank_name VARCHAR(150) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY fk_beneficiary_user (user_id) REFERENCES users(customer_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_account (user_id, account_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 8: notifications
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    user_id VARCHAR(20) NOT NULL,
    type ENUM('transaction', 'security', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY fk_notification_user (user_id) REFERENCES users(customer_id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 9: audit_log
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    action_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NULL DEFAULT NULL,
    entity_type VARCHAR(50) NULL DEFAULT NULL,
    actor_id VARCHAR(100) NOT NULL,
    actor_role ENUM('admin', 'staff', 'user', 'system') NOT NULL,
    details TEXT NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    rate DECIMAL(5, 2) NOT NULL CHECK (rate >= 0),
    apply_from_type ENUM('Immediately', 'Next Month', 'Custom Date') NOT NULL,
    apply_from_date DATE NULL DEFAULT NULL,
    changed_by_admin_id VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY fk_interest_admin (changed_by_admin_id) REFERENCES admin_users(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 11: password_resets
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    user_id VARCHAR(20) NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY fk_reset_user (user_id) REFERENCES users(customer_id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 12: system_settings
-- =====================================================
CREATE TABLE IF NOT EXISTS system_settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  setting_type ENUM('string','number','boolean','json') NOT NULL DEFAULT 'string',
  setting_category ENUM('banking','security','application','operational') NOT NULL DEFAULT 'application',
  label         VARCHAR(255) NOT NULL,
  description   TEXT NULL,
  updated_by    VARCHAR(20) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 13: kyc_applications
-- =====================================================
CREATE TABLE IF NOT EXISTS kyc_applications (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           VARCHAR(20) NOT NULL,
  application_no    VARCHAR(30) NOT NULL UNIQUE,
  status            ENUM('draft','submitted','under_review','approved','rejected') NOT NULL DEFAULT 'draft',
  submission_count  TINYINT DEFAULT 1,
  dob               DATE NULL,
  gender            VARCHAR(30) NULL,
  father_name       VARCHAR(150) NULL,
  occupation        VARCHAR(100) NULL,
  annual_income     VARCHAR(50) NULL,
  street            VARCHAR(255) NULL,
  city              VARCHAR(100) NULL,
  state             VARCHAR(100) NULL,
  pincode           CHAR(6) NULL,
  id_type           VARCHAR(50) NULL,
  id_number         VARCHAR(100) NULL,
  id_front_path     VARCHAR(500) NULL,
  id_back_path      VARCHAR(500) NULL,
  account_type      ENUM('savings','current') NULL,
  initial_deposit   DECIMAL(15,2) NULL,
  reviewed_by       VARCHAR(20) NULL,
  reviewed_at       TIMESTAMP NULL,
  rejection_reason  TEXT NULL,
  admin_notes       TEXT NULL,
  submitted_at      TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(customer_id) ON DELETE RESTRICT,
  FOREIGN KEY (reviewed_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_application_no (application_no),
  INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 14: admin_lock_log
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_lock_log (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id        VARCHAR(20) NOT NULL,
  event_type      ENUM('locked_password','locked_answer','unlocked_self','unlocked_by_super_admin','answer_attempt_failed','password_reset_completed') NOT NULL,
  triggered_by    VARCHAR(20) NULL,
  ip_address      VARCHAR(45) NULL,
  details         TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin_users(admin_id) ON DELETE CASCADE,
  FOREIGN KEY (triggered_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_admin_id (admin_id),
  INDEX idx_event_type (event_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 15: staff_shifts
-- =====================================================
CREATE TABLE IF NOT EXISTS staff_shifts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id        VARCHAR(20) NOT NULL,
    shift_date      DATE NOT NULL,
    clock_in        TIMESTAMP NULL DEFAULT NULL,
    clock_out       TIMESTAMP NULL DEFAULT NULL,
    status          ENUM('active', 'completed', 'absent') NOT NULL DEFAULT 'active',
    notes           VARCHAR(255) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    INDEX idx_staff_id (staff_id),
    INDEX idx_shift_date (shift_date),
    UNIQUE KEY uniq_staff_date (staff_id, shift_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 16: chart_of_accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    account_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category ENUM('asset', 'liability', 'equity', 'income', 'expense', 'contra', 'suspense') NOT NULL,
    parent_id INT UNSIGNED NULL DEFAULT NULL,
    normal_balance ENUM('debit', 'credit') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    description TEXT NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES chart_of_accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 17: accounting_periods
-- =====================================================
CREATE TABLE IF NOT EXISTS accounting_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    period_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open', 'closed', 'locked') NOT NULL DEFAULT 'open',
    closed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 18: journal_entries
-- =====================================================
CREATE TABLE IF NOT EXISTS journal_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    journal_number VARCHAR(100) NOT NULL UNIQUE,
    reference VARCHAR(100) NULL DEFAULT NULL,
    posting_date DATE NOT NULL,
    value_date DATE NOT NULL,
    description TEXT NOT NULL,
    source_module VARCHAR(100) NOT NULL,
    status ENUM('draft', 'posted', 'reversed', 'cancelled') NOT NULL DEFAULT 'posted',
    created_by_staff_id INT UNSIGNED NULL DEFAULT NULL,
    approved_by_staff_id INT UNSIGNED NULL DEFAULT NULL,
    period_id INT UNSIGNED NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_staff_id) REFERENCES staff(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by_staff_id) REFERENCES staff(id) ON DELETE RESTRICT,
    FOREIGN KEY (period_id) REFERENCES accounting_periods(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 19: journal_lines
-- =====================================================
CREATE TABLE IF NOT EXISTS journal_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE DEFAULT (UUID()),
    journal_entry_id INT UNSIGNED NOT NULL,
    account_id INT UNSIGNED NOT NULL,
    debit_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00 CHECK (debit_amount >= 0),
    credit_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00 CHECK (credit_amount >= 0),
    narration VARCHAR(255) NULL DEFAULT NULL,
    sequence_number INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FINANCIAL POSTING ENGINE (DB TRIGGERS)
-- =====================================================
DELIMITER //

CREATE TRIGGER after_transaction_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    DECLARE v_period_id INT UNSIGNED;
    DECLARE v_journal_id INT UNSIGNED;
    DECLARE v_coa_debit INT UNSIGNED;
    DECLARE v_coa_credit INT UNSIGNED;
    DECLARE v_journal_num VARCHAR(100);
    
    -- Get open period
    SELECT id INTO v_period_id FROM accounting_periods WHERE status = 'open' LIMIT 1;
    IF v_period_id IS NULL THEN
        INSERT INTO accounting_periods (period_name, start_date, end_date, status) VALUES ('Current Period', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'open');
        SET v_period_id = LAST_INSERT_ID();
    END IF;

    -- Ensure COA exists for mapping
    SELECT id INTO v_coa_debit FROM chart_of_accounts WHERE category = 'asset' LIMIT 1;
    IF v_coa_debit IS NULL THEN
        INSERT INTO chart_of_accounts (account_code, name, category, normal_balance) VALUES (CONCAT('1000-', FLOOR(RAND()*1000)), 'General Asset', 'asset', 'debit');
        SET v_coa_debit = LAST_INSERT_ID();
    END IF;
    
    SELECT id INTO v_coa_credit FROM chart_of_accounts WHERE category = 'liability' LIMIT 1;
    IF v_coa_credit IS NULL THEN
        INSERT INTO chart_of_accounts (account_code, name, category, normal_balance) VALUES (CONCAT('2000-', FLOOR(RAND()*1000)), 'General Liability', 'liability', 'credit');
        SET v_coa_credit = LAST_INSERT_ID();
    END IF;

    SET v_journal_num = CONCAT('JNL-', REPLACE(NEW.txn_id, 'TXN', ''));

    INSERT INTO journal_entries (journal_number, reference, posting_date, value_date, description, source_module, status, period_id)
    VALUES (v_journal_num, NEW.txn_id, CURDATE(), CURDATE(), COALESCE(NEW.description, 'System Posting'), 'Core Banking', 'posted', v_period_id);
    
    SET v_journal_id = LAST_INSERT_ID();

    -- Double Entry Accounting
    INSERT INTO journal_lines (journal_entry_id, account_id, debit_amount, credit_amount, narration, sequence_number)
    VALUES (v_journal_id, v_coa_debit, NEW.amount, 0, 'Auto Debit', 1);

    INSERT INTO journal_lines (journal_entry_id, account_id, debit_amount, credit_amount, narration, sequence_number)
    VALUES (v_journal_id, v_coa_credit, 0, NEW.amount, 'Auto Credit', 2);
    
    -- In a real DB we would UPDATE transactions SET journal_entry_id = v_journal_id WHERE id = NEW.id
    -- But since this is AFTER INSERT, we cannot update the same row directly without a BEFORE INSERT or autonomous transaction in MySQL.
END//

DELIMITER ;

-- =====================================================
-- SEED DATA
-- =====================================================

-- IMPORTANT: Change this password immediately after first login.
-- Seed Admin User: admin@finzo.com / Admin@Finzo2026!
INSERT INTO admin_users (admin_id, full_name, email, password_hash, admin_role, access_level, status)
VALUES ('ADM001', 'Super Admin', 'admin@finzo.com', '$2y$12$1.GA7mKfOstMarLAKnxFQ.HI2BydufGS6IJ7Jo1uYZgtImsfxtTU6', 'super_admin', 'full', 'active');

INSERT INTO branches (name, branch_code, state, type, status) VALUES
('Barasat HQ', 'FNZ-WB-001', 'West Bengal', 'system', 'active'),
('North Branch', 'FNZ-WB-002', 'West Bengal', 'system', 'active'),
('South Branch', 'FNZ-WB-003', 'West Bengal', 'system', 'active');

INSERT INTO interest_rates (rate, apply_from_type, apply_from_date, changed_by_admin_id) VALUES
(4.25, 'Immediately', CURDATE(), 'ADM001');

INSERT INTO system_settings (setting_key, setting_value, setting_type, label, description) VALUES
  ('app_name', 'Finzo', 'string', 'Application Name', 'Name of the banking application'),
  ('min_initial_deposit', '500', 'number', 'Minimum Initial Deposit (₹)', 'Minimum amount required to open an account'),
  ('daily_transfer_limit', '10000', 'number', 'Daily Transfer Limit (₹)', 'Maximum amount a customer can transfer per day'),
  ('max_withdrawal_amount', '50000', 'number', 'Maximum Single Withdrawal (₹)', 'Maximum amount for a single cash withdrawal'),
  ('min_transfer_amount', '1', 'number', 'Minimum Transfer Amount (₹)', 'Minimum amount for a fund transfer'),
  ('default_savings_interest', '4.25', 'number', 'Default Savings Interest Rate (%)', 'Interest rate applied to new savings accounts'),
  ('default_current_interest', '0.00', 'number', 'Default Current Interest Rate (%)', 'Interest rate applied to current accounts'),
  ('max_failed_login_attempts', '5', 'number', 'Max Failed Login Attempts (Customers)', 'Number of wrong passwords before customer lockout'),
  ('customer_lockout_duration', '1800', 'number', 'Customer Lockout Duration (seconds)', 'How long customer account stays locked — 1800 = 30 min'),
  ('max_beneficiaries', '10', 'number', 'Maximum Beneficiaries Per Customer', 'How many beneficiaries a customer can save'),
  ('maintenance_mode', 'false', 'boolean', 'Maintenance Mode', 'When true all customer logins are blocked'),
  ('maintenance_message', 'We are currently under maintenance. Please try again later.', 'string', 'Maintenance Message', 'Message shown to customers during maintenance'),
  ('bank_ifsc_code', 'FNZO0000001', 'string', 'Bank IFSC Code', 'Official IFSC code of the bank'),
  ('bank_address', 'Barasat, West Bengal, India', 'string', 'Bank Registered Address', 'Official registered address of the bank'),
  ('support_email', 'support@finzo.com', 'string', 'Support Email', 'Customer support email address'),
  ('support_phone', '1800-000-0000', 'string', 'Support Phone Number', 'Customer support phone number');

-- End of schema
