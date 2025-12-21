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
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    
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
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <a href="<?php echo url('index.php'); ?>" class="logo"><?php echo APP_NAME; ?></a>
                <?php 
                $currentPage = basename($_SERVER['PHP_SELF']);
                $isActive = function($page) use ($currentPage) {
                    return $currentPage === $page ? 'active' : '';
                };
                ?>
                <?php if (Auth::isLoggedIn()): 
                ?>
                    <div class="nav-links">
                        <a href="<?php echo url('index.php'); ?>" class="<?php echo $isActive('index.php'); ?>">Home</a>
                        <a href="<?php echo url('id-card.php'); ?>" class="<?php echo $isActive('id-card.php'); ?>">My ID Card</a>
                        <?php if (RBAC::isAdmin()): ?>
                            <a href="<?php echo url('admin/employees.php'); ?>" class="<?php echo (strpos($currentPage, 'employees.php') !== false) ? 'active' : ''; ?>">Employees</a>
                            <a href="<?php echo url('admin/organisational-structure.php'); ?>" class="<?php echo (strpos($currentPage, 'organisational-structure') !== false) ? 'active' : ''; ?>">Structure</a>
                        <?php endif; ?>
                        <?php if (RBAC::isSuperAdmin()): ?>
                            <a href="<?php echo url('admin/organisations.php'); ?>" class="<?php echo (strpos($currentPage, 'organisations.php') !== false) ? 'active' : ''; ?>">Organisations</a>
                            <a href="<?php echo url('admin/users.php'); ?>" class="<?php echo (strpos($currentPage, 'users.php') !== false) ? 'active' : ''; ?>">Users</a>
                        <?php endif; ?>
                        <a href="<?php echo url('logout.php'); ?>">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="nav-links">
                        <a href="<?php echo url('index.php'); ?>" class="<?php echo $isActive('index.php'); ?>">Home</a>
                        <a href="<?php echo url('features.php'); ?>" class="<?php echo $isActive('features.php'); ?>">Features</a>
                        <a href="<?php echo url('security.php'); ?>" class="<?php echo $isActive('security.php'); ?>">Security</a>
                        <a href="<?php echo url('docs.php'); ?>" class="<?php echo $isActive('docs.php'); ?>">Documentation</a>
                        <a href="<?php echo url('login.php'); ?>" class="<?php echo $isActive('login.php'); ?>">Login</a>
                        <a href="<?php echo url('register.php'); ?>" class="<?php echo $isActive('register.php'); ?>">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="container">

