<?php

function validateSignup(array $data): array
{
    $errors = [];

    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        $errors['name'] = 'Name must be between 2 and 50 characters.';
    } elseif (!preg_match("/^[A-Za-z\s'-]+$/", $name)) {
        $errors['name'] = 'Name contains invalid characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    }

    if (strlen($password) < 12) {
        $errors['password'] = 'Password must be at least 12 characters.';
    } elseif (
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $errors['password'] = 'Password must include uppercase, lowercase, number, and special character.';
    }

    return $errors;
}

function validateSignin(array $data): array
{
    $errors = [];

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    return $errors;
}