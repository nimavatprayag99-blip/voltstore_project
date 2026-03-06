
(function () {
    'use strict';

    // ==========================================
    // INTERSECTION OBSERVER FOR SCROLL ANIMATIONS
    // ==========================================

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                // Add delay if specified
                const delay = entry.target.dataset.delay || 0;

                setTimeout(() => {
                    entry.target.classList.add('visible');

                    // Trigger counter animation for stat cards
                    if (entry.target.classList.contains('stat-card')) {
                        animateCounter(entry.target);
                    }
                }, parseInt(delay));
                
                // Stop observing once animated
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all animated elements
    function observeElements() {
        const animatedElements = document.querySelectorAll(
            '.fade-in-up, .fade-in-left, .fade-in-right'
        );

        animatedElements.forEach(el => {
            observer.observe(el);
        });
    }

    // ==========================================
    // COUNTER ANIMATION
    // ==========================================

    function animateCounter(card) {
        const numberElement = card.querySelector('.stat-number');
        if (!numberElement || numberElement.dataset.animated) return;

        const target = parseFloat(numberElement.dataset.target);
        const decimals = parseInt(numberElement.dataset.decimals) || 0;
        const duration = 2000; // 2 seconds
        const startTime = Date.now();
        const startValue = 0;

        function updateCounter() {
            const currentTime = Date.now();
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (easeOutCubic)
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (target - startValue) * easeProgress;

            // Format number
            if (decimals > 0) {
                numberElement.textContent = currentValue.toFixed(decimals);
            } else {
                numberElement.textContent = Math.floor(currentValue).toLocaleString();
            }
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                numberElement.dataset.animated = 'true';
            }
        }

        requestAnimationFrame(updateCounter);
    }

    // ==========================================
    // HERO ANIMATIONS ON LOAD
    // ==========================================

    function animateHeroElements() {
        const heroElements = document.querySelectorAll('.about-hero .fade-in-up');

        heroElements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add('visible');
            }, index * 100);
        });
    }
    
    // ==========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ==========================================

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '') return;

                e.preventDefault();
                const target = document.querySelector(href);

                if (target) {
                    const navbarHeight = document.querySelector('.navbar')?.offsetHeight || 0;
                    const targetPosition = target.offsetTop - navbarHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // ==========================================
    // PARALLAX EFFECT FOR HERO BACKGROUND
    // ==========================================

    function initParallax() {
        const heroBackground = document.querySelector('.hero-background');
        if (!heroBackground) return;

        let ticking = false;

        function updateParallax() {
            const scrolled = window.pageYOffset;
            const heroHeight = document.querySelector('.about-hero')?.offsetHeight || 0;

            if (scrolled < heroHeight) {
                heroBackground.style.transform = `translateY(${scrolled * 0.5}px)`;
            }

            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });
    }
    
    // ==========================================
    // FEATURE CARD TILT EFFECT (OPTIONAL)
    // ==========================================

    function initCardTilt() {
        const cards = document.querySelectorAll('.feature-card');

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;

                card.style.transform = `
                    perspective(1000px)
                    rotateX(${rotateX}deg)
                    rotateY(${rotateY}deg)
                    translateY(-12px)
                `;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    // ==========================================
    // INITIALIZATION
    // ==========================================

    function init() {
        // Animate hero on page load
        animateHeroElements();

        // Setup scroll-triggered animations
        observeElements();

        // Enable smooth scrolling
        initSmoothScroll();

        // Add parallax effect
        initParallax();

        // Optional: Add card tilt effect (disable if performance issues)
        // initCardTilt();
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
