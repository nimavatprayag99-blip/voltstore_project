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
         $stmt->execute([$_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Cart fetch error: " . $e->getMessage());
    }
} else {
    // Guest cart
    if (!empty($_SESSION['guest_cart'])) {
        try {
            $db = getDB();
            $productIds = array_keys($_SESSION['guest_cart']);
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            
            $stmt = $db->prepare("
                SELECT p.*, cat.name as category_name 
                FROM products p 
                LEFT JOIN categories cat ON p.category_id = cat.id 
                WHERE p.id IN ($placeholders) AND p.status = 1
            ");
            $stmt->execute($productIds);
            $products = $stmt->fetchAll();
            
            foreach ($products as $product) {
                $cartItems[] = array_merge($product, [
                    'cart_id' => $product['id'],
                    'quantity' => $_SESSION['guest_cart'][$product['id']]
                ]);
            }
        } catch (PDOException $e) {
            error_log("Guest cart fetch error: " . $e->getMessage());
        }
    }
}

// Calculate totals
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $cartTotal += $price * $item['quantity'];
    $cartCount += $item['quantity'];
}

$shippingCost = $cartTotal >= 999 ? 0 : 99;
$finalTotal = $cartTotal + $shippingCost;

$pageTitle = 'Shopping Cart';
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="section-sm" style="background: var(--bg-secondary); padding-top: 100px;">
    <div class="container">
        <nav style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
            <i class="fas fa-chevron-right" style="margin: 0 8px; font-size: 0.75rem;"></i>
            <span style="color: var(--text-primary);">Shopping Cart</span>
        </nav>
        
        <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--text-primary);">
            Shopping Cart
        </h1>
    </div>
</section>