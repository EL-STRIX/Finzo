<?php
namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Shared\Security\EncryptionService;

class EncryptionServiceTest extends TestCase
{
    private string $originalAppKey;

    protected function setUp(): void
    {
        parent::setUp();
        // Generate a 32-byte key and base64 encode it for APP_KEY
        $this->originalAppKey = defined('APP_KEY') ? APP_KEY : '';
        if (!defined('APP_KEY')) {
            define('APP_KEY', base64_encode(random_bytes(32)));
        }
    }

    public function test_it_encrypts_and_decrypts_data()
    {
        $service = new EncryptionService(base64_encode(random_bytes(32)));
        
        $plaintext = "Sensitive Data 123";
        $encrypted = $service->encrypt($plaintext);
        
        $this->assertNotEquals($plaintext, $encrypted);
        $this->assertNotEmpty($encrypted);
        
        $decrypted = $service->decrypt($encrypted);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function test_it_handles_empty_string()
    {
        $service = new EncryptionService(base64_encode(random_bytes(32)));
        
        $encrypted = $service->encrypt('');
        $this->assertEquals('', $encrypted);
        
        $decrypted = $service->decrypt('');
        $this->assertEquals('', $decrypted);
    }

    public function test_it_encrypts_searchable_deterministically()
    {
        $service = new EncryptionService(base64_encode(random_bytes(32)));
        
        $plaintext = "PII_123456789";
        
        $encrypted1 = $service->encryptSearchable($plaintext);
        $encrypted2 = $service->encryptSearchable($plaintext);
        
        // Since IV is deterministic in encryptSearchable, the ciphertexts should match exactly
        $this->assertEquals($encrypted1, $encrypted2);
        
        $decrypted = $service->decrypt($encrypted1);
        $this->assertEquals($plaintext, $decrypted);
    }
}
