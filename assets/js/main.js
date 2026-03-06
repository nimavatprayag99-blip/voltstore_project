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
