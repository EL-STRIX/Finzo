<?php
namespace App\Shared\Security;

class EncryptionService
{
    private string $key;
    private const CIPHER = 'aes-256-gcm';

    public function __construct(string $key = '')
    {
        $this->key = $key ?: APP_KEY;
        if (empty($this->key)) {
            throw new \Exception('Encryption key not set.');
        }
        // Key should be exactly 32 bytes after base64 decode
        $this->key = base64_decode($this->key);
    }

    public function encrypt(string $plaintext): string
    {
        if (empty($plaintext)) return '';
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLen);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function encryptSearchable(string $plaintext): string
    {
        if (empty($plaintext)) return '';
        // Deterministic IV for searchable fields (e.g. Exact Match)
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr(hash('sha256', $this->key . 'searchable'), 0, $ivLen);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $payload): string
    {
        if (empty($payload)) return '';
        $data = base64_decode($payload);
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        
        if (strlen($data) <= $ivLen + 16) {
            return $payload; // Return original if not encrypted properly or legacy plaintext
        }

        $iv = substr($data, 0, $ivLen);
        $tag = substr($data, $ivLen, 16); // GCM tag is 16 bytes
        $ciphertext = substr($data, $ivLen + 16);
        
        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($decrypted === false) {
            // Support legacy plaintext gracefully during transition
            return $payload; 
        }
        return $decrypted;
    }
}
