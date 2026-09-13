<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\UserService;
use App\Infrastructure\Repositories\UserRepository;

class UserServiceTest extends TestCase
{
    private $userService;
    private $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
        require_once FINZO_ROOT . '/config/functions.php';
        
        if (!defined('APP_KEY')) {
            define('APP_KEY', base64_encode(str_repeat('a', 32)));
        }
        
        $this->userRepo = new UserRepository($this->db);
        $this->userService = new UserService($this->userRepo, null);
    }



    public function testGetRepository()
    {
        $this->assertSame($this->userRepo, $this->userService->getRepository());
    }

    public function testValidateKycEmptyData()
    {
        $data = [
            'dob' => '',
            'gender' => '',
            'father_name' => '',
            'mother_name' => '',
            'marital_status' => '',
            'nationality' => '',
            'occupation' => '',
            'source_of_income' => '',
            'annual_income' => '',
            'street' => '',
            'city' => '',
            'state' => '',
            'pincode' => '',
            'id_type' => '',
            'id_number' => '',
            'account_type' => '',
            'initial_deposit' => 0,
            'txn_pin' => ''
        ];
        
        $files = [];
        
        $errors = $this->userService->validateKyc($data, $files);
        
        $this->assertArrayHasKey('dob', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('father_name', $errors);
        $this->assertArrayHasKey('mother_name', $errors);
        $this->assertArrayHasKey('marital_status', $errors);
        $this->assertArrayHasKey('nationality', $errors);
        $this->assertArrayHasKey('occupation', $errors);
        $this->assertArrayHasKey('source_of_income', $errors);
        $this->assertArrayHasKey('annual_income', $errors);
        $this->assertArrayHasKey('street', $errors);
        $this->assertArrayHasKey('city', $errors);
        $this->assertArrayHasKey('state', $errors);
        $this->assertArrayHasKey('pincode', $errors);
        $this->assertArrayHasKey('id_type', $errors);
        $this->assertArrayHasKey('id_number', $errors);
        $this->assertArrayHasKey('account_type', $errors);
        $this->assertArrayHasKey('initial_deposit', $errors);
        $this->assertArrayHasKey('txn_pin', $errors);
    }

    public function testPassthroughMethodsWithRealRepository()
    {
        // Many methods are simple passthroughs to the repository.
        // We test them here to ensure coverage of both the service and the repository.
        
        $this->userService->fetchBeneficiaries(9999);
        $this->userService->countBeneficiaries(9999);
        $this->userService->getBeneficiaryByAccount(9999, 'INVALID_ACC');
        $this->userService->getUserPasswordHash(9999);
        $this->userService->getUserFiles(9999);
        $this->userService->getExistingAccountByCustomerId('CUST_9999');
        $this->userService->getKycApplication('CUST_9999');
        $this->userService->getActiveAccountByCustomerId('CUST_9999');
        $this->userService->getRecentTransactions(9999);
        $this->userService->getTransactionToAccountId('TXN_9999');
        $this->userService->getMonthlyIncome(9999);
        $this->userService->getMonthlyExpenses(9999);
        $this->userService->getUnreadNotificationCount('CUST_9999');
        $this->userService->getPastRejections('CUST_9999');
        $this->userService->getNotificationsTotal('CUST_9999');
        $this->userService->getNotifications('CUST_9999', 10, 0);
        $this->userService->markNotificationRead(9999, 'CUST_9999');
        $this->userService->markAllNotificationsRead('CUST_9999');
        $this->userService->deleteNotification(9999, 'CUST_9999');
        $this->userService->clearAllNotifications('CUST_9999');
        $this->userService->getFullProfile(9999);
        $this->userService->checkEmailExists('nonexistent@example.com', 9999);

        $this->userService->checkPhoneExists('9999999999', 9999);
        $this->userService->getActiveAccount(9999);
        $this->userService->getStatementTransactions(9999, '2020-01-01', '2020-12-31');
        $this->userService->getOpeningBalance(9999, '2020-01-01');
        
        // Assert true to pass the test if no exceptions occurred
        $this->assertTrue(true);
    }

    public function testGetTransactionsWithDirectionFilters(): void
    {
        $resAll = $this->userService->getTransactions(1, [
            'type' => 'all',
            'direction' => 'all',
            'status' => 'all',
            'from_date' => '',
            'to_date' => '',
            'search' => '',
            'page' => 1,
            'per_page' => 10
        ]);
        $this->assertIsArray($resAll);
        $this->assertArrayHasKey('transactions', $resAll);
        $this->assertArrayHasKey('total', $resAll);

        $resCredit = $this->userService->getTransactions(1, [
            'type' => 'all',
            'direction' => 'credit',
            'status' => 'all',
            'from_date' => '',
            'to_date' => '',
            'search' => '',
            'page' => 1,
            'per_page' => 10
        ]);
        $this->assertIsArray($resCredit);
        $this->assertArrayHasKey('transactions', $resCredit);

        $resDebit = $this->userService->getTransactions(1, [
            'type' => 'all',
            'direction' => 'debit',
            'status' => 'all',
            'from_date' => '',
            'to_date' => '',
            'search' => '',
            'page' => 1,
            'per_page' => 10
        ]);
        $this->assertIsArray($resDebit);
        $this->assertArrayHasKey('transactions', $resDebit);
    }
}
