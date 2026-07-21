<?php

namespace App\Domain\Models;

class Account extends BaseModel { public function getBalance(): float { return (float)($this->attributes["balance"] ?? 0); } }
