SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE admin_lock_log DROP FOREIGN KEY admin_lock_log_ibfk_1;

CREATE TABLE admin_lock_log_new (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    admin_id varchar(20) NOT NULL,
    event_type enum('locked_password','locked_answer','unlocked_self','unlocked_by_super_admin','answer_attempt_failed','password_reset_completed') NOT NULL,
    triggered_by varchar(20) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    details text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY idx_admin_id (admin_id),
    KEY idx_event_type (event_type),
    KEY idx_created_at (created_at),
    CONSTRAINT admin_lock_log_ibfk_1 FOREIGN KEY (admin_id) REFERENCES admin_users (admin_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_lock_log_new (id, admin_id, event_type, triggered_by, ip_address, details, created_at)
SELECT a.id, u1.admin_id, a.event_type, u2.admin_id, a.ip_address, a.details, a.created_at
FROM admin_lock_log a
LEFT JOIN admin_users u1 ON a.admin_id = u1.id OR a.admin_id = u1.admin_id
LEFT JOIN admin_users u2 ON a.triggered_by = u2.id OR a.triggered_by = u2.admin_id;

RENAME TABLE admin_lock_log TO admin_lock_log_old, admin_lock_log_new TO admin_lock_log;

SET FOREIGN_KEY_CHECKS = 1;
