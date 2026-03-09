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