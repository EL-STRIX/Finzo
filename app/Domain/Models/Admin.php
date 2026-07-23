<?php

namespace App\Domain\Models;

class Admin extends BaseModel
{
    public function getId() { return $this->attributes['id'] ?? null; }
    public function getAdminId() { return $this->attributes['admin_id'] ?? null; }
    public function getPasswordHash() { return $this->attributes['password_hash'] ?? null; }
    public function getFullName() { return $this->attributes['full_name'] ?? null; }
    public function getEmail() { return $this->attributes['email'] ?? null; }
    public function getStatus() { return $this->attributes['status'] ?? null; }
    public function getCreatedAt() { return $this->attributes['created_at'] ?? null; }
}
