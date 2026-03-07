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
    
    // Validation
    if (!$productId || !$rating || $rating < 1 || $rating > 5) {
        setFlashMessage('error', 'Invalid rating or product.');
        redirect($_SERVER['HTTP_REFERER'] ?? SITE_URL);
    }

    if (empty($comment)) {
        setFlashMessage('error', 'Please write a comment for your review.');
        redirect($_SERVER['HTTP_REFERER'] . '#review-form');
    }

    try {
        $db = getDB();

        // Check if user already reviewed this product
        $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        
        if ($stmt->fetch()) {
            setFlashMessage('warning', 'You have already reviewed this product.');
            redirect($_SERVER['HTTP_REFERER'] . '#reviews');
        }

        // Insert review
        $stmt = $db->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $productId, $rating, $comment])) {
            setFlashMessage('success', 'Thank you! Your review has been submitted.');
        } else {
            setFlashMessage('error', 'Failed to submit review. Please try again.');
        }

    } catch (PDOException $e) {
        error_log("Review submission error: " . $e->getMessage());
        setFlashMessage('error', 'An error occurred. Please try again later.');
    }

    // Redirect back to product page
    redirect($_SERVER['HTTP_REFERER'] . '#reviews');
} else {
    redirect(SITE_URL);
}