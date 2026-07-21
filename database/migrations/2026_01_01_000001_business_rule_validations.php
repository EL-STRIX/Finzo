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

        // 2. Validate Transactions (Status, Limits, Duplicates)
        $db->exec("
            CREATE TRIGGER trg_validate_transaction
            BEFORE INSERT ON transactions
            FOR EACH ROW
            BEGIN
                DECLARE v_account_status VARCHAR(20);
                DECLARE v_daily_total DECIMAL(15,2);
                DECLARE v_daily_limit DECIMAL(15,2);
                DECLARE v_recent_duplicate INT;

                -- Prevent duplicates (same amount from same account in last 5 seconds)
                SELECT COUNT(*) INTO v_recent_duplicate 
                FROM transactions 
                WHERE from_account_id = NEW.from_account_id 
                  AND amount = NEW.amount 
                  AND type = NEW.type 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND);
                
                IF v_recent_duplicate > 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Duplicate transaction detected. Please wait before retrying.';
                END IF;

                -- Check frozen/closed states for outgoing accounts
                IF NEW.from_account_id IS NOT NULL THEN
                    SELECT status, daily_limit INTO v_account_status, v_daily_limit FROM accounts WHERE id = NEW.from_account_id;
                    
                    IF v_account_status != 'active' THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot process transaction: Source account is not active.';
                    END IF;
                    
                    -- Check daily limits for transfers
                    IF NEW.type = 'transfer' THEN
                        SELECT COALESCE(SUM(amount), 0) INTO v_daily_total 
                        FROM transactions 
                        WHERE from_account_id = NEW.from_account_id 
                          AND type = 'transfer' 
                          AND status = 'completed'
                          AND DATE(created_at) = CURDATE();
                          
                        IF (v_daily_total + NEW.amount) > v_daily_limit THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transaction exceeds daily transfer limit.';
                        END IF;
                    END IF;
                END IF;
                
                -- Check frozen/closed states for incoming accounts
                IF NEW.to_account_id IS NOT NULL THEN
                    SELECT status INTO v_account_status FROM accounts WHERE id = NEW.to_account_id;
                    IF v_account_status != 'active' THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot process transaction: Destination account is not active.';
                    END IF;
                END IF;
            END;
        ");
        
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
        $db->exec("DROP TRIGGER IF EXISTS trg_validate_transaction");
        $db->exec("DROP TRIGGER IF EXISTS trg_validate_journal_balancing");
    }
}
