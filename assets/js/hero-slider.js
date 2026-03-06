
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
    
    // DOM Elements
    const slider = document.querySelector('.hero-slider');
    if (!slider) return;

    const slides = [...document.querySelectorAll('.hero-slide')];
    const dots = [...document.querySelectorAll('.slider-dot')];
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    const sliderContainer = document.querySelector('.hero-slider-container');

    totalSlides = slides.length;

    /**
     * Initialize the slider
     */
    function init() {
        // Preload images
        preloadImages();

        // Setup event listeners
        setupEventListeners();

        // Start autoplay
        startAutoPlay();

        // Initialize parallax
        if (CONFIG.enableParallax) {
            initParallax();
        }

        // Initialize 3D tilt (if enabled)
        if (CONFIG.enable3DTilt) {
            init3DTilt();
        }


    }