<?php
namespace Tests\Integration\ApiFlows;

use Tests\TestCase;
use Tests\Factories\AdminFactory;
use App\Infrastructure\Repositories\AdminStaffBranchRepository;
use App\Application\Services\AdminStaffBranchService;

class BranchManagementTest extends TestCase {

    private AdminStaffBranchRepository $repo;
    private AdminStaffBranchService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->repo = new AdminStaffBranchRepository($this->db);
        $this->service = new AdminStaffBranchService($this->repo);
    }

    public function testBranchCreationAndRetrieval() {
        $adminPw = 'Admin123!';
        $adminId = AdminFactory::createAdmin(['password' => $adminPw]);

        $branchName = 'Test Hub ' . uniqid();
        $state = 'Maharashtra';
        $data = [
            'branch_name' => $branchName,
            'state'       => $state,
            'city'        => 'Mumbai',
            'address'     => '100 Nariman Point',
            'description' => 'Test financial center'
        ];

        $result = $this->service->createBranch($data, null, $adminId, $adminPw);
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['branch_id']);
        $this->assertStringStartsWith('FNZ-', $result['branch_code']);

        $branchId = (int)$result['branch_id'];

        $fetchRes = $this->service->getBranchById($branchId);
        $this->assertNotNull($fetchRes);
        $this->assertEquals($branchName, $fetchRes['branch']['name']);

        $updateRes = $this->service->updateBranch($branchId, [
            'branch_name' => $branchName . ' Updated',
            'city'        => 'Pune'
        ], null, $adminId, null);
        $this->assertTrue($updateRes['success']);

        // Branch code updates must be persisted (regression guard: repo allowed-columns)
        $newCode = 'FNZ-TS-' . strtoupper(substr(uniqid(), -4));
        $codeRes = $this->service->updateBranch($branchId, ['branch_code' => $newCode], null, $adminId, null);
        $this->assertTrue($codeRes['success']);
        $refetched = $this->service->getBranchById($branchId);
        $this->assertEquals($newCode, $refetched['branch']['branch_code']);
        $this->assertEquals('Pune', $refetched['branch']['city']);

        // Explicitly clearing city/address/description must remove stored [Location: ...] meta
        $clearRes = $this->service->updateBranch($branchId, ['city' => '', 'address' => '', 'description' => ''], null, $adminId, null);
        $this->assertTrue($clearRes['success']);
        $cleared = $this->service->getBranchById($branchId);
        $this->assertEquals('', $cleared['branch']['city']);
        $this->assertEquals('', $cleared['branch']['address']);

        $listRes = $this->service->getBranchesList($branchName, 'all', 'all');
        $this->assertTrue($listRes['success']);
        $this->assertNotEmpty($listRes['branches']);

        $delRes = $this->service->deleteBranch($branchId, $adminId, $adminPw, $branchName . ' Updated', null);
        $this->assertTrue($delRes['success']);
    }

    public function testSystemBranchCannotBeDeleted() {
        $adminPw = 'Admin123!';
        $adminId = AdminFactory::createAdmin(['password' => $adminPw]);

        $stmt = $this->db->query("SELECT id, name FROM branches WHERE type = 'system' LIMIT 1");
        $sysBranch = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$sysBranch) {
            $this->db->prepare("INSERT INTO branches (name, branch_code, state, type, status, created_at) VALUES ('Barasat', 'FNZ-WB-001', 'West Bengal', 'system', 'active', NOW())")->execute();
            $sysBranchId = (int)$this->db->lastInsertId();
            $sysBranchName = 'Barasat';
        } else {
            $sysBranchId = (int)$sysBranch['id'];
            $sysBranchName = $sysBranch['name'];
        }

        $this->expectException(\App\Shared\Core\Exceptions\ApiException::class);
        $this->expectExceptionMessage('System branches cannot be deleted');
        $this->service->deleteBranch($sysBranchId, $adminId, $adminPw, $sysBranchName, null);
    }
}
