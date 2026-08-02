<?php

namespace App\Application\Services;

use App\Domain\Contracts\Services\LoanServiceInterface;
use App\Domain\Contracts\Repositories\LoanRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\AdminOperationsRepositoryInterface;
use App\Domain\Contracts\Security\SecurityServiceInterface;
use App\Presentation\Validators\LoanValidator;
use App\Shared\Core\Database;
use App\Shared\Core\Exceptions\BusinessRuleException;
use App\Shared\Core\Exceptions\ResourceNotFoundException;
use Exception;

class LoanService implements LoanServiceInterface
{
    private LoanRepositoryInterface $repository;
    private UserRepositoryInterface $userRepository;
    private AdminOperationsRepositoryInterface $adminOpsRepository;
    private SecurityServiceInterface $security;
    private LoanValidator $validator;

    public function __construct(
        LoanRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        AdminOperationsRepositoryInterface $adminOpsRepository,
        SecurityServiceInterface $security
    ) {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->adminOpsRepository = $adminOpsRepository;
        $this->security = $security;
        $this->validator = new LoanValidator();
    }

    public function createLoanProduct(array $data): array
    {
        $sanitized = $this->validator->validateProductCreation($data);

        $productId = $this->repository->createLoanProduct(
            $sanitized['name'],
            $sanitized['description'] ?? '',
            (float)$sanitized['interest_rate'],
            (float)$sanitized['min_amount'],
            (float)$sanitized['max_amount'],
            (int)$sanitized['min_tenure_months'],
            (int)$sanitized['max_tenure_months'],
            'active'
        );

        return ['success' => true, 'message' => 'Loan product created successfully', 'product_id' => $productId];
    }

    public function getLoanProducts(string $status = 'all'): array
    {
        return $this->repository->getLoanProducts($status);
    }

    public function applyForLoan(int $userId, array $data): array
    {
        $productId = (int)($data['loan_product_id'] ?? 0);
        $product = $this->repository->getLoanProductById($productId);

        if (!$product || $product['status'] !== 'active') {
            throw new ResourceNotFoundException('Loan product not found or inactive');
        }

        $sanitized = $this->validator->validateLoanApplication($data, $product);

        $applicationId = $this->repository->createLoanApplication(
            $userId,
            $productId,
            (float)$sanitized['amount'],
            (int)$sanitized['tenure_months'],
            $sanitized['purpose']
        );

        $this->adminOpsRepository->addNotification($userId, 'system', 'Loan Application Submitted', 'Your loan application for ₹' . number_format($sanitized['amount'], 2) . ' is pending review.');

        return ['success' => true, 'message' => 'Loan application submitted successfully', 'application_id' => $applicationId];
    }

    public function getLoanApplications(array $filters = []): array
    {
        return $this->repository->getLoanApplications($filters);
    }

    public function approveApplication(int $applicationId, int $adminId, string $notes = ''): array
    {
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $application = $this->repository->getLoanApplicationForUpdate($applicationId);
            if (!$application) {
                throw new ResourceNotFoundException('Application not found');
            }
            if ($application['status'] !== 'pending') {
                throw new BusinessRuleException('Only pending applications can be approved');
            }

            $userId = $application['user_id'];
            $accounts = $this->userRepository->getUserAccounts($application['customer_id'] ?? ''); 
            $savingsAccount = null;
            // The getLoanApplicationById query joins with `users u` but might not fetch customer_id. 
            // Wait, we need the actual user object to get customer_id.
            $user = $this->userRepository->getUserProfile($userId);
            if (!$user) {
                throw new ResourceNotFoundException('User not found');
            }
            
            $accounts = $this->userRepository->getUserAccounts($user['customer_id']);
            foreach ($accounts as $acc) {
                if ($acc['account_type'] === 'savings' && $acc['status'] === 'active') {
                    $savingsAccount = $acc;
                    break;
                }
            }

            if (!$savingsAccount) {
                throw new BusinessRuleException('User does not have an active savings account for disbursement');
            }

            $principal = (float)$application['amount'];
            $product = $this->repository->getLoanProductById($application['loan_product_id']);
            $interestRate = (float)$product['interest_rate'];
            
            $calcService = new LoanCalculationService();
            $schedule = $calcService->generateAmortizationSchedule($principal, $interestRate, $application['tenure_months'], date('Y-m-d'));
            
            // Calculate total payable from the schedule
            $totalPayable = 0;
            foreach ($schedule as $emi) {
                $totalPayable += $emi['emi_amount'];
            }
            
            $loanIdRef = 'LN' . strtoupper(bin2hex(random_bytes(6)));

            // 1. Mark Application Approved
            $this->repository->updateApplicationStatus($applicationId, 'approved', $notes);

            // 2. Create Loan
            $loanId = $this->repository->createActiveLoan(
                $loanIdRef,
                $userId,
                $application['loan_product_id'],
                $applicationId,
                $savingsAccount['id'],
                $principal,
                $interestRate,
                $application['tenure_months'],
                $totalPayable
            );

            // 2.5 Save Schedule
            foreach ($schedule as $emi) {
                $this->repository->insertLoanSchedule(
                    $loanId,
                    $emi['installment_no'],
                    $emi['due_date'],
                    $emi['emi_amount'],
                    $emi['principal_component'],
                    $emi['interest_component'],
                    $emi['outstanding_balance']
                );
            }

            // 3. Disburse Funds
            $lockedAcc = $this->adminOpsRepository->getAccountForUpdate($savingsAccount['id']);
            $newBalance = (float)$lockedAcc['balance'] + $principal;
            
            $txnId = 'TXN' . strtoupper(bin2hex(random_bytes(8)));
            $this->adminOpsRepository->addTransaction(
                $txnId,
                null, // System generated
                $lockedAcc['id'],
                'deposit', // Technically a loan disbursement, but 'deposit' works to increase balance. Actually enum only allows 'deposit', 'withdrawal', 'transfer', 'interest_credit'
                $principal,
                $newBalance,
                'Loan Disbursement',
                "Ref: {$loanIdRef}",
                'completed'
            );
            $this->adminOpsRepository->updateAccountBalance($newBalance, $lockedAcc['id']);

            // 4. Notifications & Audit
            $this->adminOpsRepository->addNotification($userId, 'system', 'Loan Approved & Disbursed', "Your loan of ₹" . number_format($principal, 2) . " has been approved and credited to your savings account.");
            $this->security->writeAuditLog('loan_approved', $loanId, 'loans', $adminId, 'admin', "Approved loan application {$applicationId} for user {$userId}");

            $pdo->commit();
            return ['success' => true, 'message' => 'Loan approved and disbursed successfully'];
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($e instanceof BusinessRuleException || $e instanceof ResourceNotFoundException) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Loan approval error: ', ['context' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred during loan approval'];
        }
    }

    public function rejectApplication(int $applicationId, int $adminId, string $notes = ''): array
    {
        $application = $this->repository->getLoanApplicationById($applicationId);
        if (!$application || $application['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Application not found or not pending'];
        }

        $this->repository->updateApplicationStatus($applicationId, 'rejected', $notes);
        $this->adminOpsRepository->addNotification($application['user_id'], 'system', 'Loan Application Rejected', 'Your recent loan application was rejected.');
        
        return ['success' => true, 'message' => 'Loan application rejected successfully'];
    }

    public function getLoanSchedules(int $loanId): array
    {
        return $this->repository->getLoanSchedules($loanId);
    }

    public function processRepayment(int $userId, int $loanId, int $scheduleId): array
    {
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $loan = $this->repository->getLoanForUpdate($loanId);
            if (!$loan || (int)$loan['user_id'] !== $userId || $loan['status'] !== 'active') {
                throw new ResourceNotFoundException('Active loan not found for this user');
            }

            $schedule = $this->repository->getScheduleForUpdate($scheduleId);
            if (!$schedule || (int)$schedule['loan_id'] !== $loanId) {
                throw new ResourceNotFoundException('Schedule not found');
            }
            if ($schedule['status'] === 'paid') {
                throw new BusinessRuleException('This EMI is already paid');
            }

            // Deduct from savings account
            $user = $this->userRepository->getUserProfile($userId);
            $accounts = $this->userRepository->getUserAccounts($user['customer_id']);
            $savingsAccount = null;
            foreach ($accounts as $acc) {
                if ($acc['account_type'] === 'savings' && $acc['status'] === 'active') {
                    $savingsAccount = $acc;
                    break;
                }
            }

            if (!$savingsAccount) {
                throw new BusinessRuleException('No active savings account found to deduct EMI');
            }

            $emiAmount = (float)$schedule['emi_amount'];
            $lockedAcc = $this->adminOpsRepository->getAccountForUpdate($savingsAccount['id']);
            if ((float)$lockedAcc['balance'] < $emiAmount) {
                throw new BusinessRuleException('Insufficient funds in savings account for EMI payment');
            }

            $newBalance = (float)$lockedAcc['balance'] - $emiAmount;
            
            $txnId = 'TXN' . strtoupper(bin2hex(random_bytes(8)));
            $this->adminOpsRepository->addTransaction(
                $txnId,
                $lockedAcc['id'],
                null,
                'withdrawal', // System deduction
                $emiAmount,
                $newBalance,
                'EMI Repayment',
                "Loan: {$loan['loan_id_ref']} | Installment: {$schedule['installment_no']}",
                'completed'
            );
            $this->adminOpsRepository->updateAccountBalance($newBalance, $lockedAcc['id']);

            // Update loan tables
            // Update loan tables
            $this->repository->markSchedulePaid($scheduleId);
            $this->repository->recordRepayment($loanId, $scheduleId, $emiAmount, $txnId);
            $this->repository->updateLoanTotals($loanId, $emiAmount);

            // Check if all schedules are paid to auto-close the loan
            $unpaidPrincipal = $this->repository->getUnpaidPrincipalSum($loanId);
            if ($unpaidPrincipal <= 0) {
                $this->repository->updateLoanStatus($loanId, 'closed');
                $this->adminOpsRepository->addNotification($userId, 'system', 'Loan Closed', "Congratulations! Your loan {$loan['loan_id_ref']} has been successfully closed.");
            }

            // Audit & Notification
            $this->adminOpsRepository->addNotification($userId, 'transaction', 'EMI Paid', "EMI of ₹" . number_format($emiAmount, 2) . " has been successfully deducted for Loan {$loan['loan_id_ref']}.");
            
            $pdo->commit();
            return ['success' => true, 'message' => 'EMI repayment processed successfully'];
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($e instanceof BusinessRuleException || $e instanceof ResourceNotFoundException) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Loan repayment error: ', ['context' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred during repayment'];
        }
    }

    public function getLoanRepayments(int $loanId): array
    {
        return $this->repository->getLoanRepayments($loanId);
    }

    public function forecloseLoan(int $loanId, int $adminId, float $penaltyRate): array
    {
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $loan = $this->repository->getLoanForUpdate($loanId);
            if (!$loan || $loan['status'] !== 'active') {
                throw new ResourceNotFoundException('Active loan not found');
            }

            $unpaidPrincipal = $this->repository->getUnpaidPrincipalSum($loanId);
            if ($unpaidPrincipal <= 0) {
                throw new BusinessRuleException('No unpaid principal left to foreclose');
            }

            // Calculate penalty
            $penaltyAmount = round(($unpaidPrincipal * $penaltyRate) / 100, 2);
            $totalForeclosureAmount = $unpaidPrincipal + $penaltyAmount;

            // Deduct from savings account
            $user = $this->userRepository->getUserProfile($loan['user_id']);
            $accounts = $this->userRepository->getUserAccounts($user['customer_id']);
            $savingsAccount = null;
            foreach ($accounts as $acc) {
                if ($acc['account_type'] === 'savings' && $acc['status'] === 'active') {
                    $savingsAccount = $acc;
                    break;
                }
            }

            if (!$savingsAccount) {
                throw new BusinessRuleException('No active savings account found to deduct foreclosure amount');
            }

            $lockedAcc = $this->adminOpsRepository->getAccountForUpdate($savingsAccount['id']);
            if ((float)$lockedAcc['balance'] < $totalForeclosureAmount) {
                throw new BusinessRuleException('Insufficient funds in user savings account for foreclosure settlement');
            }

            $newBalance = (float)$lockedAcc['balance'] - $totalForeclosureAmount;
            
            $txnId = 'TXN' . strtoupper(bin2hex(random_bytes(8)));
            $this->adminOpsRepository->addTransaction(
                $txnId,
                $lockedAcc['id'],
                null,
                'withdrawal',
                $totalForeclosureAmount,
                $newBalance,
                'Loan Foreclosure Settlement',
                "Loan: {$loan['loan_id_ref']} | Principal: {$unpaidPrincipal} | Penalty: {$penaltyAmount}",
                'completed'
            );
            $this->adminOpsRepository->updateAccountBalance($newBalance, $lockedAcc['id']);

            // Clean up schedules and mark closed
            $this->repository->cancelPendingSchedules($loanId);
            $this->repository->recordRepayment($loanId, 0, $totalForeclosureAmount, $txnId); // schedule_id is NULL for advance payment
            $this->repository->updateLoanTotals($loanId, $totalForeclosureAmount);
            $this->repository->updateLoanStatus($loanId, 'closed');

            // Audit & Notification
            $this->adminOpsRepository->logAudit($adminId, \App\Domain\Enums\AuditActionEnum::USER_STATUS_UPDATE->value, "Foreclosed Loan {$loan['loan_id_ref']} with penalty {$penaltyRate}%");
            $this->adminOpsRepository->addNotification($loan['user_id'], 'system', 'Loan Foreclosed', "Your loan {$loan['loan_id_ref']} has been fully settled and closed.");
            
            $pdo->commit();
            return ['success' => true, 'message' => 'Loan foreclosed successfully'];
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($e instanceof BusinessRuleException || $e instanceof ResourceNotFoundException) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            \App\Shared\Core\Logging\Logger::getInstance()->error('Loan foreclosure error: ', ['context' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred during loan foreclosure'];
        }
    }
}
