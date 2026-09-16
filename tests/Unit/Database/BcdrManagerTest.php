<?php

namespace Tests\Unit\Database;

use Tests\TestCase;
use Database\BcdrManager;

require_once FINZO_ROOT . '/database/BcdrManager.php';

class BcdrManagerTest extends TestCase
{
    private BcdrManager $bcdr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bcdr = new BcdrManager($this->db);
    }

    public function testValidateIntegrityExecutesWithoutErrors(): void
    {
        ob_start();
        $this->bcdr->validateIntegrity();
        $output = ob_get_clean();

        $this->assertStringContainsString('Validating Database Integrity', $output);
        $this->assertStringContainsString('Database Integrity Validation Completed', $output);
    }
}
