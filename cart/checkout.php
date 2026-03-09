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
                
                // Create order items
                foreach ($cartItems as $item) {
                    $price = $item['sale_price'] ?: $item['price'];
                    $subtotal = $price * $item['quantity'];
                    
                    insert('order_items', [
                        'order_id' => $orderId,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'product_image' => $item['featured_image'],
                        'price' => $price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal
                    ]);
                    
                    // Update product stock
                    $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")
                       ->execute([$item['quantity'], $item['id']]);
                }
                
                // Clear cart
                $db->prepare("DELETE FROM cart WHERE user_id = ?")
                   ->execute([$_SESSION['user_id']]);
                
                $db->commit();
                
                // Set success message and redirect
                setFlashMessage('success', 'Order placed successfully!');
                redirect(SITE_URL . '/cart/order_confirmation.php?order_number=' . $orderNumber);
                
            } catch (PDOException $e) {
                $db->rollBack();
                error_log("Checkout error: " . $e->getMessage());
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}

$pageTitle = 'Checkout';
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="section-sm" style="background: var(--bg-secondary); padding-top: 100px;">
    <div class="container">
        <nav style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
            <i class="fas fa-chevron-right" style="margin: 0 8px; font-size: 0.75rem;"></i>
            <a href="<?php echo SITE_URL; ?>/cart/cart.php">Cart</a>
            <i class="fas fa-chevron-right" style="margin: 0 8px; font-size: 0.75rem;"></i>
            <span style="color: var(--text-primary);">Checkout</span>
        </nav>
        
        <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--text-primary);">
            Checkout
        </h1>
    </div>
</section>

<!-- Checkout Section -->
<section class="section" style="background: var(--bg-primary);">
    <div class="container">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: 24px;">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 32px;">
                <!-- Checkout Form -->
                <div>
                    <!-- Shipping Information -->
                    <div style="background: var(--bg-secondary); border-radius: 18px; padding: 32px; margin-bottom: 24px;">
                        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;">
                            <i class="fas fa-shipping-fast" style="color: var(--primary); margin-right: 10px;"></i>
                            Shipping Information
                        </h2>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="shipping_name" class="form-input" 
                                       value="<?php echo $_POST['shipping_name'] ?? ($user['first_name'] . ' ' . $user['last_name']) ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="shipping_email" class="form-input" 
                                       value="<?php echo $_POST['shipping_email'] ?? $user['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="shipping_phone" class="form-input" 
                                   value="<?php echo $_POST['shipping_phone'] ?? $user['phone'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <textarea name="shipping_address" class="form-input" rows="3" required><?php echo $_POST['shipping_address'] ?? $user['address'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">City *</label>
                                <input type="text" name="shipping_city" class="form-input" 
                                       value="<?php echo $_POST['shipping_city'] ?? $user['city'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">State *</label>
                                <input type="text" name="shipping_state" class="form-input" 
                                       value="<?php echo $_POST['shipping_state'] ?? $user['state'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">ZIP Code *</label>
                                <input type="text" name="shipping_zip" class="form-input" 
                                       value="<?php echo $_POST['shipping_zip'] ?? $user['zip_code'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div style="background: var(--bg-secondary); border-radius: 18px; padding: 32px;">
                        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;">
                            <i class="fas fa-credit-card" style="color: var(--primary); margin-right: 10px;"></i>
                            Payment Method
                        </h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--bg-primary); border-radius: 12px; cursor: pointer; border: 2px solid var(--border-color); transition: all 0.2s;"
                                   onmouseover="this.style.borderColor='var(--primary)'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border-color)'">
                                <input type="radio" name="payment_method" value="cod" style="width: 20px; height: 20px; accent-color: var(--primary);"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : ''; ?> required>
                                <i class="fas fa-money-bill-wave" style="font-size: 1.5rem; color: var(--accent-green);"></i>
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 2px;">Cash on Delivery</p>
                                    <p style="font-size: 0.875rem; color: var(--text-muted);">Pay when you receive</p>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--bg-primary); border-radius: 12px; cursor: pointer; border: 2px solid var(--border-color); transition: all 0.2s;"
                                   onmouseover="this.style.borderColor='var(--primary)'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border-color)'">
                                <input type="radio" name="payment_method" value="card" style="width: 20px; height: 20px; accent-color: var(--primary);"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'card' ? 'checked' : ''; ?>>
                                <i class="fas fa-credit-card" style="font-size: 1.5rem; color: var(--primary);"></i>
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 2px;">Credit/Debit Card</p>
                                    <p style="font-size: 0.875rem; color: var(--text-muted);">Secure online payment</p>
                                </div>
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--bg-primary); border-radius: 12px; cursor: pointer; border: 2px solid var(--border-color); transition: all 0.2s;"
                                   onmouseover="this.style.borderColor='var(--primary)'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border-color)'">
                                <input type="radio" name="payment_method" value="upi" style="width: 20px; height: 20px; accent-color: var(--primary);"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'upi' ? 'checked' : ''; ?>>
                                <i class="fas fa-mobile-alt" style="font-size: 1.5rem; color: var(--accent-purple);"></i>
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 2px;">UPI</p>
                                    <p style="font-size: 0.875rem; color: var(--text-muted);">Pay using UPI apps</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div>
                    <div class="cart-summary" style="position: sticky; top: 80px; background: var(--bg-secondary); border-radius: 18px; padding: 32px;">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;">Order Summary</h3>
                        
                        <!-- Order Items -->
                        <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                            <?php foreach ($cartItems as $item): 
                                $price = $item['sale_price'] ?: $item['price'];
                            ?>
                            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['featured_image'] ?: 'placeholder.jpg'; ?>" 
                                     alt="<?php echo $item['name']; ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.src='https://via.placeholder.com/60x60/f5f5f7/86868b?text=<?php echo urlencode(substr($item['name'], 0, 2)); ?>'">
                                <div style="flex: 1;">
                                    <p style="font-size: 0.875rem; font-weight: 500; margin-bottom: 2px;"><?php echo $item['name']; ?></p>
                                    <p style="font-size: 0.8125rem; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?></p>
                                </div>
                                <p style="font-weight: 600;"><?php echo formatPrice($price * $item['quantity']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shippingCost === 0 ? 'FREE' : formatPrice($shippingCost); ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($finalTotal); ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top: 24px;">
                            Place Order
                            <i class="fas fa-check-circle"></i>
                        </button>
                        
                        <p style="text-align: center; font-size: 0.8125rem; color: var(--text-muted); margin-top: 16px;">
                            <i class="fas fa-lock"></i>
                            Your information is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>