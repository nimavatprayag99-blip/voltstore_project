/**
 * VoltStore - Premium Navigation JavaScript
 * 
 * Features:
 * - Sticky navbar on scroll
 * - Mega menu hover delay
 * - Mobile menu toggle
 * - Smooth scroll performance
 * 
 * @package VoltStore
 * @version 2.0
 */

(function () {
    'use strict';

    // ===== PREMIUM NAVBAR SCROLL BEHAVIOR =====
    let lastScrollPosition = 0;
    let scrollDirection = 'up';
    const navbar = document.querySelector('.navbar');
    let ticking = false;
    const scrollThreshold = 10; // Minimum scroll distance to trigger changes
    const hideThreshold = 100; // Scroll position where hiding starts

    function updateNavbar(scrollPos) {
        // Determine scroll direction
        if (scrollPos > lastScrollPosition && scrollPos > hideThreshold) {
            scrollDirection = 'down';
        } else if (scrollPos < lastScrollPosition) {
            scrollDirection = 'up';
        }

        // Add scrolled class for compact mode
        if (scrollPos > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }

        // Auto-hide navbar on scroll down, reveal on scroll up
        if (Math.abs(scrollPos - lastScrollPosition) > scrollThreshold) {
            if (scrollDirection === 'down' && scrollPos > hideThreshold) {
                navbar.classList.add('navbar-hidden');
                navbar.classList.remove('navbar-visible');
            } else if (scrollDirection === 'up') {
                navbar.classList.remove('navbar-hidden');
                navbar.classList.add('navbar-visible');
            }
        }

        // Always show navbar at top of page
        if (scrollPos < 100) {
            navbar.classList.remove('navbar-hidden');
            navbar.classList.add('navbar-visible');
        }

        lastScrollPosition = scrollPos;
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        const scrollPos = window.pageYOffset;

        if (!ticking) {
            window.requestAnimationFrame(function () {
                updateNavbar(scrollPos);
            });
            ticking = true;
        }
    }, { passive: true });