SET FOREIGN_KEY_CHECKS = 0;

-- Finzo Banking System - Admin Role Based Access Control Schema Update
-- Step 1: Drop existing table
DROP TABLE IF EXISTS admin_users;

-- Step 2: Create new admin_users table
CREATE TABLE admin_users (
  id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id                VARCHAR(20) NOT NULL UNIQUE,

  -- Personal Information
  full_name               VARCHAR(150) NOT NULL,
  date_of_birth           DATE NULL,
  gender                  ENUM('Male','Female','Other','Prefer not to say') NULL,
  profile_photo           VARCHAR(500) NULL,

  -- Contact Information
  email                   VARCHAR(255) NOT NULL UNIQUE,
  phone                   VARCHAR(15) NULL,

  -- Identity & Security
  password_hash           VARCHAR(255) NOT NULL,
  two_fa_enabled          TINYINT(1) DEFAULT 0,
  two_fa_secret           VARCHAR(255) NULL,
  security_question       VARCHAR(255) NULL,
  security_answer_hash    VARCHAR(255) NULL,

  -- Role & Access
  -- super_admin: only one, hardcoded, cannot be created via UI
  -- admin: added by super_admin, full access with restrictions
  -- moderator: added by super_admin, limited access only
  admin_role              ENUM('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  access_level            ENUM('full','restricted') NOT NULL DEFAULT 'restricted',
  status                  ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',

  -- Activity Records (auto)
  last_login_at           TIMESTAMP NULL,
  last_login_ip           VARCHAR(45) NULL,
  last_password_changed   TIMESTAMP NULL,
  failed_attempts         TINYINT DEFAULT 0,
  locked_until            TIMESTAMP NULL,

  -- System Records (auto)
  -- created_by is NULL for super_admin
  -- created_by is super_admin id for all other admins
  created_by              VARCHAR(20) NULL,
  created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (created_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_email (email),
  INDEX idx_role (admin_role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: No INSERT here. Super Admin will be inserted at the end manually

SET FOREIGN_KEY_CHECKS = 1;

-- Seed super admin (replace the hash below with: php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT);")
INSERT INTO admin_users (admin_id, full_name, email, password_hash, admin_role, access_level, status)
VALUES ('STF001', 'Super Admin', 'admin@finzo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'full', 'active');
