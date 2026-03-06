
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
    
    /**
     * Handle visibility change (pause when tab is hidden)
     */
    function handleVisibilityChange() {
        if (document.hidden) {
            pauseAutoPlay();
        } else {
            resumeAutoPlay();
        }
    }

    /**
     * Initialize swipe gestures for touch devices
     */
    function initSwipeGestures() {
        let touchStartX = 0;
        let touchEndX = 0;
        const minSwipeDistance = 50;

        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const swipeDistance = touchEndX - touchStartX;

            if (Math.abs(swipeDistance) < minSwipeDistance) return;

            if (swipeDistance > 0) {
                // Swipe right - go to previous
                goToPreviousSlide();
            } else {
                // Swipe left - go to next
                goToNextSlide();
            }
        }
    }
    
    /**
     * Initialize parallax mouse tracking
     */
    function initParallax() {
        let mouseX = 0;
        let mouseY = 0;
        let currentX = 0;
        let currentY = 0;

        slider.addEventListener('mousemove', (e) => {
            const rect = slider.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            mouseX = (e.clientX - centerX) * CONFIG.parallaxIntensity;
            mouseY = (e.clientY - centerY) * CONFIG.parallaxIntensity;
        });

        // Smooth parallax animation using requestAnimationFrame
        function animateParallax() {
            // Smooth interpolation
            currentX += (mouseX - currentX) * 0.1;
            currentY += (mouseY - currentY) * 0.1;

            // Apply parallax to background
            const bgGradient = slider.querySelector('.bg-gradient');
            if (bgGradient) {
                bgGradient.style.transform = `translate(${currentX * 0.5}px, ${currentY * 0.5}px)`;
            }

            // Apply parallax to active slide text (opposite direction for depth)
            const activeSlide = slider.querySelector('.hero-slide.active');
            if (activeSlide) {
                const slideText = activeSlide.querySelector('.hero-slide-text');
                if (slideText) {
                    slideText.style.transform = `translate(${currentX * -0.3}px, ${currentY * -0.3}px)`;
                }
            }

            requestAnimationFrame(animateParallax);
        }

        animateParallax();
    }
    
    /**
     * Initialize 3D tilt effect on product images
     */
    function init3DTilt() {
        slides.forEach(slide => {
            const imageWrapper = slide.querySelector('.image-wrapper');
            const productImage = slide.querySelector('.product-image');

            if (!imageWrapper || !productImage) return;

            imageWrapper.addEventListener('mousemove', (e) => {
                if (!slide.classList.contains('active')) return;

                const rect = imageWrapper.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;

                const mouseX = e.clientX - centerX;
                const mouseY = e.clientY - centerY;

                const rotateX = (mouseY / rect.height) * CONFIG.tiltIntensity;
                const rotateY = (mouseX / rect.width) * CONFIG.tiltIntensity;

                // Apply 3D transform with smooth transition
                requestAnimationFrame(() => {
                    productImage.style.transform = `
                        perspective(1200px)
                        rotateX(${-rotateX}deg)
                        rotateY(${rotateY}deg)
                        scale3d(1.02, 1.02, 1.02)
                        translateZ(30px)
                    `;
                });
            });

            imageWrapper.addEventListener('mouseleave', () => {
                // Reset to original position
                requestAnimationFrame(() => {
                    productImage.style.transform = '';
                });
            });
        });
    }