<?php

namespace App\Domain\Models;

class Branch extends BaseModel { public function getCode(): string { return $this->attributes["branch_code"] ?? ""; } }
