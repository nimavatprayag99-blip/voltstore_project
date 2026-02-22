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