<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Domain\Models\BaseModel;
use App\Domain\Models\User;
use App\Domain\Models\Account;

class DummyModel extends BaseModel {
    public function getDummy() {
        return $this->attributes['dummy'] ?? null;
    }
}

class ModelTest extends TestCase {
    
    public function testBaseModelInitialization() {
        $model = new DummyModel();
        $this->assertEmpty($model->toArray());
    }

    public function testBaseModelWithAttributes() {
        $model = new DummyModel(['dummy' => 'value']);
        $this->assertEquals('value', $model->getDummy());
        $this->assertEquals('value', $model->toArray()['dummy']);
    }
    
    public function testBaseModelJsonSerialization() {
        $model = new DummyModel(['dummy' => 'value']);
        $json = json_encode($model);
        $this->assertEquals('{"dummy":"value"}', $json);
    }
    
    public function testUserAccessors() {
        $data = [
            'id' => 1,
            'customer_id' => 'CUST123',
            'full_name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '1234567890',
            'status' => 'active',
            'created_at' => '2023-01-01 00:00:00'
        ];
        
        $user = new User($data);
        
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('CUST123', $user->getCustomerId());
        $this->assertEquals('John Doe', $user->getFullName());
        $this->assertEquals('john@test.com', $user->getEmail());
        $this->assertEquals('1234567890', $user->getPhone());
        $this->assertEquals('active', $user->getStatus());
        $this->assertEquals('2023-01-01 00:00:00', $user->getCreatedAt());
    }

    public function testAccountAccessors() {
        $data = [
            'id' => 2,
            'user_id' => 'CUST123',
            'account_no' => 'ACC123',
            'account_type' => 'savings',
            'balance' => 500.50,
            'status' => 'active',
            'created_at' => '2023-01-01 00:00:00'
        ];
        
        $account = new Account($data);
        
        $this->assertEquals(2, $account->getId());
        $this->assertEquals('CUST123', $account->getUserId());
        $this->assertEquals('ACC123', $account->getAccountNo());
        $this->assertEquals('savings', $account->getAccountType());
        $this->assertEquals(500.50, $account->getBalance());
        $this->assertEquals('active', $account->getStatus());
        $this->assertEquals('2023-01-01 00:00:00', $account->getCreatedAt());
    }
}
