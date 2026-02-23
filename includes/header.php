<?php
/**
 * VoltStore - Header Template
 * 
 * @package VoltStore
 * @version 1.0
 */

// Ensure config is loaded
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/db.php';
}
require_once __DIR__ . '/functions.php';

// Get cart count if user is logged in
$cartCount = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>