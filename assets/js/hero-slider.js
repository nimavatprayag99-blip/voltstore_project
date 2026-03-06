
(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        autoPlayDelay: 5000,        // 5 seconds per slide
        transitionDuration: 600,    // 600ms transition (faster)
        parallaxIntensity: 0.01,    // Reduced parallax
        tiltIntensity: 10,          // Reduced tilt angle
        pauseOnHover: true,         // Pause auto-play on hover
        enableParallax: false,      // Disabled for performance
        enableKeyboard: true,       // Enable keyboard navigation
        enableSwipe: true,          // Enable touch swipe navigation
        enable3DTilt: false         // Disabled for performance
    };

    // State
    let currentSlide = 1;
    let totalSlides = 0;
    let autoPlayTimer = null;
    let isTransitioning = false;
    let isPaused = false;