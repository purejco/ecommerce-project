<?php

require_once dirname(__DIR__, 2) . '/config/app.php';

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $sessionName = envValue('SESSION_NAME', 'secure_shop_session');
    $lifetime = (int) envValue('SESSION_LIFETIME', '7200');

    session_name($sessionName);

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => isProduction(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_start();
}

function loginUserSession(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    clearPendingMfaSession();
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function startPendingMfaSession(int $userId): void
{
    session_regenerate_id(true);

    $_SESSION['pending_mfa'] = [
        'user_id' => $userId,
        'expires_at' => time() + 300
    ];
}

function getPendingMfaUserId(): ?int
{
    if (empty($_SESSION['pending_mfa']['user_id']) || empty($_SESSION['pending_mfa']['expires_at'])) {
        return null;
    }

    if ((int) $_SESSION['pending_mfa']['expires_at'] < time()) {
        clearPendingMfaSession();
        return null;
    }

    return (int) $_SESSION['pending_mfa']['user_id'];
}

function clearPendingMfaSession(): void
{
    unset($_SESSION['pending_mfa']);
}

function logoutUserSession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}