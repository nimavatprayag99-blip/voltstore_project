
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
    
    /**
     * Preload all slide images for smooth transitions
     */
    function preloadImages() {
        slides.forEach(slide => {
            const img = slide.querySelector('.product-image');
            if (img && img.src) {
                const preloadImg = new Image();
                preloadImg.src = img.src;
            }
        });
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        // Navigation buttons
        if (prevBtn) prevBtn.addEventListener('click', () => goToPreviousSlide());
        if (nextBtn) nextBtn.addEventListener('click', () => goToNextSlide());

        // Dot indicators
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const slideNum = parseInt(dot.dataset.slide);
                goToSlide(slideNum);
            });
        });
        
        // Pause on hover
        if (CONFIG.pauseOnHover) {
            slider.addEventListener('mouseenter', pauseAutoPlay);
            slider.addEventListener('mouseleave', resumeAutoPlay);
        }

        // Keyboard navigation
        if (CONFIG.enableKeyboard) {
            document.addEventListener('keydown', handleKeyboard);
        }

        // Touch swipe navigation
        if (CONFIG.enableSwipe && 'ontouchstart' in window) {
            initSwipeGestures();
        }

        // Visibility change (pause when tab is not visible)
        document.addEventListener('visibilitychange', handleVisibilityChange);
    }
    
    /**
     * Go to next slide
     */
    function goToNextSlide() {
        const nextSlide = currentSlide >= totalSlides ? 1 : currentSlide + 1;
        goToSlide(nextSlide);
    }

    /**
     * Go to previous slide
     */
    function goToPreviousSlide() {
        const prevSlide = currentSlide <= 1 ? totalSlides : currentSlide - 1;
        goToSlide(prevSlide);
    }

    /**
     * Go to specific slide
     */
    function goToSlide(slideNum) {
        if (isTransitioning || slideNum === currentSlide || slideNum < 1 || slideNum > totalSlides) {
            return;
        }

        isTransitioning = true;
        
        // Update active states
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => {
            dot.classList.remove('active');
            // Reset progress animation
            const progressBar = dot.querySelector('::before');
            if (progressBar) {
                dot.style.animation = 'none';
                void dot.offsetHeight; // Trigger reflow
                dot.style.animation = null;
            }
        });

        // Activate new slide
        const newSlide = slides[slideNum - 1];
        const newDot = dots[slideNum - 1];

        if (newSlide) newSlide.classList.add('active');
        if (newDot) newDot.classList.add('active');

        currentSlide = slideNum;

        // Reset transition lock after animation completes
        setTimeout(() => {
            isTransitioning = false;
        }, CONFIG.transitionDuration);

        // Restart autoplay timer
        resetAutoPlay();
    }
    
    /**
     * Start auto-play
     */
    function startAutoPlay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);

        autoPlayTimer = setInterval(() => {
            if (!isPaused && !isTransitioning) {
                goToNextSlide();
            }
        }, CONFIG.autoPlayDelay);
    }

    /**
     * Pause auto-play
     */
    function pauseAutoPlay() {
        isPaused = true;
    }

    /**
     * Resume auto-play
     */
    function resumeAutoPlay() {
        isPaused = false;
    }

    /**
     * Reset auto-play timer
     */
    function resetAutoPlay() {
        startAutoPlay();
    }

    /**
     * Handle keyboard navigation
     */
    function handleKeyboard(e) {
        if (e.key === 'ArrowLeft') {
            goToPreviousSlide();
        } else if (e.key === 'ArrowRight') {
            goToNextSlide();
        }
    }