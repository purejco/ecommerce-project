<?php
require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php';
require_once 'app/services/MfaService.php';
require_once 'app/models/User.php';

startSecureSession();

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$pendingUserId = getPendingMfaUserId();

if (!$pendingUserId) {
    header('Location: signin.php');
    exit;
}

$user = User::findById($pendingUserId);

if (!$user || empty($user['mfa_enabled']) || empty($user['mfa_secret'])) {
    clearPendingMfaSession();
    header('Location: signin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid CSRF token.';
    } else {
        $otp = trim($_POST['otp'] ?? '');
        $secret = decryptMfaSecret($user['mfa_secret']);

        if (!$secret || !verifyMfaCode($secret, $otp)) {
            $error = 'Invalid MFA code.';
        } else {
            loginUserSession($user);
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify MFA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 auth-container">
    <h2>MFA Verification</h2>
    <p>Enter the 6-digit code from Microsoft Authenticator.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>

        <input
            type="text"
            name="otp"
            class="form-control mb-3"
            maxlength="6"
            pattern="\d{6}"
            placeholder="123456"
            required
        >

        <button class="btn btn-success w-100">Verify</button>
    </form>

    <div class="mt-3">
        <a href="signin.php">Back to Login</a>
    </div>
</div>

</body>
</html>