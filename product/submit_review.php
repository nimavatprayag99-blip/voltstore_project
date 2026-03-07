<?php
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to submit a review.');
    $redirectInfo = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/products.php';
    redirect($redirectInfo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request session. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? SITE_URL);
    }

    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = sanitize($_POST['comment'] ?? '');
    $userId = $_SESSION['user_id'];