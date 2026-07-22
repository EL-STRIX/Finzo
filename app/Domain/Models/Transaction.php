<?php

namespace App\Domain\Models;

class Transaction extends BaseModel
{
    public function getTxnId() { return $this->attributes['txn_id'] ?? null; }
    public function getType() { return $this->attributes['type'] ?? null; }
    public function getAmount() { return $this->attributes['amount'] ?? null; }
    public function getBalanceAfter() { return $this->attributes['balance_after'] ?? null; }
    public function getStatus() { return $this->attributes['status'] ?? null; }
    public function getCreatedAt() { return $this->attributes['created_at'] ?? null; }
}
