<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Features';
include INCLUDES_PATH . '/header.php';
?>

<style>
.hero-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    padding: 4rem 0;
    margin-bottom: 4rem;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.hero-content p {
    font-size: 1.25rem;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.hero-image {
    background-color: #f3f4f6;
    border-radius: 12px;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.125rem;
    position: relative;
    overflow: hidden;
}

.hero-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%23f3f4f6"/><path d="M20 20h60v60h-60z" fill="none" stroke="%23d1d5db" stroke-width="2"/><circle cx="50" cy="50" r="15" fill="none" stroke="%23d1d5db" stroke-width="2"/></svg>');
    background-repeat: repeat;
    opacity: 0.3;
}

.hero-image-placeholder {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
}

.hero-image-placeholder i {
    font-size: 4rem;
    margin-bottom: 1rem;
    display: block;
    color: #d1d5db;
}

@media (max-width: 968px) {
    .hero-section {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .hero-image {
        order: -1;
    }
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

.feature-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 12px rgba(0,0,0,0.15);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    color: #06b6d4;
}

.feature-card h3 {
    color: #06b6d4;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.feature-card p {
    color: #666;
    line-height: 1.8;
}

.benefits-section {
    background-color: #f5f7fa;
    padding: 3rem 2rem;
    border-radius: 12px;
    margin: 3rem 0;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.benefit-item {
    display: flex;
    align-items: start;
    gap: 1rem;
}

.benefit-icon {
    font-size: 1.5rem;
    color: #10b981;
    flex-shrink: 0;
}

.benefit-item h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
}

.benefit-item p {
    margin: 0;
    color: #6b7280;
    font-size: 0.95rem;
}

.cta-section {
    background-color: #06b6d4;
    color: white;
    padding: 3rem 2rem;
    border-radius: 12px;
    text-align: center;
    margin: 3rem 0;
}

.cta-section h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.cta-section p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.95;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
    text-align: center;
}

.stat-item {
    padding: 1.5rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: bold;
    color: #06b6d4;
    display: block;
}

.stat-label {
    color: #6b7280;
    margin-top: 0.5rem;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 2rem;
    }
    
    .hero p {
        font-size: 1rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="hero-section">
    <div class="hero-content">
        <h1>Transform Your Identity Management</h1>
        <p>Secure, verifiable digital ID cards designed specifically for social care providers. Replace paper-based systems with modern, secure technology.</p>
        <?php if (!Auth::isLoggedIn()): ?>
            <a href="<?php echo url('register.php'); ?>" class="btn btn-primary">Get Started Free</a>
        <?php else: ?>
            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary">View Your ID Card</a>
        <?php endif; ?>
    </div>
    <div class="hero-image">
        <div class="hero-image-placeholder">
            <i class="fas fa-id-card"></i>
            <div>Digital ID Card Preview</div>
        </div>
    </div>
</div>

<div class="card">
    <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">Why Choose Digital ID?</h2>
    
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-shield-alt feature-icon"></i>
            <h3>Bank-Grade Security</h3>
            <p>Multi-layered verification with visual checks, time-limited QR codes, and NFC technology. Every verification is logged and auditable.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-mobile-alt feature-icon"></i>
            <h3>Always Accessible</h3>
            <p>Access your ID card from any device - smartphone, tablet, or computer. Install as an app on your phone for instant access. No need to carry physical cards that can be lost or damaged.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-download feature-icon"></i>
            <h3>Install as App</h3>
            <p>Install Digital ID as a Progressive Web App (PWA) on your phone. Quick access from your home screen, works offline, and feels like a native app - no app store required.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-bolt feature-icon"></i>
            <h3>Instant Verification</h3>
            <p>Verify employee identity in seconds with QR code scanning or NFC tap. Perfect for banks, service providers, and emergency situations.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-building feature-icon"></i>
            <h3>Multi-Tenant Ready</h3>
            <p>Perfect for organisations with multiple teams. Each organisation has isolated data with their own administrators and settings.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-link feature-icon"></i>
            <h3>Microsoft 365 Integration</h3>
            <p>Seamlessly integrate with Microsoft Entra ID and Office 365. Single sign-on and automatic employee synchronisation.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-chart-line feature-icon"></i>
            <h3>Complete Audit Trail</h3>
            <p>Every verification attempt is logged with timestamps, locations, and results. Perfect for compliance and security audits.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-door-open feature-icon"></i>
            <h3>Smart Access Control</h3>
            <p>Use your digital ID for door access systems, meeting attendance, fire drill tracking, and lone working safety checks.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-database feature-icon"></i>
            <h3>Data Portability</h3>
            <p>Export and import employee data in JSON format. Easy migration when staff move between organisations or roles.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-users feature-icon"></i>
            <h3>Service User Friendly</h3>
            <p>Service users, families, and carers can easily verify staff identity through our public verification system.</p>
        </div>
    </div>
</div>

<div class="benefits-section">
    <h2 style="text-align: center; margin-bottom: 1rem; color: #1f2937;">Key Benefits for Social Care Providers</h2>
    
    <div class="benefits-grid">
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Bank Transactions</h4>
                <p>Prove employee identity when acting on behalf of vulnerable clients at banks and financial institutions</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Emergency Situations</h4>
                <p>Quick identity verification during emergencies, fire drills, and safety checks</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Meeting Attendance</h4>
                <p>Automatically track attendance at meetings, training sessions, and mandatory briefings</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Lone Working Safety</h4>
                <p>Verify staff identity for lone working checks and safety protocols</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Service User Confidence</h4>
                <p>Give service users and families confidence that staff are verified and legitimate</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Compliance Ready</h4>
                <p>Complete audit trails for regulatory compliance and quality assurance</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Cost Effective</h4>
                <p>Eliminate costs of printing, replacing, and managing physical ID cards</p>
            </div>
        </div>
        
        <div class="benefit-item">
            <i class="fas fa-check-circle benefit-icon"></i>
            <div>
                <h4>Environmentally Friendly</h4>
                <p>Reduce paper waste and plastic card production with digital solutions</p>
            </div>
        </div>
    </div>
</div>

<div class="stats-section">
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

<div class="cta-section">
    <h2>Ready to Get Started?</h2>
    <p>Join organisations already using Digital ID to streamline their identity management</p>
    <div class="cta-buttons">
        <?php if (Auth::isLoggedIn()): ?>
            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-secondary" style="background: white; color: #06b6d4;">View Your ID Card</a>
        <?php else: ?>
            <a href="<?php echo url('register.php'); ?>" class="btn btn-secondary" style="background: white; color: #06b6d4;">Get Started Free</a>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">Login</a>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

