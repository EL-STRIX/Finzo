<?php

class FixDatabaseConstraints
{
    public function up(PDO $db): void
    {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

        // 1. Add missing UUID to admin_users
        $db->exec("
            ALTER TABLE admin_users
            ADD COLUMN uuid VARCHAR(36) UNIQUE DEFAULT (UUID()) AFTER id;
        ");

        // 2. Fix Financial Constraints (Change ON DELETE CASCADE to ON DELETE RESTRICT)
        // Beneficiaries
        $db->exec("
            ALTER TABLE beneficiaries 
            DROP FOREIGN KEY fk_beneficiary_user,
            ADD CONSTRAINT fk_beneficiary_user FOREIGN KEY (user_id) REFERENCES users(customer_id) ON DELETE RESTRICT;
        ");

        // Loan Applications
        $db->exec("
            ALTER TABLE loan_applications 
            DROP FOREIGN KEY fk_loan_app_user,
            ADD CONSTRAINT fk_loan_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
        ");

        // Loans
        $db->exec("
            ALTER TABLE loans 
            DROP FOREIGN KEY fk_loan_user,
            ADD CONSTRAINT fk_loan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
        ");

        // Loan Schedules
        $db->exec("
            ALTER TABLE loan_schedules 
            DROP FOREIGN KEY fk_schedule_loan,
            ADD CONSTRAINT fk_schedule_loan FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE RESTRICT;
        ");

        // Loan Repayments
        $db->exec("
            ALTER TABLE loan_repayments 
            DROP FOREIGN KEY fk_repayment_loan,
            ADD CONSTRAINT fk_repayment_loan FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE RESTRICT;
        ");

        // Card Requests
        $db->exec("
            ALTER TABLE card_requests 
            DROP FOREIGN KEY fk_card_request_user,
            ADD CONSTRAINT fk_card_request_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
        ");

        // Cards
        $db->exec("
            ALTER TABLE cards 
            DROP FOREIGN KEY fk_card_user,
            ADD CONSTRAINT fk_card_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
        ");

        // Card Transactions
        $db->exec("
            ALTER TABLE card_transactions 
            DROP FOREIGN KEY fk_card_txn_card,
            ADD CONSTRAINT fk_card_txn_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE RESTRICT;
        ");

        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down(PDO $db): void
    {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

        // 1. Remove UUID from admin_users
        $db->exec("
            ALTER TABLE admin_users
            DROP COLUMN uuid;
        ");

        // 2. Revert Financial Constraints back to ON DELETE CASCADE
        // Beneficiaries
        $db->exec("
            ALTER TABLE beneficiaries 
            DROP FOREIGN KEY fk_beneficiary_user,
            ADD CONSTRAINT fk_beneficiary_user FOREIGN KEY (user_id) REFERENCES users(customer_id) ON DELETE CASCADE;
        ");

        // Loan Applications
        $db->exec("
            ALTER TABLE loan_applications 
            DROP FOREIGN KEY fk_loan_app_user,
            ADD CONSTRAINT fk_loan_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
        ");

        // Loans
        $db->exec("
            ALTER TABLE loans 
            DROP FOREIGN KEY fk_loan_user,
            ADD CONSTRAINT fk_loan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
        ");

        // Loan Schedules
        $db->exec("
            ALTER TABLE loan_schedules 
            DROP FOREIGN KEY fk_schedule_loan,
            ADD CONSTRAINT fk_schedule_loan FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE;
        ");

        // Loan Repayments
        $db->exec("
            ALTER TABLE loan_repayments 
            DROP FOREIGN KEY fk_repayment_loan,
            ADD CONSTRAINT fk_repayment_loan FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE;
        ");

        // Card Requests
        $db->exec("
            ALTER TABLE card_requests 
            DROP FOREIGN KEY fk_card_request_user,
            ADD CONSTRAINT fk_card_request_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
        ");

        // Cards
        $db->exec("
            ALTER TABLE cards 
            DROP FOREIGN KEY fk_card_user,
            ADD CONSTRAINT fk_card_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
        ");

        // Card Transactions
        $db->exec("
            ALTER TABLE card_transactions 
            DROP FOREIGN KEY fk_card_txn_card,
            ADD CONSTRAINT fk_card_txn_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE;
        ");

        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
