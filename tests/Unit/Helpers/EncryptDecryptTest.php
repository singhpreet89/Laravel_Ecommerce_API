<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class EncryptDecryptTest extends TestCase
{
    public function test_encrypt_returns_string(): void
    {
        $result = encryptDecrypt('42', 'encrypt');

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertNotEquals('42', $result);
    }

    public function test_decrypt_round_trip(): void
    {
        $original = '99';
        $encrypted = encryptDecrypt($original, 'encrypt');
        $decrypted = encryptDecrypt($encrypted, 'decrypt');

        $this->assertEquals($original, $decrypted);
    }

    public function test_returns_null_for_null_payload(): void
    {
        $result = encryptDecrypt(null);

        $this->assertNull($result);
    }

    public function test_returns_null_for_invalid_type(): void
    {
        $result = encryptDecrypt('some-value', 'invalid');

        $this->assertNull($result);
    }
}
