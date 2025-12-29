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
    border-radius: 0;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.125rem;
    position: relative;
    overflow: hidden;
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
    border-radius: 0;
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
}

.feature-card:nth-child(3n+1) .feature-icon {
    color: #3b82f6;
}

.feature-card:nth-child(3n+2) .feature-icon {
    color: #10b981;
}

.feature-card:nth-child(3n+3) .feature-icon {
    color: #8b5cf6;
}

.feature-card h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.feature-card:nth-child(3n+1) h3 {
    color: #3b82f6;
}

.feature-card:nth-child(3n+2) h3 {
    color: #10b981;
}

.feature-card:nth-child(3n+3) h3 {
    color: #8b5cf6;
}

.feature-card p {
    color: #666;
    line-height: 1.8;
}

.benefits-section {
    background-color: #f5f7fa;
    padding: 3rem 2rem;
    border-radius: 0;
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
    background: #2563eb;
    color: white;
    padding: 3rem 2rem;
    border-radius: 0;
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
    display: block;
}

.stat-item:nth-child(1) .stat-number {
    color: #3b82f6;
}

.stat-item:nth-child(2) .stat-number {
    color: #10b981;
}

.stat-item:nth-child(3) .stat-number {
    color: #8b5cf6;
}

.stat-item:nth-child(4) .stat-number {
    color: #06b6d4;
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

/* Features Slider Styles */
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
    width: 400%;
    height: 100%;
    transition: transform 0.5s ease-in-out;
    will-change: transform;
}

.feature-slide {
    width: 25%;
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
    
    .slider-nav {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .slider-nav.prev {
        left: 1rem;
    }
    
    .slider-nav.next {
        right: 1rem;
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
    
    .slider-nav {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .slider-nav.prev {
        left: 0.5rem;
    }
    
    .slider-nav.next {
        right: 0.5rem;
    }
}
</style>

<div class="hero-section">
    <div class="hero-content">
        <h1>Transform Your Identity Management</h1>
        <p>Secure, verifiable digital ID cards designed specifically for social care providers. Replace paper-based systems with modern, secure technology.</p>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
            <?php if (!Auth::isLoggedIn()): ?>
                <a href="<?php echo url('request-access.php'); ?>" class="btn btn-primary">Request Access</a>
                <a href="<?php echo url('demo-id-card.php'); ?>" class="btn btn-secondary">See Example ID Card</a>
            <?php else: ?>
                <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary">View Your ID Card</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-image">
        <img src="<?php echo url('assets/images/features/Transform Your Identity Management.png'); ?>" alt="Transform Your Identity Management" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
    </div>
</div>

<div class="card">
    <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">Why Choose Digital ID?</h2>
    
    <div class="features-grid">
        <!-- Keep 6 cards visible -->
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
            <i class="fas fa-microsoft feature-icon"></i>
            <h3>Microsoft 365 Integration</h3>
            <p>Seamlessly integrate with Microsoft Entra ID. Single sign-on login and automatic user synchronisation directly from Microsoft 365 - no CSV exports needed.</p>
        </div>
    </div>
    
    <!-- Feature Slider -->
    <div class="features-slider-wrapper" style="margin-top: 4rem;">
        <div class="features-slider">
            <div class="features-slider-track" id="featuresPageSliderTrack">
                <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-one.png'); ?>');">
                    <div class="feature-slide-content">
                        <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-door-open" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h3>Smart Access Control</h3>
                        <p>Use your digital ID for door access systems, meeting attendance, fire drill tracking, and lone working safety checks.</p>
                    </div>
                </div>
                
                <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-two.png'); ?>');">
                    <div class="feature-slide-content">
                        <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-database" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h3>Data Portability</h3>
                        <p>Export and import employee data in JSON format. Easy migration when staff move between organisations or roles.</p>
                    </div>
                </div>
                
                <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-three.png'); ?>');">
                    <div class="feature-slide-content">
                        <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-users" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h3>Service User Friendly</h3>
                        <p>Service users, families, and carers can easily verify staff identity through our public verification system.</p>
                    </div>
                </div>
                
                <div class="feature-slide" style="background-image: url('<?php echo url('assets/images/home/home-silder/slide-four.png'); ?>');">
                    <div class="feature-slide-content">
                        <div class="feature-icon-wrapper" style="background: rgba(6, 182, 212, 0.2); width: 80px; height: 80px; border-radius: 0; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-microsoft" style="font-size: 2.5rem; color: white;"></i>
                        </div>
                        <h3>Microsoft 365 Workflows</h3>
                        <p>Seamlessly sync check-in data to SharePoint Lists, trigger Power Automate workflows, and send Teams notifications. Perfect for organisations already using Microsoft 365.</p>
                    </div>
                </div>
            </div>
            
            <button class="slider-nav slider-prev" aria-label="Previous slide" onclick="changeFeaturesPageSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav slider-next" aria-label="Next slide" onclick="changeFeaturesPageSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="slider-dots">
                <span class="slider-dot active" onclick="goToFeaturesPageSlide(0)"></span>
                <span class="slider-dot" onclick="goToFeaturesPageSlide(1)"></span>
                <span class="slider-dot" onclick="goToFeaturesPageSlide(2)"></span>
                <span class="slider-dot" onclick="goToFeaturesPageSlide(3)"></span>
            </div>
        </div>
    </div>
</div>

<!-- Additional Features Section -->
<div class="card" style="margin-top: 4rem;">
    <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">Advanced Features</h2>
    
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-chart-line feature-icon"></i>
            <h3>Complete Audit Trail</h3>
            <p>Every verification attempt is logged with timestamps, locations, and results. Perfect for compliance and security audits.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-clipboard-check feature-icon"></i>
            <h3>Check-In Sessions</h3>
            <p>Create check-in sessions for fire drills, safety meetings, and emergencies. Staff can check in using QR codes or manually, with automatic attendance tracking and Microsoft 365 integration.</p>
        </div>
        
        <div class="feature-card">
            <i class="fas fa-fire feature-icon"></i>
            <h3>Fire Drill & Emergency Tracking</h3>
            <p>Track attendance during fire drills and emergency evacuations. Real-time check-in system ensures accurate headcounts and compliance reporting. Export attendance records for safety audits.</p>
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
            <a href="<?php echo url('id-card.php'); ?>" class="btn btn-secondary" style="background: white; color: #3b82f6;">View Your ID Card</a>
        <?php else: ?>
            <a href="<?php echo url('request-access.php'); ?>" class="btn btn-secondary" style="background: white; color: #3b82f6;">Request Access</a>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">Login</a>
        <?php endif; ?>
    </div>
</div>

<script>
// Features Page Slider
let currentFeaturesPageSlide = 0;
const totalFeaturesPageSlides = 4;
let featuresPageAutoAdvanceInterval = null;
let isFeaturesPagePaused = false;

function updateFeaturesPageSlider() {
    const slides = document.getElementById('featuresPageSliderTrack');
    const dots = document.querySelectorAll('.features-slider-wrapper .slider-dot');
    
    if (!slides) return;
    
    slides.style.transform = `translateX(-${currentFeaturesPageSlide * 25}%)`;
    
    dots.forEach((dot, index) => {
        if (index === currentFeaturesPageSlide) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function changeFeaturesPageSlide(direction) {
    currentFeaturesPageSlide += direction;
    
    if (currentFeaturesPageSlide < 0) {
        currentFeaturesPageSlide = totalFeaturesPageSlides - 1;
    } else if (currentFeaturesPageSlide >= totalFeaturesPageSlides) {
        currentFeaturesPageSlide = 0;
    }
    
    updateFeaturesPageSlider();
    pauseFeaturesPageAutoAdvance();
    setTimeout(() => {
        if (!isFeaturesPagePaused) {
            startFeaturesPageAutoAdvance();
        }
    }, 20000);
}

function goToFeaturesPageSlide(index) {
    currentFeaturesPageSlide = index;
    updateFeaturesPageSlider();
    pauseFeaturesPageAutoAdvance();
    setTimeout(() => {
        if (!isFeaturesPagePaused) {
            startFeaturesPageAutoAdvance();
        }
    }, 20000);
}

function startFeaturesPageAutoAdvance() {
    pauseFeaturesPageAutoAdvance();
    featuresPageAutoAdvanceInterval = setInterval(() => {
        if (!isFeaturesPagePaused) {
            changeFeaturesPageSlide(1);
        }
    }, 15000);
}

function pauseFeaturesPageAutoAdvance() {
    if (featuresPageAutoAdvanceInterval) {
        clearInterval(featuresPageAutoAdvanceInterval);
        featuresPageAutoAdvanceInterval = null;
    }
}

const featuresPageSlider = document.querySelector('.features-slider-wrapper .features-slider');
if (featuresPageSlider) {
    featuresPageSlider.addEventListener('mouseenter', () => {
        isFeaturesPagePaused = true;
        pauseFeaturesPageAutoAdvance();
    });
    
    featuresPageSlider.addEventListener('mouseleave', () => {
        isFeaturesPagePaused = false;
        startFeaturesPageAutoAdvance();
    });
}

updateFeaturesPageSlider();
startFeaturesPageAutoAdvance();
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

