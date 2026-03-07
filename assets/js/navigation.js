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
    
    // ===== MEGA MENU HOVER DELAY =====
    const dropdowns = document.querySelectorAll('.has-dropdown');
    let menuOpenTimeout;
    let menuCloseTimeout;

    dropdowns.forEach(dropdown => {
        const megaMenu = dropdown.querySelector('.mega-menu');

        dropdown.addEventListener('mouseenter', function () {
            clearTimeout(menuCloseTimeout);

            menuOpenTimeout = setTimeout(() => {
                megaMenu.style.opacity = '1';
                megaMenu.style.visibility = 'visible';
                megaMenu.style.pointerEvents = 'auto';
                megaMenu.style.transform = 'translateX(-50%) translateY(0)';
            }, 150);
        });

        dropdown.addEventListener('mouseleave', function () {
            clearTimeout(menuOpenTimeout);

            menuCloseTimeout = setTimeout(() => {
                megaMenu.style.opacity = '0';
                megaMenu.style.visibility = 'hidden';
                megaMenu.style.pointerEvents = 'none';
                megaMenu.style.transform = 'translateX(-50%) translateY(-10px)';
            }, 300);
        });
    });

    // ===== MOBILE MENU TOGGLE =====
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');

    // Create overlay if it doesn't exist
    let overlay = document.querySelector('.mobile-menu-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        document.body.appendChild(overlay);
    }

    function openMobileMenu() {
        mobileMenu.classList.add('active');
        overlay.classList.add('active');
        mobileMenuBtn.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        overlay.classList.remove('active');
        mobileMenuBtn.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function () {
            if (mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }

    // Close mobile menu on window resize (desktop)
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth > 1024) {
                closeMobileMenu();
            }
        }, 250);
    });
    
    // ===== ACTIVE LINK HIGHLIGHTING =====
    const currentPage = window.location.pathname.split('/').pop().split('.')[0] || 'index';
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop().split('.')[0];
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });

    // ===== PREVENT MEGA MENU CLOSE ON CLICK INSIDE =====
    const megaMenus = document.querySelectorAll('.mega-menu');
    megaMenus.forEach(menu => {
        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 70; // Account for fixed navbar
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });


})();