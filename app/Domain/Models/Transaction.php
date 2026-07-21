<?php

namespace App\Domain\Models;

class Transaction extends BaseModel { public function getAmount(): float { return (float)($this->attributes["amount"] ?? 0); } }
