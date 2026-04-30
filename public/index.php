<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 

require_once 'app/services/SessionService.php'; 
require_once 'app/services/SecurityService.php'; 

startSecureSession(); 
$user = currentUser(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Gadget Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">MKJY SECURE Store</a>

        <div>
            <?php if ($user): ?>
                <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            <?php else: ?>
                <a href="signin.php" class="btn btn-outline-light">Login</a>
                <a href="signup.php" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
            <a href="cart.php" class="btn btn-warning">Cart</a>
        </div>
    </div>
</nav>

<div class="hero">
    <h1>Secure Gadget Store</h1>
    <p>Shop safely with MFA &amp; Secure Payments</p>
    <a href="products.php" class="btn btn-light">Shop Now</a>
</div>

<div class="container mt-5">
    <div class="row text-center">
        <div class="col-md-4">
            <h4> Secure Login</h4>
            <p>Protected with Multi-Factor Authentication (MFA)</p>
        </div>

        <div class="col-md-4">
            <h4> Safe Payments</h4>
            <p>Encrypted transactions with secure payment gateway</p>
        </div>

        <div class="col-md-4">
            <h4> Data Protection</h4>
            <p>Your data is encrypted and protected.</p>
        </div>
    </div>
</div>

</body>
</html>