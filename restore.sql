INSERT INTO notifications (id, user_id, type, title, message, is_read, created_at)
SELECT id, 'FNZ-USR-001', type, title, message, is_read, created_at FROM notifications_old;
