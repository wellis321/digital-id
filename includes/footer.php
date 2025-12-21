    </main>
    <footer>
        <div class="container">
            <div class="footer-section">
                <h3><?php echo APP_NAME; ?></h3>
                <p style="color: #9ca3af; line-height: 1.6;">
                    Secure, verifiable digital identification for social care providers. 
                    Replace paper-based ID cards with modern, secure technology.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Product</h3>
                <ul>
                    <li><a href="<?php echo url('features.php'); ?>">Features</a></li>
                    <li><a href="<?php echo url('index.php'); ?>">How It Works</a></li>
                    <li><a href="<?php echo url('security.php'); ?>">Security</a></li>
                    <li><a href="<?php echo url('docs.php'); ?>">Documentation</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Resources</h3>
                <ul>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">API Reference</a></li>
                    <li><a href="#">Support</a></li>
                    <li><a href="#">Case Studies</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Company</h3>
                <ul>
                    <li><a href="mailto:<?php echo CONTACT_EMAIL; ?>">Contact</a></li>
                    <li><a href="<?php echo url('request-access.php'); ?>">Request Access</a></li>
                    <li><a href="<?php echo url('privacy-policy.php'); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo url('terms-of-service.php'); ?>">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

