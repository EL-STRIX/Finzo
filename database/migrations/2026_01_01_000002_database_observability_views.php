<?php

class DatabaseObservabilityViews
{
    public function up(PDO $db): void
    {
        // 1. Storage Monitoring View
        $db->exec("
            CREATE OR REPLACE VIEW vw_monitoring_storage AS
            SELECT 
                table_name AS 'table',
                table_rows AS 'rows',
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'size_mb',
                ROUND(data_free / 1024 / 1024, 2) AS 'free_mb'
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC;
        ");

        // 2. Ledger Health View (Identifies broken/unbalanced journals instantly)
        $db->exec("
            CREATE OR REPLACE VIEW vw_monitoring_ledger_health AS
            SELECT 
                je.id AS journal_id,
                je.journal_number,
                je.status,
                COALESCE(SUM(jl.debit_amount), 0) AS total_debit,
                COALESCE(SUM(jl.credit_amount), 0) AS total_credit,
                (COALESCE(SUM(jl.debit_amount), 0) - COALESCE(SUM(jl.credit_amount), 0)) AS variance
            FROM journal_entries je
            LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
            GROUP BY je.id
            HAVING variance != 0 AND je.status = 'posted';
        ");

        // 3. Operational Metrics View (Daily volume)
        $db->exec("
            CREATE OR REPLACE VIEW vw_monitoring_operational_metrics AS
            SELECT 
                DATE(created_at) as activity_date,
                COUNT(id) as total_transactions,
                COALESCE(SUM(amount), 0) as total_volume,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_txns,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_txns
            FROM transactions
            GROUP BY DATE(created_at)
            ORDER BY activity_date DESC;
        ");

        // 4. Alerting Thresholds Table (Configuration for alerts)
        $db->exec("
            CREATE TABLE IF NOT EXISTS monitoring_alerts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                alert_name VARCHAR(100) NOT NULL UNIQUE,
                threshold_value VARCHAR(255) NOT NULL,
                severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
                is_active TINYINT(1) DEFAULT 1,
                last_triggered_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed basic alert thresholds
        $db->exec("
            INSERT IGNORE INTO monitoring_alerts (alert_name, threshold_value, severity) VALUES 
            ('max_storage_mb', '10240', 'warning'),
            ('max_query_time_seconds', '5', 'warning'),
            ('max_failed_txns_per_hour', '50', 'critical'),
            ('unbalanced_journals_allowed', '0', 'critical');
        ");
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP VIEW IF EXISTS vw_monitoring_storage");
        $db->exec("DROP VIEW IF EXISTS vw_monitoring_ledger_health");
        $db->exec("DROP VIEW IF EXISTS vw_monitoring_operational_metrics");
        $db->exec("DROP TABLE IF EXISTS monitoring_alerts");
    }
}
