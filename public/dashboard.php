<?php
require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/models/User.php';

startSecureSession();
requireAuth();

$user = currentUser();
$fullUser = User::findById((int) $user['id']);
$mfaEnabled = !empty($fullUser['mfa_enabled']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">MKJY SECURE Store</a>
        <div>
            <a href="products.php" class="btn btn-outline-light">Products</a>
            <a href="cart.php" class="btn btn-warning">Cart</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2>Welcome, <?= e($user['name']) ?></h2>
            <p><strong>Email:</strong> <?= e($user['email']) ?></p>
            <p><strong>Role:</strong> <?= e($user['role']) ?></p>
            <p class="text-success">You are signed in with secure session-based authentication.</p>

            <hr>

            <h4>Multi-Factor Authentication</h4>
            <p>
                <strong>Status:</strong>
                <?= $mfaEnabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Not enabled</span>' ?>
            </p>

            <?php if (!$mfaEnabled): ?>
                <a href="setup-mfa.php" class="btn btn-primary">Set Up Microsoft Authenticator</a>
            <?php else: ?>
                <a href="setup-mfa.php" class="btn btn-outline-secondary">View MFA Setup Status</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>