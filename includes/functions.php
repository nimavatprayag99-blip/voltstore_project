<?php
/**
 * VoltStore - Helper Functions
 * 
 * Contains common database queries and helper logic to keep views clean.
 * 
 * @package VoltStore
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Get featured products
 * 
 * @param int $limit Number of products to return
 * @return array
 */
function getFeaturedProducts($limit = 8) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.featured = 1 AND p.status = 1 
            ORDER BY p.created_at DESC 
            LIMIT :limit
        ");
         $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Featured products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get categories with product counts
 * 
 * @param int $limit Number of categories to return
 * @return array
 */
function getCategoriesWithCount($limit = 6) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 1 
            WHERE c.status = 1 
            GROUP BY c.id 
            ORDER BY c.name 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Categories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get new arrivals
 * 
 * @param int $limit Number of products to return
 * @return array
 */
function getNewArrivals($limit = 4) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 1 
            ORDER BY p.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("New arrivals error: " . $e->getMessage());
        return [];
    }
}