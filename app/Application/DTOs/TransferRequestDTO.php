<?php

namespace App\Application\DTOs;

class TransferRequestDTO
{
    public int $userId;
    public string $toAccountNo;
    public float $amount;
    public string $description;
    public string $txnPin;

    public function __construct(int $userId, array $data)
    {
        $this->userId = $userId;
        $this->toAccountNo = $data['to_account_no'] ?? '';
        $this->amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $this->description = $data['description'] ?? '';
        $this->txnPin = $data['txn_pin'] ?? '';
    }
}
