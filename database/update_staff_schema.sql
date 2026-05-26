SET FOREIGN_KEY_CHECKS = 0;

-- STEP 0: Clear references from other tables
UPDATE branches SET manager_id = NULL;
UPDATE transactions SET processed_by_staff_id = NULL;

-- STEP 1: Drop all existing staff related tables
-- Drop in correct order to avoid foreign key errors

DROP TABLE IF EXISTS staff_shifts;
DROP TABLE IF EXISTS staff_address;
DROP TABLE IF EXISTS staff_documents;
DROP TABLE IF EXISTS staff;

-- STEP 2: Create staff table (main)

CREATE TABLE staff (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  -- Personal Information
  full_name             VARCHAR(150) NOT NULL,
  date_of_birth         DATE NULL,
  gender                ENUM('Male','Female','Other','Prefer not to say') NULL,
  profile_photo         VARCHAR(500) NULL,

  -- Contact Information
  personal_email        VARCHAR(255) NOT NULL UNIQUE,
  phone                 VARCHAR(15) NOT NULL,
  emergency_contact_name  VARCHAR(150) NULL,
  emergency_contact_phone VARCHAR(15) NULL,

  -- Employment Information
  staff_id              VARCHAR(20) NOT NULL UNIQUE,
  designation           ENUM('Teller','Supervisor','Manager') NOT NULL DEFAULT 'Teller',
  department            ENUM('Teller Operations') NOT NULL DEFAULT 'Teller Operations',
  branch_id             INT UNSIGNED NULL,
  date_of_joining       DATE NOT NULL DEFAULT (CURDATE()),

  -- Security & Access
  password_hash         VARCHAR(255) NOT NULL,
  status                ENUM('active','suspended') NOT NULL DEFAULT 'active',

  -- System Records (auto)
  created_by            VARCHAR(20) NULL,
  created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login_at         TIMESTAMP NULL,
  last_login_ip         VARCHAR(45) NULL,

  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES admin_users(admin_id) ON DELETE SET NULL,
  INDEX idx_staff_id (staff_id),
  INDEX idx_status (status),
  INDEX idx_branch_id (branch_id),
  INDEX idx_personal_email (personal_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 3: Create staff_documents table

CREATE TABLE staff_documents (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id        VARCHAR(20) NOT NULL,
  id_type         ENUM('Aadhaar','PAN','Passport') NOT NULL,
  id_number       VARCHAR(100) NOT NULL,
  front_image     VARCHAR(500) NULL,
  back_image      VARCHAR(500) NULL,
  uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
  INDEX idx_staff_id (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 4: Create staff_address table

CREATE TABLE staff_address (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id        VARCHAR(20) NOT NULL UNIQUE,
  street          VARCHAR(255) NULL,
  city            VARCHAR(100) NULL,
  state           VARCHAR(100) NULL,
  pincode         CHAR(6) NULL,
  country         VARCHAR(50) DEFAULT 'India',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 5: Create staff_shifts table

CREATE TABLE staff_shifts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id        VARCHAR(20) NOT NULL,
    shift_date      DATE NOT NULL,
    clock_in        TIMESTAMP NULL DEFAULT NULL,
    clock_out       TIMESTAMP NULL DEFAULT NULL,
    status          ENUM('active', 'completed', 'absent') NOT NULL DEFAULT 'active',
    notes           VARCHAR(255) NULL DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    INDEX idx_staff_id (staff_id),
    INDEX idx_shift_date (shift_date),
    UNIQUE KEY uniq_staff_date (staff_id, shift_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
