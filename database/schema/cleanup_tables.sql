-- STEP 1: Drop admin_profile_photos
-- Reason: profile photo path already stored
-- directly in admin_users.profile_photo column

DROP TABLE IF EXISTS admin_profile_photos;

-- STEP 2: Drop login_attempts
-- Reason: failed_attempts, locked_until already
-- exist in users table — this table is duplicate

DROP TABLE IF EXISTS login_attempts;

-- STEP 4: Create system_settings table
-- Stores all app-wide configurable settings
-- Admin can change values without touching code

CREATE TABLE IF NOT EXISTS system_settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  setting_type  ENUM('string','number','boolean','json')
                NOT NULL DEFAULT 'string',
  label         VARCHAR(255) NOT NULL,
  description   TEXT NULL,
  updated_by    VARCHAR(20) NULL,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES admin_users(admin_id)
    ON DELETE SET NULL,
  INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Seed default system settings
INSERT INTO system_settings
  (setting_key, setting_value, setting_type,
   label, description)
VALUES
  ('app_name',
   'Finzo',
   'string',
   'Application Name',
   'Name of the banking application'),

  ('min_initial_deposit',
   '500',
   'number',
   'Minimum Initial Deposit (₹)',
   'Minimum amount required to open an account'),

  ('daily_transfer_limit',
   '10000',
   'number',
   'Daily Transfer Limit (₹)',
   'Maximum amount a customer can transfer per day'),

  ('max_withdrawal_amount',
   '50000',
   'number',
   'Maximum Single Withdrawal (₹)',
   'Maximum amount for a single cash withdrawal'),

  ('min_transfer_amount',
   '1',
   'number',
   'Minimum Transfer Amount (₹)',
   'Minimum amount for a fund transfer'),

  ('default_savings_interest',
   '4.25',
   'number',
   'Default Savings Interest Rate (%)',
   'Interest rate applied to new savings accounts'),

  ('default_current_interest',
   '0.00',
   'number',
   'Default Current Interest Rate (%)',
   'Interest rate applied to current accounts'),

  ('max_failed_login_attempts',
   '5',
   'number',
   'Max Failed Login Attempts (Customers)',
   'Number of wrong passwords before customer lockout'),

  ('customer_lockout_duration',
   '1800',
   'number',
   'Customer Lockout Duration (seconds)',
   'How long customer account stays locked — 1800 = 30 min'),

  ('max_beneficiaries',
   '10',
   'number',
   'Maximum Beneficiaries Per Customer',
   'How many beneficiaries a customer can save'),

  ('maintenance_mode',
   'false',
   'boolean',
   'Maintenance Mode',
   'When true all customer logins are blocked'),

  ('maintenance_message',
   'We are currently under maintenance. Please try again later.',
   'string',
   'Maintenance Message',
   'Message shown to customers during maintenance'),

  ('bank_ifsc_code',
   'FNZO0000001',
   'string',
   'Bank IFSC Code',
   'Official IFSC code of the bank'),

  ('bank_address',
   'Barasat, West Bengal, India',
   'string',
   'Bank Registered Address',
   'Official registered address of the bank'),

  ('support_email',
   'support@finzo.com',
   'string',
   'Support Email',
   'Customer support email address'),

  ('support_phone',
   '1800-000-0000',
   'string',
   'Support Phone Number',
   'Customer support phone number');

-- STEP 5: Create kyc_applications table
-- Tracks every KYC submission separately
-- from the users table
-- Professional banking systems never mix
-- profile data with application tracking

CREATE TABLE IF NOT EXISTS kyc_applications (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           VARCHAR(20) NOT NULL,

  -- Application Status Tracking
  application_no    VARCHAR(30) NOT NULL UNIQUE,
  status            ENUM('draft','submitted','under_review',
                    'approved','rejected') NOT NULL
                    DEFAULT 'draft',
  submission_count  TINYINT DEFAULT 1,

  -- Personal Info snapshot at time of submission
  dob               DATE NULL,
  gender            VARCHAR(30) NULL,
  father_name       VARCHAR(150) NULL,
  occupation        VARCHAR(100) NULL,
  annual_income     VARCHAR(50) NULL,

  -- Address snapshot
  street            VARCHAR(255) NULL,
  city              VARCHAR(100) NULL,
  state             VARCHAR(100) NULL,
  pincode           CHAR(6) NULL,

  -- Identity Documents
  id_type           VARCHAR(50) NULL,
  id_number         VARCHAR(100) NULL,
  id_front_path     VARCHAR(500) NULL,
  id_back_path      VARCHAR(500) NULL,

  -- Account Preference
  account_type      ENUM('savings','current') NULL,
  initial_deposit   DECIMAL(15,2) NULL,

  -- Review Info
  reviewed_by       VARCHAR(20) NULL,
  reviewed_at       TIMESTAMP NULL,
  rejection_reason  TEXT NULL,
  admin_notes       TEXT NULL,

  -- Timestamps
  submitted_at      TIMESTAMP NULL,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(customer_id)
    ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES admin_users(admin_id)
    ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_application_no (application_no),
  INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
