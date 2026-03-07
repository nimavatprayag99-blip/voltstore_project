<?php
/**
 * UrbanCart - Product Detail Page
 * 
 * @package UrbanCart
 * @version 1.2
 */

require_once __DIR__ . '/config/db.php';

// Get product slug
$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    setFlashMessage('error', 'Product not found.');
    redirect(SITE_URL . '/products.php');
}

// Fetch product
$product = null;
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ? AND p.status = 1
    ");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Product fetch error: " . $e->getMessage());
}

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect(SITE_URL . '/products.php');
}

// Fetch related products
$relatedProducts = [];
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product['id']]);
    $relatedProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Related products error: " . $e->getMessage());
}

// Fetch reviews
$reviews = [];
$averageRating = 0;
$totalReviews = 0;
try {
    $db = getDB();
    // Get reviews with user details
    $stmt = $db->prepare("
        SELECT r.*, u.first_name, u.last_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();
    $totalReviews = count($reviews);
    
    if ($totalReviews > 0) {
        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $review['rating'];
        }
        $averageRating = round($sum / $totalReviews, 1);
    }
} catch (PDOException $e) {
    error_log("Reviews fetch error: " . $e->getMessage());
}

// Parse product images
$productImages = [];
if ($product['images']) {
    $productImages = json_decode($product['images'], true) ?: [];
}
if (empty($productImages) && $product['featured_image']) {
    $productImages[] = $product['featured_image'];
}

$pageTitle = $product['name'];
$pageStyles = '
    .product-page-container {
        padding-top: 40px;
        padding-bottom: 80px;
    }

    .product-layout {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 60px;
        align-items: start;
    }
    
    @media (max-width: 992px) {
        .product-layout {
            grid-template-columns: 1fr;
            gap: 40px;
        }
    }
        
    /* Gallery Styles */
    .gallery-container {
        position: sticky;
        top: 100px;
        animation: slideInLeft 0.6s ease forwards;
    }

    .main-image-wrapper {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        background: var(--bg-secondary);
        aspect-ratio: 1/1;
        margin-bottom: 20px;
        box-shadow: var(--shadow-md);
        cursor: zoom-in;
    }

    .main-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 40px;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .main-image:hover {
        transform: scale(1.05);
    }

    .thumbnails-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
        
    .thumbnail-btn {
        aspect-ratio: 1/1;
        border-radius: 12px;
        border: 2px solid transparent;
        background: var(--bg-secondary);
        cursor: pointer;
        padding: 8px;
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .thumbnail-btn.active {
        border-color: var(--primary);
        background: rgba(0, 113, 227, 0.05);
    }

    .thumbnail-btn img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.2s ease;
    }
    
    .thumbnail-btn:hover img {
        transform: scale(1.1);
    }

    /* Product Info Styles */
    .product-info-wrapper {
        animation: fadeInUp 0.6s ease forwards;
    }
        
    .product-category-badge {
        display: inline-block;
        padding: 6px 12px;
        background: rgba(0, 113, 227, 0.1);
        color: var(--primary);
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 50px;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .product-title {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 16px;
        color: var(--text-primary);
        letter-spacing: -0.02em;
    }

    .rating-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        font-size: 1rem;
    }

    .price-container {
        display: flex;
        align-items: baseline;
        gap: 16px;
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid var(--border-color);
    }
        
    .current-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .original-price {
        font-size: 1.25rem;
        color: var(--text-muted);
        text-decoration: line-through;
    }

    .discount-badge {
        background: var(--accent-red);
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .description-text {
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--text-secondary);
        margin-bottom: 32px;
    }

    /* Action Buttons */
    .actions-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 32px;
    }
        
    .quantity-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 50px;
        padding: 4px 8px;
        height: 56px;
        max-width: 140px;
    }
    
    .qty-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--bg-tertiary); /* Distinct from container */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: var(--text-primary);
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .qty-btn:hover {
        background: var(--primary);
        color: white;
    }
    
    .qty-input {
        width: 40px;
        text-align: center;
        background: transparent;
        border: none;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--text-primary);
        outline: none;
    }
        
    .btn-add-cart, .btn-buy-now {
        height: 56px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        width: 100%;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-add-cart {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-add-cart:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.2);
    }

    .btn-buy-now {
        background: var(--primary);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
    }
        
    .btn-buy-now:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 113, 227, 0.4);
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 40px;
        padding: 24px;
        background: var(--bg-tertiary);
        border-radius: 16px;
    }
    
    .feature-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 8px;
    }
    
    .feature-icon {
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 4px;
    }

    /* Tabs */
    .tabs-header {
        display: flex;
        gap: 40px;
        border-bottom: 1px solid var(--border-color);
        margin-top: 80px;
        margin-bottom: 40px;
    }
        
    .tab-btn {
        font-size: 1.1rem;
        font-weight: 600;
        padding: 16px 0;
        color: var(--text-secondary);
        position: relative;
        cursor: pointer;
        transition: all 0.3s;
    }

    .tab-btn.active {
        color: var(--text-primary);
    }

    .tab-btn.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary);
    }
    
    .review-card {
        background: var(--bg-secondary);
        padding: 24px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
        
    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    /* Custom Toast */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .custom-toast {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-left: 4px solid var(--primary);
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        transform: translateX(100px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
        
    .custom-toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .custom-toast.success { border-left-color: var(--accent-green); }
    .custom-toast.error { border-left-color: var(--accent-red); }
    
    .toast-icon { font-size: 1.25rem; }
    .toast-message { font-weight: 500; color: var(--text-primary); }
';

include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<section class="section-sm" style="background: var(--bg-secondary); padding-top: 100px; padding-bottom: 20px;">
    <div class="container">
        <nav style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">
            <a href="<?php echo SITE_URL; ?>/index.php" style="color: var(--text-secondary);">Home</a>
            <i class="fas fa-chevron-right" style="margin: 0 12px; font-size: 0.75rem; opacity: 0.5;"></i>
            <a href="<?php echo SITE_URL; ?>/products.php" style="color: var(--text-secondary);">Products</a>
            <i class="fas fa-chevron-right" style="margin: 0 12px; font-size: 0.75rem; opacity: 0.5;"></i>
            <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>" style="color: var(--text-secondary);">
                <?php echo $product['category_name']; ?>
            </a>
            <i class="fas fa-chevron-right" style="margin: 0 12px; font-size: 0.75rem; opacity: 0.5;"></i>
            <span style="color: var(--text-primary);"><?php echo $product['name']; ?></span>
        </nav>
    </div>
</section>

<!-- Product Main Section -->
<section class="section product-page-container" style="background: var(--bg-primary);">
    <div class="container">
        <div class="product-layout">
            <!-- Left Column: Gallery -->
            <div class="gallery-container">
                <div class="main-image-wrapper">
                    <img id="mainImage" class="main-image"
                         src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $productImages[0] ?? $product['featured_image'] ?: 'placeholder.jpg'; ?>" 
                         alt="<?php echo $product['name']; ?>"
                         onerror="this.src='https://via.placeholder.com/600x600/f5f5f7/86868b?text=<?php echo urlencode($product['name']); ?>'">
                </div>
                
                <?php if (count($productImages) > 1): ?>
                <div class="thumbnails-grid">
                    <?php foreach ($productImages as $index => $image): ?>
                    <button class="thumbnail-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                            onclick="changeImage('<?php echo SITE_URL; ?>/assets/images/products/<?php echo $image; ?>', this)">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $image; ?>" 
                             alt="View <?php echo $index + 1; ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Info -->
            <div class="product-info-wrapper">
                <span class="product-category-badge">
                    <?php echo $product['category_name']; ?>
                </span>
                
                <h1 class="product-title"><?php echo $product['name']; ?></h1>
                
                <div class="rating-row">
                    <div style="color: #FFB400;">
                         <?php 
                            $stars = round($averageRating * 2) / 2; // Round to nearest 0.5
                            for($i=1; $i<=5; $i++): 
                        ?>
                            <i class="<?php echo $i <= $stars ? 'fas' : ($i - 0.5 <= $stars ? 'fas fa-star-half-alt' : 'far'); ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span style="color: var(--text-secondary); font-weight: 500;"><?php echo $averageRating; ?> (<?php echo $totalReviews; ?> Review<?php echo $totalReviews !== 1 ? 's' : ''; ?>)</span>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span style="color: var(--accent-green); margin-left: auto; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-check-circle"></i> In Stock
                        </span>
                    <?php else: ?>
                        <span style="color: var(--accent-red); margin-left: auto; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="price-container">
                    <span class="current-price"><?php echo formatPrice($product['sale_price'] ?: $product['price']); ?></span>
                    <?php if ($product['sale_price']): ?>
                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                        <span class="discount-badge">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="description-text">
                    <?php echo nl2br($product['short_description'] ?: substr($product['description'], 0, 200) . '...'); ?>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                <form id="addToCartForm" class="actions-wrapper">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 24px;">
                       <label style="font-weight: 600; color: var(--text-primary);">Quantity</label>
                       <div class="quantity-control">
                            <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                            <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                            <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="actions-container">
                        <button type="button" class="btn-add-cart">
                            <i class="fas fa-shopping-bag"></i>
                            Add to Cart
                        </button>
                        <button type="button" class="btn-buy-now">
                            <i class="fas fa-bolt"></i>
                            Buy Now
                        </button>
                        <button type="button" class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>, this)" style="
                            height: 56px;
                            width: 56px;
                            border-radius: 50%;
                            border: 1px solid var(--border-color);
                            background: var(--bg-secondary);
                            color: var(--text-secondary);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            cursor: pointer;
                            transition: all 0.2s;
                        ">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    
                </form>
                <?php else: ?>
                    <div class="alert alert-error" style="border-radius: 12px;">
                        This item is currently out of stock.
                    </div>
                <?php endif; ?>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-truck feature-icon"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">Free Shipping</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">On orders over ₹999</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-undo feature-icon"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">Easy Returns</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">14-day return policy</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">Secure Checkout</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">SSL Encrypted</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs Section -->
        <div class="tabs-header">
            <div class="tab-btn active" onclick="switchTab('description', this)">Description</div>
            <div class="tab-btn" onclick="switchTab('specs', this)">Specifications</div>
            <div class="tab-btn" onclick="switchTab('reviews', this)">Reviews (<?php echo $totalReviews; ?>)</div>
        </div>
        
        <div id="description" class="tab-content">
            <div style="max-width: 800px; color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem;">
                <h3 style="color: var(--text-primary); margin-bottom: 20px;">Product Details</h3>
                <?php echo nl2br($product['description'] ?: 'No detailed description available.'); ?>
            </div>
        </div>
        
        <div id="specs" class="tab-content" style="display: none;">
             <table style="width: 100%; max-width: 600px; border-collapse: collapse;">
                <?php 
                $specs = [
                    'SKU' => $product['sku'],
                    'Brand' => $product['brand'],
                    'Weight' => $product['weight'] ? $product['weight'] . ' kg' : null,
                    'Dimensions' => $product['dimensions']
                ];
                foreach ($specs as $label => $value): if ($value): 
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 16px 0; color: var(--text-secondary); font-weight: 500;"><?php echo $label; ?></td>
                    <td style="padding: 16px 0; color: var(--text-primary); font-weight: 600; text-align: right;"><?php echo $value; ?></td>
                </tr>
                <?php endif; endforeach; ?>
            </table>
        </div>