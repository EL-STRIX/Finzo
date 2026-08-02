<?php

namespace App\Infrastructure\Repositories;

use PDO;

class LoanRepository extends BaseRepository implements \App\Domain\Contracts\Repositories\LoanRepositoryInterface
{
    public function createLoanProduct(string $name, string $description, float $interestRate, float $minAmount, float $maxAmount, int $minTenure, int $maxTenure, string $status): int
    {
        $this->execute("
            INSERT INTO loan_products (name, description, interest_rate, min_amount, max_amount, min_tenure_months, max_tenure_months, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [$name, $description, $interestRate, $minAmount, $maxAmount, $minTenure, $maxTenure, $status]);
        
        return (int)$this->db->lastInsertId();
    }

    public function getLoanProducts(string $status = 'all'): array
    {
        $sql = "SELECT * FROM loan_products";
        $params = [];
        if ($status !== 'all') {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getLoanProductById(int $productId)
    {
        return $this->fetchOne("SELECT * FROM loan_products WHERE id = ?", [$productId]);
    }

    public function createLoanApplication(int $userId, int $productId, float $amount, int $tenureMonths, string $purpose): int
    {
        $this->execute("
            INSERT INTO loan_applications (user_id, loan_product_id, amount, tenure_months, purpose, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ", [$userId, $productId, $amount, $tenureMonths, $purpose]);
        
        return (int)$this->db->lastInsertId();
    }

    public function getLoanApplications(array $filters = []): array
    {
        $sql = "
            SELECT la.*, p.name as product_name, p.interest_rate, u.full_name, u.email
            FROM loan_applications la
            JOIN loan_products p ON la.loan_product_id = p.id
            JOIN users u ON la.user_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND la.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND la.user_id = ?";
            $params[] = $filters['user_id'];
        }
        $sql .= " ORDER BY la.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getLoanApplicationById(int $applicationId)
    {
        return $this->fetchOne("
            SELECT la.*, p.name as product_name, p.interest_rate, u.full_name, u.customer_id
            FROM loan_applications la
            JOIN loan_products p ON la.loan_product_id = p.id
            JOIN users u ON la.user_id = u.id
            WHERE la.id = ?
        ", [$applicationId]);
    }

    public function getLoanApplicationForUpdate(int $applicationId)
    {
        return $this->fetchOne("SELECT * FROM loan_applications WHERE id = ? FOR UPDATE", [$applicationId]);
    }

    public function updateApplicationStatus(int $applicationId, string $status, string $adminNotes = ''): void
    {
        $this->execute("UPDATE loan_applications SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?", [$status, $adminNotes, $applicationId]);
    }

    public function createActiveLoan(string $loanIdRef, int $userId, int $productId, int $applicationId, int $accountId, float $principal, float $interestRate, int $tenureMonths, float $totalPayable): int
    {
        $this->execute("
            INSERT INTO loans (loan_id_ref, user_id, loan_product_id, application_id, account_id, principal_amount, interest_rate, tenure_months, total_amount_payable, total_amount_paid, status, disbursed_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 'active', NOW(), NOW())
        ", [$loanIdRef, $userId, $productId, $applicationId, $accountId, $principal, $interestRate, $tenureMonths, $totalPayable]);
        
        return (int)$this->db->lastInsertId();
    }

    public function insertLoanSchedule(int $loanId, int $installmentNo, string $dueDate, float $emiAmount, float $principalComponent, float $interestComponent, float $outstandingBalance): void
    {
        $this->execute("
            INSERT INTO loan_schedules (loan_id, installment_no, due_date, emi_amount, principal_component, interest_component, outstanding_balance, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ", [$loanId, $installmentNo, $dueDate, $emiAmount, $principalComponent, $interestComponent, $outstandingBalance]);
    }

    public function getLoanSchedules(int $loanId): array
    {
        return $this->fetchAll("SELECT * FROM loan_schedules WHERE loan_id = ? ORDER BY installment_no ASC", [$loanId]);
    }

    public function getScheduleForUpdate(int $scheduleId)
    {
        return $this->fetchOne("SELECT * FROM loan_schedules WHERE id = ? FOR UPDATE", [$scheduleId]);
    }

    public function getLoanForUpdate(int $loanId)
    {
        return $this->fetchOne("SELECT * FROM loans WHERE id = ? FOR UPDATE", [$loanId]);
    }

    public function markSchedulePaid(int $scheduleId): void
    {
        $this->execute("UPDATE loan_schedules SET status = 'paid', updated_at = NOW() WHERE id = ?", [$scheduleId]);
    }

    public function recordRepayment(int $loanId, int $scheduleId, float $amount, string $transactionId): void
    {
        $this->execute("
            INSERT INTO loan_repayments (loan_id, schedule_id, amount_paid, transaction_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$loanId, $scheduleId, $amount, $transactionId]);
    }

    public function updateLoanTotals(int $loanId, float $amountPaid): void
    {
        $this->execute("UPDATE loans SET total_amount_paid = total_amount_paid + ?, updated_at = NOW() WHERE id = ?", [$amountPaid, $loanId]);
    }

    public function updateLoanStatus(int $loanId, string $status): void
    {
        $this->execute("UPDATE loans SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $loanId]);
    }

    public function getLoanRepayments(int $loanId): array
    {
        return $this->fetchAll("
            SELECT lr.*, ls.installment_no, ls.due_date
            FROM loan_repayments lr
            LEFT JOIN loan_schedules ls ON lr.schedule_id = ls.id
            WHERE lr.loan_id = ?
            ORDER BY lr.created_at DESC
        ", [$loanId]);
    }

    public function getUnpaidPrincipalSum(int $loanId): float
    {
        $result = $this->fetchOne("SELECT SUM(principal_component) as unpaid_principal FROM loan_schedules WHERE loan_id = ? AND status = 'pending'", [$loanId]);
        return (float)($result['unpaid_principal'] ?? 0);
    }

    public function cancelPendingSchedules(int $loanId): void
    {
        // Cancel all pending schedules (status 'closed' isn't in ENUM, so we can just set them to 'paid' with 0 amount, or better, add 'cancelled' to ENUM. But wait, I defined ENUM('pending', 'paid', 'late') for loan_schedules.)
        // Actually, deleting them is safer or just leaving them as pending, but if loan is closed, they are ignored.
        // Or we just update them to 'paid' but they won't have a repayment record, so we know they were foreclosed. 
        // Let's just delete them to cleanly prune the schedule for foreclosure.
        $this->execute("DELETE FROM loan_schedules WHERE loan_id = ? AND status = 'pending'", [$loanId]);
    }
}
