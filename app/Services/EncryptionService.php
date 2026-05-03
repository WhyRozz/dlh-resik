<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EncryptionService
{
    /**
     * Kunci enkripsi - ganti dengan string 32-byte acak di production
     * Simpan di .env: ADMIN_ENCRYPTION_KEY=your-32-char-key-here
     */
    private static function getKey(): string
    {
        return env('ADMIN_ENCRYPTION_KEY', 'SIMPELSI_2025_ADMIN_ENCRYPTION_KEY_STRONG_512');
    }

    /**
     * Enkripsi password dengan AES-256-CBC
     */
    public static function encrypt(string $plaintext): ?string
    {
        if (empty($plaintext)) return null;

        try {
            $cipher = 'AES-256-CBC';
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext = openssl_encrypt($plaintext, $cipher, self::getKey(), 0, $iv);

            if ($ciphertext === false) {
                Log::error('Encryption failed: ' . openssl_error_string());
                return null;
            }

            return base64_encode($iv . $ciphertext);
        } catch (\Exception $e) {
            Log::error('Encryption exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Dekripsi password dengan AES-256-CBC
     */
    public static function decrypt(?string $ciphertext): ?string
    {
        if (empty($ciphertext)) return null;

        try {
            $cipher = 'AES-256-CBC';
            $ciphertext = base64_decode($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = substr($ciphertext, 0, $ivlen);
            $ciphertext = substr($ciphertext, $ivlen);

            $decrypted = openssl_decrypt($ciphertext, $cipher, self::getKey(), 0, $iv);

            if ($decrypted === false) {
                Log::error('Decryption failed: ' . openssl_error_string());
                return null;
            }

            return $decrypted;
        } catch (\Exception $e) {
            Log::error('Decryption exception: ' . $e->getMessage());
            return null;
        }
    }
}
