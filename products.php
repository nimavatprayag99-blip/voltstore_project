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

// Get all categories for filter
$categories = [];
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 1 
        WHERE c.status = 1 
        GROUP BY c.id 
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Categories error: " . $e->getMessage());
}

$pageTitle = $search ? "Search: $search" : ($category ? ucfirst($category) : 'All Products');
include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="section-sm" style="background: var(--bg-secondary); padding-top: 100px;">
    <div class="container">
        <nav style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
            <i class="fas fa-chevron-right" style="margin: 0 8px; font-size: 0.75rem;"></i>
            <span>Products</span>
            <?php if ($category): ?>
            <i class="fas fa-chevron-right" style="margin: 0 8px; font-size: 0.75rem;"></i>
            <span><?php echo ucfirst($category); ?></span>
            <?php endif; ?>
        </nav>
        
        <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--text-primary);">
            <?php echo $search ? "Search Results for \"$search\"" : ($category ? ucfirst($category) : 'All Products'); ?>
        </h1>
        <p style="color: var(--text-secondary); margin-top: 8px;">
            Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
        </p>
    </div>
</section>

<!-- Products Section -->
<section class="section" style="background: var(--bg-primary);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px;">
            <!-- Sidebar Filters -->
            <aside style="position: sticky; top: 80px; height: fit-content;">
                <div style="background: var(--bg-secondary); border-radius: 18px; padding: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                        <h3 style="font-size: 1.125rem; font-weight: 600;">
                            <i class="fas fa-filter" style="margin-right: 8px;"></i>
                            Filters
                        </h3>
                        <?php if ($category || $search || $minPrice > 0 || $maxPrice < 1000000): ?>
                        <a href="<?php echo SITE_URL; ?>/products.php" style="font-size: 0.875rem; color: var(--primary);">
                            Clear All
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Search Filter -->
                    <div style="margin-bottom: 24px;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                            Search
                        </h4>
                        <form action="" method="GET" style="display: flex; gap: 8px;">
                            <input type="text" name="search" value="<?php echo $search; ?>" 
                                   placeholder="Search products..."
                                   style="flex: 1; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.875rem;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Categories Filter -->
                    <div style="margin-bottom: 24px;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                            Categories
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($categories as $cat): ?>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9375rem;">
                                <input type="radio" name="category_filter" 
                                       <?php echo $category === $cat['slug'] ? 'checked' : ''; ?>
                                       onchange="window.location.href='<?php echo $category === $cat['slug'] ? SITE_URL . '/products.php' : SITE_URL . '/products.php?category=' . $cat['slug']; ?>'"
                                       style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <span><?php echo $cat['name']; ?></span>
                                <span style="margin-left: auto; color: var(--text-muted); font-size: 0.8125rem;">
                                    (<?php echo $cat['product_count']; ?>)
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Filter -->
                    <div>
                        <h4 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                            Price Range
                        </h4>
                        <form action="" method="GET" style="display: flex; flex-direction: column; gap: 12px;">
                            <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?php echo $category; ?>">
                            <?php endif; ?>
                            <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo $search; ?>">
                            <?php endif; ?>
                            
                            <div style="display: flex; gap: 8px;">
                                <input type="number" name="min_price" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" 
                                       placeholder="Min" min="0"
                                       style="width: 50%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.875rem;">
                                <input type="number" name="max_price" value="<?php echo $maxPrice < 1000000 ? $maxPrice : ''; ?>" 
                                       placeholder="Max" min="0"
                                       style="width: 50%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.875rem;">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-full">
                                Apply Filter
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div>
                <!-- Sort Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                    <p style="color: var(--text-secondary); font-size: 0.9375rem;">
                        Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $totalProducts); ?> of <?php echo $totalProducts; ?> results
                    </p>
                    
                    <form action="" method="GET" style="display: flex; align-items: center; gap: 8px;">
                        <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                        <?php endif; ?>
                        <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo $search; ?>">
                        <?php endif; ?>
                        <?php if ($minPrice > 0): ?>
                        <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                        <?php endif; ?>
                        <?php if ($maxPrice < 1000000): ?>
                        <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                        <?php endif; ?>
                        
                        <label style="font-size: 0.9375rem; color: var(--text-secondary);">Sort by:</label>
                        <select name="sort" onchange="this.form.submit()" 
                                style="padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.875rem; background: white; cursor: pointer;">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </form>
                </div>