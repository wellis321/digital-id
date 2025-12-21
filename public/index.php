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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2.5rem;
    margin-top: 3rem;
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
    background: #f3f4f6;
    border-radius: 0;
    padding: 3rem;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    text-align: center;
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
<div class="home-hero">
    <h1>Digital ID for Social Care Providers</h1>
    <p class="subtitle">
        Secure, verifiable employee identification designed for organisations where trust is critical. 
        Replace paper-based ID cards with modern, secure technology that protects your staff and service users.
    </p>
    
    <div class="cta-buttons">
        <?php if (Auth::isLoggedIn()): ?>
            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-hero-primary">View Your ID Card</a>
            <a href="<?php echo url('features.php'); ?>" class="btn btn-hero-secondary">Learn More</a>
        <?php else: ?>
            <a href="<?php echo url('register.php'); ?>" class="btn btn-hero-primary">Register</a>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-hero-secondary">Login</a>
            <a href="<?php echo url('request-access.php'); ?>" class="btn btn-hero-secondary" style="font-size: 0.9375rem;">Request Organisation Access</a>
        <?php endif; ?>
    </div>
</div>

<!-- Trust Indicators Section (Placeholder) -->
<div class="trust-section">
    <p>Trusted by organisations</p>
    <div class="trust-logos">
        <div class="placeholder-logo">Logo 1</div>
        <div class="placeholder-logo">Logo 2</div>
        <div class="placeholder-logo">Logo 3</div>
        <div class="placeholder-logo">Logo 4</div>
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
            <p>Access ID cards from any device - smartphone, tablet, or computer. Install as an app on your phone for instant home screen access. No need to carry physical cards that can be lost or damaged.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-download"></i>
            </div>
            <h3>Install as App</h3>
            <p>Install Digital ID as a Progressive Web App (PWA) on your phone. Quick access from your home screen, works offline, and feels like a native app - no app store required.</p>
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
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>Complete Audit Trail</h3>
            <p>Every verification attempt is logged with timestamps, locations, and results. Perfect for compliance and security audits.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-building"></i>
            </div>
            <h3>Multi-Tenant Ready</h3>
            <p>Perfect for organisations with multiple teams. Each organisation has isolated data with their own administrators and settings.</p>
        </div>
        
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <i class="fas fa-link"></i>
            </div>
            <h3>Microsoft 365 Integration</h3>
            <p>Seamlessly integrate with Microsoft Entra ID and Office 365. Single sign-on and automatic employee synchronisation.</p>
        </div>
        </div>
    </div>
</div>

<!-- Two Column Section - Product Showcase (Placeholder) -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="two-column-section">
    <div class="two-column-content">
        <div class="two-column-text">
            <h2>Secure accounts with biometrics</h2>
            <p>
                Digital ID's verification system uses secure, time-limited tokens to ensure only genuine account holders can access their ID cards.
            </p>
            <ul>
                <li>Certified security standards</li>
                <li>Face match accuracy against enrolled templates</li>
                <li>Proprietary defences against fraud</li>
                <li>Single integration via APIs</li>
            </ul>
        </div>
        <div class="two-column-image placeholder">
            <div>
                <i class="fas fa-image fa-3x" style="margin-bottom: 1rem; display: block;"></i>
                <p>Product Image Placeholder</p>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Works With Section (Placeholder) -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="section-header">
        <h2>Works with</h2>
        <p>Integrate Digital ID with your existing systems and workflows</p>
    </div>
    
    <div class="works-with-grid">
        <div class="works-with-item">
            <i class="fas fa-shield-halved"></i>
            <h4>Trust and Safety</h4>
            <p>Compliant moderation and safeguarding</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-user-check"></i>
            <h4>Onboarding & Access</h4>
            <p>Verify and authenticate users</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-file-signature"></i>
            <h4>Contracts & Agreements</h4>
            <p>Advanced signing with verified identity</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>Fraud Detection</h4>
            <p>Comprehensive anti-fraud measures</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-robot"></i>
            <h4>AI Attack Prevention</h4>
            <p>Prevent AI and bot attacks</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-phone"></i>
            <h4>Verified Calls</h4>
            <p>Know who you're really talking to</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-users-gear"></i>
            <h4>Access Management</h4>
            <p>Control who can access what</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-clock"></i>
            <h4>Time Tracking</h4>
            <p>Monitor attendance and hours</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-building-shield"></i>
            <h4>Compliance</h4>
            <p>Meet regulatory requirements</p>
        </div>
        
        <div class="works-with-item">
            <i class="fas fa-chart-pie"></i>
            <h4>Analytics</h4>
            <p>Track usage and insights</p>
        </div>
        </div>
    </div>
</div>

<!-- Two Column Section - Reverse Layout (Placeholder) -->
<div class="full-width-section main-section">
    <div class="section-content">
        <div class="two-column-section">
    <div class="two-column-content reverse">
        <div class="two-column-text">
            <h2>Passwordless & multifactor authentication</h2>
            <p>
                Protect accounts by replacing weak passwords with verified digital identity. Digital ID offers secure, passwordless login and multifactor authentication anchored in trusted credentials.
            </p>
            <ul>
                <li>Passwordless access for faster, safer login</li>
                <li>Verified identity as a stronger factor than SMS or email codes</li>
                <li>Account recovery without risky reset flows</li>
                <li>Grant and revoke credentials with digital ID</li>
            </ul>
        </div>
        <div class="two-column-image placeholder">
            <div>
                <i class="fas fa-image fa-3x" style="margin-bottom: 1rem; display: block;"></i>
                <p>Product Image Placeholder</p>
            </div>
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

<!-- Testimonial Section (Placeholder) -->
<div class="full-width-section main-section-light-gray">
    <div class="section-content">
        <div class="section-header">
        <h2>What our customers say</h2>
        <p>See how organisations are using Digital ID to improve their identity management</p>
    </div>
    
    <div class="testimonial-grid">
        <div class="testimonial-item">
            <p class="testimonial-text">
                "Digital ID has transformed how we verify staff identity. The QR code system is fast, secure, and our service users love the confidence it gives them."
            </p>
            <div class="testimonial-author">
                <div class="testimonial-author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="testimonial-author-info">
                    <h4>Placeholder Name</h4>
                    <p>Organisation Name</p>
                </div>
            </div>
        </div>
        
        <div class="testimonial-item">
            <p class="testimonial-text">
                "The audit trail feature has been invaluable for compliance. We can now prove every verification attempt with complete details."
            </p>
            <div class="testimonial-author">
                <div class="testimonial-author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="testimonial-author-info">
                    <h4>Placeholder Name</h4>
                    <p>Organisation Name</p>
                </div>
            </div>
        </div>
        
        <div class="testimonial-item">
            <p class="testimonial-text">
                "Replacing paper ID cards has saved us time and money. Staff can access their digital ID from any device, and it's always up to date."
            </p>
            <div class="testimonial-author">
                <div class="testimonial-author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="testimonial-author-info">
                    <h4>Placeholder Name</h4>
                    <p>Organisation Name</p>
                </div>
            </div>
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


<?php include INCLUDES_PATH . '/footer.php'; ?>

