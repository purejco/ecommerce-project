<?php
require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php';
require_once 'app/services/MfaService.php';
require_once 'app/models/User.php';
require_once 'app/middleware/AuthMiddleware.php';

startSecureSession();
requireAuth();

$user = currentUser();
$fullUser = User::findById((int) $user['id']);

if (!$fullUser) {
    logoutUserSession();
    header('Location: signin.php');
    exit;
}

if (!empty($fullUser['mfa_enabled']) && !empty($fullUser['mfa_secret'])) {
    $alreadyEnabled = true;
} else {
    $alreadyEnabled = false;
}

if (empty($_SESSION['mfa_setup_secret']) && !$alreadyEnabled) {
    $_SESSION['mfa_setup_secret'] = generateMfaSecret();
}

$tempSecret = $_SESSION['mfa_setup_secret'] ?? null;
$provisioningUri = $tempSecret ? getMfaProvisioningUri($fullUser['email'], $tempSecret) : null;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid CSRF token.';
    } elseif ($alreadyEnabled) {
        $error = 'MFA is already enabled for this account.';
    } else {
        $otp = trim($_POST['otp'] ?? '');

        if (!verifyMfaCode($tempSecret ?? '', $otp)) {
            $error = 'Invalid MFA code. Please try again.';
        } else {
            $encryptedSecret = encryptMfaSecret($tempSecret);
            User::enableMfa((int) $fullUser['id'], $encryptedSecret);
            unset($_SESSION['mfa_setup_secret']);
            $alreadyEnabled = true;
            $message = 'MFA was enabled successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup MFA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 auth-container">
    <h2>Set Up Microsoft Authenticator</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($alreadyEnabled): ?>
        <div class="alert alert-info">MFA is already enabled on your account.</div>
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    <?php else: ?>
        <p class="mb-2">
            In Microsoft Authenticator, add a new account and choose manual setup.
        </p>

        <div class="mb-3">
            <label class="form-label"><strong>Issuer</strong></label>
            <input type="text" class="form-control" value="<?= e(getMfaIssuer()) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Account</strong></label>
            <input type="text" class="form-control" value="<?= e($fullUser['email']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Secret Key</strong></label>
            <input type="text" class="form-control" value="<?= e($tempSecret ?? '') ?>" readonly>
            <div class="form-text">Use this key for manual entry in Microsoft Authenticator.</div>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Provisioning URI</strong></label>
            <textarea class="form-control" rows="3" readonly><?= e($provisioningUri ?? '') ?></textarea>
        </div>

        <form method="POST">
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label"><strong>Enter the 6-digit code from Microsoft Authenticator</strong></label>
                <input
                    type="text"
                    name="otp"
                    class="form-control"
                    maxlength="6"
                    pattern="\d{6}"
                    placeholder="123456"
                    required
                >
            </div>

            <button type="submit" class="btn btn-success w-100">Enable MFA</button>
        </form>

        <div class="mt-3">
            <a href="dashboard.php">Cancel</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>