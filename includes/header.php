<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Digital ID">
    <meta name="description" content="Secure digital ID card for social care providers">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo url('manifest.json'); ?>">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="<?php echo url('assets/icons/icon-192x192.png'); ?>">
    
    <!-- Font Awesome 6 (Free) - Icon Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/public/assets/css/style.css'); ?>">
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo url('service-worker.js'); ?>')
                    .then((registration) => {
                        console.log('Service Worker registered:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    <!-- Mobile Menu Toggle -->
    <script>
        (function() {
            function updateMenuVisibility() {
                const menuToggle = document.querySelector('.mobile-menu-toggle');
                const navLinks = document.querySelector('.nav-links');
                const isMobile = window.innerWidth <= 968;
                
                if (menuToggle) {
                    // Force visibility based on screen width
                    if (isMobile) {
                        menuToggle.style.display = 'block';
                    } else {
                        menuToggle.style.display = 'none';
                        // Also remove any inline styles that might interfere
                        menuToggle.removeAttribute('style');
                        menuToggle.style.display = 'none';
                    }
                }
                
                if (navLinks) {
                    if (isMobile) {
                        // On mobile, ensure menu starts closed
                        if (!navLinks.classList.contains('active')) {
                            navLinks.style.display = 'none';
                        }
                    } else {
                        // On desktop, always show nav-links
                        navLinks.style.display = 'flex';
                        navLinks.classList.remove('active');
                    }
                }
            }
            
            function resetMenuOnPageLoad() {
                const navLinks = document.querySelector('.nav-links');
                const menuToggle = document.querySelector('.mobile-menu-toggle');
                
                // Update visibility first
                updateMenuVisibility();
                
                if (navLinks) {
                    navLinks.classList.remove('active');
                    navLinks.setAttribute('data-menu-state', 'closed');
                }
                
                if (menuToggle) {
                    const icon = menuToggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
            
            // Run immediately
            resetMenuOnPageLoad();
            
            // Also run on DOM ready and window resize
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', resetMenuOnPageLoad);
            } else {
                resetMenuOnPageLoad();
            }
            
            // Update on window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(updateMenuVisibility, 100);
            });
            
            document.addEventListener('DOMContentLoaded', function() {
                updateMenuVisibility();
                
                const menuToggle = document.querySelector('.mobile-menu-toggle');
                const navLinks = document.querySelector('.nav-links');
                if (menuToggle && navLinks) {
                    menuToggle.addEventListener('click', function(e) {
                        // Only work on mobile
                        if (window.innerWidth > 968) {
                            e.preventDefault();
                            return;
                        }
                        
                        e.preventDefault();
                        e.stopPropagation();
                        
                        navLinks.classList.toggle('active');
                        const icon = menuToggle.querySelector('i');
                        if (icon) {
                            if (navLinks.classList.contains('active')) {
                                icon.classList.remove('fa-bars');
                                icon.classList.add('fa-times');
                                navLinks.setAttribute('data-menu-state', 'open');
                                navLinks.style.display = 'flex';
                            } else {
                                icon.classList.remove('fa-times');
                                icon.classList.add('fa-bars');
                                navLinks.setAttribute('data-menu-state', 'closed');
                                navLinks.style.display = 'none';
                            }
                        }
                    });
                    
                    // Handle dropdown toggles
                    navLinks.querySelectorAll('.nav-dropdown-toggle').forEach(function(toggle) {
                        toggle.addEventListener('click', function(e) {
                            // Only work on desktop
                            if (window.innerWidth <= 968) {
                                return; // On mobile, let it expand naturally
                            }
                            
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            
                            const dropdown = toggle.closest('.nav-dropdown');
                            if (!dropdown) {
                                return;
                            }
                            
                            const isActive = dropdown.classList.contains('active');
                            
                            // Close all dropdowns
                            navLinks.querySelectorAll('.nav-dropdown').forEach(function(dd) {
                                dd.classList.remove('active');
                            });
                            
                            // Toggle this dropdown if it wasn't active
                            if (!isActive) {
                                dropdown.classList.add('active');
                            }
                        });
                    });
                    
                    // Close dropdowns when clicking outside
                    // Use setTimeout to ensure toggle handler runs first
                    document.addEventListener('click', function(e) {
                        if (window.innerWidth > 968) {
                            // Delay to allow toggle handler to run first
                            setTimeout(function() {
                                // Don't close if clicking on the toggle button itself
                                if (e.target.closest('.nav-dropdown-toggle')) {
                                    return;
                                }
                                // Don't close if clicking inside the dropdown menu
                                if (e.target.closest('.nav-dropdown-menu')) {
                                    return;
                                }
                                // Close all dropdowns if clicking outside
                                if (!e.target.closest('.nav-dropdown')) {
                                    navLinks.querySelectorAll('.nav-dropdown').forEach(function(dd) {
                                        dd.classList.remove('active');
                                    });
                                }
                            }, 10);
                        }
                    });
                    
                    // Close menu when clicking on a link
                    navLinks.querySelectorAll('a').forEach(function(link) {
                        link.addEventListener('click', function() {
                            if (window.innerWidth <= 968) {
                                navLinks.classList.remove('active');
                                navLinks.setAttribute('data-menu-state', 'closed');
                                navLinks.style.display = 'none';
                                const icon = menuToggle.querySelector('i');
                                if (icon) {
                                    icon.classList.remove('fa-times');
                                    icon.classList.add('fa-bars');
                                }
                            }
                            // Close dropdowns on desktop when clicking a link
                            if (window.innerWidth > 968) {
                                navLinks.querySelectorAll('.nav-dropdown').forEach(function(dd) {
                                    dd.classList.remove('active');
                                });
                            }
                        });
                    });
                }
            });
        })();
    </script>
</head>
<body>
    <!-- Skip to main content link for keyboard navigation -->
    <a href="#main-content" class="skip-link" style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;">Skip to main content</a>
    <style>
        .skip-link:focus {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 10000;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 0;
            font-weight: 600;
            width: auto;
            height: auto;
            overflow: visible;
        }
    </style>
    <header>
        <nav>
            <div class="container">
                <a href="<?php echo url('index.php'); ?>" class="logo"><?php echo APP_NAME; ?></a>
                <button class="mobile-menu-toggle" aria-label="Toggle menu" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <?php 
                $currentPage = basename($_SERVER['PHP_SELF']);
                $isActive = function($page) use ($currentPage) {
                    return $currentPage === $page ? 'active' : '';
                };
                ?>
                <?php if (Auth::isLoggedIn()): 
                ?>
                    <div class="nav-links" data-menu-state="closed">
                        <a href="<?php echo url('index.php'); ?>" class="<?php echo $isActive('index.php'); ?>">Home</a>
                        <?php if (!RBAC::isSuperAdmin()): ?>
                            <a href="<?php echo url('id-card.php'); ?>" class="<?php echo $isActive('id-card.php'); ?>">My ID Card</a>
                        <?php endif; ?>
                        <?php if (RBAC::isOrganisationAdmin()): ?>
                            <?php
                            // Check if any admin page is active for dropdown highlighting
                            $isAdminPageActive = (strpos($currentPage, 'employees.php') !== false || 
                                                 strpos($currentPage, 'organisational-structure') !== false || 
                                                 strpos($currentPage, 'photo-approvals.php') !== false || 
                                                 strpos($currentPage, 'reference-settings.php') !== false ||
                                                 strpos($currentPage, 'entra-settings.php') !== false ||
                                                 strpos($currentPage, 'verification-logs.php') !== false);
                            
                            // Get notification counts
                            $employeesBadgeCount = 0;
                            $photosBadgeCount = 0;
                            try {
                                if (Auth::isLoggedIn() && RBAC::isOrganisationAdmin()) {
                                    require_once SRC_PATH . '/classes/AdminNotifications.php';
                                    $orgId = Auth::getOrganisationId();
                                    if ($orgId) {
                                        // Employees badge
                                        $usersNeedingNumbers = AdminNotifications::getUsersNeedingEmployeeNumbers($orgId);
                                        $employeesBadgeCount = count($usersNeedingNumbers);
                                        
                                        // Photos badge
                                        $db = getDbConnection();
                                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE organisation_id = ? AND photo_approval_status = 'pending' AND photo_pending_path IS NOT NULL");
                                        $stmt->execute([$orgId]);
                                        $pending = $stmt->fetch();
                                        if ($pending && $pending['count'] > 0) {
                                            $photosBadgeCount = $pending['count'];
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                // Silently fail if database isn't available
                            }
                            ?>
                            <div class="nav-dropdown">
                                <a href="#" class="nav-dropdown-toggle <?php echo $isAdminPageActive ? 'active' : ''; ?>">
                                    Organisation <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="nav-dropdown-menu">
                                    <a href="<?php echo url('admin/employees.php'); ?>" class="<?php echo (strpos($currentPage, 'employees.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-users"></i> Employees
                                        <?php if ($employeesBadgeCount > 0): ?>
                                            <span class="nav-badge"><?php echo $employeesBadgeCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="<?php echo (strpos($currentPage, 'organisational-structure') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-sitemap"></i> Structure
                                    </a>
                                    <a href="<?php echo url('admin/photo-approvals.php'); ?>" class="<?php echo (strpos($currentPage, 'photo-approvals.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-images"></i> Photos
                                        <?php if ($photosBadgeCount > 0): ?>
                                            <span class="nav-badge"><?php echo $photosBadgeCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <a href="<?php echo url('admin/reference-settings.php'); ?>" class="<?php echo (strpos($currentPage, 'reference-settings.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                    <a href="<?php echo url('admin/entra-settings.php'); ?>" class="<?php echo (strpos($currentPage, 'entra-settings.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-microsoft"></i> Microsoft 365 SSO
                                    </a>
                                    <a href="<?php echo url('admin/verification-logs.php'); ?>" class="<?php echo (strpos($currentPage, 'verification-logs.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-clipboard-list"></i> Verification Logs
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (RBAC::isSuperAdmin()): ?>
                            <div class="nav-dropdown">
                                <a href="#" class="nav-dropdown-toggle <?php echo (strpos($currentPage, 'organisations.php') !== false || strpos($currentPage, 'users.php') !== false) ? 'active' : ''; ?>">
                                    System <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="nav-dropdown-menu">
                                    <a href="<?php echo url('admin/organisations.php'); ?>" class="<?php echo (strpos($currentPage, 'organisations.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-building"></i> Organisations
                                    </a>
                                    <a href="<?php echo url('admin/users.php'); ?>" class="<?php echo (strpos($currentPage, 'users.php') !== false) ? 'active' : ''; ?>">
                                        <i class="fas fa-user-friends"></i> Users
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo url('logout.php'); ?>">Logout</a>
                    </div>
                <?php else: ?>
                    <nav id="nav-links" class="nav-links" data-menu-state="closed" role="navigation" aria-label="Main navigation">
                        <a href="<?php echo url('index.php'); ?>" class="<?php echo $isActive('index.php'); ?>">Home</a>
                        <a href="<?php echo url('features.php'); ?>" class="<?php echo $isActive('features.php'); ?>">Features</a>
                        <a href="<?php echo url('security.php'); ?>" class="<?php echo $isActive('security.php'); ?>">Security</a>
                        <a href="<?php echo url('docs.php'); ?>" class="<?php echo $isActive('docs.php'); ?>">Documentation</a>
                        <a href="<?php echo url('request-access.php'); ?>" class="<?php echo $isActive('request-access.php'); ?>">Request Access</a>
                        <a href="<?php echo url('login.php'); ?>" class="<?php echo $isActive('login.php'); ?>">Login</a>
                    </nav>
                <?php endif; ?>
            </div>
        </nav>
            </header>
            
            <!-- PWA Install Prompt - Available on all pages -->
            <?php include __DIR__ . '/../public/pwa-install-prompt.php'; ?>
            
            <main id="main-content" class="container">

