/**
 * VoltStore - Main JavaScript File
 * 
 * Features:
 * - Smooth animations and transitions
 * - 3D card hover effects
 * - Cart functionality
 * - Form validation
 * - Mobile menu toggle
 * - Scroll effects
 * - Lazy loading
 * 
 * @package VoltStore
 * @version 1.0
 */

(function () {
    'use strict';

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================

    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));

    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };