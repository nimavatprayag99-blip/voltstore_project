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
                        
                        <div class="cart-item-details">
                            <h3 class="cart-item-name">
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $item['slug']; ?>">
                                    <?php echo $item['name']; ?>
                                </a>
                            </h3>
                            <p class="cart-item-variant"><?php echo $item['category_name']; ?></p>
                            <p class="cart-item-price"><?php echo formatPrice($price); ?></p>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="quantity-selector">
                                <button type="button" class="qty-minus">-</button>
                                <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock_quantity']; ?>"
                                       data-cart-item-id="<?php echo $item['cart_id']; ?>">
                                <button type="button" class="qty-plus">+</button>
                            </div>
                            
                            <a href="<?php echo SITE_URL; ?>/cart/remove.php?id=<?php echo $item['cart_id']; ?>" 
                               class="remove-item" 
                               data-confirm="Are you sure you want to remove this item?">
                                <i class="fas fa-trash-alt"></i> Remove
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" name="update_cart" class="btn btn-primary btn-outline" style="margin-top: 16px;">
                        <i class="fas fa-sync-alt"></i>
                        Update Cart
                    </button>
                </div>
                
                <!-- Cart Summary -->
                <div>
                    <div class="cart-summary" style="position: sticky; top: 80px; background: var(--bg-secondary); border-radius: 20px; padding: 32px;">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;">Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="cart-subtotal"><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shippingCost === 0 ? 'FREE' : formatPrice($shippingCost); ?></span>
                        </div>
                        
                        <?php if ($shippingCost === 0): ?>
                        <div style="padding: 12px; background: rgba(52, 199, 89, 0.1); border-radius: 8px; margin: 16px 0;">
                            <p style="font-size: 0.875rem; color: var(--accent-green);">
                                <i class="fas fa-check-circle"></i>
                                You qualify for free shipping!
                            </p>
                        </div>
                        <?php else: ?>
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: 8px; margin: 16px 0;">
                            <p style="font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-info-circle"></i>
                                Add <?php echo formatPrice(999 - $cartTotal); ?> more for free shipping
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="cart-total"><?php echo formatPrice($finalTotal); ?></span>
                        </div>
                        
                        <a href="<?php echo SITE_URL; ?>/cart/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top: 24px;">
                            Proceed to Checkout
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        
                        <div style="margin-top: 24px; text-align: center;">
                            <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 12px;">
                                We accept:
                            </p>
                            <div style="display: flex; justify-content: center; gap: 12px; opacity: 0.6;">
                                <i class="fab fa-cc-visa" style="font-size: 1.5rem;"></i>
                                <i class="fab fa-cc-mastercard" style="font-size: 1.5rem;"></i>
                                <i class="fab fa-cc-paypal" style="font-size: 1.5rem;"></i>
                                <i class="fas fa-university" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>