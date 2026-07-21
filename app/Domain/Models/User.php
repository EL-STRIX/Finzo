<?php

namespace App\Domain\Models;

class User extends BaseModel { public function getFullName(): string { return $this->attributes["full_name"] ?? ""; } }
