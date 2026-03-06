
(function () {
    'use strict';

    // Check if reduced motion is preferred
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        return; // Skip mouse tracking if user prefers reduced motion
    }

    // Get hero section
    const hero = document.querySelector('.hero-slider');
    const overlay = document.querySelector('.bg-overlay-v2');

    if (!hero || !overlay) {
        return; // Elements not found, exit gracefully
    }
