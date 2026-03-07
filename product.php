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