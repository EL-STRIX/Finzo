<?php

namespace App\Domain\Models;

class Staff extends BaseModel { public function getStaffId(): string { return $this->attributes["staff_id"] ?? ""; } }
