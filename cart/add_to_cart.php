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

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $response['message'] = 'Invalid request. Please refresh the page.';
    sendResponse($response, $isAjax);
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity = max(1, intval($_POST['quantity'] ?? 1));

if (!$productId) {
    $response['message'] = 'Product not found.';
    sendResponse($response, $isAjax);
}

// Verify product exists and is in stock
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, stock_quantity, stock_status FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $response['message'] = 'Product not found.';
        sendResponse($response, $isAjax);
    }
    
    if ($product['stock_status'] === 'out_of_stock' || $product['stock_quantity'] < 1) {
        $response['message'] = 'Product is out of stock.';
        sendResponse($response, $isAjax);
    }
    
    if ($quantity > $product['stock_quantity']) {
        $response['message'] = 'Only ' . $product['stock_quantity'] . ' units available.';
        sendResponse($response, $isAjax);
    }
    
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        
        // Check if product already in cart
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($newQuantity > $product['stock_quantity']) {
                $response['message'] = 'Cannot add more. Only ' . $product['stock_quantity'] . ' units available.';
                sendResponse($response, $isAjax);
            }
            
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Add new item
            $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity]);
        }
        
        // Get updated cart count
        $stmt = $db->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $response['cartCount'] = $result['count'] ?? 0;
        
    } else {
        // Guest cart - store in session
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        
        if (isset($_SESSION['guest_cart'][$productId])) {
            $newQuantity = $_SESSION['guest_cart'][$productId] + $quantity;
            
            if ($newQuantity > $product['stock_quantity']) {
                $response['message'] = 'Cannot add more. Only ' . $product['stock_quantity'] . ' units available.';
                sendResponse($response, $isAjax);
            }
            
            $_SESSION['guest_cart'][$productId] = $newQuantity;
        } else {
            $_SESSION['guest_cart'][$productId] = $quantity;
        }
        
        $response['cartCount'] = array_sum($_SESSION['guest_cart']);
    }
    
    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully!';
    
} catch (PDOException $e) {
    error_log("Add to cart error: " . $e->getMessage());
    $response['message'] = 'Something went wrong. Please try again.';
}

sendResponse($response, $isAjax);

// Helper to clean output
function cleanOutput() {
    if (ob_get_length()) ob_clean();
}

/**
 * Send response based on request type
 */
function sendResponse($response, $isAjax) {
    if ($isAjax) {
        cleanOutput(); // Ensure no previous output (warnings/notices) corrupts JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Regular form submission
        if ($response['success']) {
            setFlashMessage('success', $response['message']);
        } else {
            setFlashMessage('error', $response['message']);
        }
        redirect($_SERVER['HTTP_REFERER'] ?? SITE_URL . '/products.php');
    }
}
?>