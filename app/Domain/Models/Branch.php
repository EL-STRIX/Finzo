<?php

namespace App\Domain\Models;

class Branch extends BaseModel
{
    public function getId() { return $this->attributes['id'] ?? null; }
    public function getName() { return $this->attributes['name'] ?? null; }
    public function getBranchCode() { return $this->attributes['branch_code'] ?? null; }
    public function getState() { return $this->attributes['state'] ?? null; }
    public function getCity() { return $this->attributes['city'] ?? null; }
    public function getType() { return $this->attributes['type'] ?? null; }
    public function getStatus() { return $this->attributes['status'] ?? null; }
    public function getCreatedAt() { return $this->attributes['created_at'] ?? null; }
}
