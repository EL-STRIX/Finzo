<?php

namespace App\Domain\Models;

class Admin extends BaseModel { public function getRole(): string { return $this->attributes["admin_role"] ?? ""; } }
