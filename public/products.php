<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'app/services/SessionService.php';
require_once 'app/services/SecurityService.php'; 

startSecureSession();
$user = currentUser();

// fallback in case e() somehow still missing
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$products = [
    1 => [
        'name' => 'Smartphone',
        'price' => 699,
        'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9'
    ],
    2 => [
        'name' => 'Laptop',
        'price' => 1199,
        'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8'
    ],
    3 => [
        'name' => 'Smartwatch',
        'price' => 299,
        'image' => 'https://images.unsplash.com/photo-1510017803434-a899398421b3'
    ]
];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];

    if (isset($products[$productId])) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'name' => $products[$productId]['name'],
                'price' => $products[$productId]['price'],
                'quantity' => 1
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity']++;
        }
    }

    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    
    <link href="assets/style/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">SecureStore</a>
        <div>
            <?php if ($user): ?>
                <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
            <?php else: ?>
                <a href="signin.php" class="btn btn-outline-light">Login</a>
            <?php endif; ?>
            <a href="cart.php" class="btn btn-warning">Cart</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">

        <?php foreach ($products as $id => $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <img src="<?= e($product['image']) ?>" 
                         class="card-img-top product-img" 
                         alt="<?= e($product['name']) ?>">

                    <div class="card-body">
                        <h5><?= e($product['name']) ?></h5>
                        <p>$<?= e((string)$product['price']) ?></p>

                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <button type="submit" class="btn btn-primary">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

</body>
</html>