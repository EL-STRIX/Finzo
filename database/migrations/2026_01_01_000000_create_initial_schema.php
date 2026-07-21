<?php

class CreateInitialSchema
{
    public function up(PDO $db): void
    {
        $sql = file_get_contents(__DIR__ . '/../schema/finzo_database.sql');
        
        // Remove DELIMITER statements as PDO doesn't support them natively
        // Since PDO execute() doesn't handle DELIMITER natively, we need to split triggers manually or just load them if the DB permits it.
        // The safest enterprise way to load a massive SQL dump with triggers in PHP is to split by DELIMITER manually.
        
        $statements = explode('DELIMITER //', $sql);
        
        // The first part is standard SQL
        $db->exec($statements[0]);
        
        // The rest are trigger blocks
        for ($i = 1; $i < count($statements); $i++) {
            $parts = explode('DELIMITER ;', $statements[$i]);
            $triggerBlock = trim($parts[0]);
            if (!empty($triggerBlock)) {
                // PDO doesn't understand // as a terminator, it only understands standard SQL statements or single complete blocks
                $triggerBlock = str_replace('//', '', $triggerBlock);
                $db->exec($triggerBlock);
            }
            if (isset($parts[1])) {
                $rem = trim($parts[1]);
                if (!empty($rem)) {
                    $db->exec($rem);
                }
            }
        }
    }

    public function down(PDO $db): void
    {
        // For the initial schema, down is dropping all tables and views
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            if ($table !== 'migrations') {
                $db->exec("DROP TABLE IF EXISTS {$table}");
            }
        }
        $views = $db->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($views as $view) {
            $db->exec("DROP VIEW IF EXISTS {$view}");
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
