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
