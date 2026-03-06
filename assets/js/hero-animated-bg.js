
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

    // Throttle mouse move events for performance
    let rafId = null;
    let lastX = 50;
    let lastY = 50;

    function updateMousePosition(e) {
        if (rafId) {
            return; // Already scheduled
        }

        rafId = requestAnimationFrame(() => {
            const rect = hero.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            // Smooth transition
            lastX = lastX + (x - lastX) * 0.1;
            lastY = lastY + (y - lastY) * 0.1;

            overlay.style.setProperty('--mouse-x', `${lastX}%`);
            overlay.style.setProperty('--mouse-y', `${lastY}%`);

            rafId = null;
        });
    }