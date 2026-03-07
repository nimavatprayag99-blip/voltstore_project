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
