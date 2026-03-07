<?php
/**
 * UrbanCart - Products Listing Page
 * 
 * @package UrbanCart
 * @version 1.0
 */

require_once __DIR__ . '/config/db.php';

// Get filter parameters
$category = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 1000000);
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$whereConditions = ['p.status = 1'];
$params = [];

if ($category) {
    $whereConditions[] = 'c.slug = ?';
    $params[] = $category;
}

if ($search) {
    $whereConditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($minPrice > 0) {
    $whereConditions[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = $minPrice;
}

if ($maxPrice < 1000000) {
    $whereConditions[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = $maxPrice;
}

$whereClause = implode(' AND ', $whereConditions);

// Sort options
$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'price_low':
        $orderBy = 'COALESCE(p.sale_price, p.price) ASC';
        break;
    case 'price_high':
        $orderBy = 'COALESCE(p.sale_price, p.price) DESC';
        break;
    case 'name':
        $orderBy = 'p.name ASC';
        break;
    case 'popular':
        $orderBy = 'p.id DESC';
        break;
}

// Get total count
$totalProducts = 0;
try {
    $db = getDB();
    $countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $totalProducts = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Product count error: " . $e->getMessage());
}

$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;

// Get products
$products = [];
try {
    $db = getDB();
    $sql = "
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereClause 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Products fetch error: " . $e->getMessage());
}