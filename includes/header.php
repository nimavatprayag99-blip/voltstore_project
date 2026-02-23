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
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VoltStore - Your Premium E-Commerce Destination">
    <meta name="keywords" content="ecommerce, shopping, online store, products, electronics">
    <meta name="author" content="VoltStore">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%230071e3'/%3E%3Ctext x='50' y='68' font-size='50' text-anchor='middle' fill='white' font-family='Arial'%3EV%3C/text%3E%3C/svg%3E">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Premium Navbar Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/navbar-premium.css">
    
    <!-- Hero Slider Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/hero-slider.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/hero-animated-bg-v2.css">
    
    <!-- Page-specific styles -->
    <?php if (isset($pageStyles)): ?>
    <style><?php echo $pageStyles; ?></style>
    <?php endif; ?>
    
    <!-- Navigation Script -->
    <script src="<?php echo SITE_URL; ?>/assets/js/navigation.js" defer></script>
    
    <!-- Hero Slider Script -->
    <script src="<?php echo SITE_URL; ?>/assets/js/hero-slider.js" defer></script>

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-inner">
                <!-- Logo -->
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    <div class="logo-icon">V</div>
                    <span><?php echo SITE_NAME; ?></span>
                </a>
                
                <!-- Navigation Menu -->
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a></li>
                    
                    <!-- Categories Mega Menu -->
                    <li class="has-dropdown">
                        <a href="<?php echo SITE_URL; ?>/products.php" class="nav-link <?php echo $currentPage === 'products' || $currentPage === 'category' ? 'active' : ''; ?>">
                            Categories <i class="fas fa-chevron-down" style="font-size: 0.75rem; margin-left: 4px;"></i>
                        </a>
                        <div class="mega-menu">
                            <div class="mega-menu-inner">
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=electronics" class="mega-menu-header">
                                        <i class="fas fa-laptop"></i> Electronics
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=smartphones">Smartphones</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=laptops">Laptops</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=audio">Audio</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=smartwatches">Smartwatches</a>
                                </div>
                                
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=clothing" class="mega-menu-header">
                                        <i class="fas fa-tshirt"></i> Clothing
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=men-clothing">Men</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=women-clothing">Women</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=kids-clothing">Kids</a>
                                </div>
                                
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=furniture" class="mega-menu-header">
                                        <i class="fas fa-couch"></i> Furniture
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=living-room-furniture">Living Room</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=bedroom-furniture">Bedroom</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=office-furniture">Office</a>
                                </div>
                                
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=sports-fitness" class="mega-menu-header">
                                        <i class="fas fa-dumbbell"></i> Sports & Fitness
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=fitness-equipment">Fitness Equipment</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=sportswear">Sportswear</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=outdoor-gear">Outdoor Gear</a>
                                </div>
                                
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=home-kitchen" class="mega-menu-header">
                                        <i class="fas fa-utensils"></i> Home & Kitchen
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=cookware">Cookware</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=kitchen-appliances">Kitchen Appliances</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=home-decor">Home Decor</a>
                                </div>
                                
                                <div class="mega-menu-column">
                                    <a href="<?php echo SITE_URL; ?>/category.php?slug=lifestyle" class="mega-menu-header">
                                        <i class="fas fa-gem"></i> Lifestyle
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=fashion-accessories">Fashion Accessories</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=health-beauty">Health & Beauty</a>
                                    <a href="<?php echo SITE_URL; ?>/products.php?category=bags-luggage">Bags & Luggage</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
                 
                <!-- Navigation Actions -->
                <div class="nav-actions">
                    <!-- Search -->
                    <a href="<?php echo SITE_URL; ?>/products.php" class="nav-icon" title="Search">
                        <i class="fas fa-search"></i>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <!-- User Account -->
                        <a href="<?php echo SITE_URL; ?>/user/profile.php" class="nav-icon" title="My Account">
                            <i class="fas fa-user"></i>
                        </a>
                        
                        <!-- Wishlist -->
                        <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="nav-icon" title="Wishlist">
                            <i class="far fa-heart"></i>
                        </a>
                        
                        <!-- Cart -->
                        <a href="<?php echo SITE_URL; ?>/cart/cart.php" class="nav-icon" title="Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Logout -->
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-icon" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                        <?php else: ?>
                        <!-- Login -->
                        <a href="<?php echo SITE_URL; ?>/login.php" class="nav-icon" title="Login">
                            <i class="fas fa-user"></i>
                        </a>
                        
                        <!-- Wishlist -->
                        <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="nav-icon" title="Wishlist">
                            <i class="far fa-heart"></i>
                        </a>
                        
                        <!-- Cart (for guests) -->
                        <a href="<?php echo SITE_URL; ?>/cart/cart.php" class="nav-icon" title="Cart">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Button -->
                    <button class="mobile-menu-btn" aria-label="Toggle Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu Drawer -->
    <div class="mobile-menu">
        <div class="mobile-menu-header">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                <div class="logo-icon">V</div>
                <span><?php echo SITE_NAME; ?></span>
            </a>
            <button class="mobile-menu-close" aria-label="Close Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
         
        <nav class="mobile-menu-links">
            <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-menu-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="<?php echo SITE_URL; ?>/products.php" class="mobile-menu-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> All Products
            </a>
            <a href="<?php echo SITE_URL; ?>/about.php" class="mobile-menu-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i> About
            </a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="mobile-menu-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Contact
            </a>
        </nav>
        
        <div class="mobile-menu-category">
            <div class="mobile-menu-category-title">Shop by Category</div>
            
            <a href="<?php echo SITE_URL; ?>/category.php?slug=electronics" class="mobile-menu-category-item">
                <i class="fas fa-laptop"></i>
                <span>Electronics</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/category.php?slug=clothing" class="mobile-menu-category-item">
                <i class="fas fa-tshirt"></i>
                <span>Clothing</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/category.php?slug=furniture" class="mobile-menu-category-item">
                <i class="fas fa-couch"></i>
                <span>Furniture</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/category.php?slug=sports-fitness" class="mobile-menu-category-item">
                <i class="fas fa-dumbbell"></i>
                <span>Sports & Fitness</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/category.php?slug=home-kitchen" class="mobile-menu-category-item">
                <i class="fas fa-utensils"></i>
                <span>Home & Kitchen</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/category.php?slug=lifestyle" class="mobile-menu-category-item">
                <i class="fas fa-gem"></i>
                <span>Lifestyle</span>
            </a>
        </div>
        
        <?php if (isLoggedIn()): ?>
        <div class="mobile-menu-category">
            <div class="mobile-menu-category-title">My Account</div>
            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="mobile-menu-category-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/cart/cart.php" class="mobile-menu-category-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart <?php if ($cartCount > 0) echo "($cartCount)"; ?></span>
            </a>
            <a href="<?php echo SITE_URL; ?>/logout.php" class="mobile-menu-category-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
        <?php else: ?>
        <div class="mobile-menu-category">
            <a href="<?php echo SITE_URL; ?>/login.php" class="mobile-menu-category-item">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); if ($flash): ?>
    <div class="container" style="padding-top: 80px;">
        <div class="alert alert-<?php echo $flash['type']; ?> animate-fadeIn">
            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <span><?php echo sanitize($flash['message']); ?></span>
        </div>
    </div>
    <?php endif; ?>