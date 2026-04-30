<?php
require_once 'app/services/SessionService.php';
startSecureSession();

// clear cart after successful payment
$_SESSION['cart'] = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5 text-center">
    <h1>Payment Successful 🎉</h1>
    <p>Your order has been placed successfully.</p>

    <a href="products.php" class="btn btn-primary mt-3">
        Continue Shopping
    </a>
</div>

</body>
</html>