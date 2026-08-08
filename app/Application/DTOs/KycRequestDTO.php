<?php

namespace App\Application\DTOs;

class KycRequestDTO
{
    public int $userId;
    public string $idType;
    public string $idNumber;
    public string $address;
    public array $postData;
    public array $filesData;

    public function __construct(int $userId, array $postData, array $filesData)
    {
        $this->userId = $userId;
        $this->idType = $postData['id_type'] ?? '';
        $this->idNumber = $postData['id_number'] ?? '';
        $this->address = $postData['address'] ?? '';
        $this->postData = $postData;
        $this->filesData = $filesData;
    }
}
