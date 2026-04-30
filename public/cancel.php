<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/services/SessionService.php';
startSecureSession();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Cancelled</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">SecureStore</a>
        <div>
            <a href="products.php" class="btn btn-outline-light">Products</a>
            <a href="cart.php" class="btn btn-warning">Cart</a>
        </div>
    </div>
</nav>

<div class="container mt-5 text-center">
    <h1>Payment Cancelled</h1>
    <p>Your payment was cancelled. Your cart has not been changed.</p>
    <a href="cart.php" class="btn btn-primary mt-3">Return to Cart</a>
</div>

</body>
</html>