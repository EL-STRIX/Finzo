<?php

namespace App\Domain\Models;

class User extends BaseModel
{
    public function getId() { return $this->attributes['id'] ?? null; }
    public function getCustomerId() { return $this->attributes['customer_id'] ?? null; }
    public function getFullName() { return $this->attributes['full_name'] ?? null; }
    public function getEmail() { return $this->attributes['email'] ?? null; }
    public function getPhone() { return $this->attributes['phone'] ?? null; }
    public function getStatus() { return $this->attributes['status'] ?? null; }
    public function getCreatedAt() { return $this->attributes['created_at'] ?? null; }
}
