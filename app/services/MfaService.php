<?php

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

function getGoogle2faInstance(): Google2FA
{
    return new Google2FA();
}

function generateMfaSecret(): string
{
    return getGoogle2faInstance()->generateSecretKey();
}

function getMfaIssuer(): string
{
    return envValue('MFA_ISSUER', 'MKJY SECURE Store');
}

function getMfaProvisioningUri(string $email, string $secret): string
{
    return getGoogle2faInstance()->getQRCodeUrl(
        getMfaIssuer(),
        $email,
        $secret
    );
}

function verifyMfaCode(string $secret, string $code): bool
{
    $code = trim($code);

    if (!preg_match('/^\d{6}$/', $code)) {
        return false;
    }

    return getGoogle2faInstance()->verifyKey($secret, $code);
}

function getMfaEncryptionKey(): string
{
    $rawKey = envValue('MFA_ENCRYPTION_KEY', '');

    if ($rawKey === '') {
        throw new RuntimeException('MFA_ENCRYPTION_KEY is not configured.');
    }

    return hash('sha256', $rawKey, true);
}

function encryptMfaSecret(string $plainSecret): string
{
    $cipher = 'AES-256-CBC';
    $key = getMfaEncryptionKey();
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);

    $encrypted = openssl_encrypt(
        $plainSecret,
        $cipher,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($encrypted === false) {
        throw new RuntimeException('Failed to encrypt MFA secret.');
    }

    return base64_encode($iv . $encrypted);
}

function decryptMfaSecret(?string $storedValue): ?string
{
    if (empty($storedValue)) {
        return null;
    }

    $cipher = 'AES-256-CBC';
    $key = getMfaEncryptionKey();
    $decoded = base64_decode($storedValue, true);

    if ($decoded === false) {
        return null;
    }

    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($decoded, 0, $ivLength);
    $ciphertext = substr($decoded, $ivLength);

    $decrypted = openssl_decrypt(
        $ciphertext,
        $cipher,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return $decrypted === false ? null : $decrypted;
}