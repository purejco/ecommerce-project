<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php';

startSecureSession();
$user = currentUser();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_product_id'])) {
        $productId = (int) $_POST['remove_product_id'];
        unset($_SESSION['cart'][$productId]);
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }

    header('Location: cart.php');
    exit;
}

$cartItems = $_SESSION['cart'];
$total = 0;

foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Your CSS -->
    <link href="assets/style/style.css" rel="stylesheet">

    <!-- FORCE FIX (just in case CSS isn’t loading) -->
    <style>
        .cart-icon {
            width: 60px !important;
            height: 60px !important;
            max-width: 60px !important;
            object-fit: contain;
        }
    </style>
</head>
<body>

<div class="container mt-5">

    <!-- Header with fixed image -->
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Your Cart</h2>

    <img src="https://cdn-icons-png.flaticon.com/512/263/263142.png"
         alt="Cart"
         class="cart-icon">
</div>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
        <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
    <?php else: ?>

        <table class="table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>

            <?php foreach ($cartItems as $productId => $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td>$<?= e((string)$item['price']) ?></td>
                    <td><?= e((string)$item['quantity']) ?></td>
                    <td>$<?= e((string)($item['price'] * $item['quantity'])) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="remove_product_id" value="<?= $productId ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h4>Total: $<?= e((string)$total) ?></h4>

        <div class="mt-3">
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            <a href="products.php" class="btn btn-secondary">Continue Shopping</a>

            <form method="POST" class="d-inline">
                <input type="hidden" name="clear_cart" value="1">
                <button type="submit" class="btn btn-outline-danger">Clear Cart</button>
            </form>
        </div>

    <?php endif; ?>

</div>

</body>
</html>