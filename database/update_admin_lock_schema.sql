-- STEP 1: Add new columns to admin_users table
-- Only add if they do not already exist

ALTER TABLE admin_users
  ADD COLUMN IF NOT EXISTS account_locked       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS locked_at            TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS lock_reason          VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS answer_attempts      TINYINT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS answer_locked_until  TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS security_question    VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS security_answer_hash VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS password_reset_token      VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS password_reset_expires_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS security_question_set TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS auto_unlock_at          TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS temp_password_expiry    TIMESTAMP NULL DEFAULT NULL;

-- Explanation of each new column:
-- account_locked: 1 = locked, 0 = not locked
-- locked_at: when the account was locked
-- lock_reason: 'failed_password' or 'failed_answer'
-- answer_attempts: wrong security answer count
-- answer_locked_until: locked from answering until this time
-- security_question: the question text set by admin
-- security_answer_hash: bcrypt hash of the answer
-- password_reset_token: temp token for forced password reset
-- password_reset_expires_at: token expiry (30 minutes)
-- security_question_set: 1 if admin has set their question

-- STEP 2: Create admin_lock_log table
-- Tracks every lock and unlock event for audit

CREATE TABLE IF NOT EXISTS admin_lock_log (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id        VARCHAR(20) NOT NULL,
  event_type      ENUM(
                    'locked_password',
                    'locked_answer',
                    'unlocked_self',
                    'unlocked_by_super_admin',
                    'answer_attempt_failed',
                    'password_reset_completed'
                  ) NOT NULL,
  triggered_by    VARCHAR(20) NULL,
  ip_address      VARCHAR(45) NULL,
  details         TEXT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin_users(admin_id)
    ON DELETE CASCADE,
  FOREIGN KEY (triggered_by) REFERENCES admin_users(admin_id)
    ON DELETE SET NULL,
  INDEX idx_admin_id (admin_id),
  INDEX idx_event_type (event_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Synchronize security_question_set flag for existing data
UPDATE admin_users
SET security_question_set = 1
WHERE security_question IS NOT NULL
AND security_question != ''
AND security_answer_hash IS NOT NULL
AND security_answer_hash != '';
