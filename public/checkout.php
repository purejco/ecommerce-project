<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/services/SessionService.php';

startSecureSession();

// Load Stripe config
$stripeConfig = require __DIR__ . '/config/stripe.php';

// Set Stripe secret key
\Stripe\Stripe::setApiKey($stripeConfig['secret_key']);

// If cart is empty, go back
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Build Stripe line items
$lineItems = [];

foreach ($_SESSION['cart'] as $item) {
    $lineItems[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $item['name'],
            ],
            'unit_amount' => $item['price'] * 100, // cents
        ],
        'quantity' => $item['quantity'],
    ];
}


$YOUR_DOMAIN = 'https://yjkgroup13.infinityfree.me';

// Create Stripe session
$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $lineItems,
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . '/success.php',
    'cancel_url' => $YOUR_DOMAIN . '/cancel.php',
]);

// Redirect to Stripe
header('Location: ' . $checkout_session->url);
exit;