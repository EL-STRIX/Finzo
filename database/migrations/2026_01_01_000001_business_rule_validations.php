<?php

class BusinessRuleValidations
{
    public function up(PDO $db): void
    {
        // Add Business Validation Triggers

        // 1. Validate Account Creation (Customer must be active)
        $db->exec("
            CREATE TRIGGER trg_validate_account_creation
            BEFORE INSERT ON accounts
            FOR EACH ROW
            BEGIN
                DECLARE v_user_status VARCHAR(20);
                SELECT status INTO v_user_status FROM users WHERE customer_id = NEW.user_id;
                
                IF v_user_status != 'active' AND NEW.status != 'pending' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot create account: Customer KYC is not active.';
                END IF;
                
                IF NEW.balance < (SELECT setting_value FROM system_settings WHERE setting_key = 'min_initial_deposit' LIMIT 1) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot create account: Initial deposit does not meet minimum requirements.';
                END IF;
            END;
        ");

        // 2. [REMOVED] Validate Transactions (Status, Limits, Duplicates)
        // This logic has been successfully migrated to the PHP Service Layer (UserService & StaffService)
        // to conform to Clean Architecture.

        
        // 3. Ensure Ledger Lines always balance before marking a journal as posted
        $db->exec("
            CREATE TRIGGER trg_validate_journal_balancing
            BEFORE UPDATE ON journal_entries
            FOR EACH ROW
            BEGIN
                DECLARE v_total_debits DECIMAL(15,2);
                DECLARE v_total_credits DECIMAL(15,2);
                
                IF NEW.status = 'posted' AND OLD.status != 'posted' THEN
                    SELECT COALESCE(SUM(debit_amount), 0), COALESCE(SUM(credit_amount), 0)
                    INTO v_total_debits, v_total_credits
                    FROM journal_lines
                    WHERE journal_entry_id = NEW.id;
                    
                    IF v_total_debits != v_total_credits THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot post journal entry: Debits do not equal credits.';
                    END IF;
                END IF;
            END;
        ");
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TRIGGER IF EXISTS trg_validate_account_creation");
        $db->exec("DROP TRIGGER IF EXISTS trg_validate_journal_balancing");
    }
}
