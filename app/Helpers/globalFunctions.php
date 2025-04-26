<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;

/**
 * Encrypts or decrypts a string using AES-256-CBC with a static IV.
 * Identical plaintext ⇒ identical ciphertext
 */
if (!function_exists('encryptDecrypt')) {
    function encryptDecrypt(?string $payload, string $type = 'encrypt'): ?string
    {
        if ($payload === null) {
            return null;
        }

        $rawKey         = config('app.key');
        $key            = Str::startsWith($rawKey, 'base64:') ? base64_decode(substr($rawKey, 7), true) : $rawKey;
        $initialVector  = config('constants.APP_IV');   // Static IV
        $cipher         = 'AES-256-CBC';                // AES cipher

        try {
            if ($type === 'encrypt') {
               $cipherText = base64_encode(openssl_encrypt($payload, strtolower($cipher), $key, 0, $initialVector));
    
                if ($cipherText === false) {
                    throw new EncryptException('Could not encrypt the data.');
                }

                return $cipherText;
            }

            if ($type === 'decrypt') {
                $plaintext = openssl_decrypt(base64_decode($payload), strtolower($cipher), $key, 0, $initialVector);
    
                if ($plaintext === false) {
                    throw new DecryptException('Could not decrypt the data.');
                }

                return $plaintext;
            }

            throw new \InvalidArgumentException('Type must be "encrypt" or "decrypt".');
        } catch (\Exception $ex) {
            Log::error($ex);
            return null;
        }
    }
}