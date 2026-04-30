<?php

require_once dirname(__DIR__) . '/services/SessionService.php';

function requireAuth(): void
{
    if (!currentUser()) {
        header('Location: signin.php');
        exit;
    }
}