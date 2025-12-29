<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Home';

// Get admin notifications
$usersNeedingEmployeeNumbers = [];
$countUsersNeedingNumbers = 0;
if (Auth::isLoggedIn() && RBAC::isAdmin()) {
    $organisationId = Auth::getOrganisationId();
    require_once dirname(__DIR__) . '/src/classes/AdminNotifications.php';
    $usersNeedingEmployeeNumbers = AdminNotifications::getUsersNeedingEmployeeNumbers($organisationId);
    $countUsersNeedingNumbers = count($usersNeedingEmployeeNumbers);
}

include INCLUDES_PATH . '/header.php';
?>

<style>
/* Hero Section - Clean and Professional */
.home-hero {
    background: #ffffff;
    padding: 5rem 2rem 4rem;
    text-align: center;
    margin: -2rem -20px 0 -20px;
    border-bottom: 1px solid #e5e7eb;
}

.home-hero h1 {
    font-size: 4rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    line-height: 1.1;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.home-hero .subtitle {
    font-size: 1.5rem;
    color: #4b5563;
    max-width: 800px;
    margin: 0 auto 2.5rem;
    line-height: 1.6;
    font-weight: 400;
}

.home-hero .cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.home-hero .btn-hero-primary {
    background-color: #06b6d4;
    color: white;
    padding: 0.875rem 2rem;
    font-size: 1.0625rem;
    font-weight: 500;
    border-radius: 0;
    transition: all 0.2s;
    border: none;
}

.home-hero .btn-hero-primary:hover {
    background-color: #0891b2;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.home-hero .btn-hero-secondary {
    background-color: white;
    color: #2563eb;
    padding: 0.875rem 2rem;
    font-size: 1.0625rem;
    font-weight: 500;
    border-radius: 0;
    border: 1px solid #2563eb;
    transition: all 0.2s;
}

.home-hero .btn-hero-secondary:hover {
    background-color: #f3f4f6;
}

/* Full Width Section Wrapper */
.full-width-section {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    padding: 5rem 0;
}

.full-width-section .section-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Main Content Sections */
.main-section {
    background: #ffffff;
}

.main-section-alt {
    background: #f9fafb;
}

.main-section-light-blue {
    background: #f0f9ff;
}

.main-section-light-gray {
    background: #f9fafb;
}

.section-header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto 3rem;
}

.section-header h2 {
    font-size: 3rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.section-header p {
    font-size: 1.25rem;
    color: #6b7280;
    line-height: 1.7;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 3rem;
}

@media (max-width: 1024px) {
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
}

.feature-item {
    text-align: left;
}

.feature-icon-wrapper {
    width: 64px;
    height: 64px;
    background-color: #f0f9ff;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.feature-icon-wrapper i {
    font-size: 1.75rem;
    color: #06b6d4;
}

.feature-item h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}

.feature-item p {
    font-size: 1.125rem;
    color: #6b7280;
    line-height: 1.6;
}

/* Features Slider */
.features-slider-wrapper {
    max-width: 1200px;
    margin: 4rem auto;
    padding: 0 20px;
}

.features-slider {
    position: relative;
    width: 100%;
    height: 600px;
    overflow: hidden;
    border-radius: 0.75rem;
}

.features-slider-track {
    display: flex;
    width: 500%;
    height: 100%;
    transition: transform 0.5s ease-in-out;
    will-change: transform;
}

.feature-slide {
    width: 20%;
    height: 100%;
    position: relative;
    flex-shrink: 0;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    overflow: hidden;
}

.feature-slide::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.3) 0%, rgba(37, 99, 235, 0.25) 100%);
    z-index: 1;
}

.feature-slide-content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4rem 4rem 4rem 6rem;
    color: white;
    max-width: 800px;
    margin-left: 30px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 0.75rem;
    backdrop-filter: blur(1px);
}

.feature-slide-content h3 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    color: white;
    font-weight: 700;
}

.feature-slide-content p {
    color: rgba(255, 255, 255, 0.95);
    line-height: 1.8;
    font-size: 1.125rem;
    margin-bottom: 1rem;
}

.feature-slide-content p:last-child {
    margin-bottom: 0;
}

@media (max-width: 968px) {
    .features-slider-wrapper {
        padding: 0 20px;
    }
    
    .features-slider {
        height: 500px;
    }
    
    .feature-slide-content {
        padding: 2rem;
    }
    
    .feature-slide-content h3 {
        font-size: 2rem;
    }
    
    .feature-slide-content p {
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    .features-slider {
        height: 400px;
    }
    
    .feature-slide-content {
        padding: 1.5rem;
        margin-left: 0;
    }
    
    .feature-slide-content h3 {
        font-size: 1.5rem;
    }
    
    .feature-slide-content p {
        font-size: 0.9rem;
    }
}

.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #06b6d4;
    z-index: 10;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    flex-shrink: 0;
}

.slider-nav:hover {
    background: white;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}


.slider-prev {
    left: 1rem;
}

.slider-next {
    right: 1rem;
}

.slider-dots {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 0.75rem;
    z-index: 10;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid white;
    cursor: pointer;
    transition: all 0.3s;
    padding: 0;
}

.slider-dot.active {
    background: white;
    transform: scale(1.2);
}

/* Why Section */
.why-section {
    background: #ffffff;
    padding: 4rem 0;
}

.why-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.why-item {
    padding: 2rem;
    background: #f9fafb;
    border-radius: 0;
    border: 1px solid #e5e7eb;
}

.why-item h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}

.why-item p {
    font-size: 1rem;
    color: #6b7280;
    line-height: 1.6;
}

/* CTA Section */
.cta-section {
    background-color: #2563eb;
    color: white;
    padding: 4rem 2rem;
    text-align: center;
    border-radius: 0;
    margin: 4rem 0;
}

.cta-section h2 {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-section p {
    font-size: 1.125rem;
    opacity: 0.95;
    margin-bottom: 2rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-section .btn {
    background: white;
    color: #06b6d4;
    padding: 0.875rem 2rem;
    font-weight: 500;
    margin: 0 0.5rem;
}

.cta-section .btn:hover {
    background: #f3f4f6;
}

/* Trust Indicators Section */
.trust-section {
    background: #ffffff;
    padding: 3rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.trust-section p {
    text-align: center;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 2rem;
}

.trust-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 3rem;
    flex-wrap: wrap;
    opacity: 0.6;
}

.trust-logos .placeholder-logo {
    height: 40px;
    width: 120px;
    background: #e5e7eb;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 0.75rem;
}

/* Two Column Layout Section */
.two-column-section {
    padding: 0;
    background: transparent;
}

.two-column-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.two-column-content.reverse {
    direction: rtl;
}

.two-column-content.reverse > * {
    direction: ltr;
}

.two-column-text h2 {
    font-size: 3rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.two-column-text p {
    font-size: 1.25rem;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}

.two-column-text ul {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.two-column-text ul li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    color: #4b5563;
    font-size: 1.0625rem;
}

.two-column-text ul li:before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #10b981;
}

.two-column-image {
    background: transparent;
    border-radius: 0;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    text-align: center;
}

.two-column-image img {
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 600px;
    object-fit: contain;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.two-column-image a:hover img {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    cursor: pointer;
}

.two-column-image.placeholder {
    border: 2px dashed #d1d5db;
}

/* Works With Section */
.works-with-section {
    background: #f9fafb;
    padding: 4rem 0;
}

.works-with-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}

@media (max-width: 1024px) {
    .works-with-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .works-with-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.works-with-item {
    text-align: center;
    padding: 1.5rem;
    background: white;
    border-radius: 0;
    border: 1px solid #e5e7eb;
    transition: transform 0.2s, box-shadow 0.2s;
}

.works-with-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.works-with-item i {
    font-size: 2.5rem;
    color: #06b6d4;
    margin-bottom: 1rem;
}

.works-with-item h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.works-with-item p {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Stats Section */
.stats-section {
    background: #ffffff;
    padding: 4rem 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 3rem;
    text-align: center;
}

.stat-item {
    padding: 1rem;
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 700;
    color: #2563eb;
    display: block;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1.125rem;
    color: #6b7280;
    font-weight: 500;
}

/* Testimonial Section */
.testimonial-section {
    background: #f9fafb;
    padding: 5rem 0;
}

.testimonial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.testimonial-item {
    background: white;
    padding: 2rem;
    border-radius: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.testimonial-text {
    font-size: 1.125rem;
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.testimonial-author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 0;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.testimonial-author-info h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.testimonial-author-info p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Product Showcase Section */
.product-showcase {
    padding: 5rem 0;
    background: #ffffff;
}

/* Placeholder Styles */
.placeholder-section {
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 0;
    padding: 3rem 2rem;
    text-align: center;
    color: #9ca3af;
    margin: 2rem 0;
}

.placeholder-section h3 {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.placeholder-section p {
    font-size: 0.875rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .home-hero {
        padding: 3rem 1.5rem 2.5rem;
    }
    
    .home-hero h1 {
        font-size: 2.5rem;
    }
    
    .home-hero .subtitle {
        font-size: 1.25rem;
    }
    
    .section-header h2 {
        font-size: 2.25rem;
    }
    
    .section-header p {
        font-size: 1.125rem;
    }
    
    .two-column-text h2 {
        font-size: 2.25rem;
    }
    
    .two-column-text p {
        font-size: 1.125rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .why-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-section {
        padding: 3rem 1.5rem;
    }
    
    .cta-section h2 {
        font-size: 2rem;
    }
    
    .two-column-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .two-column-content.reverse {
        direction: ltr;
    }
    
    .trust-logos {
        gap: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
    
    .testimonial-grid {
        grid-template-columns: 1fr;
    }
    
    .full-width-section {
        padding: 3rem 0;
    }
}
</style>


<!-- Hero Section -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-text">
                    <h2>Digital ID for Social Care Providers</h2>
                    <p>
                        Secure, verifiable employee identification designed for organisations where trust is critical. 
                        Replace paper-based ID cards with modern, secure technology that protects your staff and service users.
                        Integrate with Microsoft 365 for seamless single sign-on and automatic user management.
                    </p>
                    <ul>
                        <li>Bank-grade security with multi-layered verification</li>
                        <li>Instant verification via QR codes and NFC</li>
                        <li>Complete audit trail for compliance</li>
                        <li>Works offline with PWA technology</li>
                    </ul>
                    <div class="cta-buttons" style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php if (Auth::isLoggedIn()): ?>
                            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-hero-primary" style="background-color: #06b6d4; color: white; padding: 0.875rem 2rem; font-size: 1.0625rem; font-weight: 500; border-radius: 0; transition: all 0.2s; border: none; text-decoration: none;">View Your ID Card</a>
                            <a href="<?php echo url('features.php'); ?>" class="btn btn-hero-secondary" style="background-color: white; color: #2563eb; padding: 0.875rem 2rem; font-size: 1.0625rem; font-weight: 500; border-radius: 0; border: 1px solid #2563eb; transition: all 0.2s; text-decoration: none;">Learn More</a>
                        <?php else: ?>
                            <a href="<?php echo url('register.php'); ?>" class="btn btn-hero-primary" style="background-color: #06b6d4; color: white; padding: 0.875rem 2rem; font-size: 1.0625rem; font-weight: 500; border-radius: 0; transition: all 0.2s; border: none; text-decoration: none;">Register</a>
                            <a href="<?php echo url('demo-id-card.php'); ?>" class="btn btn-hero-secondary" style="background-color: white; color: #2563eb; padding: 0.875rem 2rem; font-size: 1.0625rem; font-weight: 500; border-radius: 0; border: 1px solid #2563eb; transition: all 0.2s; text-decoration: none;">See Example ID Card</a>
                            <a href="<?php echo url('login.php'); ?>" class="btn btn-hero-secondary" style="background-color: white; color: #2563eb; padding: 0.875rem 2rem; font-size: 1.0625rem; font-weight: 500; border-radius: 0; border: 1px solid #2563eb; transition: all 0.2s; text-decoration: none;">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="two-column-image">
                    <a href="<?php echo url('demo-id-card.php'); ?>" style="display: block; text-decoration: none; transition: transform 0.2s;">
                        <img src="<?php echo url('assets/images/home/id-image.png'); ?>" alt="Digital ID Card" style="transition: transform 0.2s;">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Features Section -->
<div class="full-width-section main-section-light-blue">
    <div class="section-content">
        <div class="section-header">
            <h2>Authentication to protect your organisation and keep operations secure</h2>
            <p>
                Digital ID provides secure employee identification built for social care providers where trust is critical. 
                Use it to verify staff identity, protect service users, and maintain compliance with complete audit trails.
            </p>
        </div>
        
        <div class="features-grid">
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Bank-Grade Security</h3>
            <p>Multi-layered verification with visual checks, time-limited QR codes, and NFC technology. Every verification is logged and auditable.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h3>Always Accessible</h3>
            <p>Access ID cards from any device - smartphone, tablet, or computer. No need to carry physical cards that can be lost or damaged.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-download"></i>
            </div>
            <h3>Install as App</h3>
            <p>Install Digital ID as a Progressive Web App (PWA) on your phone. Quick access from your home screen, works offline, and feels like a native app.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-bolt"></i>
            </div>
            <h3>Instant Verification</h3>
            <p>Verify employee identity in seconds with QR code scanning or NFC tap. Perfect for banks, service providers, and emergency situations.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>Complete Audit Trail</h3>
            <p>Every verification attempt is logged with timestamps, IP addresses, device info, and results. Admins can view, filter, and export logs for compliance reporting and security monitoring.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-building"></i>
            </div>
            <h3>Multi-Tenant Ready</h3>
            <p>Perfect for organisations with multiple teams. Each organisation has isolated data with their own administrators and settings.</p>
        </div>
        </div>
        
        <!-- Feature Slider -->
        <div class="features-slider-wrapper" style="margin-top: 4rem;">
            <div class="features-slider">
                <div class="features-slider-track" id="featuresSliderTrack">
                    <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-one.png'); ?>');">
                        <div class="feature-slide-content">
                            <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <i class="fas fa-qrcode" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3>QR Code Verification</h3>
                            <p>Universal QR code support works on all devices and browsers. Time-limited tokens ensure secure verification every time.</p>
                        </div>
                    </div>
                    
                    <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-two.png'); ?>');">
                        <div class="feature-slide-content">
                            <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <i class="fas fa-wifi" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3>Offline Capable</h3>
                            <p>View your ID card even without internet connection. PWA technology caches your card for reliable access anywhere.</p>
                        </div>
                    </div>
                    
                    <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-three.png'); ?>');">
                        <div class="feature-slide-content">
                            <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <i class="fas fa-user-check" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3>Photo Verification</h3>
                            <p>Admin-approved employee photos ensure visual identity confirmation. Clear guidelines help staff upload professional photos.</p>
                        </div>
                    </div>
                    
                    <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-four.png'); ?>');">
                        <div class="feature-slide-content">
                            <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <i class="fas fa-clipboard-check" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3>Check-In Sessions</h3>
                            <p>Track attendance for fire drills, safety meetings, and emergencies. Staff check in using QR codes or manually, with automatic Microsoft 365 synchronisation.</p>
                        </div>
                    </div>
                    
                    <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-five.png'); ?>');">
                        <div class="feature-slide-content">
                            <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <i class="fas fa-fire" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3>Fire Drill Tracking</h3>
                            <p>Real-time attendance tracking during fire drills and emergency evacuations. Export attendance records for compliance and safety audits.</p>
                        </div>
                    </div>
                </div>
                
                <button class="slider-nav slider-prev" aria-label="Previous slide" onclick="changeFeatureSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-nav slider-next" aria-label="Next slide" onclick="changeFeatureSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="slider-dots">
                    <span class="slider-dot active" onclick="goToFeatureSlide(0)"></span>
                    <span class="slider-dot" onclick="goToFeatureSlide(1)"></span>
                    <span class="slider-dot" onclick="goToFeatureSlide(2)"></span>
                    <span class="slider-dot" onclick="goToFeatureSlide(3)"></span>
                    <span class="slider-dot" onclick="goToFeatureSlide(4)"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integrations Section -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="section-header">
            <h2>Integrations</h2>
            <p>Digital ID integrates with your existing systems and workflows</p>
        </div>
        
        <div class="works-with-grid">
            <div class="works-with-item">
                <i class="fab fa-microsoft"></i>
                <h4>Microsoft 365</h4>
                <p>Single sign-on and user synchronisation</p>
            </div>
            
            <div class="works-with-item">
                <i class="fas fa-share-alt"></i>
                <h4>SharePoint Lists</h4>
                <p>Sync check-in data automatically</p>
            </div>
            
            <div class="works-with-item">
                <i class="fas fa-bolt"></i>
                <h4>Power Automate</h4>
                <p>Trigger workflows from check-ins</p>
            </div>
            
            <div class="works-with-item">
                <i class="fas fa-users"></i>
                <h4>Microsoft Teams</h4>
                <p>Send notifications to channels</p>
            </div>
            
            <div class="works-with-item">
                <i class="fas fa-clipboard-check"></i>
                <h4>Check-In Sessions</h4>
                <p>Fire drill and safety tracking</p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section (Placeholder) -->
<div class="full-width-section main-section-light-blue">
    <div class="section-content">
        <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number">3</span>
            <span class="stat-label">Verification Methods</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">24/7</span>
            <span class="stat-label">Access Available</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">100%</span>
            <span class="stat-label">Audit Trail</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">5min</span>
            <span class="stat-label">Token Expiry</span>
        </div>
        </div>
    </div>
</div>

<!-- Why Digital ID Section -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="section-header">
        <h2>Why Digital ID?</h2>
        <p>
            Social care providers need secure, verifiable employee identification for bank transactions, 
            service delivery, and emergency situations. Our digital ID system provides bank-grade security 
            with the convenience of modern technology.
        </p>
    </div>
    
    <div class="why-grid">
        <div class="why-item">
            <h3>Bank Transactions</h3>
            <p>Prove employee identity when acting on behalf of vulnerable clients at banks and financial institutions. Replace paper-based authorisation with secure digital verification.</p>
        </div>
        
        <div class="why-item">
            <h3>Emergency Situations</h3>
            <p>Quick identity verification during emergencies, fire drills, and safety checks. Ensure the right people have access when it matters most.</p>
        </div>
        
        <div class="why-item">
            <h3>Service User Confidence</h3>
            <p>Give service users, families, and carers confidence that staff are verified and legitimate. Public verification system allows easy identity confirmation.</p>
        </div>
        
        <div class="why-item">
            <h3>Compliance Ready</h3>
            <p>Complete audit trails for regulatory compliance and quality assurance. Every verification is logged with full details for inspections and reviews.</p>
        </div>
        
        <div class="why-item">
            <h3>Cost Effective</h3>
            <p>Eliminate costs of printing, replacing, and managing physical ID cards. Reduce administrative overhead and environmental impact.</p>
        </div>
        
        <div class="why-item">
            <h3>Meeting Attendance</h3>
            <p>Automatically track attendance at meetings, training sessions, and mandatory briefings. Digital records replace paper sign-in sheets.</p>
        </div>
        </div>
    </div>
</div>

<!-- Microsoft 365 Integration Section -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="section-header">
            <h2>Microsoft 365 Integration</h2>
            <p>Seamlessly integrate Digital ID with your existing Microsoft 365 infrastructure for streamlined user management and authentication.</p>
        </div>
        
        <div class="two-column-section">
            <div class="two-column-content">
                <div class="two-column-image">
                    <img src="<?php echo url('assets/images/home/microsoft.jpeg'); ?>" alt="Microsoft 365 Integration">
                </div>
                <div class="two-column-text">
                    <h3>Seamless Microsoft 365 Integration</h3>
                    <p>
                        Digital ID integrates seamlessly with your existing Microsoft 365 infrastructure, making it easy for your staff to access their digital ID cards and for administrators to manage users.
                    </p>
                    <p>
                        Enable single sign-on so staff can log in using their existing Microsoft 365 credentials - no need to remember another password. They can use the same login they use for Office 365, Teams, SharePoint, and other Microsoft services.
                    </p>
                    <p>
                        Keep your Digital ID user list in sync with Microsoft 365 automatically. User accounts are created and updated automatically from Microsoft Entra ID, eliminating the need for manual CSV exports or duplicate user management.
                    </p>
                    
                    <div style="margin-top: 2rem;">
                        <a href="<?php echo url('docs.php?section=entra-integration'); ?>" class="btn btn-primary">
                            <i class="fas fa-book"></i> Learn More About Integration
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="padding: 1.5rem; background: white; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #f0f9ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-sign-in-alt" style="font-size: 1.5rem; color: #06b6d4;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">Single Sign-On</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">Users log in with their Microsoft 365 credentials - no separate password needed.</p>
            </div>
            
            <div style="padding: 1.5rem; background: white; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #f0f9ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-sync" style="font-size: 1.5rem; color: #06b6d4;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">User Sync</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">Automatically sync users from Microsoft Entra ID - no CSV exports required.</p>
            </div>
            
            <div style="padding: 1.5rem; background: white; border: 1px solid #e5e7eb; border-radius: 0;">
                <div style="width: 48px; height: 48px; background-color: #f0f9ff; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-shield-alt" style="font-size: 1.5rem; color: #06b6d4;"></i>
                </div>
                <h4 style="margin-top: 0; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">Secure & Compliant</h4>
                <p style="color: #6b7280; font-size: 0.9375rem; margin: 0;">Uses industry-standard OAuth 2.0 and Microsoft Graph API for secure integration.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!Auth::isLoggedIn()): ?>
<!-- CTA Section -->
<div class="full-width-section" style="background: #2563eb; padding: 5rem 0;">
    <div class="section-content">
        <div class="cta-section" style="background: transparent; padding: 0; margin: 0;">
            <h2>Ready to Get Started?</h2>
            <p>Join organisations already using Digital ID to streamline their identity management and protect their staff and service users.</p>
            <div>
                <a href="<?php echo url('register.php'); ?>" class="btn" style="background: white; color: #2563eb; border: none; font-weight: 600;">Register</a>
                <a href="<?php echo url('login.php'); ?>" class="btn" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid white;">Login</a>
                <a href="<?php echo url('request-access.php'); ?>" class="btn" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid white; font-size: 0.9375rem;">Request Organisation Access</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<script>
// Features Slider
let currentFeatureSlide = 0;
const totalFeatureSlides = 5;
let featureAutoAdvanceInterval = null;
let isFeaturePaused = false;

function updateFeatureSlider() {
    const slides = document.getElementById('featuresSliderTrack');
    const dots = document.querySelectorAll('.slider-dot');
    
    if (!slides) return;
    
    slides.style.transform = `translateX(-${currentFeatureSlide * 20}%)`;
    
    dots.forEach((dot, index) => {
        if (index === currentFeatureSlide) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function changeFeatureSlide(direction) {
    currentFeatureSlide += direction;
    
    if (currentFeatureSlide < 0) {
        currentFeatureSlide = totalFeatureSlides - 1;
    } else if (currentFeatureSlide >= totalFeatureSlides) {
        currentFeatureSlide = 0;
    }
    
    updateFeatureSlider();
    // Pause auto-advance when user manually navigates
    pauseFeatureAutoAdvance();
    // Resume after 20 seconds of inactivity
    setTimeout(() => {
        if (!isFeaturePaused) {
            startFeatureAutoAdvance();
        }
    }, 20000);
}

function goToFeatureSlide(index) {
    currentFeatureSlide = index;
    updateFeatureSlider();
    // Pause auto-advance when user manually navigates
    pauseFeatureAutoAdvance();
    // Resume after 20 seconds of inactivity
    setTimeout(() => {
        if (!isFeaturePaused) {
            startFeatureAutoAdvance();
        }
    }, 20000);
}

function startFeatureAutoAdvance() {
    pauseFeatureAutoAdvance(); // Clear any existing interval
    featureAutoAdvanceInterval = setInterval(() => {
        if (!isFeaturePaused) {
            changeFeatureSlide(1);
        }
    }, 15000);
}

function pauseFeatureAutoAdvance() {
    if (featureAutoAdvanceInterval) {
        clearInterval(featureAutoAdvanceInterval);
        featureAutoAdvanceInterval = null;
    }
}

// Pause on hover
const featureSlider = document.querySelector('.features-slider');
if (featureSlider) {
    featureSlider.addEventListener('mouseenter', () => {
        isFeaturePaused = true;
        pauseFeatureAutoAdvance();
    });
    
    featureSlider.addEventListener('mouseleave', () => {
        isFeaturePaused = false;
        startFeatureAutoAdvance();
    });
}

// Initialize
updateFeatureSlider();
startFeatureAutoAdvance();
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

