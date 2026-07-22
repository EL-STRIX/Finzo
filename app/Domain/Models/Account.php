<?php

namespace App\Domain\Models;

class Account extends BaseModel
{
    public function getId() { return $this->attributes['id'] ?? null; }
    public function getUserId() { return $this->attributes['user_id'] ?? null; }
    public function getAccountNo() { return $this->attributes['account_no'] ?? null; }
    public function getAccountType() { return $this->attributes['account_type'] ?? null; }
    public function getBalance() { return $this->attributes['balance'] ?? null; }
    public function getStatus() { return $this->attributes['status'] ?? null; }
    public function getCreatedAt() { return $this->attributes['created_at'] ?? null; }
}
