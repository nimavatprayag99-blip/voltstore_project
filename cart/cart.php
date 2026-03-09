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

<!-- Cart Section -->
<section class="section" style="background: var(--bg-primary);">
    <div class="container">
        <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <!-- Empty Cart -->
        <div style="text-align: center; padding: 80px 24px; background: var(--bg-secondary); border-radius: 24px;">
            <i class="fas fa-shopping-cart" style="font-size: 5rem; color: var(--text-muted); margin-bottom: 24px;"></i>
            <h2 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">Your cart is empty</h2>
            <p style="color: var(--text-secondary); margin-bottom: 32px; max-width: 400px; margin-left: auto; margin-right: auto;">
                Looks like you haven't added anything to your cart yet. Browse our products and find something you love!
            </p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping
            </a>
        </div>
        <?php else: ?>
        <!-- Cart Content -->
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 32px;">
                <!-- Cart Items -->
                <div style="background: var(--bg-secondary); border-radius: 20px; padding: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                        <h2 style="font-size: 1.25rem; font-weight: 600;">
                            <?php echo $cartCount; ?> Item<?php echo $cartCount > 1 ? 's' : ''; ?> in Cart
                        </h2>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-ghost btn-sm" style="color: var(--primary);">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
                    </div>
                    
                    <?php foreach ($cartItems as $item): 
                        $price = $item['sale_price'] ?: $item['price'];
                        $subtotal = $price * $item['quantity'];
                    ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['featured_image'] ?: 'placeholder.jpg'; ?>" 
                                 alt="<?php echo $item['name']; ?>"
                                 onerror="this.src='https://via.placeholder.com/100x100/f5f5f7/86868b?text=<?php echo urlencode(substr($item['name'], 0, 2)); ?>'">
                        </div>