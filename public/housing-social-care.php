<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Housing & Social Care';
include INCLUDES_PATH . '/header.php';

// Unsplash images - professional stock photos for housing & social care context
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
    color: #0d2b2e;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.hero-content p {
    font-size: 1.25rem;
    color: #4a5a5c;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.hero-image {
    background-color: #f6f5f1;
    border-radius: 0;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4a5a5c;
    font-size: 1.125rem;
    position: relative;
    overflow: hidden;
}

.hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0;
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

.workflow-intro {
    text-align: center;
    max-width: 900px;
    margin: 0 auto 4rem;
    padding: 0 1rem;
}

.workflow-intro h2 {
    font-size: 2.5rem;
    color: #0d2b2e;
    margin-bottom: 1.5rem;
}

.workflow-intro p {
    font-size: 1.125rem;
    color: #4a5a5c;
    line-height: 1.8;
}

.use-case-section {
    margin: 5rem 0;
    padding: 3rem 0;
    border-top: 1px solid #dcd8cd;
}

.use-case-section:first-of-type {
    border-top: none;
}

.use-case-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    margin-top: 2rem;
}

.use-case-grid.reverse {
    direction: rtl;
}

.use-case-grid.reverse > * {
    direction: ltr;
}

.use-case-content h3 {
    font-size: 2rem;
    color: #0d2b2e;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.use-case-icon {
    width: 60px;
    height: 60px;
    background: #0e5f5a;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.use-case-content p {
    font-size: 1.125rem;
    color: #4a5a5c;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.use-case-features {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.use-case-features li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    color: #2f484b;
    font-size: 1rem;
}

.use-case-features li::before {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #1f6b45;
    font-size: 1rem;
}

.use-case-image {
    width: 100%;
    border-radius: 0;
    box-shadow: none;
    aspect-ratio: 4/3;
    object-fit: cover;
    background: #f6f5f1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4a5a5c;
    font-size: 1.125rem;
}

.use-case-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0;
}

.call-to-action {
    background: #f4f6f5;
    padding: 4rem 2rem;
    text-align: center;
    margin: 5rem -20px -2rem -20px;
    border-top: 1px solid #dcd8cd;
}

.call-to-action h2 {
    font-size: 2.5rem;
    color: #0d2b2e;
    margin-bottom: 1rem;
}

.call-to-action p {
    font-size: 1.125rem;
    color: #4a5a5c;
    max-width: 700px;
    margin: 0 auto 2rem;
    line-height: 1.8;
}

@media (max-width: 968px) {
    .workflow-hero h1 {
        font-size: 2.5rem;
    }
    
    .use-case-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .use-case-grid.reverse {
        direction: ltr;
    }
}
</style>

<div class="hero-section">
    <div class="hero-content">
        <h1>Digital ID for Housing & Social Care</h1>
        <p>See how Digital ID supports your team throughout their working day, from building access to client verification and beyond. One secure solution for every interaction.</p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
            <?php if (!Auth::isLoggedIn()): ?>
                <a href="<?php echo url('request-access.php'); ?>" class="btn btn-primary">Request Access</a>
                <a href="<?php echo url('features.php'); ?>" class="btn btn-secondary">View Features</a>
            <?php else: ?>
                <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary">View Your ID Card</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="workflow-intro">
        <h2>A Day in the Life</h2>
        <p>Digital ID streamlines your staff's daily activities, providing secure, verifiable identity verification for every interaction. From the moment they arrive at work to accessing rewards, your team has one simple, secure solution.</p>
    </div>

    <!-- Starting the Day: Building Access -->
    <section class="use-case-section">
        <div class="use-case-grid">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    Starting the Day: Building Access
                </h3>
                <p>Your team arrives at the office or service location. Instead of fumbling for keys or access cards, they simply scan their Digital ID QR code at the turnstile or door access panel.</p>
                <ul class="use-case-features">
                    <li>Instant building access verification</li>
                    <li>Automatic attendance logging</li>
                    <li>Secure, time-limited QR codes</li>
                    <li>Works with any turnstile or access system</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Visiting Clients: Care Home Access -->
    <section class="use-case-section">
        <div class="use-case-grid reverse">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    Visiting Clients: Care Home Access
                </h3>
                <p>When visiting care homes or supported living facilities, staff can verify their identity quickly and securely. Family members and care home staff can verify your team's credentials instantly.</p>
                <ul class="use-case-features">
                    <li>Instant identity verification for families</li>
                    <li>QR code scanning for quick access</li>
                    <li>Verified employment status</li>
                    <li>Peace of mind for vulnerable clients</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Banking on Behalf of Clients -->
    <section class="use-case-section">
        <div class="use-case-grid">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    Banking on Behalf of Clients
                </h3>
                <p>When acting on behalf of vulnerable clients at banks and financial institutions, your staff can prove their identity and employment status instantly. Bank staff can verify credentials in seconds.</p>
                <ul class="use-case-features">
                    <li>Real-time employment status confirmation</li>
                    <li>Secure, logged verification process</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Housing Provider Visits -->
    <section class="use-case-section">
        <div class="use-case-grid reverse">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    Housing Provider Visits
                </h3>
                <p>When visiting housing provider offices or properties, your team can quickly verify their identity. Housing staff can confirm who they're dealing with, ensuring secure and trusted interactions.</p>
                <ul class="use-case-features">
                    <li>Quick verification at housing offices</li>
                    <li>Property access verification</li>
                    <li>Professional appearance and credibility</li>
                    <li>Streamlined check-in processes</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Hospital and Medical Visits -->
    <section class="use-case-section">
        <div class="use-case-grid">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    Hospital and Medical Visits
                </h3>
                <p>When accompanying clients to hospital appointments or medical facilities, staff can verify their identity quickly. Medical staff can confirm your team's credentials for patient support.</p>
                <ul class="use-case-features">
                    <li>Fast verification at medical facilities</li>
                    <li>Patient support authorization</li>
                    <li>Secure access to restricted areas</li>
                    <li>Professional medical setting verification</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Training Sessions -->
    <section class="use-case-section">
        <div class="use-case-grid reverse">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    Training Sessions: Check-In and Out
                </h3>
                <p>During mandatory training sessions, staff can check in and out using their Digital ID. Automatically track attendance for compliance and CPD requirements.</p>
                <ul class="use-case-features">
                    <li>Quick QR code check-in</li>
                    <li>Automatic attendance tracking</li>
                    <li>Training session logging</li>
                    <li>Compliance reporting</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Employee Rewards and Discounts -->
    <section class="use-case-section">
        <div class="use-case-grid">
            <div class="use-case-content">
                <h3>
                    <div class="use-case-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    Employee Rewards and Discounts
                </h3>
                <p>Register for employee benefits and discounts using your Digital ID. Retail partners can verify your employment status instantly, unlocking exclusive offers and savings.</p>
                <ul class="use-case-features">
                    <li>Instant employment verification</li>
                    <li>Access to exclusive discounts</li>
                    <li>Simple registration process</li>
                    <li>Multiple retail partners supported</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Benefits Summary -->
    <section class="use-case-section">
        <h2 style="text-align: center; font-size: 2.5rem; color: #0d2b2e; margin-bottom: 3rem;">Why Housing & Social Care Providers Choose Digital ID</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Save Time</h3>
                <p style="color: #4a5a5c;">Quick verification means more time with clients, less time on paperwork.</p>
            </div>
            
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Enhanced Security</h3>
                <p style="color: #4a5a5c;">Secure, verifiable identity protection for your team and clients.</p>
            </div>
            
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Compliance Ready</h3>
                <p style="color: #4a5a5c;">Automatic logging and audit trails for regulatory compliance.</p>
            </div>
            
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Always Accessible</h3>
                <p style="color: #4a5a5c;">Access your Digital ID from any device, anytime, anywhere.</p>
            </div>
            
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Building Access</h3>
                <p style="color: #4a5a5c;">Seamless building and turnstile access with automatic attendance logging.</p>
            </div>
            
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #1f6b45; margin-bottom: 1rem;">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 style="color: #0d2b2e; margin-bottom: 1rem;">Client Trust</h3>
                <p style="color: #4a5a5c;">Build confidence with families and clients through instant identity verification.</p>
            </div>
        </div>
    </section>
</div>

<div class="call-to-action">
    <h2>Ready to Transform Your Staff's Daily Routine?</h2>
    <p>Join housing and social care providers across the UK who are using Digital ID to streamline operations and enhance security.</p>
    <?php if (!Auth::isLoggedIn()): ?>
        <a href="<?php echo url('request-access.php'); ?>" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
            <i class="fas fa-rocket"></i> Request Access
        </a>
        <a href="<?php echo url('features.php'); ?>" class="btn btn-secondary" style="font-size: 1.125rem; padding: 1rem 2rem; margin-left: 1rem;">
            <i class="fas fa-info-circle"></i> Learn More
        </a>
    <?php else: ?>
        <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
            <i class="fas fa-id-card"></i> View Your Digital ID
        </a>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

