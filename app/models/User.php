<?php

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/config/app.php';

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function create(string $name, string $email, string $passwordHash): int
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password_hash, role, mfa_enabled, mfa_secret)
            VALUES (?, ?, ?, ?, 0, NULL)
        ');
        $stmt->execute([$name, $email, $passwordHash, 'customer']);

        return (int) $pdo->lastInsertId();
    }

    public static function enableMfa(int $userId, string $encryptedSecret): void
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET mfa_enabled = 1, mfa_secret = ? WHERE id = ?');
        $stmt->execute([$encryptedSecret, $userId]);
    }

    public static function disableMfa(int $userId): void
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET mfa_enabled = 0, mfa_secret = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public static function resetLoginFailures(int $userId): void
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, lock_until = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public static function incrementLoginFailure(array $user): void
    {
        $pdo = getPDO();

        $maxAttempts = (int) envValue('MAX_LOGIN_ATTEMPTS', '5');
        $lockoutMinutes = (int) envValue('LOCKOUT_MINUTES', '15');
        $newAttempts = ((int) $user['failed_login_attempts']) + 1;

        if ($newAttempts >= $maxAttempts) {
            $lockUntil = (new DateTime())->modify("+{$lockoutMinutes} minutes")->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, lock_until = ? WHERE id = ?');
            $stmt->execute([$lockUntil, $user['id']]);
            return;
        }

        $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = ? WHERE id = ?');
        $stmt->execute([$newAttempts, $user['id']]);
    }

    public static function isLocked(array $user): bool
    {
        if (empty($user['lock_until'])) {
            return false;
        }

        return strtotime($user['lock_until']) > time();
    }
}