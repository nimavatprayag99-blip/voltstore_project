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