<?php
/**
 * VoltStore - Footer Template
 * 
 * @package VoltStore
 * @version 1.0
 */
?>
 <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                        <div class="logo-icon">V</div>
                        <span><?php echo SITE_NAME; ?></span>
                    </a>
                    <p>Your premium destination for the latest tech products. We bring you quality, innovation, and exceptional service.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com/prayagnimavat99" target="_blank" rel="noopener noreferrer" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-links">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php">Products</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/categories.php">Categories</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="footer-links">
                    <h4 class="footer-title">Customer Service</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php">FAQ</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/shipping.php">Shipping Info</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns.php">Returns & Exchanges</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/terms.php">Terms & Conditions</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-links">
                    <h4 class="footer-title">Get In Touch</h4>
                    <ul>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>VoltStore India Pvt. Ltd.</strong><br>
                                Tech Plaza, 2nd Floor<br>
                                Kalawad Road, Rajkot<br>
                                Gujarat - 360005, India
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Sales:</strong> +91 9876-543-210<br>
                                <strong>Support:</strong> +91 8765-432-109
                            </div>
                        </li>
                         <i class="fas fa-envelope"></i>
                            <div>
                                <a href="mailto:info@voltstore.in">info@voltstore.in</a><br>
                                <a href="mailto:support@voltstore.in">support@voltstore.in</a>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Mon - Fri:</strong> 9:00 AM - 7:00 PM<br>
                                <strong>Sat - Sun:</strong> 10:00 AM - 2:00 PM<br>
                                <small style="opacity: 0.7;">(IST)</small>
                            </div>
                        </li>
                        <li style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <i class="fas fa-user-tie"></i>
                            <div>
                                <strong>Prayag Nimavat</strong><br>
                                <small style="opacity: 0.8;">CEO & Founder</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Footer Bottom - Centered -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                        <span class="footer-divider">|</span>
                        <span class="footer-founder">Founded by Prayag Nimavat</span>
                    </p>
                     <div class="footer-payment">
                        <span class="payment-label">We accept:</span>
                        <div class="payment-icons">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" height="24" class="payment-icon">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" height="24" class="payment-icon">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" height="24" class="payment-icon">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/UPI-Logo-vector.svg" alt="UPI" height="24" class="payment-icon">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" aria-label="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- Main JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($pageScripts)): ?>
    <script><?php echo $pageScripts; ?></script>
    <?php endif; ?>
    
    <!-- Inline script for immediate execution -->
    <script>
        // Add padding to body for fixed navbar
        document.body.style.paddingTop = '52px';
        
        // Back to top button styles
        const backToTopStyles = document.createElement('style');
        backToTopStyles.textContent = `
            .back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 48px;
                height: 48px;
                background: var(--primary);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                transform: translateY(20px);
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
                z-index: 999;
                border: none;
            }
            
            .back-to-top.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            
            .back-to-top:hover {
                background: var(--primary-dark);
                transform: translateY(-4px);
            }
            
            .notification {
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 300px;
                max-width: 400px;
                z-index: 10000;
                transform: translateX(120%);
                transition: transform 0.3s ease;
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification.hide {
                transform: translateX(120%);
            }
            
            .notification-success {
                border-left: 4px solid var(--accent-green);
            }
            
            .notification-error {
                border-left: 4px solid var(--accent-red);
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }
            
            .notification-close {
                background: none;
                border: none;
                font-size: 20px;
                color: var(--text-muted);
                cursor: pointer;
                padding: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
            }