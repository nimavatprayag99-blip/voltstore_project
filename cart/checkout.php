<?php
/**
 * UrbanCart - Checkout Page
 * 
 * @package UrbanCart
 * @version 1.0
 */

require_once __DIR__ . '/../config/db.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/cart/checkout.php';
    setFlashMessage('warning', 'Please login to proceed with checkout.');
    redirect(SITE_URL . '/login.php');
}

// Get cart items
$cartItems = [];
$cartTotal = 0;

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.id as cart_id, c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Cart fetch error: " . $e->getMessage());
}

// Redirect if cart is empty
if (empty($cartItems)) {
    setFlashMessage('warning', 'Your cart is empty.');
    redirect(SITE_URL . '/products.php');
}

// Calculate totals
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $cartTotal += $price * $item['quantity'];
}

$shippingCost = $cartTotal >= 999 ? 0 : 99;
$finalTotal = $cartTotal + $shippingCost;

// Get user details
$user = null;
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("User fetch error: " . $e->getMessage());
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate shipping details
        $shippingName = sanitize($_POST['shipping_name'] ?? '');
        $shippingEmail = sanitize($_POST['shipping_email'] ?? '');
        $shippingPhone = sanitize($_POST['shipping_phone'] ?? '');
        $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
        $shippingCity = sanitize($_POST['shipping_city'] ?? '');
        $shippingState = sanitize($_POST['shipping_state'] ?? '');
        $shippingZip = sanitize($_POST['shipping_zip'] ?? '');
        $paymentMethod = sanitize($_POST['payment_method'] ?? '');
        
        if (empty($shippingName)) $errors[] = 'Full name is required.';
        if (empty($shippingEmail) || !filter_var($shippingEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required.';
        }
        if (empty($shippingPhone)) $errors[] = 'Phone number is required.';
        if (empty($shippingAddress)) $errors[] = 'Shipping address is required.';
        if (empty($shippingCity)) $errors[] = 'City is required.';
        if (empty($shippingState)) $errors[] = 'State is required.';
        if (empty($shippingZip)) $errors[] = 'ZIP code is required.';
        if (empty($paymentMethod)) $errors[] = 'Please select a payment method.';
        
        if (empty($errors)) {
            try {
                $db = getDB();
                $db->beginTransaction();
                
                // Generate order number
                $orderNumber = 'UC' . date('Ymd') . strtoupper(substr(uniqid(), -6));
                
                // Create order
                $orderId = insert('orders', [
                    'order_number' => $orderNumber,
                    'user_id' => $_SESSION['user_id'],
                    'total_amount' => $cartTotal,
                    'shipping_amount' => $shippingCost,
                    'final_amount' => $finalTotal,
                    'payment_method' => $paymentMethod,
                    'shipping_name' => $shippingName,
                    'shipping_email' => $shippingEmail,
                    'shipping_phone' => $shippingPhone,
                    'shipping_address' => $shippingAddress,
                    'shipping_city' => $shippingCity,
                    'shipping_state' => $shippingState,
                    'shipping_zip' => $shippingZip,
                    'shipping_country' => 'India',
                    'status' => 'pending',
                    'payment_status' => 'pending'
                ]);