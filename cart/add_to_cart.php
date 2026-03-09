<?php
/**
 * UrbanCart - Add to Cart Handler
 * 
 * @package UrbanCart
 * @version 1.0
 */

ob_start(); // Start output buffering
require_once __DIR__ . '/../config/db.php';

// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$response = ['success' => false, 'message' => '', 'cartCount' => 0];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    sendResponse($response, $isAjax);
}