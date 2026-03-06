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