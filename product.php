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