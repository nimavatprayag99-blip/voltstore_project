# ⚡ VoltStore — E-Commerce Platform

A modern, full-featured e-commerce platform built with **PHP**, **MySQL**, and a premium dark-themed UI. VoltStore delivers a complete online shopping experience with product browsing, cart management, secure checkout, user accounts, and a powerful admin dashboard.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

### 🛍️ Storefront
- **Homepage** — Hero slider, featured products & category highlights
- **Product Catalog** — Browse, search & filter products by category
- **Product Pages** — Detailed views with images, pricing & descriptions
- **Category Pages** — Organized product listings per category

### 🛒 Shopping
- **Cart Management** — Add, update quantity & remove items
- **Secure Checkout** — Complete order placement flow
- **Order Confirmation** — Instant confirmation with order details

### 👤 User Accounts
- **Registration & Login** — Secure authentication with password hashing
- **User Profile** — View & edit account details
- **Order History** — Track past orders and status
- **Wishlist** — Save favourite products for later

### 🔐 Admin Panel
- **Dashboard** — Sales stats, recent orders & quick metrics
- **Product Management** — Add, edit & delete products
- **Category Management** — Create & organize product categories
- **Order Management** — View orders & update statuses
- **User Management** — View & manage registered users

### 🛡️ Security
- CSRF token protection on all forms
- PDO prepared statements (SQL injection prevention)
- Input sanitization & XSS protection
- Secure password hashing with `bcrypt`

---

## 🗂️ Project Structure

```
voltstore/
├── admin/                  # Admin panel
│   ├── dashboard.php       # Admin dashboard
│   ├── products.php        # Product management
│   ├── add_product.php     # Add new product
│   ├── categories.php      # Category management
│   ├── orders.php          # Order management
│   ├── order_view.php      # Order details
│   ├── users.php           # User management
│   ├── login.php           # Admin login
│   └── logout.php          # Admin logout
├── assets/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── images/             # Product & site images
├── cart/
│   ├── cart.php            # Shopping cart page
│   ├── checkout.php        # Checkout page
│   ├── order_confirmation.php
│   ├── add_to_cart.php     # Add item to cart
│   ├── update_cart.php     # Update cart quantities
│   └── remove_from_cart.php
├── config/
│   └── db.php              # Database config & helper functions
├── database/
│   ├── voltstore.sql       # Main database schema & seed data
│   └── category_expansion.sql
├── includes/
│   ├── header.php          # Site header & navigation
│   ├── footer.php          # Site footer
│   └── functions.php       # Shared utility functions
├── product/                # Product-related pages
├── user/
│   ├── profile.php         # User profile
│   ├── orders.php          # Order history
│   ├── wishlist.php        # User wishlist
│   ├── add_to_wishlist.php
│   └── remove_from_wishlist.php
├── index.php               # Homepage
├── products.php            # All products listing
├── product.php             # Single product page
├── category.php            # Category page
├── search.php              # Search functionality
├── login.php               # User login
├── register.php            # User registration
├── logout.php              # Logout handler
├── about.php               # About page
├── contact.php             # Contact page
├── install.php             # Installation script
├── setup_db.php            # Database setup helper
├── Dockerfile              # Docker image config
└── docker-compose.yml      # Docker Compose config
```

---

## 🚀 Getting Started

### Prerequisites

- **PHP** 8.0+
- **MySQL** 5.7+ / 8.0
- **Apache** with `mod_rewrite` enabled
- **XAMPP** / **WAMP** / **MAMP** (or any LAMP stack)

### Option 1 — XAMPP (Recommended)

1. **Clone** the repository into your XAMPP `htdocs` directory:
   ```bash
   git clone https://github.com/your-username/voltstore.git C:/xampp/htdocs/php/voltstore
   ```

2. **Import the database**:
   - Open **phpMyAdmin** → `http://localhost/phpmyadmin`
   - Create a new database named `voltstore`
   - Import `database/voltstore.sql`
   - *(Optional)* Import `database/category_expansion.sql` for additional categories

3. **Configure database** (if needed):
   - Edit `config/db.php` and update credentials:
     ```php
     define('DB_HOST', '127.0.0.1');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'voltstore');
     ```

4. **Launch**:
   - Start Apache & MySQL from XAMPP Control Panel
   - Visit → `http://localhost/php/voltstore`

### Option 2 — Docker

```bash
docker-compose up -d
```
The app will be available at `http://localhost:8080`. The database is automatically initialized from the SQL dump.

---

## 🔑 Default Credentials

| Role  | Email / Username        | Password   |
|-------|-------------------------|------------|
| Admin | `admin@voltstore.com`   | `password` |

> ⚠️ **Change the default admin password immediately after first login.**

---

## 🛠️ Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | PHP 8.2, PDO (MySQL)                |
| Database   | MySQL 8.0                           |
| Frontend   | HTML5, CSS3, JavaScript             |
| Server     | Apache (`mod_rewrite`)              |
| Container  | Docker, Docker Compose              |
| Currency   | ₹ INR                               |

---

## 📸 Pages Overview

| Page                | URL Path                         |
|---------------------|----------------------------------|
| Homepage            | `/`                              |
| All Products        | `/products.php`                  |
| Single Product      | `/product.php?id=`               |
| Category            | `/category.php?id=`              |
| Search              | `/search.php?q=`                 |
| Cart                | `/cart/cart.php`                  |
| Checkout            | `/cart/checkout.php`             |
| Login               | `/login.php`                     |
| Register            | `/register.php`                  |
| User Profile        | `/user/profile.php`              |
| User Orders         | `/user/orders.php`               |
| Wishlist            | `/user/wishlist.php`             |
| Admin Dashboard     | `/admin/dashboard.php`           |
| Admin Login         | `/admin/login.php`               |

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.