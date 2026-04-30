<?php

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    $token = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function validateCsrfToken(?string $submittedToken): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? null;

    if (!$sessionToken || !$submittedToken) {
        return false;
    }

    return hash_equals($sessionToken, $submittedToken);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}