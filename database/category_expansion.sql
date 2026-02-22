-- =====================================================
-- VoltStore Multi-Category Expansion Migration
-- Version: 2.0
-- Description: Add support for Clothing, Furniture, Sports, Home & Kitchen, and Lifestyle categories
-- =====================================================

USE voltstore;

-- =====================================================
-- TABLE: product_attributes
-- Description: Store category-specific product attributes (size, color, material, etc.)
-- =====================================================
CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_attribute (attribute_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =====================================================
-- UPDATE EXISTING CATEGORIES - Add Parent Category
-- =====================================================

-- Create Electronics parent category
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Electronics', 'electronics', 'Cutting-edge technology and gadgets', NULL, 1);

SET @electronics_id = LAST_INSERT_ID();

-- Update existing categories to be children of Electronics
UPDATE categories SET parent_id = @electronics_id WHERE slug IN ('smartphones', 'laptops', 'audio', 'smartwatches', 'accessories');

-- =====================================================
-- INSERT NEW PARENT CATEGORIES
-- =====================================================

-- Clothing
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Clothing', 'clothing', 'Fashion for everyone - men, women, and kids', NULL, 1);

SET @clothing_id = LAST_INSERT_ID();

-- Furniture
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Furniture', 'furniture', 'Premium furniture for home and office', NULL, 1);

SET @furniture_id = LAST_INSERT_ID();

-- Sports & Fitness
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Sports & Fitness', 'sports-fitness', 'Stay active and healthy with our fitness gear', NULL, 1);

SET @sports_id = LAST_INSERT_ID();

-- Home & Kitchen
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Home & Kitchen', 'home-kitchen', 'Essential appliances and cookware for your home', NULL, 1);

SET @kitchen_id = LAST_INSERT_ID();

-- Lifestyle
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Lifestyle', 'lifestyle', 'Accessories, beauty, and lifestyle products', NULL, 1);

SET @lifestyle_id = LAST_INSERT_ID();

-- =====================================================
-- INSERT SUB-CATEGORIES
-- =====================================================

-- Clothing Sub-categories
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Men', 'men-clothing', 'Fashion and apparel for men', @clothing_id, 1),
('Women', 'women-clothing', 'Trendy clothing for women', @clothing_id, 1),
('Kids', 'kids-clothing', 'Comfortable clothing for children', @clothing_id, 1);

-- Furniture Sub-categories
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Living Room', 'living-room-furniture', 'Sofas, tables, and living room essentials', @furniture_id, 1),
('Bedroom', 'bedroom-furniture', 'Beds, wardrobes, and bedroom furniture', @furniture_id, 1),
('Office', 'office-furniture', 'Desks, chairs, and office essentials', @furniture_id, 1);

-- Sports & Fitness Sub-categories
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Fitness Equipment', 'fitness-equipment', 'Dumbbells, yoga mats, and workout gear', @sports_id, 1),
('Sportswear', 'sportswear', 'Athletic clothing and shoes', @sports_id, 1),
('Outdoor Gear', 'outdoor-gear', 'Camping, hiking, and outdoor equipment', @sports_id, 1);

-- Home & Kitchen Sub-categories
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Cookware', 'cookware', 'Pots, pans, and cooking essentials', @kitchen_id, 1),
('Kitchen Appliances', 'kitchen-appliances', 'Blenders, kettles, and kitchen gadgets', @kitchen_id, 1),
('Home Decor', 'home-decor', 'Decorative items and home accessories', @kitchen_id, 1);

-- Lifestyle Sub-categories
INSERT INTO categories (name, slug, description, parent_id, status) VALUES
('Fashion Accessories', 'fashion-accessories', 'Watches, sunglasses, and jewelry', @lifestyle_id, 1),
('Health & Beauty', 'health-beauty', 'Skincare, grooming, and wellness products', @lifestyle_id, 1),
('Bags & Luggage', 'bags-luggage', 'Backpacks, suitcases, and travel bags', @lifestyle_id, 1);