<?php

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/validators/AuthValidator.php';
require_once dirname(__DIR__) . '/services/SessionService.php';
require_once dirname(__DIR__) . '/services/SecurityService.php';
require_once dirname(__DIR__) . '/services/MfaService.php';

class AuthController
{
    public static function handleSignup(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request method.', 'errors' => []];
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
            return ['success' => false, 'message' => 'Invalid CSRF token.', 'errors' => []];
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $errors = validateSignup([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors];
        }

        if (User::findByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email already in use.',
                'errors' => ['email' => 'Email already in use.']
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userId = User::create($name, $email, $passwordHash);

        loginUserSession([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'customer',
        ]);

        return [
            'success' => true,
            'message' => 'Account created successfully.',
            'errors' => [],
            'redirect' => 'dashboard.php'
        ];
    }

    public static function handleSignin(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request method.', 'errors' => []];
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
            return ['success' => false, 'message' => 'Invalid CSRF token.', 'errors' => []];
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $errors = validateSignin([
            'email' => $email,
            'password' => $password,
        ]);

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors];
        }

        $user = User::findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials.', 'errors' => []];
        }

        if (User::isLocked($user)) {
            return ['success' => false, 'message' => 'Account temporarily locked. Try again later.', 'errors' => []];
        }

        if (!password_verify($password, $user['password_hash'])) {
            User::incrementLoginFailure($user);
            return ['success' => false, 'message' => 'Invalid credentials.', 'errors' => []];
        }

        User::resetLoginFailures((int) $user['id']);

        if (!empty($user['mfa_enabled']) && !empty($user['mfa_secret'])) {
            startPendingMfaSession((int) $user['id']);

            return [
                'success' => true,
                'message' => 'Password verified. Enter your MFA code.',
                'errors' => [],
                'redirect' => 'verify-mfa.php'
            ];
        }

        loginUserSession($user);

        return [
            'success' => true,
            'message' => 'Signed in successfully.',
            'errors' => [],
            'redirect' => 'dashboard.php'
        ];
    }
}