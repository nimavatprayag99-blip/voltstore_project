<?php
/**
 * UrbanCart - Shopping Cart Page
 * 
 * @package UrbanCart
 * @version 1.0
 */

require_once __DIR__ . '/../config/db.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        foreach ($_POST['quantities'] as $cartId => $quantity) {
            $quantity = max(1, intval($quantity));
            
            if (isLoggedIn()) {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$quantity, $cartId, $_SESSION['user_id']]);
                } catch (PDOException $e) {
                    error_log("Cart update error: " . $e->getMessage());
                }
            } else {
                // Update guest cart
                if (isset($_SESSION['guest_cart'][$cartId])) {
                    $_SESSION['guest_cart'][$cartId] = $quantity;
                }
            }
        }
         setFlashMessage('success', 'Cart updated successfully.');
    }
    redirect(SITE_URL . '/cart/cart.php');
}

// Get cart items
$cartItems = [];
$cartTotal = 0;
$cartCount = 0;

if (isLoggedIn()) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.id as cart_id, c.quantity, p.*, cat.name as category_name 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            LEFT JOIN categories cat ON p.category_id = cat.id 
            WHERE c.user_id = ? AND p.status = 1
        ");