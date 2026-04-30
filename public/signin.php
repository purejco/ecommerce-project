<?php
require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php';
require_once 'app/controllers/AuthController.php';

startSecureSession();

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$result = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::handleSignin();

    if ($result['success']) {
        $redirect = $result['redirect'] ?? 'dashboard.php';
        header('Location: ' . $redirect);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 auth-container">
    <h2>Login</h2>

    <?php if (!empty($result['message']) && !$result['success']): ?>
        <div class="alert alert-danger"><?= e($result['message']) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <?= csrfField() ?>

        <input
            type="email"
            name="email"
            class="form-control mb-2"
            placeholder="Email"
            value="<?= e($_POST['email'] ?? '') ?>"
            required
        >
        <?php if (!empty($result['errors']['email'])): ?>
            <div class="text-danger mb-2"><?= e($result['errors']['email']) ?></div>
        <?php endif; ?>

        <input
            type="password"
            name="password"
            class="form-control mb-2"
            placeholder="Password"
            required
        >
        <?php if (!empty($result['errors']['password'])): ?>
            <div class="text-danger mb-2"><?= e($result['errors']['password']) ?></div>
        <?php endif; ?>

        <button class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-3">
        <a href="signup.php">Need an account? Sign up</a>
    </div>
</div>

</body>
</html>