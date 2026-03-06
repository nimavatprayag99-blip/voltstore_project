/**
 * VoltStore - Main JavaScript File
 * 
 * Features:
 * - Smooth animations and transitions
 * - 3D card hover effects
 * - Cart functionality
 * - Form validation
 * - Mobile menu toggle
 * - Scroll effects
 * - Lazy loading
 * 
 * @package VoltStore
 * @version 1.0
 */

(function () {
    'use strict';

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================

    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));

    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
    const throttle = (func, limit) => {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    // =====================================================
    // NAVIGATION
    // =====================================================

    const initNavigation = () => {
        const navbar = $('.navbar');
        const mobileMenuBtn = $('.mobile-menu-btn');
        const navMenu = $('.nav-menu');

        // Navbar scroll effect
        let lastScroll = 0;

        window.addEventListener('scroll', throttle(() => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        }, 100));
        
        // Mobile menu toggle
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenuBtn.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });
        }

        // Close mobile menu on link click
        $$('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuBtn?.classList.remove('active');
                navMenu?.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
    };
    
    // =====================================================
    // 3D CARD HOVER EFFECT
    // =====================================================

    const init3DCards = () => {
        const cards = $$('.product-card, .card-3d');

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px) scale(1.02)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    };

    // =====================================================
    // SCROLL ANIMATIONS
    // =====================================================

    const initScrollAnimations = () => {
        const animatedElements = $$('[data-animate]');

        if (!animatedElements.length) return;

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const animation = element.dataset.animate;
                    const delay = element.dataset.delay || 0;

                    setTimeout(() => {
                        element.classList.add(`animate-${animation}`);
                        element.style.opacity = '1';
                    }, delay * 100);

                    observer.unobserve(element);
                }
            });
        }, observerOptions);

        animatedElements.forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    };
    
    // =====================================================
    // LAZY LOADING IMAGES
    // =====================================================

    const initLazyLoading = () => {
        const lazyImages = $$('img[data-src]');

        if (!lazyImages.length) return;

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    };

    // =====================================================
    // CART FUNCTIONALITY
    // =====================================================

    const initCart = () => {
        // Add to cart buttons
        $$('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', handleAddToCart);
        });

        // Quantity selectors
        $$('.quantity-selector').forEach(selector => {
            const minusBtn = selector.querySelector('.qty-minus');
            const plusBtn = selector.querySelector('.qty-plus');
            const input = selector.querySelector('input');

            minusBtn?.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                    updateCartItem(input);
                }
            });

            plusBtn?.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.dataset.max) || 99;
                if (value < max) {
                    input.value = value + 1;
                    updateCartItem(input);
                }
            });

            input?.addEventListener('change', () => {
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.dataset.max) || 99;
                input.value = Math.min(Math.max(value, 1), max);
                updateCartItem(input);
            });
        });
        
        // Remove item buttons
        $$('.remove-item').forEach(btn => {
            btn.addEventListener('click', handleRemoveItem);
        });
    };

    const handleAddToCart = async (e) => {
        const btn = e.currentTarget;
        const productId = btn.dataset.productId;

        if (!productId) return;

        // Add loading state
        btn.classList.add('loading');
        btn.disabled = true;

        try {
            const csrfToken = $('meta[name="csrf-token"]')?.content;
            const response = await fetch('cart/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1&csrf_token=${csrfToken}`
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Product added to cart!', 'success');
                updateCartBadge(data.cartCount);
                animateCartIcon();
            } else {
                showNotification(data.message || 'Failed to add product', 'error');
            }
        } catch (error) {
            showNotification('Something went wrong. Please try again.', 'error');
        } finally {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    };
    
    const updateCartItem = debounce(async (input) => {
        const cartItemId = input.dataset.cartItemId;
        const quantity = input.value;

        try {
            const csrfToken = $('meta[name="csrf-token"]')?.content;
            const response = await fetch('cart/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_item_id=${cartItemId}&quantity=${quantity}&csrf_token=${csrfToken}`
            });

            const data = await response.json();

            if (data.success) {
                updateCartTotals(data.totals);
            } else {
                showNotification(data.message || 'Failed to update cart', 'error');
            }
        } catch (error) {
            console.error('Error updating cart:', error);
        }
    }, 500);

    const handleRemoveItem = async (e) => {
        const btn = e.currentTarget;
        const cartItemId = btn.dataset.cartItemId;

        if (!confirm('Are you sure you want to remove this item?')) return;

        try {
            const csrfToken = $('meta[name="csrf-token"]')?.content;
            const response = await fetch('cart/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_item_id=${cartItemId}&csrf_token=${csrfToken}`
            });
            
            const data = await response.json();

            if (data.success) {
                const cartItem = btn.closest('.cart-item');
                cartItem.style.opacity = '0';
                cartItem.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    cartItem.remove();
                    updateCartBadge(data.cartCount);
                    updateCartTotals(data.totals);

                    if (data.cartCount === 0) {
                        location.reload();
                    }
                }, 300);

                showNotification('Item removed from cart', 'success');
            }
        } catch (error) {
            showNotification('Failed to remove item', 'error');
        }
    };

    const updateCartBadge = (count) => {
        const badge = $('.cart-badge');
        if (badge) {
            badge.textContent = count;
            badge.classList.add('bounce');
            setTimeout(() => badge.classList.remove('bounce'), 500);
        }
    };

    const animateCartIcon = () => {
        const cartIcon = $('.nav-icon[href*="cart"]');
        if (cartIcon) {
            cartIcon.classList.add('pulse');
            setTimeout(() => cartIcon.classList.remove('pulse'), 500);
        }
    };
    
    const updateCartTotals = (totals) => {
        if (!totals) return;

        const subtotalEl = $('.cart-subtotal');
        const totalEl = $('.cart-total');

        if (subtotalEl) subtotalEl.textContent = formatPrice(totals.subtotal);
        if (totalEl) totalEl.textContent = formatPrice(totals.total);
    };

    // =====================================================
    // FORM VALIDATION
    // =====================================================

    const initFormValidation = () => {
        $$('form[data-validate]').forEach(form => {
            form.addEventListener('submit', validateForm);

            // Real-time validation
            $$('input, textarea, select', form).forEach(field => {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => clearError(field));
            });
        });
    };

    const validateForm = (e) => {
        const form = e.target;
        let isValid = true;

        $$('[data-validate]', form).forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }

        return isValid;
    };

    const validateField = (field) => {
        const rules = field.dataset.validate.split('|');
        const value = field.value.trim();
        let error = '';

        for (const rule of rules) {
            if (rule === 'required' && !value) {
                error = 'This field is required';
                break;
            }

            if (rule === 'email' && value && !isValidEmail(value)) {
                error = 'Please enter a valid email address';
                break;
            }
            
            if (rule.startsWith('min:')) {
                const min = parseInt(rule.split(':')[1]);
                if (value.length < min) {
                    error = `Must be at least ${min} characters`;
                    break;
                }
            }

            if (rule.startsWith('max:')) {
                const max = parseInt(rule.split(':')[1]);
                if (value.length > max) {
                    error = `Must be no more than ${max} characters`;
                    break;
                }
            }

            if (rule === 'phone' && value && !isValidPhone(value)) {
                error = 'Please enter a valid phone number';
                break;
            }
        }

        if (error) {
            showFieldError(field, error);
            return false;
        }

        clearError(field);
        return true;
    };

    const showFieldError = (field, message) => {
        clearError(field);

        field.classList.add('error');

        const errorEl = document.createElement('span');
        errorEl.className = 'form-error';
        errorEl.textContent = message;

        field.parentNode.appendChild(errorEl);
    };

    const clearError = (field) => {
        field.classList.remove('error');
        const errorEl = field.parentNode.querySelector('.form-error');
        if (errorEl) {
            errorEl.remove();
        }
    };

    const isValidEmail = (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };

    const isValidPhone = (phone) => {
        return /^[\d\s\-\+\(\)]{10,}$/.test(phone);
    };
    
    // =====================================================
    // PREMIUM TOAST NOTIFICATIONS
    // =====================================================

    const showNotification = (message, type = 'info') => {
        const existingNotification = $('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Icon mapping
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle',
            warning: 'fas fa-exclamation-triangle'
        };

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="${icons[type] || icons.info}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" aria-label="Close">&times;</button>
            <div class="notification-progress">
                <div class="notification-progress-bar"></div>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in (slide from bottom-right)
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Auto dismiss after 2.5 seconds
        const dismissTimeout = setTimeout(() => {
            dismissNotification(notification);
        }, 2500);

        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            clearTimeout(dismissTimeout);
            dismissNotification(notification);
        });
    };

    const dismissNotification = (notification) => {
        notification.classList.remove('show');
        notification.classList.add('hide');

        setTimeout(() => {
            notification.remove();
        }, 400);
    };
    
    // =====================================================
    // PRODUCT GALLERY
    // =====================================================

    const initProductGallery = () => {
        const thumbnails = $$('.product-thumbnail');
        const mainImage = $('.product-main-image img');

        if (!thumbnails.length || !mainImage) return;

        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                // Update active state
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');

                // Update main image with fade effect
                const newSrc = thumb.dataset.src || thumb.querySelector('img').src;

                mainImage.style.opacity = '0';

                setTimeout(() => {
                    mainImage.src = newSrc;
                    mainImage.style.opacity = '1';
                }, 200);
            });
        });
    };
