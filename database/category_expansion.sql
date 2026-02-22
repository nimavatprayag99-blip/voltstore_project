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

-- =====================================================
-- INSERT SAMPLE PRODUCTS - CLOTHING
-- =====================================================

-- Get category IDs
SET @men_cat = (SELECT id FROM categories WHERE slug = 'men-clothing');
SET @women_cat = (SELECT id FROM categories WHERE slug = 'women-clothing');
SET @kids_cat = (SELECT id FROM categories WHERE slug = 'kids-clothing');

INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock_quantity, category_id, featured_image, brand, featured, status) VALUES
-- Men's Clothing
('Classic Cotton T-Shirt', 'mens-cotton-tshirt', 'Premium quality 100% cotton t-shirt. Comfortable, breathable, and perfect for everyday wear. Available in multiple colors.', 'Soft cotton t-shirt for daily comfort', 799.00, 599.00, 'MEN-TSHIRT-001', 150, @men_cat, 'mens-tshirt.jpg', 'VoltFashion', 1, 1),
('Slim Fit Denim Jeans', 'mens-denim-jeans', 'Stylish slim-fit jeans made from premium denim. Features 5-pocket design and comfortable stretch fabric.', 'Modern slim-fit jeans', 1999.00, 1499.00, 'MEN-JEANS-001', 100, @men_cat, 'mens-jeans.jpg', 'DenimPro', 1, 1),
('Formal Cotton Shirt', 'mens-formal-shirt', 'Crisp white formal shirt perfect for office or special occasions. Easy-iron fabric with modern fit.', 'Professional formal shirt', 1299.00, 999.00, 'MEN-SHIRT-001', 80, @men_cat, 'mens-shirt.jpg', 'FormalWear', 0, 1),

-- Women's Clothing
('Floral Print Summer Dress', 'womens-summer-dress', 'Lightweight and breezy summer dress with beautiful floral print. Perfect for casual outings and beach wear.', 'Elegant summer dress', 1899.00, 1399.00, 'WMN-DRESS-001', 120, @women_cat, 'womens-dress.jpg', 'StyleHer', 1, 1),
('High-Waist Skinny Jeans', 'womens-skinny-jeans', 'Trendy high-waist skinny jeans that combine style and comfort. Premium stretch denim for all-day wear.', 'Fashionable skinny jeans', 2199.00, 1699.00, 'WMN-JEANS-001', 90, @women_cat, 'womens-jeans.jpg', 'DenimPro', 1, 1),
('Casual Cotton Top', 'womens-cotton-top', 'Versatile cotton top suitable for both casual and semi-formal occasions. Available in various colors.', 'Comfortable everyday top', 899.00, 699.00, 'WMN-TOP-001', 130, @women_cat, 'womens-top.jpg', 'CasualChic', 0, 1),

-- Kids' Clothing
('Kids Graphic T-Shirt', 'kids-graphic-tshirt', 'Fun and colorful graphic t-shirt for kids. Soft cotton fabric, durable print, perfect for active children.', 'Playful kids t-shirt', 499.00, 399.00, 'KIDS-TSHIRT-001', 200, @kids_cat, 'kids-tshirt.jpg', 'KidZone', 1, 1),
('Kids Denim Shorts', 'kids-denim-shorts', 'Comfortable denim shorts for kids. Adjustable waist, durable construction for everyday play.', 'Durable kids shorts', 699.00, 549.00, 'KIDS-SHORTS-001', 150, @kids_cat, 'kids-shorts.jpg', 'PlayWear', 0, 1);

-- =====================================================
-- INSERT SAMPLE PRODUCTS - FURNITURE
-- =====================================================

SET @living_cat = (SELECT id FROM categories WHERE slug = 'living-room-furniture');
SET @bedroom_cat = (SELECT id FROM categories WHERE slug = 'bedroom-furniture');
SET @office_cat = (SELECT id FROM categories WHERE slug = 'office-furniture');

INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock_quantity, category_id, featured_image, brand, featured, status) VALUES
-- Living Room
('3-Seater Fabric Sofa', '3-seater-fabric-sofa', 'Elegant 3-seater sofa with premium fabric upholstery. Comfortable seating with solid wooden frame. Dimensions: 200cm x 90cm x 85cm.', 'Comfortable 3-seater sofa', 34999.00, 29999.00, 'SOFA-3SEAT-001', 25, @living_cat, 'sofa-3seater.jpg', 'HomeFurniture', 1, 1),
('Modern Coffee Table', 'modern-coffee-table', 'Contemporary wooden coffee table with elegant finish. Features storage shelf and sturdy construction. 120cm x 60cm x 45cm.', 'Stylish coffee table', 8999.00, 7499.00, 'TABLE-COFFEE-001', 40, @living_cat, 'coffee-table.jpg', 'WoodCraft', 1, 1),
('TV Unit Stand', 'tv-unit-stand', 'Spacious TV unit with multiple storage compartments. Supports TVs up to 65 inches. Made from engineered wood.', 'Modern TV stand', 12999.00, 10999.00, 'TV-UNIT-001', 30, @living_cat, 'tv-unit.jpg', 'MediaFurniture', 0, 1),

-- Bedroom
('Queen Size Bed Frame', 'queen-size-bed-frame', 'Solid wood queen size bed frame with elegant headboard. Strong construction, easy assembly. Dimensions: 200cm x 160cm.', 'Premium wooden bed', 24999.00, 21999.00, 'BED-QUEEN-001', 20, @bedroom_cat, 'queen-bed.jpg', 'SleepWell', 1, 1),
('Sliding Door Wardrobe', 'sliding-door-wardrobe', 'Spacious 3-door sliding wardrobe with hanging space and shelves. Perfect for organizing clothing. 180cm x 60cm x 200cm.', 'Large storage wardrobe', 18999.00, 15999.00, 'WARDROBE-3D-001', 15, @bedroom_cat, 'wardrobe.jpg', 'StoragePlus', 1, 1),

-- Office
('Ergonomic Office Chair', 'ergonomic-office-chair', 'Professional ergonomic chair with lumbar support and adjustable height. Breathable mesh back, comfortable for long hours.', 'Comfortable office chair', 7999.00, 6499.00, 'CHAIR-ERGO-001', 50, @office_cat, 'office-chair.jpg', 'WorkComfort', 1, 1),
('Executive Study Table', 'executive-study-table', 'Spacious study table with built-in drawers and cable management. Premium finish, durable construction. 150cm x 75cm.', 'Modern study desk', 11999.00, 9999.00, 'DESK-EXEC-001', 35, @office_cat, 'study-table.jpg', 'OfficePro', 1, 1);