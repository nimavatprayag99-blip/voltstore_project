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